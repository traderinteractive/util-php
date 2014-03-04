<?php
/**
 * Defines the \DominionEnterprises\Util\File class.
 */

namespace DominionEnterprises\Util;
use DominionEnterprises\Util;

/**
 * Class of static file utility functions.
 */
final class File
{
    /**
     * Recursively deletes directory contents
     *
     * @param string $directoryPath absolute path of directory
     *
     * @return void
     *
     * @throws \InvalidArgumentException if $directoryPath is not a string
     * @throws \Exception if file cannot be deleted
     * @throws \Exception if directory cannot be deleted
     * @throws \Exception if $directoryPath cannot be listed
     */
    public static function deleteDirectoryContents($directoryPath)
    {
        if (!is_string($directoryPath)) {
            throw new \InvalidArgumentException('$directoryPath is not a string');
        }

        $paths = scandir($directoryPath);
        if ($paths === false) {
            throw new \Exception("cannot list directory '{$directoryPath}'");
        }

        foreach ($paths as $path) {
            if ($path === '.' || $path === '..') {
                continue;
            }

            $fullPath = "{$directoryPath}/{$path}";

            if (is_dir($fullPath)) {
                self::deleteDirectoryContents($fullPath);//RECURSIVE CALL
                if (!rmdir($fullPath)) {
                    throw new \Exception("cannot delete '{$fullPath}'", 1);
                }
            } else {
                if (!unlink($fullPath)) {
                    throw new \Exception("cannot delete '{$fullPath}'", 2);
                }
            }
        }
    }

    /**
     * Deletes the given file specified by $path
     *
     * @param string $filename path to the file to be deleted
     *
     * @return void
     *
     * @throws \InvalidArgumentException if $path is not a string or is whitespace
     * throws \Exception if unlink returns false
     */
    public static function delete($path)
    {
        if (!is_string($path) || trim($path) === '') {
            throw new \InvalidArgumentException('$path is not a string or is whitespace');
        }

        if (!file_exists($path)) {
            return;
        }

        try {
            Util::ensure(true, unlink($path), "unlink returned false for '{$path}'");
        } catch (\Exception $e) {
            if (file_exists($path)) {
                throw $e;
            }
        }
    }
}
