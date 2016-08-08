<?php

require_once './pdo_wrap.class.php';

/*
 * DB操作
 */

class Model {

    protected static $conn;
    protected $table = '';
    protected $pk = '';

    /**
     * 一覧データ取得
     * @param:args  検索条件
     * 		order  並び順
     * 		page   ページ数
     * 		order  表示件数
     * @return array 取得結果
     * */
    public function getList(
    array $args = array(), array $order = array(), $page = 1, $limit = 20) {
        $sql = " SELECT * FROM " . $this->table . $this->__createWhereQuery($args);
        if ($order)
            $sql .= self::__createOrderQuery($order);
        $limit = (int) $limit;
        $offset = $limit * ((int) $page - 1);
        $sql .= " LIMIT {$limit} OFFSET {$offset} ";
        $sth = self::__bindSql($sql, $args);
        $res = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $res[] = $this->__fileterOutput($row);
        }
        return $res;
    }

    public function getGroupCount($group, array $args = array(), array $order = array(), $page = 1, $limit = 20) {
        $group = self::$conn->quote($group);
        $sql = " SELECT {$group}, COUNT(*) FROM " . $this->table
                . $this->__createWhereQuery($args)
                . "GROUP BY {$group} ";
        if ($order)
            $sql .= self::__createOrderQuery($order);
        $limit = (int) $limit;
        $offset = $limit * ((int) $page - 1);
        $sql .= " LIMIT {$limit} OFFSET {$offset} ";
        $sth = self::__bindSql($sql, $args);
        return $sth->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getCount(array $args = array()) {
        $sql = " SELECT COUNT(*) FROM " . $this->table . $this->__createWhereQuery($args);
        $sth = self::__bindSql($sql, $args);
        $res = $sth->fetch(PDO::FETCH_ASSOC);
        return $res["COUNT(*)"];
    }

    public function get($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE " . $this->pk . " = :" . $this->pk;
        $sth = self::__bindSql($sql, array($this->pk => (int) $id));
        return $this->__fileterOutput($sth->fetch(PDO::FETCH_ASSOC));
    }

    public function add(array $args) {
        $sql = "INSERT INTO " . $this->table . ' (';
        $sql .= implode(',', array_keys($args)) . ') VALUES (';
        $querys = array();
        foreach ($args as $key => $value) {
            $querys[] = ":{$key}";
        }
        $sql .= implode(',', $querys) . ')';
        $sth = self::__bindSql($sql, $this->__fileterInput($args));
        return self::$conn->lastInsertId();
    }

    public function delete($id) {
        $sql = "DELETE FROM " . $this->table;
        $sql .= " WHERE " . $this->pk . " = :" . $this->pk;
        return self::__bindSql($sql, array($this->pk => (int) $id));
    }

    public function update(array $args, $id) {
        $sql = "UPDATE " . $this->table . " SET ";
        $querys = array();
        foreach (array_keys($args) as $key) {
            if ($key != $this->pk)
                $querys[] = " {$key} =  :{$key}";
        }
        $sql .= implode(',', $querys);
        $sql .= " WHERE " . $this->pk . " = :" . $this->pk;
        $args[$this->pk] = $id;
        return self::__bindSql($sql, $this->__fileterInput($args));
    }

    public static function getConnect() {
        self::connect();
        return self::$conn;
    }

    static public function connect() {
        if (!self::$conn) {
            self::$conn = PDOWrap::connect();
        }
    }

    protected function __bindSql($sql, $args) {
        try {
            $this->__castTypeArgs($args);
            self::connect();
            return self::$conn->createStatement($sql, $args);
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }
        return false;
    }

    protected function __createOrderQuery(array $args) {
        $querys = array();
        foreach ($args as $key => $value) {
            $querys[] = self::$conn->quote(" {$key} {$value} ");
        }
        return " ORDER BY" . implode(',', $querys);
    }

    protected function __createWhereQuery(array $args) {
        return '';
    }

    protected function __castTypeArgs(&$args) {

    }

    protected function __fileterOutput($data) {
        return $data;
    }

    protected function __fileterInput($data) {
        return $data;
    }

}
