<?php

function formatToTwoSignifcant($num, $min = 2){
    if($num < 1){
        $pos = -1;
        $x = 0;

        while($x == 0){
            $pos++;
            $x = floor($num * pow(10, $pos));
        }

        $pos++;

        if($pos < 2){
            $pos = $min;
        }
    } else {
        $pos = $min;
    }

    return number_format((float)$num, $pos, '.', '');
}