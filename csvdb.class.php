<?php

/**
 * CSVからのデータ抽出（登録機能なし）
 */
class CsvDb{
  private static $_splfileobjs = array();
  private $_splfileobj;
  private $_has_clumn_name;

  const SERCH_TYPE_AND = 'AND';
  const SERCH_TYPE_OR = 'OR';

  /**
   * [__construct description]
   * @param string  $path           [対象CSVパス]
   * @param boolean $has_clumn_name [一行目がカラム名になっているCSVか]
   * @param [type]  $setFlags       [SplFileObject参照]
   */
  public function __construct($path, $has_clumn_name = false, $setFlags = null){
    $this->_splfileobj = self::_connect($path);
    $this->_has_clumn_name = $has_clumn_name;//一行目をカラム名にできるか？
    $this->_splfileobj->setFlags($setFlags == null ? SplFileObject::READ_CSV:$setFlags);
  }

  /**
   * CSVから検索をかけて、該当レコード取得
   * @param  array  $serach [array(検索項目=>検索値)]
   * @param  string $type   [ANDかOR検索の指定]
   * @return array  検索結果のレコード配列
   */
  public function findSerach($serach = array(), $type = null){
    $res = array();
    $column_names = $this->_getColumnNames();// カラム名を取得（指定がなければindex数になる）
    foreach ( $this->_splfileobj as $key => $line ) {

      // 空行スキップ
      if(count($line) === 1 && empty($line[0]))continue;

      // 文字コード変更
      mb_convert_variables("UTF-8", array("ASCII","JIS","UTF-8","EUC-JP","SJIS"), $line);

      // カラム名を追加
      $line = $this->_addColumnName2Keys($line, $column_names);

      if(!$serach){
        $res[] = $line;
        continue;
      }

      //該当しない条件項目の指定があればスキップ
      foreach ($serach as $key => $value) {
        if(array_search($key, $column_names) === false){
          continue 2;
        }
      }
      // AND検索
      if(!$type || $type == self::SERCH_TYPE_AND){
        foreach ($line as $line_key => $value) {
          if(isset($serach[$line_key]) && $serach[$line_key] != $value){
            // 違うデータであれば次行スキップ
            continue 2;
          }
        }
        $res[] = $line;
        continue;
      }

      // OR検索
      if($type == self::SERCH_TYPE_OR){
        foreach ($line as $line_key => $value) {
          if(isset($serach[$line_key]) && $serach[$line_key] == $value){
            // 該当データがあれば格納、スキップ
            $res[] = $line;
            continue 2;
          }
        }
      }
    }
    return $res;
  }

  public function insert(array $data){

    if(!$this->_splfileobj->isWritable()){
      return false;
    }
    $fileobj = $this->_splfileobj->openFile('a');
    $fileobj->fputcsv($data);
    return true;
  }

  public function inserts(array $data){
    if(!$this->_splfileobj->isWritable()){
      return false;
    }
    $fileobj = $this->_splfileobj->openFile('a');
    foreach($data as $line){
      $fileobj->fputcsv($line);
    }
    return true;
  }

  public static function create($file, $data, $has_clumn_name = false){
    touch($file);
    $file = new SplFileObject($file, 'w');
    if(count($data) > 0 && !$file->isWritable()){
      delete($file);
      return false;
    }
    if($has_clumn_name){
      $file->fputcsv(array_keys($data[0]));
    }
    foreach ($data as $line) {
        $file->fputcsv($line);
    }
    return true;
  }

  private static function _connect($path){
    if(!isset(self::$_splfileobjs[$path])){
      self::$_splfileobjs[$path] = new SplFileObject($path);
    }
    return self::$_splfileobjs[$path];
  }

  private function _getColumnNames(){
    $this->_splfileobj->rewind();
    $columns = $this->_splfileobj->fgetcsv();
    // 最初からカラム名取得
    if($this->_has_clumn_name){
      $column_names = $columns;
      mb_convert_variables("UTF-8", array("ASCII","JIS","UTF-8","EUC-JP","SJIS"), $column_names);
      foreach ($column_names as $k => &$v) {
        if(strlen($v) == 0){
          $v = $k;
        }
      }
    }else{
      $column_names = array_keys($columns);
    }
    return $column_names;
  }

  private function _addColumnName2Keys($line, $column_names){
    $res = array();
    foreach ($line as $key => $value) {
      $res[$column_names[$key]] = $value;
    }
    return $res;
  }

}
