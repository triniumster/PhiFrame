<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */


class TabedPane {
    private $randomKey = null;
    private $tabIndex = 0;
    private $currentIndex = 0;
    private $tabs = [];
    private $displayTabBar = true;
    
    public function __construct(){
        $this->randomKey = randomKey();
    }
    
    public static function script(){ ?>
        <script>
            TabedPane = {
                selectTab: function(tabObj){
                    var panekey = $(tabObj).attr('data-panekey');

                    $('.tab-link-'+panekey).removeClass('current');
                    $(tabObj).addClass('current');
                    $('.tab-content-'+panekey).removeClass('current');

                    if($("#"+$(tabObj).attr('data-contentid')).length > 0){
                        $("#"+$(tabObj).attr('data-contentid')).addClass('current');
                    }else{
                        $("#tab-content-"+panekey+"-udef").html('Loading ...');
                        $("#tab-content-"+panekey+"-udef").addClass('current');

                        Ajax.send({
                            action: 'tabedpanecontent',
                            panekey: panekey,
                            contentid: $(tabObj).attr('data-contentid')
                        }, function(result){
                            $("#tab-content-"+panekey+"-udef").html(result.panecontent);
                        }, function(eid, jqXHR, textStatus){
                            $("#tab-content-"+panekey+"-udef").html("Can't load this content ("+eid+", "+textStatus+", "+jqXHR.responseText+")");
                        });
                    }
                },

                onTabChange: function(tabObj, idx, title){
                    //this function is for overwrite by user for event handle
                },

                refreshCurrent: function(obj){
                    if(typeof obj !== 'undefined'){
                        var panekey = $(obj).attr('data-panekey');
                        TabedPane.selectTab($(".tab-link-"+panekey+".current")[0]);
                    } else {
                        $('.tabs-container').each(function(idx, elm){
                            var panekey = $(elm).attr('data-panekey');
                            TabedPane.selectTab($(".tab-link-"+panekey+".current")[0]);
                        });
                    }
                }
            };
        </script>
    <?php }
    
    public static function style(){ ?>
        <style>
            div.tabs-container {
                width: 100%;
                margin: 0;
                padding: 0;
                position: relative;
            }

            div.tabs-container > ul.tabs {
                margin: 0;
                padding: 0;
                list-style: none;
                overflow: hidden;
                width: 100%;
                position: relative;
                background: none;
                z-index: 2;
            }

            div.tabs-container > ul.tabs > li{
                background-image: linear-gradient(to bottom, #eee, #bbb);  
                z-index: 3;
                border: 1px solid #aaa;
                display: inline-block;
                cursor: pointer;
                float: left;
                margin: 0;
                padding: 1mm 2mm;
                float: left;
                text-shadow: 0 1px 0 rgba(255,255,255,.8);
                border-radius: 1.5mm 1.5mm 0 0;
            }

            div.tabs-container > ul.tabs > li.current {
                background-image: linear-gradient(to bottom, #fff, #f7f7f7);  
                z-index: 4;
                border: 1px solid #777;
                border-bottom: 1px solid #f7f7f7;
            }

            div.tabs-container > .tab-content {
                z-index: 1;
                display: none;
                background: #f7f7f7;
                padding: 2mm;
                border: 1px solid #777;
                position: relative;
                top: -1px;
                border-radius: 0 2mm 2mm 2mm;
                text-align: left
            }

            div.tabs-container > .tab-content.current {
                display: inherit;
            }
        </style>
    <?php }
    
    public static function ajaxServe($callback){
        if(fstr('action') == 'tabedpanecontent'){
            $content = $callback(fstr_null('contentid'), fstr_null('panekey'));
            
            if($content === null || $content === false)
                $rcontent = 'CONTENT NOT FOUND';
            elseif(gettype($content) === 'object')
                $rcontent = $content->__toString();
            else
                $rcontent = (string)$content;
            
            done(['panecontent' => $rcontent]);
        }
    }
    
    public function addTab($title, $content = null, $current = false){       
        $i = $this->tabIndex;
        $dataContentId = "tab-content-$this->randomKey-$i";
        $cdiv = null;
        
        if($current)
            $this->currentIndex = $i;
        
        if(gettype($content) === 'string' && substr($content, 0, 1) == '#'){
            $dataContentId = substr($content, 1);
            $content = null;
        }
        
        $tab = new html('li', ".tab-link-$this->randomKey tab-link");
        $tab->attr('data-contentid', $dataContentId)->attr('data-panekey', "$this->randomKey")->html($title)->onclick('TabedPane.selectTab(this); TabedPane.onTabChange(this,'.$i.',\''.$title.'\')');
        
        if($content != null){
            $cdiv = new html('div', "#tab-content-$this->randomKey-$i; .tab-content-$this->randomKey tab-content");
            $cdiv->append($content);
        }
        
        $this->tabs[$i] = [$tab, $cdiv];
        $this->tabIndex++;
        return $i;
    }
    
    public function displayTabBar($enable = true){
        $this->displayTabBar = $enable ? true : false;
    }
    
    public function __toString(){
        $tabsContainer = new html('div', "#pane-$this->randomKey; .tabs-container");
        $tabsContainer->script()->html("$(function(){TabedPane.refreshCurrent($('#pane-$this->randomKey'));})");
        $tabsContainer->attr('data-panekey', "$this->randomKey");

        $tabsBar = $tabsContainer->ul('.tabs');
        
        if(!$this->displayTabBar)
            $tabsBar->display('none');
        
        $tabsContainer->div("#tab-content-$this->randomKey-udef; .tab-content-$this->randomKey tab-content");
        
        for($i=0; $i<$this->tabIndex; $i++){
            if($this->currentIndex == $i)
                $this->tabs[$i][0]->appendClass('current');
            
            $tabsBar->append($this->tabs[$i][0]);
            
            if($this->tabs[$i][1] != null)
                $tabsContainer->append($this->tabs[$i][1]);  
        }

        return $tabsContainer->__toString();
    }
}