<?php

namespace Windy\Hydra\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application as LaravelApplication;
use Illuminate\Support\Facades\Facade;
use Laravel\Lumen\Application as LumenApplication;
use PHPUnit\Framework\TestCase;
use Windy\Hydra\Core\Bench;

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

    public function createApplication()
    {
        $bench = Bench::fromName(getenv('HYDRA_BENCH'));
        $file  = $bench->getDestination() . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
        $app   = require $file;

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

    public function refreshApplication()
    {
        $this->app = $this->createApplication();
    }

    public function isLaravel()
    {
        return !$this->isLumen();
    }

    public function isLumen()
    {
        return strpos($this->app->version(), 'Lumen') !== false;
    }

    protected function setUpConfig()
    {
        return [];
    }

    protected function setUpProviders()
    {
        return [];
    }
}
