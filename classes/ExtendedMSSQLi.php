<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

class ExtendedMSSQLi extends PDO {

    public function __construct($cfg = null, string $login = null, string $password = null, string $database = null, int $port = null, string $driver = null) {
        if(is_array($cfg)){
            if(array_key_exists('server', $cfg))
                $server = $cfg['server'];
            
            if(array_key_exists('port', $cfg))
                $port = $cfg['port'];
            
            if(array_key_exists('uid', $cfg))
                $login = $cfg['uid'];
            
            if(array_key_exists('pwd', $cfg))
                $password = $cfg['pwd'];
            
            if(array_key_exists('db', $cfg))
                $database = $cfg['db'];
            
            if(array_key_exists('drv', $cfg))
                $driver = $cfg['drv'];
        } else {
            $server = $cfg;
        }
        
        if($server == null)
            die("MSSQL: server can't be NULL");
        
        if($login == null)
            die("MSSQL: login can't be NULL");
        
        if($password == null)
            die("MSSQL: password can't be NULL");
        
        $connQuery = "server=$server";
        $connQuery .= ($port != null && $port > 0 && $port < 65535 ? ",$port" : '');
        $connQuery .= $database != null ? ";database=$database" : '';
        $connQuery .= $driver != null ? ";driver=$driver" : '';
        
        try {
            parent::__construct("sqlsrv:$connQuery", $login, $password);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        } catch (PDOException $e) {
            die("MSSQL: Connection failed: {$e->getMessage()}");
        }
    }
    
    public $lastErrorException = null;
    public $notBreakAtError = false;

    public function squery(string $id, $var = null){
        $this->lastErrorException = null;
        $qs = '';
        
        try{    
            if(function_exists('query_syntax')){
                $qs = query_syntax($id, $var);

                if($qs === null)
                    return $this->query($id);
                else
                    return $this->query($qs);
            } else
                return $this->query($id);
            
            
        } catch (PDOException $e) {
            if ($this->notBreakAtError)
                $this->lastErrorException = $e;
            else
                error("<br>error code: {$e->getCode()}<br>{$e->getMessage()}<br>syntax id: $id<br>query syntax: $qs<br>variables: ".(is_array($var) ? implode('; ', $var) : $var));
        } 
    }
    
    public function getRecordsAsArray(string $id, $var = null){
        $res = $this->getRecords($id, $var);
        $arr = [];
        
        if($res !== false)
            while($rec = $res->fetch())
                $arr[] = $rec;
        
        return $arr;
    }
    
    public function getRecord(string $id, $var = null){
        $res = $this->squery($id, $var);
        
        if($res == null)
            return false;
            
        $rec = $res->fetch();

        if($rec !== false && $res->fetch() == false){
            return $rec;
        } else
            return false;
    }
    
    public function getRecords(string $id, $var = null, $callback = null){
        $res = $this->squery($id, $var);
        
        if($res == null)
            return null;

        if($callback == null)
            return $res;
        else
            while($rec = $res->fetch())
                if($callback($rec) === false){
                    while($res->fetch()){}
                    break;
                }

        return $res->rowCount();
    }
    
    public function deleteRecord(string $id, $var = null){
        return $this->squery($id, $var) === true && $this->affected_rows > 0 ? $this->affected_rows : false; 
    }
    
    public function updateRecord(string $id, $var = null){
        return $this->squery($id, $var) === true && $this->affected_rows > 0 ? $this->affected_rows : false; 
    }
    
    public function insertRecord(string $id, $var = null){
        return $this->squery($id, $var) === true ? $this->insert_id : false;
    }

    //--------------------------------------------------------------------------
    public static function open($addr, $login, $pass, &$msg){
        $conn = new ExtendedMySQLi((string)$addr, (string)$login, (string)$pass);

        if ($conn->connect_errno) {
            $msg = "Failed to connect to MySQL: (".$conn->connect_errno. ") ".iconv("windows-1250", "UTF-8", $conn->connect_error)."<br>";
            $conn = null;
        }

        if ($conn && !$conn->set_charset("utf8")) {
            $msg .= "Error loading character set utf8: ".iconv("windows-1250", "UTF-8", $conn->error)."<br>";
            $conn = null;
        }

        return $conn;
    }
}

