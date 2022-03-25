<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

class Titlebar extends PhiFrameWidget {

    protected function __code(){
        if(!empty($this->cfg('title'))){
            $html = new html('div', '#phiframe-titlebar; .'.BROWSER);
            $html->p()->html($this->cfg('title'));
            echo (string)$html;
        }
    }
}