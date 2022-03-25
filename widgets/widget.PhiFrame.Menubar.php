<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any waranty 
 * 
 */

class Menubar extends PhiFrameWidget {
    private $cmdCfg;
    
    protected function __init(&$globalConfig, &$mySqlConnection) {
        if(array_key_exists('cmd', $globalConfig))
            $this->cmdCfg = $globalConfig['cmd'];
    }
    
    protected function __code(){          
        ?>
        <div id="phiframe-menubar" <?php  echo "class='".BROWSER."'"; ?>>
            <?php 
            $this->sublink($this->configuration, $this->isLogin, 'main-sub');
            ?>
        </div>
        <?php
    }
    
    protected function sublink($cfgs, $isLogIn, $subclass = 'next-sub'){
        echo "<ul>";

        foreach($cfgs as $linkCfg){
            $get = array_key_exists("get", $linkCfg) ? '&'.$linkCfg['get'] : '';

            if(array_key_exists("login", $linkCfg) && $linkCfg["login"]=="yes" && !$isLogIn)
                continue;
            
            if(array_key_exists(privilege, $linkCfg) && !comparePrivileges($this->userPrivileges, $linkCfg[privilege]))
                continue;

            if(array_key_exists("onclick", $linkCfg)){
                echo "<li><a href='#' onclick=\"".$linkCfg["onclick"]."\">".nbsp($linkCfg["title"])."</a></li>";
            } elseif(array_key_exists("link", $linkCfg)){
                str_replace('&', '?', $get);
                echo "<li><a href='".$linkCfg["link"]."$get'>".nbsp($linkCfg["title"])."</a></li>";
            } elseif(array_key_exists("list", $linkCfg)) {
                echo "<li class='$subclass'><a href='#'>".nbsp($linkCfg["title"])."</a>";
                $this->sublink($linkCfg['list'], $isLogIn);
                echo "</li>";
            } elseif(array_key_exists("cmd", $linkCfg)) {
                $neededPrivileges = 0;

                if($this->cmdCfg && array_key_exists($linkCfg['cmd'], $this->cmdCfg) && array_key_exists(privilege, $this->cmdCfg[$linkCfg['cmd']]))
                    $neededPrivileges = $this->cmdCfg[$linkCfg['cmd']][privilege];

                if($this->hasPrivilege($neededPrivileges)){
                    echo "<li><a href='index.php?cmd=".$linkCfg["cmd"]."$get'>".nbsp($linkCfg["title"])."</a></li>";
                }
            }
        }

        echo "</ul>";
    }
    
}


        

