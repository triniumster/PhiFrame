<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any waranty 
 * 
 */

class Footbar extends PhiFrameWidget {

    protected function __code(){            
        ?>
        <div id="phiframe-footbar" <?php  echo "class='".BROWSER."'"; ?>>
            <p class="ip"><?php echo filter_var(filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_URL), FILTER_VALIDATE_IP); ?></p>
            <p class="license"><?php echo $this->cfg('licenseTitle'); ?></p>
            <p class="author"><?php echo $this->cfg('authorSign'); ?></p>
        </div>
        <?php
    }
}