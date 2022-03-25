<?php

function testBit($num, $bit){
    return ($num & (1 << $bit)) !== 0;
}

function setBit(&$num, $bit){
    $num |= (1 << $bit);
    return $num;
}

function clearBit(&$num, $bit){
    $num &= ~(1 << $bit);
    return $num;
}

function toggleBit(&$num, $bit){
    return testBit($num, $bit) ? clearBit($num, $bit) : setBit($num, $bit);
}