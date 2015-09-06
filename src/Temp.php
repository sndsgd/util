<?php

namespace sndsgd;

use InvalidArgumentException;


/**
 * Temp file and directory utility methods
 */
class Temp
{
    /**
     * The directory to use as the temp directory
     *
     * @var string|null
     */
    private static $dir = null;

    /**
     * All created paths are added here for easy removal at script exit
     *
     * @var array<string,boolean|null>
     */
    private static $files = [];

    /**
     * Set the root temp directory (overrides use of the system temp directory)
     *
     * @param string|null $path
     * @return void
     * @throws InvalidArgumentException If the provided path isn't usable
     */
    public static function setDir($path)
    {
        if ($path === null) {
            self::$dir = null;
            return;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                "invalid value provided for 'path'; ".
                "expecting an absolute directory path as string"
            );
        }
        else if (($test = Path::test($path, Dir::READABLE_WRITABLE)) !== true) {
            throw new InvalidArgumentException(
                "invalid value provided for 'path'; $test"
            );
        }

        self::$dir = $path;
    }

    /**
     * Get the root temp directory
     *
     * @return string
     */
    public static function getDir()
    {
        return (self::$dir !== null) ? self::$dir : sys_get_temp_dir();
    }

    /**
     * Register a temp path to be deleted when the script exists
     *
     * @param string $path An absolute path
     * @param boolean|null $isDir Whether or not $path is a directory
     */
    public static function registerPath($path, $isDir = null)
    {
        if (count(self::$files) === 0) {
            register_shutdown_function("sndsgd\\Temp::cleanup");
        }
        self::$files[$path] = $isDir;
    }

    /**
     * Deregister a path from the files/dirs to remove when the script exits
     *
     * @param string $path The path to remove
     * @param boolean $remove Whether or not to remove the file/directory
     * @return boolean Whether or not the path was deregistered
     */
    public static function deregisterPath($path, $remove = false)
    {
        if (array_key_exists($path, self::$files)) {
            $isDir = self::$files[$path];
            unset(self::$files[$path]);
            if ($remove === true) {
                return self::removePath($path, $isDir);
            }
            return true;
        }
        return false;
    }

    /**
     * Create a temp file
     *
     * @param string $name A name for the file name
     * @param string|null $contents Optional contents for the file
     * @return string The path to the newly created temp file
     */
    public static function file($name = "temp", $contents = null)
    {
        $tmpdir = self::getDir();
        $name = File::sanitizeName($name);
        list($name, $ext) = File::splitName($name, "");
        if ($ext !== "") {
            $ext = ".{$ext}";
        }

        do {
            $rand = substr(md5(microtime(true).mt_rand()), 10, 10);
            $path = $tmpdir.DIRECTORY_SEPARATOR."{$name}-{$rand}{$ext}";
        }
        while (file_exists($path));
        touch($path);
        if ($contents) {
            file_put_contents($path, $contents);
        }
        self::registerPath($path, false);
        return $path;
    }

    /**
     * Create a temp directory
     *
     * @param string $prefix A prefix for the directory name
     * @param octal $mode The permissions for the directory
     * @return string The path to the newly created temp directory
     */
    public static function dir($prefix = "temp", $mode = 0775)
    {
        $tmpdir = self::getDir();
        $prefix = Dir::sanitizeName($prefix);
        do {
            $rand = substr(md5(microtime(true)), 0, 6);
            $path = $tmpdir.DIRECTORY_SEPARATOR.$prefix.$rand;
        }
        while (@mkdir($path, $mode) === false);

        self::registerPath($path, true);
        return $path;
    }

    /**
     * Remove all temp files & directories created since script start
     *
     * @return boolean
     */
    public static function cleanup()
    {
        $ret = true;
        foreach (self::$files as $path => $isDir) {
            if (self::removePath($path, $isDir) === false) {
                $ret = false;
            }
        }
        return $ret;
    }

    /**
     * @param string $path Absolute filesystem path to remove
     * @param boolean|null
     * @return boolean True if the path no longer exists, false if it does
     */
    private static function removePath($path, $isDir = null)
    {
        if (file_exists($path)) {
            $isDir = ($isDir === null) ? is_dir($path) : $isDir;
            $result = ($isDir === true) ? Dir::remove($path) : @unlink($path);
            return $result === true;
        }
        return true;
    }
}
