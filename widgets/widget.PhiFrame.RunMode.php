<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class RunMode extends PhiFrameWidget {
    
    protected function __code(){
        if(!in_array(RUN_MODE, [RUN_MODE_DEV, RUN_MODE_TEST]))
            return;
        
        ?>
        <div id='run_mode_name'><?php echo RUN_MODE; ?></div>
        <script>
            var run_mode_name_object = $('#run_mode_name');
            $('body').mousemove(function(event){
                if(event.pageX < 0.25 * $(window).width()){
                    run_mode_name_object.css('left', 'auto');
                    run_mode_name_object.css('right', '1mm');
                } else {
                    run_mode_name_object.css('right', 'auto');
                    run_mode_name_object.css('left', '1mm');
                }
            });
        </script>
        <?php
    }
}




