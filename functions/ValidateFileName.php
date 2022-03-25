<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */   

function validateFileName($name){
    return preg_replace('/[\/\\\:\*\?\"\<\>\|]/', '-', $name);
}