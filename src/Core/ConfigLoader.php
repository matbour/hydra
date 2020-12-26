<?php

declare(strict_types=1);

namespace Windy\Hydra\Core;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Windy\Hydra\Utils\FileUtils;
use function dirname;
use function file_exists;
use function file_get_contents;
use function getcwd;
use function json_decode;
use function preg_match;

class ConfigLoader
{
    private const MAX_DEPTH  = 4;
    private const FILES      = [
        'hydra',
        '.hydra',
    ];
    private const EXTENSIONS = [
        '.yaml',
        '.yml',
        '.json',
        '.php',
    ];

    /**
     * @return mixed[] The parsed configuration.
     */
    public function load(): array
    {
        $path = $this->findConfig();
        return $this->parse($path);
    }

    /**
     * Find the Hydra configuration file.
     *
     * @return string The found configuration file absolute path.
     */
    public function findConfig(): string
    {
        /** @var string[] $files The potential configuration file names. */
        $files = [];

        foreach (self::FILES as $file) {
            foreach (self::EXTENSIONS as $extension) {
                $files[] = "{$file}{$extension}";
            }
        }

        // We should start at /vendor/mathieu-bour/hydra
        $start = getcwd();

        for ($i = 0; $i < self::MAX_DEPTH; $i++) {
            $dir = $i > 0 ? dirname($start, $i) : $start;

            foreach ($files as $file) {
                $path = FileUtils::join($dir, $file);

                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        throw new RuntimeException(<<<ERROR
Unable to find hydra config file.
Started from $start.

ERROR
        );
    }

    /**
     * Parse a configuration file.
     *
     * @param string $path The configuration file path.
     *
     * @return mixed[] The configuration file.
     */
    public function parse(string $path): array
    {
        if (preg_match('/\.ya?ml/', $path)) {
            $raw = Yaml::parse(file_get_contents($path));
        } elseif (preg_match('/\.json/', $path)) {
            $raw = json_decode(file_get_contents($path), true);
        } elseif (preg_match('/\.php/', $path)) {
            /** @noinspection PhpIncludeInspection */
            $raw = require $path;
        } else {
            throw new RuntimeException('Unable to parse configuration file');
        }

        $raw['hydra$path'] = dirname($path);
        $raw['sandbox']    = FileUtils::join(
            $raw['hydra$path'],
            $this->raw['sandbox'] ?? './hydra'
        );

        return $raw;
    }
}
