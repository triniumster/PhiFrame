<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

class PageSlidesNavigator {
    public static function htmlNode($idx = 'all'){
        $node = new HTML('div');
        $node->button()->html('&laquo;')->width('5mm')->onclick("PageSlidesNavigator.gotopage(1,'$idx')");
        $node->button()->html('&lt;')->width('6mm')->onclick("PageSlidesNavigator.gotopage(--PageSlidesNavigator.page['$idx'],'$idx')");
        $node->input("#pageslidesnavigator_naviinfo_$idx")->style('width: 3cm; text-align: center')->onfocus('$(this).val(\'\').select()')->onclick('$(this).val(\'\').select()')->onblur("$(this).val(PageSlidesNavigator.page['$idx'] + ' / ' + ++PageSlidesNavigator.tpage['$idx'])")->onkeydown("PageSlidesNavigator.filterkey($(this), event, '$idx')");
        $node->button()->html('&gt;')->width('6mm')->onclick("PageSlidesNavigator.gotopage(++PageSlidesNavigator.page['$idx'],'$idx')");
        $node->button()->html('&raquo;')->width('5mm')->onclick("PageSlidesNavigator.gotopage(-1,'$idx')");
        return $node;     
    }

    public static function script(){?>
        <script>
            PageSlidesNavigator = {};
            
            PageSlidesNavigator.page = [];
            PageSlidesNavigator.tpage = [];
            
            PageSlidesNavigator.gotopage = function(pg, idx){
                PageSlidesNavigator.page[idx] = pg;
                PageSlidesNavigator.tpage[idx] = 0;
                PageSlidesNavigator.onPageSlideChange(idx);
            };
            
            PageSlidesNavigator.filterkey = function(obj, event, idx){
                if(event.keyCode === 13){
                    PageSlidesNavigator.gotopage(obj.val(), idx);
                }else if((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105)){
                    event.preventDefault();
                }
            };
            
            PageSlidesNavigator.update = function (current, total, idx){
                PageSlidesNavigator.page[idx] = current;
                PageSlidesNavigator.tpage[idx] = total;
                $('#pageslidesnavigator_naviinfo_'+idx).val(PageSlidesNavigator.page[idx]+' / '+PageSlidesNavigator.tpage[idx]);
            };
            
            PageSlidesNavigator.onPageSlideChange = function(idx){
                //this function is for overwrite by user for event handle
            };
        </script>
    <?php }
}