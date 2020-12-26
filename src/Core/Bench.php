<?php

declare(strict_types=1);

namespace Windy\Hydra\Core;

use RuntimeException;
use function array_key_exists;
use const DIRECTORY_SEPARATOR;

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

        $directory         = Config::get('sandbox');
        $this->destination = $directory . DIRECTORY_SEPARATOR . $name;
    }

    public static function fromName(?string $name = null): self
    {
        if (!$name) {
            $name = Config::get('benches.default');
        }

        $benches = Config::get('benches');

        if (!array_key_exists($name, $benches)) {
            throw new RuntimeException("Bench \"{$name}\" does not exist");
        }

        $params = $benches[$name];

        return new Bench($name, $params['framework'], $params['constraint']);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFramework(): string
    {
        return $this->framework;
    }

    public function getConstraint(): string
    {
        return $this->constraint;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function __toString(): string
    {
        return "{$this->name}: ({$this->framework}:{$this->constraint})";
    }
}
