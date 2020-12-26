<?php

declare(strict_types=1);

/**
 * Hydra bootstrap, which should be used as the PHPUnit's bootstrap file.
 * Do not edit!
 */
$name = getenv('HYDRA_BENCH');

$autoload = __DIR__ . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR
    . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/** @noinspection PhpIncludeInspection */
require $autoload;
