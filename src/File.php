<?php

namespace sndsgd;

use \InvalidArgumentException;


/**
 * File utility methods
 */
class File
{
   const KILOBYTE = 1024;
   const MEGABYTE = 1048576;
   const GIGABYTE = 1.074e+9;
   const TERABYTE = 1.1e+12;

   // bitmask values for use with sndsgd\Path::test()
   // @see sndsgd\Path for sub values
   const READABLE = 19;
   const WRITABLE = 11;
   const READABLE_WRITABLE = 27;
   const EXECUTABLE = 35;

   /**
    * Verify if a file is readable
    * 
    * @param string $path An absolute path to the file to test
    * @return boolean:true The file is readable
    * @return string An error message indicating why the path test failed
    */
   public static function isReadable($path)
   {
      return Path::test($path, self::READABLE);
   }

   /**
    * Determine if a file path can be written to
    * 
    * NOTE: if the path does not exist, the parent directories will be analyzed
    * @param string $path An absolute path to the file to test
    * @return boolean:true The file is writable
    * @return string An error message indicating why the path test failed
    */
   public static function isWritable($path)
   {
      # if the file doesn't exist, ensure its parent dir is writable
      if (!file_exists($path)) {
         return Dir::isWritable(dirname($path));
      }
      return Path::test($path, self::WRITABLE);
   }

   /**
    * Prepare a file for writing
    * 
    * @param string $path An absolute path to the file to write
    * @param octal $dirPerms Permissions for new directories
    * @return boolean|string
    * @return boolean:true The path is ready for writing
    * @return string An error message indicating where the prepare failed
    */
   public static function prepare($path, $dirPerms = 0775)
   {
      if (file_exists($path)) {
         return Path::test($path, self::WRITABLE);
      }
      return Dir::prepare(dirname($path), $dirPerms);
   }

   /**
    * Convience method to prepare a file, and then write to it
    *
    * @param string $path The absolute file path
    * @param string $contents The contents to write to the file
    * @param integer $opts Options to pass to file_put_contents
    * @param octal $dirPerms Permissions for new directories
    * @return boolean|string
    * @return boolean:true The write operation was successfull
    * @return string An error message indicating a failure
    */
   public static function write($path, $contents, $opts = 0, $dirPerms = 0775)
   {
      if (($result = self::prepare($path, $dirPerms)) !== true) {
         return "file write operation prevented; $result";
      }
      else if (@file_put_contents($path, $contents, $opts) === false) {
         return "file write operation failed";
      }
      return true;
   }

   /**
    * Remove wonky characters from a file name
    *
    * @param string $name The path or filename to sanitize
    * @return string
    */
   public static function sanitizeName($name)
   {
      if (strpos($name, DIRECTORY_SEPARATOR) !== false) {
         throw new InvalidArgumentException(
            "invalid value provided for 'name'; ".
            "expecting a filename that does not contain a path"
         );
      }
      
      return preg_replace('/[^A-Za-z0-9-_.]/', '_', $name);
   }

   /**
    * Separate a filename and extension
    * 
    * bug (??) with pathinfo(): 
    * [http://bugs.php.net/bug.php?id=67048](http://bugs.php.net/bug.php?id=67048)
    * 
    * Example Usage:
    * <code>
    * $path = '/path/to/file.txt';
    * list($name, $ext) = File::splitName($path);
    * // => ['file', 'txt']
    * $ext = File::splitName($path)[1];
    * // => 'txt'
    * </code>
    * 
    * @param string $path A file path or filename
    * @param string|null The value to use when no extension is present
    * @return array
    * - [0] string basename
    * - [1] string|null extension
    */
   public static function splitName($path, $missingExtensionValue = null)
   {
      $pos = strrpos($path, '/');
      if ($pos !== false) {
         $path = substr($path, $pos + 1);
      }

      $extpos = strrpos($path, '.');
      if ($extpos === false) {
         $name = $path;
         $ext = $missingExtensionValue;
      }
      else if ($extpos === 0) {
         $name = $path;
         $ext = $missingExtensionValue;
      }
      else {
         $name = substr($path, 0, $extpos);
         $ext = substr($path, $extpos + 1);
      }
      return [$name, $ext];
   }

   /**
    * Combine prepare, chmod, and rename into a single step
    * 
    * @param string $from An absolute path to the file before moving
    * @param string $to An absolute path to the file after moving
    * @param octal $fperm An octal to pass to chmod
    * @param octal $dperm New directory permissions
    * @return boolean|string All operations were successful
    * @return boolean:true All operations were successful
    * @return string Error message indicating which operation failed
    */
   public static function rename($from, $to, $fperm = 0664, $dperm = 0775)
   {
      if (($test = Path::test($from, self::READABLE_WRITABLE)) !== true) {
         return $test;
      }
      else if (($test = self::prepare($to, $dperm)) !== true) {
         return $test;
      }
      else if (@rename($from, $to) === false) {
         return "failed to move '$from' to '$to'";
      }
      else if ($fperm !== null && @chmod($to, $fperm) === false) {
         return "failed to set permissions for '$from' to '$fperm'";
      }
      return true;
   }

   /**
    * Get a human readable file size
    * 
    * @param integer|string $bytes Bytes or an absolute file path
    * @param integer $precision The number of decimal places to return
    * @return string The formatted filesize
    * @throws InvalidArgumentException
    *   if $bytes is interpretted as a file path and does not exist
    */
   public static function formatSize($bytes, $precision = 2)
   {
      if (is_string($bytes)) {
         if (is_numeric($bytes)) {
            $bytes = (int) $bytes;
         }
         else if (is_file($bytes)) {
            $bytes = filesize($bytes);
         }
         else {
            throw new InvalidArgumentException(
               "invalid value provided for 'bytes'; ".
               "expecting an integer or an absolute file path as string"
            );
         }
      }
      else if (!is_int($bytes)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'bytes'; ".
            "expecting an integer or an absolute file path as string"
         );
      }

      $i = 0;
      $sizes = ['bytes','KB','MB','GB','TB','PB','EB'];
      while ($bytes > 1024) {
         $bytes /= 1024;
         $i++;
      }
      return number_format($bytes, $precision).' '.$sizes[$i];
   }

   /**
    * Count the lines in a file without reading the contents into memory
    * 
    * @param string $path An absolute file path
    * @return integer The number of lines in the file
    */
   public static function countLines($path)
   {
      $ret = 0;
      $fh = fopen($path, 'r');
      while (!feof($fh)) {
         $buffer = fread($fh, 8192);
         $ret += substr_count($buffer, "\n");
      }
      fclose($fh);
      return $ret;
   }
}

