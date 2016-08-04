<?php

class ClassLoader {

    static private $_list = array();

    public static function load() {
        spl_autoload_register(function ($class_name) {
            if (array_search($class_name, ClassLoader::$_list)) {
                return;
            }
            require_once dirname(__FILE__) . '/' . $class_name . '.class.php';
            ClassLoader::$_list[] = $class_name;
        });
    }

}
