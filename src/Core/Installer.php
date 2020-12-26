<?php

declare(strict_types=1);

namespace Windy\Hydra\Core;

use RuntimeException;
use Symfony\Component\Process\Process;
use Windy\Hydra\Utils\FileUtils;
use function array_merge;
use function array_replace_recursive;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function json_decode;
use function json_encode;
use function mkdir;
use function sprintf;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class Installer
{
    public const STARTING = 'starting';
    public const CREATING = 'creating project';
    public const PATCHING = 'patching project';
    public const UPDATING = 'updating dependencies';
    public const READY    = 'ready';
    public const SPINNER  = ['-', '\\', '|', '/'];

    private $bench;
    private $state = self::STARTING;
    private $create;
    private $update;
    private $spinnerTicks = 0;

    public function __construct(Bench $bench)
    {
        $this->bench  = $bench;
        $this->create = new Process([
            Config::get('composer', 'composer'),
            'create-project',
            "laravel/{$this->bench->getFramework()}",
            $this->bench->getDestination(),
        ]);
        $this->update = new Process([
            Config::get('composer', 'composer'),
            'update',
            "--working-dir={$this->bench->getDestination()}",
        ]);
    }

    /**
     * Tick the installer state machine.
     *
     * @return string The installer state.
     */
    public function tick(): string
    {
        switch ($this->state) {
            case self::STARTING:
                $this->state = self::CREATING;
                $this->setup();
                break;
            case self::CREATING:
                if (!$this->create->isStarted()) {
                    $this->create->start();
                }
                if (!$this->create->isRunning()) {
                    $this->state = self::PATCHING;
                }
                break;
            case self::PATCHING:
                $this->patch();
                $this->state = self::UPDATING;
                break;
            case self::UPDATING:
                if (!$this->update->isStarted()) {
                    $this->update->start();
                }
                if (!$this->update->isRunning()) {
                    $this->state = self::READY;
                }
                break;
        }

        return $this->__toString();
    }

    private function setup(): void
    {
        if (is_dir($this->bench->getDestination())) {
            FileUtils::rrmdir($this->bench->getDestination());
        }

        mkdir($this->bench->getDestination(), 0755, true);

        if (!is_dir($this->bench->getDestination())) {
            throw new RuntimeException(
                "Unable to create \"{$this->bench->getDestination()}\" directory"
            );
        }
    }

    private function patch(): void
    {
        $benchComposerFile   = FileUtils::join($this->bench->getDestination(), 'composer.json');
        $benchComposerData   = json_decode(file_get_contents($benchComposerFile), true);
        $packageDir          = Config::get('hydra$path');
        $packageComposerFile = FileUtils::join($packageDir, 'composer.json');

        if (!file_exists($packageComposerFile)) {
            throw new RuntimeException(
                "Unable to find the package composer.json file (tried {$packageComposerFile})"
            );
        }

        $packageComposerData = json_decode(file_get_contents($packageComposerFile), true);

        $merge = [
            'repositories' => [
                [
                    'type' => 'path',
                    'url'  => $packageDir,
                ],
            ],
            'require'      => array_merge([
                $packageComposerData['name'] => $packageComposerData['version'],
            ], $packageComposerData['require']),
            'require-dev'  => $packageComposerData['require-dev'],
        ];

        $out = array_replace_recursive($benchComposerData, $merge);

        file_put_contents(
            $benchComposerFile,
            json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function __toString(): string
    {
        $spinner            = self::SPINNER[$this->spinnerTicks];
        $this->spinnerTicks = ($this->spinnerTicks + 1) % count(self::SPINNER);

        return sprintf('%s %s %s', $spinner, $this->bench->getName(), $this->state);
    }
}
