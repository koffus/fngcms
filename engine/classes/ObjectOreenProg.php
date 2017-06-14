<?php

/**
 * Абстрактный класс
 * позволит процедурку перевести к стат методам классов
 * (решается обратная задача - поскидывать и упорядочить
 * функции в группы (классы))
 * Хуйня - но это начало начал ))
 *
*/

abstract class ObjectOreenProg {

	private $props = array();
	private static $_instance;

	function __construct(){
		// return 1;
	}

	final function __clone(){
		// return false;
	}

	public static function getInstance(){

		if( empty(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function setProperty($key, $val) {

		$this->props[$key] = $val;
	}

	public function getProperty($key) {

		return $this->props[$key];
	}

}