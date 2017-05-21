<?php

class PDOWrap extends PDO {

    const INI_PATH = "pdo.ini";

    static private $conn;

    public function __construct() {
        $ini = parse_ini_file(self::INI_PATH, true);
        parent::__construct($ini['dsn'], $ini['username'], $ini['password']);
    }

    static public function connect() {
        if (!self::$conn) {
            self::$conn = new PDOWrap();
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$conn;
    }

    public function createStatement($sql, $bind = array()) {
        $sth = $this->prepare($sql);
        foreach ($bind as $key => $value) {
            $sth->bindValue(":{$key}", $value, is_null($value) ? PDO::PARAM_NULL : ( is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
        }
        $sth->execute();
        return $sth;
    }

}
