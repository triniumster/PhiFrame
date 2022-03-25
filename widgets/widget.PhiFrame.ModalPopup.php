<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

class ModalPopup extends PhiFrameWidget {

    protected function __code(){ 
        ?>
        <div id="phiframe-modalPopup" style="display: none" onclick="hide_phiframeModalPopup(event)">
            <script>
                var phiFrame_modalPopup_contentId = null;
                var phiFrame_modalPopup_titled = false;
                

                
                $.prototype.dialog = function(config, element){
                    var backKey = this.attr('backKey');

                    if(config && typeof config === 'string'){
                        switch(config){
                            case 'close':
                                $('#'+temp[backKey][0]).html(temp[backKey][1]);
                                temp[backKey][2].remove();
                                temp[backKey] = null;
                                temp['dialogcount']--;
                                return;
                            case 'enable':
                                $("input[value='"+element+"'][backkey='"+backKey+"']").attr('disabled', false);
                                return;
                        }
                    }
                    
                    if(typeof temp === 'undefined')
                        temp = [];
                    
                    if(typeof backKey === 'undefined')
                        do {
                            backKey = randomKey(14);
                        } while(typeof temp[backKey] !== 'undefined');

                    temp[backKey] = [];
                    
                    if(typeof temp['dialogcount'] === 'undefined' || temp['dialogcount'] < 1)
                        temp['dialogcount'] = 1;
                    else
                        temp['dialogcount']++;
                    
                    this.attr('backKey', backKey);
                    
                    var dialogBox = $("<div>", {style: "position: absolute; border: 2px solid #555; border-radius: 2mm; background-color: white; min-width: 3cm; max-width: 80%; min-height: 3cm; max-height: 80%; padding: 3mm"});

                    if(isprop(config, 'title')){
                        var dialogTitle = $("<center>", {html: $("<div>", {html: config.title.trim().replace(/\s/g, '&nbsp;'), style: "width: calc(100% - 6mm); text-align: center; padding: 3mm; border-bottom: 1px solid #555; font-weight: bold; margin-bottom: 3mm"})});
                        dialogBox.append(dialogTitle);
                    }
                    
                    temp[backKey][0] = this.attr('id');
                    temp[backKey][1] = this.html();
                    
                    dialogBox.append(this.html());
                    this.empty();
                    
                    if(isprop(config, 'buttons') && typeof config.buttons === 'object'){
                        var dialogButtons = $("<center>", {html: $("<div>", {style: "width: calc(100% - 6mm); text-align: right; padding: 3mm; border-top: 1px solid #555; margin-top: 3mm"})});

                        Object.keys(config.buttons).forEach(function(btn){
                            var vbtn = config.buttons[btn];
                            var newBtn = $('<input>', {type: 'button', value: btn, backkey: backKey, style: "float: right; font-size: 1.1em; padding: 1mm; min-width: 2cm"});
                            
                            if(typeof vbtn === 'object'){
                                if(isprop(vbtn, 'action')){
                                    if(typeof vbtn.action === 'string' && vbtn.action === 'close')
                                        newBtn.on('click', function(){
                                            $(this).dialog('close');
                                        });
                                    else if(typeof vbtn.action === 'function')
                                        newBtn.on('click', vbtn.action);
                                }
                                
                                if(isprop(vbtn, 'disabled') && vbtn.disabled)
                                    newBtn.attr('disabled', true);

                            }else if(typeof vbtn === 'function'){
                                newBtn.on('click', vbtn);
                            }else if(typeof vbtn === 'string'){
                                if(vbtn === 'close'){
                                    newBtn.on('click', function(){
                                        $(this).dialog('close');
                                    });
                                }
                            }
                            
                            dialogButtons.append(newBtn);
                        });

                        dialogBox.append(dialogButtons);
                    }

                    var dialogContainer = $("<div>", {
                        style: "width: 100%; height: 100%; background-color: rgba(0,0,0,0.35); position: fixed; z-index: "+(9999+temp['dialogcount'])+"; top: 0; bottom: 0; visibility: hidden;",
                        html: dialogBox
                    });

                    $('body').append(dialogContainer);

                    dialogBox.css('left', 'calc(50% - '+(dialogBox.outerWidth()/2)+'px)');
                    dialogBox.css('top', 'calc(50% - '+(dialogBox.outerHeight()/2)+'px)');

                    dialogContainer.css('visibility', 'visible');
                    
                    temp[backKey][2] = dialogContainer;
                };
                
                
                function showDialog(id){
                    show_phiframeModalPopup(id);
                }
                
                function showContentDialog(content){
                    show_phiframeModalPopup(null, null, content);
                }
                
                function openContentDialog(content){
                    show_phiframeModalPopup(null, null, content);
                }
                
                function openDialog(id, title){
                    show_phiframeModalPopup(id, title);
                }

                function show_phiframeModalPopup(id, title, content) {
                    reset_phiframeModalPopup();
                    
                    if($(id).length > 0){
                        phiFrame_modalPopup_contentId = id;
                        
                        if(typeof title === 'undefined'){
                            $("#phiframe-modalPopup-content").html($(id).html());
                            phiFrame_modalPopup_titled = false;
                        } else {
                            $("#phiframe-modalPopup-content").html($('#phiframe-modalPopup-titled').html());
                            $("#phiframe-popup-content-title").html(title);
                            $("#phiframe-popup-content-content").html($(id).html());
                            phiFrame_modalPopup_titled = true;
                        }
                        
                        
                        $(id).empty();
                    } else {
                        if(content){
                            $("#phiframe-modalPopup-content").html(content);
                            phiFrame_modalPopup_titled = false;
                        } else 
                            $("#phiframe-modalPopup-content").html('Dialog with id '+id+' not exist.');
                    }
                    
                    $("#phiframe-modalPopup").css('bottom', '0');
                    $("#phiframe-modalPopup").show();
                    var ba = $(window).height() - $("#phiframe-modalPopup-content").height();
                    $("#phiframe-modalPopup").hide();
                    $("#phiframe-modalPopup").css('top', '0');
                    
                    if(ba > 0){
                        $("#phiframe-modalPopup-content").css('margin-top', (ba/2)+'px');
                        $("#phiframe-modalPopup").css('position', 'fixed');
                    } else {
                        $("#phiframe-modalPopup-content").css('margin-top', 0);
                        $("#phiframe-modalPopup").css('position', 'absolute');  
                    }
                    
                    $("#phiframe-modalPopup-content").css('margin-top', ba > 0 ? (ba/2)+'px' : 0);
                    forAllChildrensEnableGrayscale('#phiframe-wrapper', true);
                    $("#phiframe-modalPopup").removeClass('phiframe-grayscale');
                    $("#phiframe-modalPopup").show();
                }

                function hideDialog(){
                    hide_phiframeModalPopup(null);
                }

                function closeDialog(){
                    hide_phiframeModalPopup(null);
                }

                function hide_phiframeModalPopup(e){
                    if (e !== null) {
                        var c = $("#phiframe-modalPopup");

                        if (c.is(e.target) && c.has(e.target).length === 0) {
                            reset_phiframeModalPopup();
                        }
                    } else {
                        reset_phiframeModalPopup();
                    }
                }
                
                function reset_phiframeModalPopup(){
                    forAllChildrensEnableGrayscale('#phiframe-wrapper', false);
                    $("#phiframe-modalPopup").hide();
                    
                    $("#phiframe-modalPopup").find('input').each(function(idx, obj){
                        $(obj).removeClass('fieldError');
                    });
                    
                    if(phiFrame_modalPopup_contentId !== null){
                        $('.deleteOnExit').remove();
                        
                        if(phiFrame_modalPopup_titled){
                            $(phiFrame_modalPopup_contentId).html($("#phiframe-popup-content-content").html());
                        } else {
                            $(phiFrame_modalPopup_contentId).html($("#phiframe-modalPopup-content").html());
                        }
                        
                        $("#phiframe-modalPopup-content").empty();
                        $("#phiframe-popup-content-title").empty();
                        $("#phiframe-popup-content-content").empty();
                        
                        phiFrame_modalPopup_contentId = null;
                    } else {
                        $("#phiframe-modalPopup-content").empty();
                    }
                }
                
                function forAllChildrensEnableGrayscale(id, enable, withouts){
                    $(id).children().each(function(index, obj){
                        if(enable){
                            $(obj).addClass('phiframe-grayscale');
                        } else {
                            $(obj).removeClass('phiframe-grayscale');
                        }
                    });
                }
                
                function setInfoField(text){
                    $('.infoField').each(function(idx, obj){
                        $(obj).html(text);
                    });
                }
                
                function addToInfoField(text){
                    $('.infoField').each(function(idx, obj){
                        $(obj).html($(obj).html()+text);
                    });
                }
                
                function showDialogOK(text){
                    $('#phiframe-popup-message').html(text);
                    $('#phiframe-popup-buttonOK').css('display', 'inline-block');
                    $('#phiframe-popup-buttonYES').css('display', 'none');
                    $('#phiframe-popup-buttonNO').css('display', 'none');
                    showDialog('#phiframe-modalPopup-alert');

                }
                
                function showDialogYesNo(text, fnName){
                    showDialog('#phiframe-modalPopup-alert');
                    $('#phiframe-popup-message').html(text);
                    $('#phiframe-popup-buttonOK').css('display', 'none');
                    $('#phiframe-popup-buttonYES').css('display', 'inline-block');
                    $('#phiframe-popup-buttonNO').css('display', 'inline-block');
                    $('#phiframe-popup-buttonYES').on('click', function(){
                        closeDialog();
                        eval(fnName+'()');
                    });
                }
            </script>
            
            <center id="phiframe-modalPopup-content"></center>
            <div id="phiframe-modalPopup-titled" style="display: none">
                <div class="phiframe-popup-content-frame">
                    <center>
                        <div id="phiframe-popup-content-title"></div>
                    </center>
                    <center id="phiframe-popup-content-content"></center>
                </div>
            </div>
            <div id="phiframe-modalPopup-alert" style="display: none">
                <div class="phiframe-popup-content-frame">
                    <br>
                    <b id="phiframe-popup-message"></b><br>
                    <br>
                    <input type="button" value="OK" id="phiframe-popup-buttonOK" onclick="closeDialog()" style="width: 1cm">
                    <input type="button" value="TAK" id="phiframe-popup-buttonYES" style="width: 1cm">
                    <input type="button" value="NIE" id="phiframe-popup-buttonNO" onclick="closeDialog()" style="width: 1cm">
                </div>
            </div>
        </div>
        <?php
    }
}




