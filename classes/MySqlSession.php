<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class MySqlSession implements PhiFrameSessionInterface {
    private $table;
    private $name;
    private $conn;
    private $hash;
    private $data;
    private $time;
    
    public function __construct($config, $name = 'PhiFrameSession'){
        $message = '';
        $this->time = 0;
        $this->table = $config['db_table'];
        $this->name = $name;
        $this->conn = ExtendedMySQLi::open($config['db_address'], $config['db_login'], $config['db_password'], $config['db_name'], $message);
        
        if($this->conn == false)
            die($message);

        $this->conn->query("DELETE FROM $this->table WHERE `time` < '".time()."'");
        $this->data = [];
        
        $this->hash = fstr_null($this->name, INPUT_COOKIE);
        $data = $this->conn->getRecordSE("SELECT * FROM $this->table WHERE `hash` = '$this->hash'");
        
        if($this->hash == null || $data == false){
            $this->generateNewHash();
            $this->conn->query("INSERT INTO $this->table (`hash`, `time`, `data`) VALUES ('$this->hash', '".(time()+28800)."', '[]')"); 
        } elseif($data !== false){
            $this->data = json_decode($data['data'], true);
        }
    }

    public function regenerateId(){
        $oldHash = $this->hash;
        $this->generateNewHash();
        $this->conn->query("UPDATE $this->table SET `hash` = '$this->hash' WHERE `hash` = '$oldHash'");
    }
    
    public function setLifeTime(int $mem){
        $this->time = $mem > 0 ? time()+$mem : 0;
        $this->conn->query("UPDATE $this->table SET `time` = '$this->time' WHERE `hash` = '$this->hash'");
        setcookie($this->name, $this->hash, $this->time);
    }

    public function destroy() {
        $this->conn->query("DELETE FROM $this->table WHERE `hash` = '$this->hash'");
        setcookie($this->name, '', time()-1);
        $this->data = [];
    }

    public function is($name, $val = null) {
        return isset($this->data[$name]) ? $val == null || $this->data[$name] == $val : false;
    }

    public function set($name, $val) {
        if($val == null)
            unset($this->data[$name]);    
        else if($name != null && trim($name) != '')
            $this->data[$name] = $val;
        
        $this->updateData();
    }

    public function get($name) {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
    
    private function generateNewHash(){
        do {
            $this->hash = hash('sha256', fstr('HTTP_USER_AGENT', INPUT_SERVER).fip('REMOTE_ADDR'). randomKey(64));
        } while($this->conn->getRecordSE("SELECT * FROM $this->table WHERE `hash` = '$this->hash'") != false);
        
        setcookie($this->name, $this->hash, $this->time);
    }
    
    private function updateData(){
        $this->conn->query("UPDATE $this->table SET `data` = '".json_encode($this->data)."' WHERE `hash` = '$this->hash'");
    }

    public function __call($name, $args){
        if(isset($args[0]))
            $this->set($name, $args[0]);
        else
            return $this->get($name);
    }
    
    public function __set($name, $value){
        $this->set($name, $value);
    }
    
    public function __get($name){
        return $this->get($name);
    }
}