<?php

namespace sndsgd\util;

use \DirectoryIterator;
use \InvalidArgumentException;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \SeekableIterator;
use \SplFileInfo;
use \sndsgd\util\Dir;
use \sndsgd\util\Path;


class Find
{
   const RECURSIVE = 1;
   const RETURN_KEYS = 2;
   const RETURN_VALUES = 4;
   const CASE_SENSITIVE = 8;

   /**
    * filter a directory's contents
    * @param string $dir - an absolute path to a directory
    * @param callable $callback - a function to test the directory's children
    * @param boolean $bitmask - filter options
    * @return array.<string>
    * @throws InvalidArgumentException if $dir is not a readable directory
    */
   public static function filter($dir, callable $callback, $bitmask = 0)
   {
      if (is_string($dir) === false) {
         $err = "expecting a string value for 'dir'";
         throw new InvalidArgumentException($err);
      }
      else if (is_int($bitmask) === false) {
         $err = "expecting a integer value for 'bitmask'";
         throw new InvalidArgumentException($err);
      }
      else if (($test = Path::test($dir, Dir::READABLE)) !== true) {
         $err = "failed to find directories; $test";
         throw new InvalidArgumentException($err);
      }

      if ($bitmask & self::RECURSIVE) {
         $dirIterator = new RecursiveDirectoryIterator($dir);
         $iterator = new RecursiveIteratorIterator($dirIterator);
      }
      else {
         $iterator = new DirectoryIterator($dir);
      }

      $ret = [];
      foreach ($iterator as $file) {
         $result = $callback($file, $iterator, $ret);
         if ($result !== null) {
            $ret[$result] = true;
         }
      }

      if ($bitmask & self::RETURN_KEYS) {
         return array_keys($ret);
      }
      else if ($bitmask & self::RETURN_VALUES) {
         return array_values($ret);
      }
      else {
         return $ret;
      }
   }

   /**
    * find broken symbolic links within a given directory
    * @param string $dir - the absolute path to a directory to search within
    * @param boolean $bitmask - filter options
    * @return array
    */
   public static function brokenLinks($path, $bitmask = 0)
   {
      $filter = function($file, $iterator, &$ret) {
         if ($file->isLink()) {
            $realPath = $file->getRealPath();
            if (!file_exists($realPath)) {
               $linkPath = $file->getPath()."/".$file->getFilename();
               return $linkPath;
            }
         }
         return null;
      };
      return self::filter($path, $filter, $bitmask);
   }

   /**
    * find directories within a given directory
    * @param string $dir - the absolute path to a directory to search within
    * @param boolean $bitmask - filter options
    * @return array
    */
   public static function directories($path, $bitmask = 0)
   {
      $filter = function($file, $iterator, &$ret) {
         return (
            $file->isDir() &&
            ($path = $file->getRealPath()) &&
            !array_key_exists($path, $ret)
         )
            ? $path
            : null;
      };
      return self::filter($path, $filter, $bitmask);
   }

   /**
    * find empty directories within a given directory
    * @param string $dir - the absolute path to a directory to search within
    * @param boolean $bitmask - filter options
    * @return array
    */
   public static function emptyDirectories($path, $bitmask = 0)
   {
      $filter = function(SplFileInfo $file, $iterator, &$ret) {
         return (
            $file->isDir() &&
            ($path = $file->getRealPath()) &&
            !array_key_exists($path, $ret) &&
            Dir::isEmpty($path)
         )
            ? $path
            : null;
      };

      return self::filter($path, $filter, $bitmask);
   }

   /**
    * find files with a given extension
    * @param string $dir - the absolute path to a directory to search within
    * @param string $extension - the extension to match
    * @param boolean $bitmask - filter options
    * @return array
    */
   public static function filesByExtension($dir, $extension, $bitmask = 0)
   {
      if ($bitmask & self::CASE_SENSITIVE) {
         $filter = function($file, $iterator, &$ret) use ($extension) {
            return (
               $file->isFile() &&
               strcmp($extension, $file->getExtension()) === 0
            )
               ? $file->getRealPath()
               : null;
         };
      }
      else {
         $filter = function($file, $iterator, &$ret) use ($extension) {
            return (
               $file->isFile() &&
               strcasecmp($extension, $file->getExtension()) === 0
            )
               ? $file->getRealPath()
               : null;
         };
      }
      
      return self::filter($dir, $filter, $bitmask);
   }
}

