<?php

namespace sndsgd\util;

use \InvalidArgumentException;


/**
 * Directory utility methods
 */
class Dir
{
   // bitmask values for use with sndsgd\Path::test()
   // @see sndsgd\Path for sub values
   const READABLE = 21;
   const WRITABLE = 13;
   const READABLE_WRITABLE = 29;

   /**
    * Verify if a dir is readable
    * 
    * @param string $path An absolute path to the dir to test
    * @return boolean|string
    * @return boolean:true The dir is readable
    * @return string A message describing the failure
    */
   public static function isReadable($path)
   {
      return Path::test($path, self::READABLE);
   }

   /**
    * Determine if a directory can be written to
    *
    * if the path does not exist, parent dirs will be analyzed
    * (the directory being tested does not have to exist)
    * @param string $path An absolute path to test
    * @return boolean|string
    * @return boolean:true The directory either exists, or can be created
    * @return string A message describing the failure
    */
   public static function isWritable($path)
   {
      while (file_exists($path) === false) {
         $path = dirname($path);
      }
      return Path::test($path, self::WRITABLE);
   }

   /**
    * Prepare a directory for writing
    * 
    * @param string $path An absolute path to the directory to prepare
    * @param octal $permissions Permissions for newly created directories 
    * @return boolean|string
    * @return boolean:true The directory is ready for writing
    * @return string A message describing the failure
    */
   public static function prepare($path, $permissions = 0775)
   {
      if (file_exists($path)) {
         return Path::test($path, Path::IS_DIR | Path::IS_WRITABLE);
      }
      return @mkdir($path, $permissions, true)
         ? true
         : "failed to create directory '$path'";
   }

   /**
    * Determine if a directory is empty
    * 
    * @param string $path An absolute path to test
    * @return boolean
    * @throws InvalidArgumentException If $path is not a string of does not exist
    */
   public static function isEmpty($path)
   {
      if (!is_string($path)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'path'; ".
            "expecting a string"
         );
      }
      else if (($test = Path::test($path, Dir::READABLE)) !== true) {
         throw new InvalidArgumentException(
            "invalid value provided for 'path'; ".
            "expecting an absolute directory path; $test"
         );
      }
      return (count(scandir($path)) === 2);
   }

   /**
    * Recursively remove a directory
    * 
    * @param string $dir An absolute path to a directory
    * @return boolean|string
    * @return boolean:true The directory was removed
    * @return string An error message describing the failure
    */
   public static function remove($dir)
   { 
      $files = array_diff(scandir($dir), ['.', '..']);
      foreach ($files as $file) {
         $path = $dir.DIRECTORY_SEPARATOR.$file;
         $isDir = is_dir($path);
         $result = ($isDir) ? self::remove($path) : @unlink($path);
         if ($result !== true) {
            return ($isDir) ? $result : "failed to remove file '$path'";
         }
      }
      return (@rmdir($dir) !== true)
         ? "failed to remove directory '$dir'"
         : true;
   }
}

