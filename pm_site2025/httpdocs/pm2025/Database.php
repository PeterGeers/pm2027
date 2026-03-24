<?php
/* 
	PDO Database class for MySQL.
	Based on: https://github.com/rickardd/PHP-oop-account-template/blob/master/classes/DB.php
	
*/
require_once("pmdb_secret.php");

class Database {
	private static $_instance = null;
	private $_pdo,
			$_query,
			$_error = false,
			$_results,
			$_count = 0;

  // Open connection to DB.
	private function __construct() {
		try{
      $this->_pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE . ';charset=utf8', DB_USER, DB_PASS );
	  } catch(PDOExeption $e){
      echo "<br>There was some error connecting to the database.".$e->getMessage()."<br><br>";
      die($e->getMessage());
  	}
	}

public static function getInstance() {
  if(!isset(self::$_instance)) {
    self::$_instance = new Database();
  }
  return self::$_instance;
}
// Run query with parameter ? substitution.
public function query($sql, $params = array()) {
  $this->_error = false;
  $this->_count = 0;
  if ($this->_query = $this->_pdo->prepare($sql)) {
    $x = 1;
    if(count($params)){
      foreach ($params as $param) {
        $this->_query->bindValue($x, $param);
        $x++;
      }
    }
    if ($this->_query->execute()) {
      $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
      $this->_count = $this->_query->rowCount();
    } else{
      $this->_error = true;
    }
  }
  return $this;
}
// Replace * with specified columns to return and run query.
public function query_columns($sql, $columns = array()) {
  $sql_col = array();
  if (count($columns)) {
    foreach($columns as $column) {
      $sql_col[] = '`'.$column.'`';
    }
    if (count($sql_col)) {
      $sql = str_replace('*', implode(',', $sql_col), $sql);
    }
  }
  return $this->query($sql);
}
// Used for get (SELECT) and delete only
private function action( $action, $table, $where = array() ){
  if (count($where) === 3){
    $operators = array('=', '>', '<', '>=', '<=', '<>', '!=');
    $field = $where[0];
    $operator = $where[1];
    $value = $where[2];
    if (in_array($operator, $operators)) {
      $sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";
      if(!$this->query($sql, array($value))->error()){
        return $this;
      }
    } elseif ($operator == 'IN') {
      $sql = "{$action} FROM {$table} WHERE {$field} IN ({$value})";
      if(!$this->query($sql)->error()){
        return $this;
      }
    }
  } elseif (count($where) === 0){
    $sql = "{$action} FROM {$table};";
    if(!$this->query($sql)->error()){
      return $this;
    }
}
  return false;
}
// Do simple SELECT * with no or single WHERE clause
public function get( $table, $where = array()){
  return $this->action('SELECT *', $table, $where);
}

// Count from DB without feching data and with addition IN condition.
public function count_in_db($table, $where = array()){
  $this->_error = false;
  $rows = -1;
  $cond = '';
  $value = null;
  if (count($where) === 3 OR count($where) === 6) {
    $operators = array('=', '>', '<', '>=', '<=', '<>', '!=');
    $field = $where[0];
    $operator = $where[1];
    $value = $where[2];
    if (in_array($operator, $operators)) {
      $cond = ' WHERE '.$field.' '.$operator.' "'.$value.'" ';
    }
    if (count($where) === 3) {
      $invalue = $where[2];
    } elseif (count($where) === 6) {
      $field = $where[3];
      $operator = $where[4];
      $invalue = $where[5];
    }
    if ($operator == 'IN') {
      if ($cond == '') {
        $cond = " WHERE {$field} IN ({$invalue})";
      } else {
        $cond .= " AND {$field} IN ({$invalue})";
      }
    }
  } elseif (count($where) === 2) {
    $field = $where[0];
    $operator = $where[1];
    if (in_array($operator, array('IS NULL', 'IS NOT NULL'))) {
      $cond = " WHERE {$field} {$operator}";
    }
  }
  if ($query = $this->_pdo->prepare('SELECT COUNT(*) as total_rows FROM '.$table.$cond.';') ){
    if ($query->execute()) {
      $rows = $query->fetchColumn();
    } else{
      $this->_error = true;
    }
  }
  return $rows;
}

public function delete( $table, $where){
  return $this->action('DELETE', $table, $where);
}

public function insert($table, $fields = array()){
  if( count($fields)){
    $keys = array_keys($fields);
    $values = '';
    $x = 1;
    foreach ($fields as $field) {
      $values .= '?';
      if($x < count($fields)){
        $values .= ', ';
      }
      $x++;
    }
    $sql = "INSERT INTO {$table} (`" . implode('`,`',  $keys) . "`) VALUES ({$values}) ";
    if(!$this->query($sql, $fields)->error() ){
      return true;
    }
  }
  return false;
}

public function update($table, $id, $fields){
  $set = '';
  $x = 1;
  foreach ($fields as $name => $value) {
    $set .= "{$name} = ?";
    if($x < count($fields)){
      $set .= ', ';
    }
    $x++;
  }
  $sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";
  if(!$this->query($sql, $fields)->error()){
    return true;
  }
  return false;
}

public function error(){
  return $this->_error;
}

public function count(){
  return $this->_count;
}

public function results(){
  return $this->_results;
}

public function first(){
  return $this->results()[0];
}

}
