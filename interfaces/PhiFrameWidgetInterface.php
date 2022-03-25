<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

interface PhiFrameWidgetInterface {

    public function __construct(&$configuration = [], &$mySqlConnection = null);
    public function echoWidget();
    public function setCommand(string $cmd);
    public function setIsLogin(bool $enable);
    public function initWidget(&$globalConfig, &$mySqlConnection);
    public function updateConfig($newCfg);
    public function __get($vname);
}