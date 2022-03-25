<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

class ExtendedMySQLi extends mysqli {
    
    public function __construct(string $host = null, string $username = null, string $passwd = null, string $database = null, int $port = null, string $socket = null) {
        parent::__construct($host, $username, $passwd, $database, $port, $socket);
    }
    
    public function squery(string $id, $var = null){
        if(function_exists('query_syntax')){
            $qs = query_syntax($id, $var);
            
            if($qs === null)
                $res = $this->query($id);
            else {
                $res = $this->query($qs);
            }
        } else
            $res = $this->query($id);
        
        error($this->error, "<br>error code: $this->errno<br>syntax id: $id<br>query syntax: $qs<br>variables: ".(is_array($var) ? implode('; ', $var) : $var));
        return $res;
    }
    
    public function getRecord(string $id, $var = null){
        $rec = $this->getRecordSE($id, $var);
        
        if($rec === false)
            error("eMySQL.getRecord: required single record, found 0 (syntax id: $id)(".(is_array($var) ? implode('; ', $var) : $var).')');
        
        return $rec;
    }
    
    public function getRecordWEE(string $id, $var = null){
        $res = $this->squery($id, $var);
        
        if($res !== false){
            if($res->num_rows === 0 || $res->num_rows > 1)
                error("Found ".$res->num_rows." records. Required one. ($id)");

            return $res->fetch_assoc();
        } else
            return false;
    }
    
    public function getRecordsAsArray(string $id, $var = null){
        $res = $this->getRecords($id, $var);
        $arr = [];
        
        if($res !== false)
            while($rec = $res->fetch_assoc())
                $arr[] = $rec;
        
        return $arr;
    }
    
    public function getRecordSE(string $id, $var = null){
        $res = $this->squery($id, $var);

        if($res !== false && $res->num_rows === 1 && $rec = $res->fetch_assoc())
            return $rec;
        else
            return false;
    }
    
    public function getRecords(string $id, $var = null, $callback = null){
        $res = $this->squery($id, $var);
        
        if($this->error != '' || $res === false || $res->num_rows == 0)
            return false;

        if($callback == null)
            return $res;
        else
            while($rec = $res->fetch_assoc())
                $callback($rec);

        return true;
    }
    
    public function getRecordsWEE(string $id, $var = null, $callback = null){
        $res = $this->squery($id, $var);
        
        if($res !== false){
            if($res->num_rows > 0){
                if($callback == null){
                    return $res;
                }else{
                    while($rec = $res->fetch_assoc()){
                        $callback($rec);
                    }
                }

                return true;
            }
            
            return false;
        } else
            error("MySQLi result: FALSE ($id)(".(is_array($var) ? implode('; ', $var) : $var).')');
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
    
    public function getSyntax(string $id, $var = null){
        if(function_exists('query_syntax')){
            $qs = query_syntax($id, $var);
            return $qs === null ? $id : $qs;
        } else
            return $id;
    }

    //--------------------------------------------------------------------------
    public static function open($addr, $login, $pass, $db, &$msg){
        $conn = new ExtendedMySQLi((string)$addr, (string)$login, (string)$pass);

        if ($conn->connect_errno) {
            $msg = "Failed to connect to MySQL: (".$conn->connect_errno. ") ".iconv("windows-1250", "UTF-8", $conn->connect_error)."<br>";
            $conn = null;
        }

        if ($conn && !$conn->set_charset("utf8")) {
            $msg .= "Error loading character set utf8: ".iconv("windows-1250", "UTF-8", $conn->error)."<br>";
            $conn = null;
        }
        
        if ($conn && !$conn->select_db($db)) {
            $msg .= "Error selecting database `$db`: ".iconv("windows-1250", "UTF-8", $conn->error)."<br>";
            $conn = null;
        }

        return $conn;
    }
}

