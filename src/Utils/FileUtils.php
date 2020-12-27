<?php

declare(strict_types=1);

namespace Windy\Hydra\Utils;

use LogicException;
use function array_map;
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
