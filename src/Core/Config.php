<?php

namespace Windy\Hydra\Core;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    const MAX_DEPTH = 4;
    const FILES     = [
        'hydra.yaml',
        'hydra.yml',
        'hydra.json',
        'hydra.php',
        '.hydra.yaml',
        '.hydra.yml',
        '.hydra.json',
        '.hydra.php',
    ];

    private static $instance;
    private $raw = [];

    public function __construct()
    {
        $file = $this->locate();
        $this->parse($file);
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function locate(): string
    {
        $tried = [];


        for ($i = 0; $i <= self::MAX_DEPTH; $i++) {
            $path  = $i === 0 ? getcwd() : dirname(getcwd(), $i);
            $found = $this->checkExtensions($path, $tried);

            if ($found) {
                return $found;
            }
        }

        for ($i = 2; $i <= self::MAX_DEPTH; $i++) {
            $path  = dirname(realpath(__DIR__), $i);
            $found = $this->checkExtensions($path, $tried);

            if ($found) {
                return $found;
            }
        }

        throw new RuntimeException('Unable to find hydra config file. Tried: ' . implode("\n", $tried));
    }

    private function checkExtensions($directory, &$tried)
    {
        foreach (self::FILES as $file) {
            $fullPath = "$directory/$file";

            if (file_exists($fullPath)) {
                return $fullPath;
            } else {
                $tried[] = $fullPath;
            }
        }

        return false;
    }

    /**
     * @noinspection PhpIncludeInspection
     */
    private function parse($file)
    {
        if (preg_match('/\.ya?ml/', $file)) {
            $this->raw = Yaml::parse(file_get_contents($file));
        } elseif (preg_match('/\.json/', $file)) {
            $this->raw = json_decode(file_get_contents($file), true);
        } elseif (preg_match('/\.php/', $file)) {
            $this->raw = require $file;
        } else {
            throw new RuntimeException('Unable to parse configuration file');
        }

        $this->raw['hydra$path'] = dirname($file);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function get(string $path)
    {
        if (!$path) {
            return $this->raw;
        }

        /** @var mixed $data */
        $data  = $this->raw;
        $parts = explode('.', $path);

        foreach ($parts as $part) {
            $data = $data[$part];
        }

        return $data;
    }
}
