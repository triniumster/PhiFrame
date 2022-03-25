<?php

function randomKey($length = 12, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_'){
    $randomKey = '';
    $max = strlen($chars) - 1;
    
    for($i=0; $i<$length; $i++)
        $randomKey .= $chars[mt_rand(0, $max)];
    
    return $randomKey;
}