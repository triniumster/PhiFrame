<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

class PhiFrameContent implements PhiFrameContentInterface {
    protected $title = '';
    protected $tabTitle = '';
    protected $mysql;
    protected $mssql;
    protected $conn;
    protected $wconn;
    protected $message = '';
    protected $browser = BROWSER;
    protected $plain = false;
    protected $style = '';
    protected $script = '';
    protected $userPrivileges = 0;
    protected $userId = 0;
    protected $userFullName = '';
    protected $cfg;
    protected $requireClasses = null;
    protected $queryKey = null;
    protected $buttonImagePath = "";
    //protected $ajax_cmds = null;

    public function __construct(){}
    
    public function getButtonImagePath(){
        return $this->buttonImagePath;
    }
    
    public function __kwerenda(){}
    
    public function __init(){}
    
    public function runAjax(){
        $cmd = fstr('action');

        if(preg_match('/^[a-zA-Z_]+\w*$/', $cmd)){      
            if(method_exists($this, $cmd."_ajax")){
                if($this->{$cmd."_ajax"}() === true && method_exists($this, $cmd)){
                    if($this->{$cmd}() !== false) return;
                } elseif($this->{$cmd."_ajax"}() === false){
                    return;
                }
            }
            /*    
            if(is_array($this->ajax_cmds)){
                if(array_key_exists($cmd, $this->ajax_cmds)){
                    if(method_exists($this, $this->ajax_cmds[$cmd]) && $this->{$this->ajax_cmds[$cmd]}() !== false) return;
                } else if(in_array($cmd, $this->ajax_cmds)){
                    if(method_exists($this, $cmd) && $this->{$cmd}() !== false) return;
                }        
            }
             */
        }

        $this->__ajax($cmd);
    }
    
    protected function __ajax(){}
    
    public function plain(){
        return $this->plain;
    }
    
    public function title(){
        return $this->title;
    }
    
    public function tabTitle(){
        return $this->tabTitle;
    }
    
    public function setMySqlConnection($conn){
        if(is_array($conn)){
            switch(sizeof($conn)){
                case 2:
                    $this->mssql = $conn[1];
                    $this->wconn = $conn[1]; 
                
                case 1:
                    $this->mysql = $conn[0];
                    $this->conn = $conn[0];
            }
            
            return;
        }
            
        $this->mysql = $conn;
        $this->conn = $conn;
    }

    public function __content(){}
    
    public function loadScripts(){
        $a = $this->__export_constants_for_js();
        
        if ($a != null){
            if (is_array($a) && sizeof($a) > 0){
                echo "<script>";
                
                foreach($a as $k => $v)
                    echo "\nconst $k = $v;"; 

                echo "</script>";
            } else
                die('Export constants for JS definitions is not array'); 
        }

        $this->__script();
        return $this->script;
    }
    
    protected function __export_constants_for_js(){}
    
    protected function __script(){
        //<script> java script code </script>
    }
    
    public function loadStyles(){
        $this->__style();
        return $this->style;
    }

    protected function __style(){
        //<style> style sheet code <style>
    }

    public function updateMessage(&$message) {
        $message .= $this->message;
        return $message;
    }
    
    public function setPrivileges($userPrivileges){
        $this->userPrivileges = $userPrivileges;
    }
    
    public function setUserID($id){
        $this->userId = $id;
    }
    
    public function setUserFullName($name){
        $this->userFullName = $name;
    }
    
    public function setCfg($cfg){
        $this->cfg = $cfg;
    }
    
    protected function hasPrivilege($privilegeIndex){
        return ($this->userPrivileges & (1 << $privilegeIndex)) !== 0;
    }
    
    
}
