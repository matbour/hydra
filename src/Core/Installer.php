<?php

namespace Windy\Hydra\Core;

use RuntimeException;
use Symfony\Component\Process\Process;
use Windy\Hydra\Utils\FileUtils;

class Installer
{
    private $bench;

    public function __construct(Bench $bench)
    {
        $this->bench = $bench;
    }

    public function install()
    {
        $this->setup();;
        $this->create();
        $this->patch();
        $this->update();
        $this->teardown();
    }

    public function setup()
    {
        if (is_dir($this->bench->getDestination())) {
            FileUtils::rrmdir($this->bench->getDestination());
        }

        mkdir($this->bench->getDestination(), 0755, true);

        if (!is_dir($this->bench->getDestination())) {
            throw new RuntimeException("Unable to create \"{$this->bench->getDestination()}\" directory");
        }
    }

    public function create()
    {
        $process = new Process([
            'composer',
            'create-project',
            "laravel/{$this->bench->getFramework()}",
            $this->bench->getDestination()
        ]);
        $process->setTty(true);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

    }

    public function patch()
    {
        $benchComposerFile   = "{$this->bench->getDestination()}/composer.json";
        $benchComposerData   = json_decode(file_get_contents($benchComposerFile), true);
        $packageDir          = Config::getInstance()->get('hydra$path');
        $packageComposerFile = $packageDir . DIRECTORY_SEPARATOR . 'composer.json';

        if (!file_exists($packageComposerFile)) {
            throw new RuntimeException("Unable to find the package composer.json file (tried {$packageComposerFile})");
        }

        $packageComposerData = json_decode(file_get_contents($packageComposerFile), true);

        $merge = [
            'repositories' => [
                [
                    'type' => 'path',
                    'url'  => $packageDir,
                ],
                [
                    'type' => 'path',
                    'url'  => '/home/mathieu/Documents/mathieu-bour/hydra',
                ]
            ],
            'require'      => array_merge([
                $packageComposerData['name'] => $packageComposerData['version']
            ], $packageComposerData['require']),
            'require-dev'  => $packageComposerData['require-dev']
        ];

        $out = array_replace_recursive($benchComposerData, $merge);

        file_put_contents($benchComposerFile, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function update()
    {
        $process = new Process(['composer', 'update', "--working-dir={$this->bench->getDestination()}"]);
        $process->setTty(true);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
    }

    public function teardown()
    {
    }
}
