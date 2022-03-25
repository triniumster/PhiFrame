<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

define('separator', 'menuSeparator');

class VerticalCollapsedMenu {
    const MENU_SEPARATOR = 'MENU_SEPARATOR';
    
    public static function htmlNode($sc, $cc, $structure) {
        $rnd = randomKey();
        
        if(is_array($structure) && sizeof($structure) > 0){
            $ul = new html('ul', '.vertical-collapsed-menu');
            $ul->script()->html("$(function(){\$(\".current[data-key='$rnd']\").click()})");
            $fr = ' current';
            $prv = false;

            foreach($structure as $key => $st) {
                $isSep = $st === self::MENU_SEPARATOR;
                
                if($prv && $isSep)
                    continue;
                
                if($isSep){
                    $ul->li('.separator');
                    $prv = true;
                } else {
                    $ul->li('.link L'.$rnd.$fr)->html($st[0])->onclick("VerticalCollapsedMenu.getContent('$cc', '$sc', '".$key."', this);")->attr('data-key', $rnd);
                    $fr = '';
                    $prv = false;
                }
            }  
        
            return $ul;
        } else {
           return null;
        }
    }
    
    public static function script(){?>
        <script>
            VerticalCollapsedMenu = {};

            VerticalCollapsedMenu.getContent = function(obj, cmd, contentId, btn){
                Ajax.send({action: 'PhiFrameVerticalCollapsedMenu', content: contentId, cmd: cmd}, function(result){
                    $(obj).html(result.html);
                    
                    if(typeof btn !== 'undefined'){
                        var obtn = $(btn);
                        $('.L'+obtn.attr('data-key')).removeClass('current');
                        obtn.addClass('current');
                    }
                });
            };
        </script>
    <?php }
    
    public static function ajaxServe($callback){
        if(fstr('action') == 'PhiFrameVerticalCollapsedMenu')
            done(['html' => (string)$callback(fstr_null('content'))]);
    }
}