<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class PhpSession implements PhiFrameSessionInterface {
 
    public function __construct($config = null, $name = 'PhiFrameSession'){
        session_name($name);
        session_start();
    }
    
    public function regenerateId(){
        session_regenerate_id();
        
        if(isset($_SESSION['phiframe_session_rememberme']) && $_SESSION['phiframe_session_rememberme'] > 0){
            $params = session_get_cookie_params();
            setcookie(session_name(), fstr(session_name(), INPUT_COOKIE), time() + $_SESSION['phiframe_session_rememberme'], $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
    }
    
    public function setLifeTime(int $mem){
        $_SESSION['phiframe_session_rememberme'] = $mem > 0 ? $mem : 0;
    }

    public function destroy() {
        session_destroy();
        session_unset();
        
    }

    public function is($name, $val = null) {
        return isset($_SESSION[$name]) ? $val == null || $_SESSION[$name] == $val : false;
    }

    public function set($name, $val) {
        if($val == null)
            unset($_SESSION[$name]);    
        else if($name != null && trim($name) != '')
            $_SESSION[$name] = $val;
    }

    public function get($name) {
        return $this->is($name) ? $_SESSION[$name] : null;
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