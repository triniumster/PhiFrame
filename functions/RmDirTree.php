<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 * @param $directory (String) Directory that will be deleted (If contain any files, its will be deleted also)
 * 
 */

function rmDirTree($directory){
    if(is_dir($directory)){
        foreach(scandir($directory) as $file){
            if(!in_array($file, ['.', '..'])){
                if(is_dir($file)){
                    rmDirTree("$directory/$file");
                } else {
                    unlink("$directory/$file");
                }
            } 
        }

        rmdir($directory);
    }
}   



