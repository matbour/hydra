<?php

namespace Windy\Hydra\Utils;

use InvalidArgumentException;

class FileUtils
{
    /**
     * Check if a path is absolute
     * @param string $path The path to check.
     * @return bool If the path is absolute
     * @see https://stackoverflow.com/a/38022806 (original source)
     */
    public static function isPathAbsolute(string $path): bool
    {
        if (!ctype_print($path)) {
            throw new InvalidArgumentException("Path can NOT have non-printable characters or be empty");
        }
        // Optional wrapper(s).
        $regExp = '%^(?<wrappers>(?:[[:print:]]{2,}://)*)';

        // Optional root prefix.
        $regExp .= '(?<root>(?:[[:alpha:]]:/|/)?)';

        // Actual path.
        $regExp .= '(?<path>(?:[[:print:]]*))$%';

        $parts = [];

        if (!preg_match($regExp, $path, $parts)) {
            throw new InvalidArgumentException("Path is NOT valid, was given $path");
        }

        if ('' !== $parts['root']) {
            return true;
        }

        return false;
    }

    /**
     * Remove a directory recursively.
     * @param string $dir The directory to remove recursively.
     * @see https://stackoverflow.com/a/3352564
     */
    public static function rrmdir(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                        self::rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            rmdir($dir);
        }
    }
}
