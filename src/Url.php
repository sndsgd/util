<?php

namespace sndsgd;

use \InvalidArgumentException;


/**
 * A class for manipulating URLs
 */
class Url
{
   /**
    * Parse a url encoded string into an array of values
    *
    * php's `parse_url` requires brackets to indicate an array; this function
    * will parse values of the same name into an array if brackets don't exist
    * @param string $source
    * @return array.<string => mixed>
    */
   public static function decodeQueryString($source)
   {
      $ret = [];
      if ($source !== '') {
         $queryPairs = explode('&', $source);
         foreach ($queryPairs as $pair) {
            $parts = explode('=', $pair, 2);
            $key = rawurldecode($parts[0]);
            $value = (count($parts) === 1) ? true : rawurldecode($parts[1]);
            Arr::addValue($ret, $key, $value);
         }
      }

      return $ret;
   }

   /**
    * Create a url encoded string
    *
    * php's `http_build_query` does a wacky job of handling arrays
    * @param array $query 
    * @return string
    */
   public static function encodeQueryString(array $query)
   {
      $ret = [];
      foreach ($query as $name => $value) {
         if (is_array($value)) {
            foreach ($value as $v) {
               $ret[] = $name.'='.rawurlencode($v);
            }
         }
         else {
            $ret[] = $name.'='.rawurlencode($value);
         }
      }
      return implode('&', $ret);
   }

   /**
    * Parse a url string into an object
    * 
    * @param string $url
    * @return sndsgd\Url
    */
   public static function createFromString($url)
   {
      if (!is_string($url)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'url'; expecting a string"
         );
      }

      $ret = new Url;
      if (($url = trim($url)) === '') {
         return $ret;
      }

