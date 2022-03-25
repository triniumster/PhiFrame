<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any waranty 
 * 
 */

class Messagebar extends PhiFrameWidget {
    
    protected function __code(){       
        if(array_key_exists('message', $this->configuration) && $this->configuration['message'] != ""){
            ?>
            <div id='phiframe-messagebar'>
                <?php echo $this->configuration['message']; ?>
            </div>
            <?php
        }
    }
}


        

