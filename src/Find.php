<?php

namespace sndsgd;

use \DirectoryIterator as DI;
use \InvalidArgumentException;
use \RecursiveDirectoryIterator as RDI;
use \RecursiveIteratorIterator as RII;
use \sndsgd\Dir;
use \sndsgd\Path;


/**
 * A collection of methods for finding files and directories
 */
class Find
{
   const RECURSIVE = 1;

   /**
    * A convience method to create a filesystem iterator
    * 
    * @param string $dir An absolute path to the directory to search
    * @param integer $options A bitmask of iterator options
    * @return DirectoryIterator|RecursiveIteratorIterator
    */
   public static function getIterator($dir, $options = 0)
   {
      if (is_string($dir) === false) {
         throw new InvalidArgumentException(
            "invalid value provided for 'dir'; ".
            "expecting an absolute directory path as string"
         );
      }
      else if (($test = Dir::isReadable($dir)) !== true) {
         throw new InvalidArgumentException(
            "invalid value provided for 'dir'; $test"
         );
      }
      else if (!is_int($options)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'options'; ".
            "expecting an integer"
         );
      }

      return self::createIterator(Path::normalize($dir), $options);
   }

   /**
    * Create a filesystem iterator
    * 
    * @param string $dir An absolute path to the directory to search
    * @param integer $options A bitmask of iterator options
    * @return DirectoryIterator|RecursiveIteratorIterator
    */
   private static function createIterator($dir, $options)
   {
      return ($options & self::RECURSIVE)
         ? new RII(new RDI($dir, RDI::SKIP_DOTS), RII::SELF_FIRST)
         : new DI($dir);
   }

   /**
    * Find directories
    * 
    * @param string $dir An absolute path to the directory to search
    * @param integer $options Iterator options
    * @return array.<string>
    */
   public static function directories($dir, $options = 0)
   {
      $ret = [];
      $iterator = self::getIterator($dir, $options);
      foreach ($iterator as $file) {
         if (
            $iterator->isDot() === false &&
            $file->isDir() &&
            ($path = $file->getRealPath()) &&
            !array_key_exists($path, $ret)
         ) {
            $ret[$path] = 1;
         }
      }
      return array_keys($ret);
   }

   /**
    * Find empty directories
    * 
    * @param string $dir An absolute path to the directory to search
    * @param integer $options Iterator options
    * @return array.<string>
    */
   public static function emptyDirectories($dir, $options = 0)
   {
      $ret = [];
      $iterator = self::getIterator($dir, $options);
      foreach ($iterator as $file) {
         if (
            $iterator->isDot() === false &&
            $file->isDir() &&
            ($path = $file->getRealPath()) &&
            !array_key_exists($path, $ret) &&
            Dir::isEmpty($path)
         ) {
            $ret[$path] = 1;
         }
      }
      return array_keys($ret);
   }

   /**
    * Find files with a given extension
    * 
    * @param string $dir An absolute path to the directory to search
    * @param string $ext The extension to match (case insensitive)
    * @param integer $options Iterator options
    * @return array.<string>
    */
   public static function filesByExtension($dir, $ext, $options = 0)
   {
      $ret = [];
      $iterator = self::getIterator($dir, $options);
      foreach ($iterator as $file) {
         if (
            $file->isFile() &&
            strcasecmp($ext, $file->getExtension()) === 0 &&
            ($path = $file->getRealPath()) &&
            array_key_exists($path, $ret) === false
         ) {
            $ret[$path] = 1;
         }
      }
      return array_keys($ret);
   }

   /**
    * Find broken symbolic links
    * 
    * @param string $dir An absolute path to the directory to search
    * @param integer $options Iterator options
    * @return array.<string>
    */
   public static function brokenLinks($dir, $options = 0)
   {
      $ret = [];
      $iterator = self::getIterator($dir, $options);
      foreach ($iterator as $file) {
         if ($file->isLink() && $file->getRealPath() === false) {
            $link = $file->getPath().DIRECTORY_SEPARATOR.$file->getFilename();
            $ret[$link] = 1;
         }
      }
      return array_keys($ret);
   }
}

