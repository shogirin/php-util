<?php

class EasyAuth{

	const INI_PATH = "easy_auth.ini";

	private static $_auth;
	public $ini = array();
	public $account = array();

	private function __construct(){
		session_start();
		$this->ini = parse_ini_file(self::INI_PATH, true);
		$this->accounts = $this->ini['account'];
	}

	private static function _factory(){
		if(is_null(self::$_auth)){
			self::$_auth = new EasyAuth();
		}
		return self::$_auth;
	}

	public static function check(){
		$auth = self::_factory();
		if(!self::_auth()){
			header('location: ' . $auth->ini['path']['login']);
		}
	}

	public static function login($account, $password){
		$auth = self::_factory();
		if(isset($auth->accounts[$account]) &&
			$auth->accounts[$account] == $password){
			$_SESSION['account'] = $account;
			header('location: ' . $auth->ini['path']['home']);
			exit;
		}
		sleep(3);
		header('location: ' . $auth->ini['path']['login'] . '?auth_error=true');
	}

	public static function logout(){
		$auth = self::_factory();
		// セッション変数を全て解除する
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
		    setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
		header('location: ' . $auth->ini['path']['login']);
	}

	public static function getCurentAccount(){
		return $_SESSION['account'];
	}

	private static function _auth(){
		return isset($_SESSION['account']);
	}
}