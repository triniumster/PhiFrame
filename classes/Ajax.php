<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

class Ajax {   
    public static function style(){
        ?>
            <style type="text/css">
                @keyframes right-rotate {0% {transform: rotate(0)} 100% {transform: rotate(360deg)}}
                @keyframes left-rotate {0% {transform: rotate(0)} 100% {transform: rotate(-360deg)}}
                
                .loading-rings {
                    position: relative;
                    width: 58px;
                    height: 58px;
                    display: inline-block
                }
                
                .loading-rings-container {
                    position: relative;
                    width: 142px;
                    height: 142px;
                    transform: translate(-60px, -60px) scale(0.3) translate(60px, 60px);
                }

                .loading-rings-container div {
                    position: absolute;
                    border-radius: 50%;
                    border-style: solid;
                }

                .loading-rings-container div:nth-child(1) {
                    top: 0;
                    left: 0;
                    width: 120px;
                    height: 120px;
                    border-width: 10px;
                    border-color: #000 transparent #000 transparent;
                    animation: right-rotate 1.3s linear infinite;
                }

                .loading-rings-container div:nth-child(2) {
                    top: 14px;
                    left: 14px;
                    width: 96px;
                    height: 96px;
                    border-width: 8px;
                    border-color: #040 transparent transparent #040;
                    animation: left-rotate 1.2s linear infinite;
                }

                .loading-rings-container div:nth-child(3) {
                    top: 26px;
                    left: 26px;
                    width: 76px;
                    height: 76px;
                    border-width: 6px;
                    border-color: transparent #080 transparent  #080;
                    animation: right-rotate 1.1s linear infinite;
                }

                .loading-rings-container div:nth-child(4) {
                    top: 36px;
                    left: 36px;
                    width: 60px;
                    height: 60px;
                    border-width: 4px;
                    border-color: transparent #0a0 #0a0 transparent;
                    animation: left-rotate 1.0s linear infinite;
                }

                .loading-rings-container div:nth-child(5) {
                    top: 44px;
                    left: 44px;
                    width: 48px;
                    height: 48px;
                    border-width: 2px;
                    border-color: #0b0 #0b0 #0b0 transparent;
                    animation: right-rotate 0.9s linear infinite;
                }
            </style>
        <?php
    }
            
    public static function script($cmd = null){
        if($cmd == null)
            $cmd = fstr('cmd', INPUT_GET);
        
        ?>
        <script>
            <?php echo "Ajax = {cmd: '$cmd', tcmd: '$cmd', lcc: null, sync: false, osync: false, saving: false};"; ?>
            
            Ajax.setLoadingContentContainer = function(id){
                Ajax.lcc = $(id);
            };
            
            Ajax.onceCmd = function(cmd){
                Ajax.tcmd = cmd;
            };
            
            Ajax.onceSync = function(){
                Ajax.osync = true;
            };
            
            Ajax.sendSaving = function(dataObj, onDoneFn, onFailFn){
                Ajax.saving = true;
                Ajax.send(dataObj, onDoneFn, onFailFn);
            };
            
            Ajax.send = function(dataObj, onDoneFn, onFailFn){
                if(Ajax.saving)
                    ($('#ajaxSavingDialog')).fadeIn(250);
                
                if(Ajax.lcc !== null)
                    Ajax.lcc.html(Ajax.loadingContent());

                var opt = {
                    cache: false,
                    method: "POST",
                    url: "index.php?cmd="+Ajax.tcmd,
                    async: !(Ajax.sync || Ajax.osync)
                };
                            
                if(dataObj instanceof FormData){
                    dataObj.append('ajax', true);
                    opt.contentType = false;
                    opt.processData = false;
                } else {
                    dataObj.ajax = true;
                }
                
                opt.data = dataObj;
                
                $.ajax(opt).done(function(result){
                    if(Ajax.saving)
                        ($('#ajaxSavingDialog')).fadeOut(250);
                    
                    if(Ajax.lcc !== null)
                        Ajax.lcc.html('');
                    
                    if(typeof result !== 'object' || (result.hasOwnProperty('error') && result.error)){
                        if(typeof onFailFn === "function")
                            onFailFn(1, result);
                        else if(typeof result !== 'object')
                            Ajax.showDialog('', result);
                        else
                            Ajax.showDialog('', result.error);
                        
                        return;
                    }
                    
                    if(result.hasOwnProperty('message')){
                        alert(result.message);
                        return;
                    }
                    
                    if (typeof onDoneFn === "function")
                        onDoneFn(result);
                }).fail(function(jqXHR, textStatus) {
                    if(Ajax.saving)
                        ($('#ajaxSavingDialog')).fadeOut(250);
                    
                    if(Ajax.lcc !== null)
                        Ajax.lcc.html('');
                        
                    if(typeof onFailFn === "function")
                        onFailFn(0, jqXHR, textStatus);
                    else
                        Ajax.showDialog(textStatus, jqXHR.responseText);
                });
                
                Ajax.tcmd = Ajax.cmd;
                Ajax.saving = false;
                Ajax.osync = false;
            };
            
            Ajax.loadingContent = function(){
                return "<div class='loading-rings'><div class='loading-rings-container'><div></div><div></div><div></div><div></div><div></div></div></div>";
            };
            
            Ajax.showDialog = function(title, message){
                if(typeof openContentDialog === "function"){
                    var w = window.innerWidth*0.9;
                    var h = window.innerHeight*0.9;
                    openContentDialog("<div style='width: "+w+"px; height: "+h+"px; background: white; border: 2px solid black; overflow: auto'>AJAX: "+title+"<br>"+message+"</div>");
                } else
                    alert("AJAX: "+title+": "+message);
            };
        </script>
    <?php }
    
    public static function content(){
        $html = new html('div', '#ajaxSavingDialog');
        $html->style('width: 100%; height: 100%; position: absolute; top: 0; left: 0; display: none; background: black; color: white');
        $html->html('Zapisywanie');
        echo (string)$html;
    }
}