<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class ScreenDialog extends PhiFrameWidget {
    
    protected function __code(){ 
        ?>
        <div id="phiframe-screenlock" onclick='ScreenDialog.onClickUnlock(event)' style="display: none; position: absolute; z-index: 999; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8)">
            <script>
                ScreenDialog = {
                    lockScreenTimeout: 0,
                    lockScreenTimeFadeOut: 0,
                    manualUnlock: false
                };
                
                ScreenDialog.lockScreen = function(text, time, tfin, tfout){
                    ScreenDialog.manualUnlock = false;
                    ScreenDialog.lockScreenTimeout = !isNaN(time) && time > 0 ? Date.now()+time : 0;
                    ScreenDialog.lockScreenTimeFadeOut = !isNaN(tfout) && tfout > 0 ? tfout : 75;
                    $('#phiframe-screenlock-content').html(typeof text !== 'undefined' && text !== null ? text : '');
                    $('#phiframe-screenlock').fadeIn(!isNaN(tfin) && tfin > 0 ? tfin : 150);
                };
                
                ScreenDialog.unlockScreen = function(withoutWait){
                    if(withoutWait === true){
                        ScreenDialog.unlockScreenFadeOut();
                        return;
                    }
                    
                    var t = ScreenDialog.lockScreenTimeout - Date.now();
                    
                    if(t > 0)
                        setTimeout(ScreenDialog.unlockScreenFadeOut, t);
                    else
                        ScreenDialog.unlockScreenFadeOut();
                };
                
                ScreenDialog.unlockScreenFadeOut = function(){
                    $('#phiframe-screenlock').fadeOut(ScreenDialog.lockScreenTimeFadeOut);
                    $('#phiframe-screenlock-content').empty();
                    ScreenDialog.lockScreenTimeout = 0;
                    ScreenDialog.lockScreenTimeFadeOut = 0;
                };
                
                ScreenDialog.onClickUnlock = function(e){
                    if(ScreenDialog.manualUnlock && !document.getElementById('phiframe-screenlock-content').contains(e.target))
                        ScreenDialog.unlockScreenFadeOut();
                };
            </script>
            <center id="phiframe-screenlock-content" style="font-size: 2rem; margin-top: 15mm; color: white"></center>
        </div>
        <?php
    }
}