      foreach (parse_url($url) as $k => $v) {
         $fn = 'set'.ucfirst($k);
         call_user_func([$ret, $fn], $v);
      }
      return $ret;
   }

   /**
    * Create a url instance given an array of url properties and values
    * 
    * @param array $arr
    * @return sndsgd\Url
    */
   public static function createFromArray(array $arr)
   {
      $ret = new Url;
      $keys = ['scheme','user','pass','host','port','path','query','fragment'];
      foreach ($keys as $key) {
         if (array_key_exists($key, $arr)) {
            $fn = 'set'.ucfirst($key);
            call_user_func([$ret, $fn], $arr[$key]);
         }
      }
      return $ret;
   }


   /**
    * @var string|null
    */
   protected $scheme;

   /**
    * @var string|null
    */
   protected $user;

   /**
    * @var string|null
    */
   protected $pass;

   /**
    * @var string|null
    */
   protected $host;

   /**
    * @var integer|null
    */
   protected $port;

   /**
    * @var string|null
    */
   protected $path;

   /**
    * @var array.<string => mixed>|null
    */
   protected $query = [];

   /**
    * @var string|null
    */
   protected $fragment;


   /**
    * Set the scheme portion of the url
    * 
    * @param string $scheme
    */
   public function setScheme($scheme)
   {
      $this->scheme = $scheme;
   }

   /**
    * Get the scheme portion of the url
    * 
    * @return string|null
    */
   public function getScheme()
   {
      return $this->scheme;
   }

   /**
    * Set the user portion of the url
    * 
    * @param string $user
    */
   public function setUser($user)
   {
      $this->user = $user;
   }

   /**
    * Get the user portion of the url
    * 
    * @return string|null
    */
   public function getUser()
   {
      return $this->user;
   }

   /**
    * Set the password portion of the url
    * 
    * @param string|null $password
    */
   public function setPass($password = null)
   {
      if (!is_string($password) && !is_null($password)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'password'; expecting a string or null"
         );
      }
      $this->pass = $password;
   }

   /**
    * Get the password portion of the url
    * 
    * @return string|null
    */
   public function getPass()
   {
      return $this->pass;
   }

   /**
    * Set the host portion of the url
    * 
    * @param string $host
    */
   public function setHost($host = null)
   {
      if (!is_string($host) && !is_null($host)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'host'; expecting a string or null"
         );
      }
      $this->host = $host;
   }

   /**
    * Get the host portion of the url
    * 
    * @return string|null
    */
   public function getHost()
   {
      return $this->host;
   }

   /**
    * Set the port portion of the url
    * 
    * @param string|null $port
    */
   public function setPort($port = null)
   {
      if (is_null($port) || is_int($port)) {
         $this->port = $port;
      }
      else if (is_string($port) && preg_match('/^[0-9]+$/', $port) !== 0) {
         $this->port = Str::toNumber($port);
      }
      else {
         throw new InvalidArgumentException(
            "invalid value provided for 'port'; ".
            " expecting an integer, string of integers, or null"
         );
      }
      return $this;
   }

   /**
    * Get the port portion of the url
    * 
    * @return integer|null
    */
   public function getPort()
   {
      return $this->port;
   }

   /**
    * Set the path portion of the url
    * 
    * @return string|null
    */
   public function setPath($path = null)
   {
      if (is_string($path)) {
         if ($path{0} !== '/') {
            $path = "/$path";
         }
      }
      else if (!is_null($path)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'path'; ".
            " expecting a string or null"
         );
      }
      $this->path = $path;
   }

   /**
    * Get the path portion of the url
    * 
    * @return string|null
    */
   public function getPath()
   {
      return $this->path;
   }

   /**
    * Set the query data
    * 
    * @param string|array|null $query
    * @return sndsgd\Url
    */
   public function setQuery($query = null)
   {
      $this->query = [];
      return ($query === null) 
         ? $this 
         : $this->addQueryData($query);
   }

   /**
    * Add more data to the query
    * 
    * @param string|array $data
    * @return sndsgd\Url
    */
   public function addQueryData($data)
   {
      if (is_string($data)) {
         $data = self::decodeQueryString($data);
      }
      else if (is_object($data)) {
         $data = (array) $data;
      }
      else if (!is_array($data)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'query'; ".
            "expecting a string, array, or object"
         );
      }

      foreach ($data as $k => $v) {
         Arr::addValue($this->query, $k, $v);
      }
      return $this;
   }

   /**
    * Get the query portion of the url
    * 
    * @return array
    */
   public function getQuery()
   {
      return $this->query;
   }

   /**
    * Set the fragment
    * 
    * @param string|null $fragment
    * @return sndsgd\Url
    */
   public function setFragment($fragment = null)
   {
      if (is_string($fragment)) {
         if ($fragment{0} === '#') {
            $fragment = substr($fragment, 1);
         }
      }
      else if (!is_null($fragment)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'fragment'; ".
            "expecting a string or null"
         );
      }

      $this->fragment = $fragment;
      return $this;
   }

   /**
    * Get the fragment
    * 
    * @return string
    */
   public function getFragment()
   {
      return $this->fragment;
   }

   /**
    * Get the url as an array
    * 
    * @return array.<string,string|integer>
    */
   public function __toArray()
   {
      $ret = [];
      foreach ($this as $property => $value) {
         $ret[$property] = $value;
      }
      return $ret;
   }

   /**
    * Convert the url into a string
    * 
    * @return string
    */
   public function __toString()
   {
      $scheme = ($this->scheme !== null) ? $this->scheme.'://' : '';
      $user = ($this->user !== null) ? $this->user : '';
      $pass = ($this->pass !== null) ? $this->pass : '';
      $credentials = ($user || $pass) ? "$user:$pass@" : '';
      $host = ($this->host !== null) ? $this->host : '';
      $port = ($this->port !== null) ? ':'.$this->port : '';
      $path = ($this->path !== null) ? $this->path : '';
      if (strlen($path) === 0) {
         $path = '/';
      }

      $query = ($this->query)
         ? '?'.self::encodeQueryString($this->query)
         : '';
      $fragment = ($this->fragment !== null) ? '#'.$this->fragment : '';

      return $scheme.$credentials.$host.$port.$path.$query.$fragment;
   }

   /**
    * Update missing properties in the url with those of another url
    * 
    * @param string|sndsgd\Url $to
    * @return sndsgd\Url
    * @throws InvalidArgumentException If an invalid argument is passed
    */
   public function merge($to)
   {
      if (is_array($to)) {
         $to = Url::createFromArray($to);
      }
      else if (is_string($to)) {
         $to = self::createFromString($to);
      }
      else if (!($to instanceof Url)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'to'; expecting a url as string, ".
            "an instance of sndsgd\\Url, or an array of url properties"
         );
      }

      $toValues = $to->__toArray();

      # the query property will be an empty array if it has no value
      if (count($this->query) === 0 && $toValues['query']) {
         $this->setQuery($toValues['query']);
      }

      $keys = ['scheme','user','pass','host','port','path','fragment'];
      foreach ($keys as $key) {
         if ($this->$key === null && $toValues[$key] !== null) {
            call_user_func([$this, 'set'.ucfirst($key)], $toValues[$key]);
         }
      }

      return $this;
   }
}

