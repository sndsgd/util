<?php

use \sndsgd\util\Path;


require __DIR__.'/../vendor/autoload.php';

$updater = new MimeUpdater;
$updater->updateClassProperties();


class MimeUpdater
{
   protected $types = [
      'application/octet-stream' => null,
   ];

   protected $extensions = [];
   protected $strings = [];

   public function __construct()
   {
      $typesPath = Path::normalize(__DIR__.'/../data/mime/types.json');
      $json = file_get_contents($typesPath);
      $json = json_decode($json, true);

      foreach ($json as $type => $ext) {
         $this->addType($type, $ext);
      }
   }

   private function addType($type, $ext)
   {
      if (!array_key_exists($type, $this->types)) {
         $this->types[$type] = [];
      }

      if ($this->types[$type] === null) {
         return;
      }

      if (is_array($ext)) {
         foreach ($ext as $e) {
            $this->addType($type, $e);
         }
      }
      else {
         $this->types[$type][$ext] = 1;
         if (array_key_exists($ext, $this->extensions)) {
            $currentType = $this->extensions[$ext];
            echo 
               "dupe extension for $ext:\n".
               "  current type: '$currentType'".
               "  new type: '$type'\n\n";
         }
         else {
            $this->extensions[$ext] = $type;   
         }
      }
   }

   public function updateClassProperties()
   {
      $classPath = Path::normalize(__DIR__.'/../src/Mime.php');
      $contents = file_get_contents($classPath);

      $properties = [
         'extensions' => $this->exportExtensions(),
         'types' => $this->exportTypes()
      ];

      foreach ($properties as $prop => $replacement) {
         $regex = '/private static \\$'.$prop.' = \\[(.*?)\\];/s';
         if (preg_match($regex, $contents) == 0) {
            echo "failed to find property definition for '$prop'\n";
            exit(1);
         }
         $replacement = $this->arrayToString($prop, $replacement);
         $contents = preg_replace($regex, $replacement, $contents);
      }

      file_put_contents($classPath, $contents);
   }

   private function arrayToString($name, $values)
   {
      $r = '';
      foreach ($values as $k => $v) {
         $r .= "      '$k' => '$v',\n";
      }
      return "private static \$$name = [\n{$r}   ];";   ;
   }

   private function exportExtensions()
   {
      $ret = $this->extensions;
      ksort($ret);
      return $ret;
   }

   private function exportTypes()
   {
      $ret = array_filter($this->types, function($v) { return $v !== null; });
      $types = array_keys($ret);
      foreach ($types as $type) {
         $extensions = array_keys($ret[$type]);
         $ret[$type] = $extensions[0];
      }
      ksort($ret);
      return $ret;
   }
}

