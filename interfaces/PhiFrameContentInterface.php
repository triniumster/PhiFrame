<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

interface PhiFrameContentInterface {
    public function __construct();
    public function __init();
    public function title();
    public function plain();
    public function tabTitle();
    public function updateMessage(&$message);
    public function setMySqlConnection($connection);
    public function setCfg($cfg);
    public function loadScripts();
    public function loadStyles();
    public function runAjax();
}
