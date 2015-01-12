<?php

namespace sndsgd;

use \InvalidArgumentException;


/**
 * Filesystem path ultility methods
 */
class Path
{
   // bitmask values for use in sndsgd\Path::test()
   const EXISTS = 1;
   const IS_FILE = 2;
   const IS_DIR = 4;
   const IS_WRITABLE = 8;
   const IS_READABLE = 16;
   const IS_EXECUTABLE = 32;

   /**
    * Perform multiple tests on a file/dir in one go
    * 
    * @param string $path An absolute file or directory path
    * @param bitmask $flags Tests to perform
    * @return boolean|string
    * @return boolean:true The test was successful
    * @return string A message describing the test that failed
    */
   public static function test($path, $flags)
   {
      if (!is_string($path)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'bytes'; ".
            "expecting an absolute file or directory path as string"
         );
      }

      if ($flags & self::EXISTS && file_exists($path) === false) {
         return "'$path' does not exist";
      }
      else if ($flags & self::IS_FILE && is_file($path) === false) {
         return "'$path' is not a file";
      }
      else if ($flags & self::IS_DIR && is_dir($path) === false) {
         return "'$path' is not a directory";
      }
      else if ($flags & self::IS_WRITABLE && is_writable($path) === false) {
         return "'$path' is not writable";
      }
      else if ($flags & self::IS_READABLE && is_readable($path) === false) {
         return "'$path' is not readable";
      }
      else if ($flags & self::IS_EXECUTABLE && is_executable($path) === false) {
         return "'$path' is not executable";
      }
      else {
         return true;
      }
   }

   /**
    * Normalize a filesystem path
    * 
    * @param string $path 
    * @return string
    */
   public static function normalize($path)
   {
      if ($path{0} === '.') {
         if ($path === '.' || $path === './') {
            return getcwd();
         }
         else if ($path === '..' || $path === '../') {
            return dirname(getcwd());
         }
         else if ($path{1} === '/') {
            $path = getcwd().substr($path, 1);
         }
         else if ($path{1} === '.') {
            $path = dirname(getcwd()).substr($path, 2);
         }
      }

      $parts = explode('/', $path);
      $abs = ($parts[0] === '');
      $temp = [];
      foreach ($parts as $part) {
         if ($part === '.' || $part === '') {
            continue;
         }
         else if ($part === '..') {
            array_pop($temp);
         }
         else {
            $temp[] = $part;
         }
      }
      $temp = implode('/', $temp);
      return ($abs) ? '/'.$temp : $temp;
   }

   /**
    * Get the relative path from one file/directory to another
    * 
    * @param string $from
    * @param string $to
    * @return string
    */
   public static function relative($from, $to)
   {
      $i = 0;
      $minlen = min(strlen($from), strlen($to));
      while ($i < $minlen && $from{$i} === $to{$i}) {
         $i++;
      }

      $from = substr($from, $i);
      $to = substr($to, $i);
      $fparts = explode('/', $from);
      return str_repeat('../', count($fparts) - 1).$to;
   }
}

