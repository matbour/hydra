<?php

declare(strict_types=1);

namespace Windy\Hydra\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application as LaravelApplication;
use Illuminate\Support\Facades\Facade;
use Laravel\Lumen\Application as LumenApplication;
use PHPUnit\Framework\TestCase;
use Windy\Hydra\Core\Bench;
use function getenv;
use function strpos;
use const DIRECTORY_SEPARATOR;

/**
 * Base test class for Laravel and Lumen applications.
 */
class HydraTestCase extends TestCase
{
    /** @var LaravelApplication|LumenApplication */
    protected $app;

    public function setUp(): void
    {
        Facade::clearResolvedInstances();

        if (!$this->app) {
            $this->refreshApplication();
        }

        parent::setUp();
    }

    /**
     * @return LaravelApplication|LumenApplication The application.
     */
    public function createApplication()
    {
        $bench = Bench::fromName(getenv('HYDRA_BENCH'));
        $file  = $bench->getDestination() . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
        /** @noinspection PhpIncludeInspection */
        $app = require $file;

        if (strpos($app->version(), 'Lumen') === false) {
            // Laravel
            $app->make(Kernel::class)->bootstrap();
        }

        foreach ($this->setUpConfig() as $root => $value) {
            $app['config']->set($root, $value);
        }

        foreach ($this->setUpProviders() as $provider) {
            $app->register($provider);
        }

        return $app;
    }

    public function refreshApplication(): void
    {
        $this->app = $this->createApplication();
    }

    /**
     * @param LaravelApplication|LumenApplication|null $app The application to test. If null, use the
     *                                                      {@see HydraTestCase::$app} instead.
     *
     * @return bool If the application is a {@see LaravelApplication} instance.
     */
    protected function isLaravel($app = null): bool
    {
        return !$this->isLumen($app);
    }

    /**
     * Check if the application is a Lumen instance.
     *
     * @param LaravelApplication|LumenApplication|null $app The application to test. If null, use the
     *                                                      {@see HydraTestCase::$app} instead.
     *
     * @return bool If the application is a {@see LumenApplication} instance.
     */
    protected function isLumen($app = null): bool
    {
        $version = $app ? $app->version() : $this->app->version();

        return strpos($version, 'Lumen') !== false;
    }

    /**
     * @return mixed[] Your package configuration for the test.
     */
    protected function setUpConfig(): array
    {
        return [];
    }

    /**
     * @return string[] Your package provider classes.
     */
    protected function setUpProviders(): array
    {
        return [];
    }
}
