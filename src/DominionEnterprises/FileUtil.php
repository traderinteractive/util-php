<?php
/**
 * Defines the \DominionEnterprises\FileUtil class.
 */

namespace DominionEnterprises;

/**
 * Class of static file utility functions.
 */
final class FileUtil
{
    /**
     * Recursively deletes directory contents
     *
     * @param string $directoryPath absolute path of directory
     *
     * @throws \InvalidArgumentException if $directoryPath is not a string
     * @throws \Exception if file cannot be deleted
     * @throws \Exception if directory cannot be deleted
     * @throws \Exception if $directoryPath cannot be listed
     *
     * @return void
     */
    public static function deleteDirectoryContents($directoryPath)
    {
        Util::throwIfNotType(array('string' => array($directoryPath)));

        $paths = Util::ensureNotFalse(scandir($directoryPath), 'Exception', array("cannot list directory '{$directoryPath}'", 1));
        foreach ($paths as $path) {
            if ($path === '.' || $path === '..') {
                continue;
            }

            $fullPath = "{$directoryPath}/{$path}";

            if (is_dir($fullPath)) {
                self::deleteDirectoryContents($fullPath);//RECURSIVE CALL
                Util::ensureTrue(rmdir($fullPath), 'Exception', array("cannot delete '{$fullPath}'", 2));
            } else {
                Util::ensureTrue(unlink($fullPath), 'Exception', array("cannot delete '{$fullPath}'", 3));
            }
        }
    }

    /**
     * Try to get a temp file with timeout.
     *
     * @param string $dirPath dir to create temp file in, without the trailing slash
     * @param string $prefix prefix to put on temp file
     * @param int $timeout timeout for waiting on a temp file in milliseconds
     * @return string the path to the temp file
     * @throws \Exception if timed out trying to get temp file
     * @throws \Exception if we couldnt create the $dirPath
     */
    public static function createTempFile($dirPath, $prefix, $timeout = 30000)
    {
        Util::throwIfNotType(array('string' => $dirPath), true);
        Util::throwIfNotType(array('string' => $prefix, 'int' => $timeout));

        //mkdir ignoring result and then checking for the directory so this function can be run in parallel
        $mkdirException = null;
        try {
            //catching exception in case the use has an error hander set since mkdir generates warning.
            mkdir($dirPath, 0775, true);
        } catch (\Exception $e) {
            $mkdirException = $e;
        }

        Util::ensureTrue(is_dir($dirPath), 'Exception', array("couldnt create temp directory '{$dirPath}'", 0, $mkdirException));

        $end = TimeUtil::inMillis() + $timeout;
        $tempPath = null;
        while (true) {
            $tempPath = $dirPath . "/{$prefix}" . md5(openssl_random_pseudo_bytes(128));
            $handle = false;
            try {
                //catching exception in case the use has an error hander set since fopen generates warning.
                $handle = fopen($tempPath, 'x');
            } catch (\Exception $e) {
                $handle = false;
            }

            if ($handle !== false) {
                fclose($handle);
                break;
            }

            Util::ensureTrue(TimeUtil::inMillis() < $end, 'timed out trying to get temp file');
            usleep(100000);
        }

        return $tempPath;
    }

    /**
     * Deletes the given file specified by $path
     *
     * @param string $filename path to the file to be deleted
     *
     * @throws \Exception if unlink returns false
     */
    public static function delete($path)
    {
        Util::throwIfNotType(array('string' => array($path)), true);

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
