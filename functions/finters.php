<?php

function fstr($name, $source = INPUT_POST){
    return filter_input($source, $name, FILTER_SANITIZE_STRING);
}

function fstr_null($name, $source = INPUT_POST){
    $s = fstr($name, $source);
    return $s === false ? null : $s;
}

function fint($name, $source = INPUT_POST){
    return filter_input($source, $name,  FILTER_VALIDATE_INT);
}

function fbool($name, $source = INPUT_POST){
    return filter_input($source, $name,  FILTER_VALIDATE_BOOLEAN) ? true : false;
}

function fint_zero($name, $source = INPUT_POST){
    $i = fint($name, $source);
    return $i === null || $i === false ? 0 : $i;
}

function fip($name, $source = INPUT_SERVER){
    return filter_input($source, $name, FILTER_VALIDATE_IP);
}

function farrayStr($name, $source = INPUT_POST){
    return filter_input_array($source, [$name => ['filter' => FILTER_SANITIZE_STRING, 'flags'  => FILTER_REQUIRE_ARRAY]])[$name];
}

function farrayInt($name, $source = INPUT_POST){
    return filter_input_array($source, [$name => ['filter' => FILTER_VALIDATE_INT, 'flags'  => FILTER_REQUIRE_ARRAY]])[$name];
}

function fjson($name, $source = INPUT_POST){
    $data = filter_input($source, $name, FILTER_UNSAFE_RAW);
    
    if($data === false)
        return null;
    
    $dataObject = json_decode($data, true);
    
    if(json_last_error() != JSON_ERROR_NONE)
        return null;
    
    return $dataObject;
}