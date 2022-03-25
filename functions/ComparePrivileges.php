<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

function comparePrivileges($userPrivileges, $neededPrivileges) {
    if($neededPrivileges <= 0)
        return true;
    
    if ($userPrivileges <= 0)
        return false;
    
    $np = pow(2, $neededPrivileges);
    return ($userPrivileges & $np) == $np;
}