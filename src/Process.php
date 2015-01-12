<?php

namespace sndsgd;

use \InvalidArgumentException;
use \sndsgd\File;


/**
 * A class for executing shell commands
 */
class Process
{
   // descriptorspec indexes
   const STDIN = 0;
   const STDOUT = 1;
   const STDERR = 2;

   /**
    * The command to execute
    *
    * @var string
    */
   private $command;

   /**
    * The current working directory for the process
    *
    * @var string|null
    */
   private $cwd = null;

   /**
    * An array of environment variables or null to use the parent script env
    *
    * @var array.<string,string>|null
    */
   private $env = null;

   /**
    * The descriptorspec to pass to proc_open
    *
    * @var array.<integer,array>
    */
   private $spec = [
      self::STDIN => ['pipe', 'r'],
      self::STDOUT => ['pipe', 'w'],
      self::STDERR => ['pipe', 'w']
   ];

   /**
    * Data to pass as stdin
    *
    * @var string
    */
   private $stdin = '';

   /**
    * After a process completes, the stdout buffer will be stashed here
    *
    * @var string
    */
   private $stdout;

   /**
    * After a process completes, the stderr buffer will be stashed here
    *
    * @var string
    */
   private $stderr;

   /**
    * After a process completes, the exit code will be stashed here
    *
    * @var integer
    */
   private $exitcode;

   /**
    * @param array.<string>|string $command The command to execute
    * @param string|null $cwd The current working directory for the command
    */
   public function __construct($command, $cwd = null)
   {
      if (is_array($command)) {
         $command = implode(' ', $command);
      }
      $this->command = $command;
      $this->cwd = $cwd;
   }

   /**
    * Set the stdin contents
    *
    * @param string $str The stdin value
    */
   public function setStdin($str)
   {
      $this->spec[self::STDIN] = ['pipe', 'r'];
      $this->stdin = $str;
   }

   /**
    * Set the path to a file to read into stdin
    *
    * @param string $path An absolute file path
    * @throws InvalidArgumentException If $path is not a readable file
    */
   public function setStdinFile($path)
   {
      if (($test = File::isReadable($path)) !== true) {
         throw new InvalidArgumentException(
            "invalid value provided for 'path'; $test"
         );
      }
      $this->spec[self::STDIN] = ['file', $path, 'r'];
      $this->stdin = '';
   }

   /**
    * Set an output file for either stdout or stderr
    *
    * @param integer $stream The index of the relevant stream
    * @param string $path An absolute file path
    * @param boolean $append Whether or not to append to the file
    */
   private function setOutputFile($stream, $path, $append)
   {
      if (($test = File::isWritable($path)) !== true) {
         throw new InvalidArgumentException(
            "invalid value provided for 'path'; $test"
         );
      }

      $this->spec[$stream] = ['file', $path, $append === true ? 'a' : 'w'];
   }

   /**
    * Set the path to a file to write stdout to
    *
    * @param string $path An absolute file path
    * @param boolean $append Whether or not to append to the file
    */
   public function setStdoutFile($path, $append = false)
   {
      $this->setOutputFile(self::STDOUT, $path, $append);
   }

   /**
    * Set the path to a file to write stderr to
    *
    * @param string $path An absolute file path
    * @param boolean $append Whether or not to append to the file
    */
   public function setStderrFile($path, $append = false)
   {
      $this->setOutputFile(self::STDERR, $path, $append);
   }

   /**
    * @return string
    */
   public function getStdout()
   {
      return $this->stdout;
   }

   /**
    * @return string
    */
   public function getStderr()
   {
      return $this->stderr;
   }

   /**
    * @return string
    */
   public function getExitcode()
   {
      return $this->exitcode;
   }

   /**
    * @return string
    */
   public function getCommand()
   {
      return $this->command;
   }

   /**
    * Execute the command
    *
    * @return integer The exit code from the process
    */
   public function exec()
   {
      $process = proc_open(
         $this->command,
         $this->spec,
         $pipes,
         $this->cwd,
         $this->env
      );

      if (is_resource($process)) {
         if ($this->spec[self::STDIN][0] === 'pipe') {
            fwrite($pipes[0], $this->stdin);
            fclose($pipes[0]);
         }
         if ($this->spec[self::STDOUT][0] === 'pipe') {
            $this->stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
         }
         if ($this->spec[self::STDERR][0] === 'pipe') {
            $this->stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
         }
         $this->exitcode = proc_close($process);
      }
      return $this->exitcode;
   }

   /**
    * Export data for logging of debugging
    *
    * @return array.<string,mixed>
    */
   public function export()
   {
      return [
         'command' => $this->command,
         'cwd' => $this->cwd,
         'exit code' => $this->exitcode,
         'stdout' => $this->stdout,
         'stderr' => $this->stderr
      ];
   }
}
