<?php

declare(strict_types=1);

namespace Windy\Hydra\Utils;

use InvalidArgumentException;
use LogicException;
use function array_map;
use function ctype_print;
use function implode;
use function is_dir;
use function is_link;
use function preg_match;
use function preg_replace;
use function rmdir;
use function rtrim;
use function scandir;
use function unlink;
use const DIRECTORY_SEPARATOR;

class FileUtils
{
    /**
     * Check if a path is absolute
     *
     * @see https://stackoverflow.com/a/38022806 (original source)
     *
     * @param string $path The path to check.
     *
     * @return bool If the path is absolute
     */
    public static function isAbsolute(string $path): bool
    {
        if (!ctype_print($path)) {
            throw new InvalidArgumentException(
                "Path can NOT have non-printable characters or be empty"
            );
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

        return $parts['root'] !== '';
    }

    /**
     * Normalize a path (same behavior as {@see realpath}, but without the existence required).
     *
     * @see https://stackoverflow.com/a/20545583
     *
     * @param string $path      The path to normalize.
     * @param string $separator The directory separators.
     *
     * @return string The normalized path.
     */
    public static function normalize(string $path, string $separator = '\\/'): string
    {
        // Remove any kind of funky unicode whitespace
        $normalized = preg_replace('#\p{C}+|^\./#u', '', $path);

        // Path remove self referring paths ("/./").
        $normalized = preg_replace('#/\.(?=/)|^\./|\./$#', '', $normalized);

        // Regex for resolving relative paths
        $regex = '#\/*[^/\.]+/\.\.#Uu';

        while (preg_match($regex, $normalized)) {
            $normalized = preg_replace($regex, '', $normalized);
        }

        if (preg_match('#/\.{2}|\.{2}/#', $normalized)) {
            throw new LogicException(
                "Path is outside of the defined root, path: [{$path}], resolved: [{$normalized}]"
            );
        }

        return rtrim($normalized, $separator);
    }

    /**
     * @param string ...$parts The path parts to join.
     *
     * @return string The joined path.
     */
    public static function join(string ...$parts): string
    {
        return implode(DIRECTORY_SEPARATOR, array_map(static function (string $part) {
            return self::normalize($part);
        }, $parts));
    }

    /**
     * Remove a directory recursively.
     *
     * @see https://stackoverflow.com/a/3352564
     *
     * @param string $dir The directory to remove recursively.
     */
    public static function rrmdir(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== "." && $object !== "..") {
                    if (
                        is_dir($dir . DIRECTORY_SEPARATOR . $object)
                        && !is_link($dir . "/" . $object)
                    ) {
                        self::rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }

            rmdir($dir);
        }
    }
}
