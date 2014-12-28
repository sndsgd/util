<?php

namespace sndsgd\util;

use \DirectoryIterator as DI;
use \InvalidArgumentException;
use \RecursiveDirectoryIterator as RDI;
use \RecursiveIteratorIterator as RII;
use \SplFileInfo;
use \sndsgd\util\Dir;
use \sndsgd\util\Path;


class Find
{
   const RECURSIVE = 1;
   
   /**
    * Find directories
    * 
    * @param string $dir The absolute path to a directory to search within
    * @param integer $bitmask Filter options
    * @return array.<string>
    */
   public static function directories($dir, $bitmask = 0)
   {
      $fn = function(SplFileInfo $file, &$ret) {
         if (
            $file->isDir() &&
            ($path = $file->getRealPath()) &&
            !array_key_exists($path, $ret)
         ) {
            $ret[$path] = 1;
         }
      };

      $f = new self($dir, $fn);
      $results = $f->filter($bitmask);
      return array_keys($results);
   }

   /**
    * Find empty directories
    * 
    * @param string $dir The absolute path to a directory to search within
    * @param integer $bitmask Filter options
    * @return array.<string>
    */
   public static function emptyDirectories($dir, $bitmask = 0)
   {
      $fn = function(SplFileInfo $file, &$ret) {
         if (
            $file->isDir() &&
            ($path = $file->getRealPath()) &&
            !array_key_exists($path, $ret) &&
            Dir::isEmpty($path)
         ) {
            $ret[$path] = 1;
         }
      };

      $f = new self($dir, $fn);
      $results = $f->filter($bitmask);
      return array_keys($results);
   }

   /**
    * Find files with a given extension
    * 
    * @param string $dir The absolute path to a directory to search within
    * @param string $extension The extension to match (case insensitive)
    * @param integer $bitmask Filter options
    * @return array.<string>
    */
   public static function filesByExtension($dir, $extension, $bitmask = 0)
   {
      $fn = function(SplFileInfo $file, &$ret) use ($extension) {
         if (
            $file->isFile() &&
            strcasecmp($extension, $file->getExtension()) === 0 &&
            ($path = $file->getRealPath()) &&
            array_key_exists($path, $ret) === false
         ) {
            $ret[$path] = 1;
         }
      };

      $f = new self($dir, $fn);
      $results = $f->filter($bitmask);
      return array_keys($results);
   }

   /**
    * Find broken symbolic links
    * 
    * @param string $dir The absolute path to a directory to search within
    * @param integer $bitmask Filter options
    * @return array.<string>
    */
   public static function brokenLinks($dir, $bitmask = 0)
   {
      $fn = function(SplFileInfo $file, &$ret) {
         if (
            $file->isLink() &&
            ($realPath = $file->getRealPath()) === false
         ) {
            $link = $file->getPath().DIRECTORY_SEPARATOR.$file->getFilename();
            $ret[$link] = 1;
         }
      };

      $f = new self($dir, $fn);
      $results = $f->filter($bitmask);
      return array_keys($results);
   }


   /**
    * An absolute path to the directory to search
    * 
    * @var string
    */
   protected $dir;

   /**
    * A function that adds valid paths to the result set
    *
    * @var callable
    */
   protected $filter;

   /**
    * @param string $dir The absolute directory to search
    * @param callable $filter A unction to filter results
    */
   public function __construct($dir, callable $filter)
   {
      if (is_string($dir) === false) {
         throw new InvalidArgumentException(
            "invalid value provided for 'dir'; ".
            "expecting an absolute directory path as string"
         );
      }

      $dir = Path::normalize($dir);
      if (($test = Dir::isReadable($dir)) !== true) {
         throw new InvalidArgumentException(
            "invalid value provided for 'dir'; $test"
         );
      }

      $this->dir = $dir;
      $this->fn = $filter;
   }

   /**
    * Read files in the directory and use the callback to
    *
    * @param $bitmask Filter options
    * @return array
    */
   public function filter($bitmask = 0)
   {
      if ($bitmask & self::RECURSIVE) {
         $di = new RDI($this->dir, RDI::SKIP_DOTS);
         $iterator = new RII($di, RII::SELF_FIRST);
      }
      else {
         $iterator = new DI($this->dir);
      }

      $fn = $this->fn;
      $ret = [];
      foreach ($iterator as $file) {
         if ($iterator->isDot() === false) {
            $fn($file, $ret);
         }
      }
      return $ret;
   }
}

