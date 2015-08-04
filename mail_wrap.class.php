<?php
require_once 'pear/Mail.php';

Class MailWrap{

	const INI_PATH = "mail.ini";
	private $ini = array();
	private $internal_encoding = "UTF-8";

	public function __construct(){
		$this->ini = parse_ini_file(self::INI_PATH, true);
		$this->internal_encoding = mb_internal_encoding();
	}

	public function send_mail($subject, $body, $to_add, $type = 'mail'){
		return $this->_createObject($type)->send(
			$to_add,
			$this->_createHeader($subject, $to_add, $type),
			mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"));
	}

	private function _createObject($type){
		switch ($type) {
			case 'send_mail':
				$params["sendmail_path"] = $this->ini['send_mail']['path'];
				$params["sendmail_args"] = $this->ini['send_mail']['args'];
				break;
			case 'smtp':
				$params["host"] = $this->ini['smtp']['host'];
				$params["port"] = $this->ini['smtp']['port'];
				$params["auth"] = (bool)$this->ini['smtp']['auth'];
				$params["username"] = $this->ini['smtp']['username'];
				$params["password"] = $this->ini['smtp']['password'];
				if(!empty($this->ini['smtp']['localhost']))
					$params["localhost"] = $this->ini['smtp']['localhost'];
				if(!empty($this->ini['smtp']['timeout']))
					$params["timeout"] = (bool)$this->ini['smtp']['timeout'];
				if(!empty($this->ini['smtp']['verp']))
					$params["verp"]  = (bool)$this->ini['smtp']['verp'];
				if(!empty($this->ini['smtp']['debug']))
					$params["debug"] = (bool)$this->ini['smtp']['debug'];
				if(!empty($this->ini['smtp']['persist']))
					$params["persist"] = (bool)$this->ini['smtp']['persist'];
				break;
			case 'mail':
			default:
				$type = 'mail';
				$params = '-f '.$this->ini['base']['from'];
				break;
		}
		return Mail::factory($type, $params);
	}

	private function _createHeader($subject, $to_add, $type){
		$headers = array();
		switch ($type) {
			case 'send_mail':
			case 'smtp':
				$headers["To"] = $to_add;
				$headers["From"] = $this->ini['base']['from'];
				$headers["Subject"] = $subject;
				$headers["Return-Path"] = $this->ini['base']['from'];
				break;
			case 'mail':
				$headers["To"] = $to_add;
				$headers["From"] = $this->ini['base']['from'];
				$headers["Subject"] = $subject;
				$headers["Content-Type"] = 'text/plain; charset=ISO-2022-JP';
				$headers["Content-Transfer-Encoding"] = '7bit';
				$headers["MIME-Version"] = '1.0';
				$headers["Message-Id:"] = '<'.md5(uniqid(microtime())).'@'.
					parse_url($this->ini['base']['from'], PHP_URL_HOST).'>';
			default:
				break;
		}
		return $this->_encodeMimeheaders($headers);
	}

	private function _encodeMimeheaders(array $hedaers){
		mb_internal_encoding("ISO-2022-JP");
		foreach($hedaers as &$value){
			$value = mb_encode_mimeheader(mb_convert_encoding(
				$value, "ISO-2022-JP", "UTF-8"),"ISO-2022-JP","B","¥r¥n");
		}
		mb_internal_encoding($this->internal_encoding);
		return $hedaers;
	}
}
