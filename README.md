# Hydra

Simple and clean testing for Laravel and Lumen packages.

## Requirements
- php 7.2+
- composer command in your path

## Configuration

Hydra configuration must be written at the base of your project.
The recognized file names are the follwing:

- hydra.yaml
- hydra.yml
- hydra.json
- hydra.php
- .hydra.yaml
- .hydra.yml
- .hydra.json
- .hydra.php

### `directory` (defaults to ./hydra)
The hydra base directory, where the benches will be installed.

### `benches`
A bench is described by a name (its key) and the framework (`laravel` or `lumen`) and the framework version constraint.
For example, the following configuration:

```yaml
benches:
  laravel-7:
    framework: laravel
    constraint: ^7.0
```

will create a bench `laravel-7` using the `laravel/laravel` framework and the SemVer constraint `^7.0`.

## Usage

### Installing benches

To install the benches defined in the configuration file, run:

```shell
vendor/bin/hydra install
```

You may use the `--only [bench]` to install only a single bench (during matrix pipelines).

### In PHPUnit tests

#### Setup PHPUnit
Since Hydra will basically hijack composer autoloader, you will need to replace the PHPUnit bootstrap file.
During installation, Hydra generates a new bootstrap file which has to be specified in PHPUnit configuration.

For example, in a phpunit.xml file:

```xml
<phpunit
  bootstrap="hydra/bootstrap.php">
    <!-- Your other configurations -->
</phpunit>
```

#### Choose the test bench
The test bench selection is achieved through the `HYDRA_BENCH` environment variable.

You can run phpunit like this:

```shell
HYDRA_BENCH=laravel-7 phpunit [args]
```
