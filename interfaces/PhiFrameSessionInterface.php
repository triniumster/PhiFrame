<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

interface PhiFrameSessionInterface {
    public function __construct($onfiguration, $sname);
    public function regenerateId();
    public function setLifeTime(int $time);
    public function is($vname, $val);
    public function set($vname, $val);
    public function get($vname);
    public function destroy();
    public function __call($vname, $args);
    public function __set($vname, $value);
    public function __get($vname);
}
