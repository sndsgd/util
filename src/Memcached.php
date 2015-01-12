<?php

namespace sndsgd;


/**
 * A subclass of the php Memcached extension
 *
 * This class is included as an example of how the php 
 * [memcached extension](http://php.net/manual/en/book.memcached.php) can 
 * be included in any project
 */
class Memcached extends \Memcached
{
   public function __construct($persistentId = '-')
   {
      parent::__construct($persistentId);
      $serverList = $this->getServerList();
      if (empty($serverList)) {
         $this->setOption(\Memcached::OPT_PREFIX_KEY, 'sndsgd-');
         $this->setOption(\Memcached::OPT_RECV_TIMEOUT, 1000);
         $this->setOption(\Memcached::OPT_SEND_TIMEOUT, 1000);
         $this->setOption(\Memcached::OPT_TCP_NODELAY, true);
         $this->setOption(\Memcached::OPT_SERVER_FAILURE_LIMIT, 50);
         $this->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 500);
         $this->setOption(\Memcached::OPT_RETRY_TIMEOUT, 300);
         $this->setOption(\Memcached::OPT_REMOVE_FAILED_SERVERS, true);
         $this->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
         $this->addServer('127.0.0.1', 11211, 1);
      }
   }
}

