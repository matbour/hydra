<?php

namespace Windy\Hydra\Core;

use RuntimeException;
use Windy\Hydra\Utils\FileUtils;

class Bench
{
    private $name;
    private $framework;
    private $constraint;
    private $destination;

    public function __construct(string $name, string $framework, string $constraint)
    {
        $this->name       = $name;
        $this->framework  = $framework;
        $this->constraint = $constraint;

        $config    = Config::getInstance();
        $directory = $config->get('directory');

        if (!FileUtils::isPathAbsolute($directory)) {
            $directory = $config->get('hydra$path') . DIRECTORY_SEPARATOR . $directory;
        }

        $this->destination = $directory . DIRECTORY_SEPARATOR . $name;
    }

    public static function fromName(string $name = null): self
    {
        if (!$name) {
            $name = Config::getInstance()->get('benches.default');
        }

        $benches = Config::getInstance()->get("benches");

        if (!array_key_exists($name, $benches)) {
            throw new RuntimeException("Bench \"{$name}\" does not exist");
        }

        $params = $benches[$name];

        return new Bench($name, $params['framework'], $params['constraint']);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFramework(): string
    {
        return $this->framework;
    }

    /**
     * @return string
     */
    public function getConstraint(): string
    {
        return $this->constraint;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    public function __toString(): string
    {
        return "{$this->name}: ({$this->framework}:{$this->constraint})";
    }
}
