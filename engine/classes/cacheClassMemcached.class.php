<?php

//
// Copyright (C) 2006-2013 Next Generation CMS (http://ngcms.ru/)
// Name: cacheClassMemcached.class.php
// Description: Cache manager
// Author: Vitaly Ponomarev
//

class cacheClassMemcached extends cacheClassAbstract {
	public $cache;
	public $params;
	function __construct($params = array()) {
		$this->cache = new Memcached();

		if (!is_array($params))
			$params = array();

		if (!isset($params['prefix']))
			$params['prefix'] = 'ng';

		if (!isset($params['expiration']))
			$params['expiration'] = 60;

		$this->params = $params;
	}

	function connect($host, $port) {
		return $this->cache->addServer($host, $port);
	}

	function get($plugin, $key, $expire) {
		return $this->cache->get($this->params['prefix'].':'.$plugin.':'.$key);
	}

	function getMulti($plugin, $keyList, $expire) {
		$keyResult = array();
		if (!is_array($keyList))
			return false;

		foreach ($keyList as $k)
			$keyResult[]= $this->params['prefix'].':'.$plugin.':'.$k;

		return $this->cache->getMulti($keyResult);
	}

	function set($plugin, $key, $value, $expiration = -1) {
		return $this->cache->set($this->params['prefix'].':'.$plugin.':'.$key, $value, ($expiration>=0)?$expiration:$this->params['expiration']);
	}

	function setMulti($plugin, $keyList, $expiration = 0) {
		$keyResult = array();
		if (!is_array($keyList))
			return false;

		foreach ($keyList as $k => $v)
			$keyResult[$this->params['prefix'].':'.$plugin.':'.$k] = $v;

		return $this->cache->setMulti($keyResult, ($expiration>=0)?$expiration:$this->params['expiration']);
	}

	function getResultCode() {
		return $this->cache->getResultCode();
	}

	function getResultMessage() {
		return $this->cache->getResultMessage();
	}

	function getResult() {
		return array($this->cache->getResultCode(), $this->cache->getResultMessage());
	}

	function touch($plugin, $key, $expiration) {
		return $this->cache->touch($this->params['prefix'].':'.$plugin.':'.$key, $value, $expiration);
	}

	function increment($plugin, $key, $offset = 1) {
			return $this->cache->increment($this->params['prefix'].':'.$plugin.':'.$key, $offset);
	}

	function decrement($plugin, $key, $offset = 1) {
		return $this->cache->decrement($this->params['prefix'].':'.$plugin.':'.$key, $offset);
	}

	function del($plugin, $key) {
		return $this->cache->del($this->params['prefix'].':'.$plugin.':'.$key);
	}
}
