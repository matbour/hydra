# Hydra
Simple and clean testing for Laravel and Lumen packages.
Supports Laravel/Lumen 5.8.x, 7.x and 8.x.

## Stability disclaimer
This package is still un early development stage, and there are many bugs and planned features.
Use it at your own risk.


## Requirements
- php 7.1.3+
- composer command in your path

## Acknowledgements
This package what greatly inspired by the [orchestra/testbench](https://github.com/orchestral/testbench) package.

## Configuration
Hydra configuration must be written at the base of your project.
The recognized file names are the following:

- hydra.yaml
- hydra.yml
- hydra.json
- hydra.php
- .hydra.yaml
- .hydra.yml
- .hydra.json
- .hydra.php

### `sandbox` (defaults to ./hydra)
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

#### Use the `HydraTestCase`
In order to set up tests for your package, you have to use the `Windy\Hydra\Testing\HydraTestCase` class.

##### Define a configuration:

```php
use Windy\Hydra\Testing\HydraTestCase;

class ExampleTest extends HydraTestCase {
    protected function setUpConfig(): array
    {
        return [
            'your-package' => [
                'foo' => 'bar'
            ]
        ];
    }
}
```

##### Define your providers
```php
use Windy\Hydra\Testing\HydraTestCase;

class ExampleTest extends HydraTestCase {
    protected function setUpProviders(): array
    {
        return [
            \Me\MyPackage\MyPackageProvider::class
        ];
    }
}
```
