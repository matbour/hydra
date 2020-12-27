<?php

declare(strict_types=1);

namespace Windy\Hydra\Core;

use function array_reduce;
use function explode;

/**
 * @method static mixed get(string $name, $default = null)
 */
class Config
{
    /**
     * @var mixed[] $data The raw configuration.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    private $data = [];
    /**
     * @var Config $instance The current loaded instance.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    private static $instance;

    /**
     * @param string  $name The method to call.
     * @param mixed[] $args The method arguments.
     *
     * @return mixed The method result.
     */
    public static function __callStatic(string $name, array $args)
    {
        if (!self::$instance) {
            $loader               = new ConfigLoader();
            self::$instance       = new self();
            self::$instance->data = $loader->load();
        }

        return self::$instance->{"{$name}Internal"}(...$args);
    }

    /**
     * @param string $path    The configuration key.
     * @param mixed  $default The configuration default value.
     *
     * @return scalar|mixed[] The configuration value.
     *
     * @noinspection  PhpUnusedPrivateMethodInspection
     * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedMethod
     */
    private function getInternal(string $path, $default = null)
    {
        if (!$path) {
            return $this->data;
        }

        return array_reduce(
                explode('.', $path),
                static function ($value, string $path) {
                    return $value[$path] ?? null;
                },
                $this->data
            ) ?? $default;
    }
}
