<?php

function nbsp($string){
    return preg_replace('/\s/', "&nbsp;", $string);
}