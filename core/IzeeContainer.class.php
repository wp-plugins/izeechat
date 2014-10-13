<?php

class IzeeContainer {

	private static $_coreInst;
	private static $_requestInst;
	private static $_databaseInst;
	private static $_ApiInst;
	private static $_comboxInst;
	private static $_userInst;
	private static $_utilsInst;
	private static $_cipherInst;
	private static $_UiInst;

	public static function setCoreInst($instance) {
		self::$_coreInst = $instance;
	}

	public static function setRequestInst($instance) {
		self::$_requestInst = $instance;
	}

	public static function setBddInst($instance) {
		self::$_databaseInst = $instance;
	}

	public static function setApiInst($instance) {
		self::$_ApiInst = $instance;
	}

	public static function setUserInst($instance) {
		self::$_userInst = $instance;
	}

	public static function setUtilsInst($instance) {
		self::$_utilsInst = $instance;
	}

	public static function setComboxInst($instance) {
		self::$_comboxInst = $instance;
	}

	public static function setCipherInst($instance) {
		self::$_cipherInst = $instance;
	}

	public static function setUiInst($instance) {
		self::$_UiInst = $instance;
	}

	public static function getCoreInst() {
		 return self::$_coreInst;
	}

	public static function getRequestInst() {
		return self::$_requestInst;
	}

	public static function getBddInst() {
		return self::$_databaseInst;
	}

	public static function getApiInst() {
		return self::$_ApiInst;
	}

	public static function getUserInst() {
		return self::$_userInst;
	}

	public static function getUtilsInst() {
		return self::$_utilsInst;
	}

	public static function getComboxInst() {
		return self::$_comboxInst;
	}

	public static function getCipherInst() {
		return self::$_cipherInst;
	}

	public static function getUiInst() {
		return self::$_UiInst;
	}
}


?>