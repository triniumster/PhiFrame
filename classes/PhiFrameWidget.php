<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

class PhiFrameWidget implements PhiFrameWidgetInterface {
    protected $configuration;
    protected $isLogin = false;
    protected $browser = BROWSER;
    protected $command;
    protected $mainclass;
    protected $userPrivileges;
    
    public function __construct(&$config = [], &$dbconn = null) {
        $this->configuration = $config;
    }

    public function echoWidget() {
        $this->__code();
    }
    
    public function initWidget(&$globalConfig = [], &$mySqlConnection = null){
        $this->userPrivileges = $globalConfig['userPrivileges'];
        $this->isLogin = array_key_exists('islogin', $globalConfig) && $globalConfig['islogin'] === true; 
        $this->__init($globalConfig, $mySqlConnection);
    }
    
    protected function __init(&$globalConfig, &$mySqlConnection){}
    
    protected function __code(){}
    

    
    protected function cfg($key){
        return $this->getCfg($key);
    }
    
    protected function getCfg($key){
        if(array_key_exists($key, $this->configuration)){
            return $this->configuration[$key];
        }
        
        return null;
    }
    
    public function setCommand(string $cmd){
        $this->command = $cmd;
    }
    
    public function setIsLogin(bool $enable){
        $this->isLogin = $enable;
    }

    public function updateConfig($newCfg) {
        $this->configuration = $newCfg;
    }
    
    protected function hasPrivilege($privilegeIndex){
        return ($this->userPrivileges & (1 << $privilegeIndex)) !== 0;
    }
    
    public function __get($name){
        return $this->getCfg($name);
    }

}