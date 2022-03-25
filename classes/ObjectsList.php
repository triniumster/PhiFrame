<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class ObjectsList {
    private $list = [];
    private $formList = [];
    private $width = null;
    private $epn = false;
    private $enableDeleteObject = false;
    private $enableListFilter = true;
    private $enableObjectEdit = true;
    private $enableNameColumn = true;
    private $infoColumn = false;
    private $dbTableName = null; 
    private $sortColumnName = 'name';
    private $fields = [];
    
    public $type = '';
    public $title = '';
    public $page = 1;
    public $lastpage = 1;
    public $pagesize = 100;
    public $useUnit = false;
    public $extraCollumns = [];
    public $bar;
    
    
    public static function style(){?>
        <style>
            img.os_ico_1em {
                height: 1em;
                left: 0.28em;
                top: 0.27em;
                position: absolute
            }
            
            img.os_ico_2em {
                height: 1.2em;
                left: 0.17em;
                top: 0.17em;
                position: absolute
            }
            
            button.os_ico {
                position: relative;
                width: 1.7em;
                text-align: center;
            }
            
            .filterform {
                border-radius: 1mm;
                border: 1px solid black;
                background: #f7f7f7;
                box-shadow: 0px 0px 7px 3px rgba(0,0,0,0.6);
                position: absolute;
                padding: 1mm
            }
        </style>
    <?php }
    
    public static function script(){?>
        <script>
            ObjectsList = {};

            ObjectsList.filterkey = function(obj, event, object_list_index){
                if(event.keyCode === 13){
                    ObjectsList.gotopage(obj.val(), object_list_index);
                }else if((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105)){
                    event.preventDefault();
                }
            };
            
            ObjectsList.gotopage = function(page, objectIndex){
                ObjectsList.runFilter(objectIndex, page);
            };
            
            ObjectsList.onPageChange = function(object_list_index, object_list_page){
                //this function is for overwrite by user for event handle
            };
            
            ObjectsList.showFilter = function(objectIndex){
                var pos = $('#filterbutton_'+objectIndex).position();
                var top = pos.top + $('#filterbutton_'+objectIndex).outerHeight() + 5;
                $('#filterform_'+objectIndex).css({top: top, left: pos.left + 5});
                $('#filterform_'+objectIndex).show();
                $('#filterfield_'+objectIndex+'_name').focus();
            };
            
            ObjectsList.clearFilter = function(objectIndex){
                $('.filterfield_'+objectIndex).val('');
                $('#filterform_'+objectIndex).hide();
                ObjectsList.runFilter(objectIndex, 1);
            };
            
            ObjectsList.refresh = function(objectIndex){
                ObjectsList.runFilter(objectIndex, $('#PhiFrameObjectList_'+objectIndex).val());
            };
            
            ObjectsList.checkKey = function(objectIndex, page, e){
                if (e.which === 13 || e.keyCode === 13) {
                    e.preventDefault();
                    ObjectsList.runFilter(objectIndex, page);
                }
            };
            
            ObjectsList.runFilter = function(objectIndex, page){
                var data = [];
                
                $('.filterfield_'+objectIndex).each(function(idx, elm){
                    var val = $(elm).val().trim();
                    
                    if(val !== '')
                        data.push({id: $(elm).attr('fieldname'), val: val}); 
                });
                
                Ajax.send({action: 'PhiFrameObjectList', object_list_index: objectIndex, object_list_page: page, object_list_fields: data}, function(result){
                    $('#PhiFrameObjectList_Content_'+objectIndex).html(result.html);
                });

                $('#filterform_'+objectIndex).hide();
                ObjectsList.onPageChange(objectIndex, page);
            };
        </script>
    <?php }
    
    public static function ajaxServe($pageCallback){
        if(fstr('action') == 'PhiFrameObjectList'){
            $fields = fjson('object_list_fields', true);
            $p = fint_zero('object_list_page');
            $page = $p > 0 ? $p : 1; 
            done($pageCallback(fstr_null('object_list_index'), $page, $fields));
        }
    }
    
    public function __construct(string $type, string $title) {
        if(fstr('object_list_index') == $type){
            $p = fint_zero('object_list_page');
            $this->page = $p > 0 ? $p : 1;
            
            if(array_key_exists('object_list_fields', $_POST)){
                $f = $_POST['object_list_fields'];

                foreach ($f as $field) {
                    if(!array_key_exists('id', $field) || !array_key_exists('val', $field))
                        continue;
                    
                    $id = preg_replace('/[^A-Za-z0-9_-]/', '', $field['id']);
                    $val = $field['val'];
                    
                    if($id == '' || $val == '')
                        continue;
                    
                    foreach ([['/[^A-Za-z0-9_-]/', '%'], ['/%{2,}/', '%']] as $regex)
                        $val = preg_replace($regex[0], $regex[1], $val);
                    
                    $this->fields[$id] = $val;
                }
            }
        }
        
        $this->type = $type;
        $this->title = $title;
        $this->bar = new html('');
        $this->dbTableName = $type;
    }
    
    public function setSortColumnName(string $cname){
        if($cname == null)
            return;
        
        $this->sortColumnName = $cname;
    }
    
    public function getSortColumnName(){
        return $this->sortColumnName;
    }
    
    public function setDBTableName(string $dbtn){
        if($dbtn == null)
            return;
        
        $this->dbTableName = $dbtn;
    }
    
    public function getDBTableName(){
        return $this->dbTableName;
    }
    
    public function enableName(bool $enable = true){
        $this->enableNameColumn = $enable;
    }
    
    public function disableName(){
        $this->enableName(false);
    }
    
    public function enablePagesNavigator(bool $enable = true){
        $this->epn = $enable;
    }
    
    public function disablePageNavigator(){
        $this->enablePagesNavigator(false);
    }
    
    public function enableListFilter(bool $enable = true){
        $this->enableListFilter = $enable;
    }
    
    public function disableListFilter(){
        $this->enableListFilter(false);
    }
    
    public function enableDeleteObject(bool $enable = true){
        $this->enableDeleteObject = $enable;
    }
    
    public function disbaleDeleteObject(){
        $this->enableDeleteObject(false);
    }
    
    public function enableObjectEdit(bool $enable = true){
        $this->enableObjectEdit = $enable;
    }
    
    public function disbaleObjectEdit(){
        $this->enableObjectEdit(false);
    }
    
    public function getFilter(){
        $w = '';
        
        foreach ($this->fields as $id => $val)
            $w .= " AND `$id` LIKE '$val'";

        return $w;
    }
    
    public function addElement(array $element){
        $this->list[] = $element;
    }
    
    public function addList(array $list){
        foreach($list as $e){
            $ai = 0;
            $a = [];
            $a[$ai++] = array_key_exists('id', $e) ? $e['id'] : (array_key_exists(0, $e) ? $e[0] : null);
            
            if($this->enableNameColumn)
                $a[$ai++] = array_key_exists('name', $e) ? $e['name'] : (array_key_exists(1, $e) ? $e[1] : null);

            foreach($this->extraCollumns as $ecol)
                $a[$ai++] = array_key_exists($ecol[0], $e) ? ($e[$ecol[0]] == null ? "NULL" : $e[$ecol[0]]) : "--- Field `{$ecol[0]}` not exist ---";
            
            $this->list[] = $a;
        }
    }
    
    public function addColumn($id, $title, $width = null){
        if(is_array($id)){
            foreach ($id as $key => $value)
                if(is_array($value)){
                    if(sizeof($value) >= 2)
                        $this->extraCollumns[] = [$key, $value[0], $value[1]]; 
                    elseif(sizeof($value) == 1)
                        $this->extraCollumns[] = [$key, $value[0], null]; 
                }else{
                    $this->extraCollumns[] = [$key, $value, null]; 
                }
        } else {
            $this->extraCollumns[] = [$id, $title, $width];
        }
    }
    
    public function form($width = null, $ic= false){
        $this->width = $width;
        $this->infoColumn = $ic;
        return $this;
    }
    
    public function addToBar($html){
        $this->bar->append($html);
    }
    
    public function __toString() {
        return $this->htmlNode()->__toString();
    }
    
    public function htmlNode(){
        $html = new html('div', "#PhiFrameObjectList_Content_$this->type");
        $html->append($this->newContent());
        return $html;
    }
    
    public function getNewContentHtml(){
        return $this->newContent()->__toString();
    }
    
    private function findField($fieldname){
        return array_key_exists($fieldname, $this->fields) ? $this->fields[$fieldname] : '';   
    }

    private function newContent(){    
        $op = new html();
        $d = $op->div('.listBar')->style('position: relative');
        
        // List filter
        if($this->enableListFilter)
            $d->button("#filterbutton_$this->type; .listBarButton os_ico; *button")->onclick("ObjectsList.showFilter('$this->type')")->str('&nbsp;')->par()->img('.os_ico_1em; @libs/phiframe/images/filter.png');
        
        $ff = $d->div("#filterform_$this->type; .filterform");
        $ff->display('none');
        $fft = $ff->table()->tbody();
        
        if($this->enableNameColumn)
            $fft->tr()->td()->b()->html('Nazwa:')->par()->par()->td()->input("#filterfield_{$this->type}_name; .filterfield_$this->type; *text")->onkeypress("ObjectsList.checkKey('$this->type', 1, event)")->attr('fieldname', 'name')->value($this->findField('name'));
        
        foreach($this->extraCollumns as $ecol)
            $fft->tr()->td()->b()->html($ecol[1].':')->par()->par()->td()->input(".filterfield_$this->type; *text")->onkeypress("ObjectsList.checkKey('$this->type', 1, event)")->attr('fieldname', $ecol[0])->value($this->findField($ecol[0]));

        $fftb = $fft->tr()->td()->colspan(2)->style('text-align: center');
        $fftb->button('.listBarButton os_ico; *button')->onclick("ObjectsList.runFilter('$this->type', 1)")->style('margin-right: 2mm')->str('&nbsp;')->par()->img('.os_ico_2em; @libs/phiframe/images/ok.png');
        $fftb->button('.listBarButton os_ico; *button')->onclick("ObjectsList.clearFilter('$this->type')")->style('margin-right: 2mm')->str('&nbsp;')->par()->img('.os_ico_2em; @libs/phiframe/images/nok.png');
        
        // New element button
        if($this->enableObjectEdit)
            $d->input('.listBarButton; *button')->value('Dodaj')->onclick("newElement('$this->type')");
        
        // Page navigator
        if($this->lastpage > 1){
            $node = $d->div()->style('padding: 0 1cm; min-height: 2mm; display: inline-block');
            $node->button()->html('&laquo;')->width('5mm')->onclick("ObjectsList.gotopage(1,'$this->type')");
            $node->button()->html('&lt;')->width('6mm')->onclick("ObjectsList.gotopage(".($this->page - 1 > 0 ? $this->page - 1 : 1).",'$this->type')");
            $node->input('#PhiFrameObjectList_'.$this->type)->attr('data-objectindex', $this->type)->value($this->page)->title('Stron łącznie: '.$this->lastpage)->style('width: 3cm; text-align: center')->onfocus('$(this).val(\'\').select()')->onclick('$(this).val(\'\').select()')->onblur("$(this).val('$this->page')")->onkeydown("ObjectsList.filterkey($(this), event, '$this->type')");
            $node->button()->html('&gt;')->width('6mm')->onclick("ObjectsList.gotopage(".($this->page + 1 < $this->lastpage ? $this->page + 1 : $this->lastpage).",'$this->type')");
            $node->button()->html('&raquo;')->width('5mm')->onclick("ObjectsList.gotopage($this->lastpage,'$this->type')"); 
        }
        
        // Custom bar elements
        $d->append($this->bar);
        
        //------------
        
        // add new element form
        if($this->enableObjectEdit){
            $a = $op->div("#add_$this->type");
        
            //if($this->newObjectTitle != '' && $this->editObjectTitle != ''){
                //$a->attr('nTitle', $this->newObjectTitle);
                //$a->attr('eTitle', $this->editObjectTitle);
            //}
            
            $a->display('none');

            $x = $a->div('.phiframe-popup-content-frame');
            $bx = $x->table()->style('border-collapse: colapse; ');

            if($this->width !== null)
                $bx->width($this->width);

            foreach($this->formList as $field){
                if(!$this->useUnit)
                    $this->useUnit |= $field[2] != null;
                else
                    break;
            }

            $colspan = $this->useUnit ? 3 : 2;
            $b = $bx->tbody();
            $b->tr()->td('.phiframe-popup-title')->colspan($colspan);
            
            
            if($this->enableNameColumn){
                $nb = $b->tr();
                $nb->td('.abc0')->html('Nazwa:')->width(0);
                $nb->td()->colspan($colspan-1)->input("#name_$this->type; .abc1; *text")->attr('fieldsetname', $this->type);
                
                if($this->infoColumn)
                    $nb->td("#$this->type"."_name_info");
            }

            foreach($this->formList as $field){
                $f1 = $b->tr();

                if($field[3] === 'tr'){
                    $f1->append($field[1]);
                }elseif($field[3] === 'checkbox'){
                    $f1->td()->colspan($colspan)->append($field[1])->str($field[0]);
                } elseif($field[3] === 'subtitle'){
                    $f1->td()->colspan($colspan)->html($field[0])->style('text-align: center; font-weight: bold; padding-top: 3mm');
                } elseif($field[3] === 'label'){
                    $f1->td('#'.$field[5])->colspan($colspan);
                } else {
                    $f1->td()->style("text-align: ".$field[4])->html(str_replace(' ', '&nbsp;', $field[0]).":");
                    $f1i = $f1->td()->append($field[1]);

                    if($this->useUnit !== null){
                        if($field[2] === null)
                            $f1i->colspan(2);
                        else
                            $f1->td()->html($field[2]);
                    }
                }

                if($this->infoColumn && $field[3]!='tr')
                    if(empty($field[5]))
                        $f1->td();
                    else
                        $f1->td('#'.$this->type.'_'.$field[5].'_info; .object_list_info')->width('30%');
            }

            $f = $b->tr()->td()->colspan($colspan)->style('text-align: center');
            $f->br();
            $f->input('*button')->value('Anuluj')->onclick('closeDialog()');
            $f->input('.submitButton; *button')->value('Wykonaj')->onclick("sendElement('$this->type')");

            $a->div('.infoField');
        }
        //-----
        
        
        if(sizeof($this->list) == 0){
            $op->span()->html('BRAK OBIEKTÓW');
            return $op;
        }
            
        $table = new html('table', '.listTable');
        $trh = $table->thead()->tr();
        $trh->th('.center')->html('L.p.');
        
        if($this->enableNameColumn)
            $trh->th('.center')->html('Nazwa');
        
        foreach($this->extraCollumns as $ecol){
            $ec = $trh->th('.center')->html($ecol[1]);
            
            if(isset($ecol[2]))
                $ec->width($ecol[2]);
        }
        
        $trh->th();
        $lp = 1 + ($this->page - 1) * $this->pagesize;

        $tbody = $table->tbody("#PhiFrameObjectList_Elements_$this->type");

        foreach($this->list as $elm){
            $rr = $tbody->tr();
            $rr->td('.center')->html($lp)->width('6mm');

            for($i = 1; $i < sizeof($elm); $i++){
                $rs = $rr->td()->html($elm[$i]);
                
                if($this->enableObjectEdit)
                    $rs->onclick("editElement(".$elm[0].", '$this->type')");
            }
            
            $delb = $rr->td()->style('width: 10mm; padding: 0');
        
            if($this->enableObjectEdit){
                $delbb = $delb->input('.delete_button; *button; =Del')->onclick("deleteElement(".$elm[0].", '$this->type')")->width('100%');

                if(!$this->enableDeleteObject)
                    $delbb->disabled();
            }

            $lp++;
        }

        $op->append($table);
        return $op;  
    }
    
    //--- create form methods
    
    private $newObjectTitle = "";
    private $editObjectTitle = "";
    
    public function setFormTitles(string $not, string $eot){
        $this->newObjectTitle = $not == null ? "" : $not;
        $this->editObjectTitle = $eot == null ? "" : $eot;
    }
    
    public function text($title, $id, $unit = null, $titleAlign = 'right'){
        return $this->field($title, $id, 'text', $unit, $titleAlign);
    }

    public function number($title, $id, $unit = null, $titleAlign = 'right'){
        return $this->field($title, $id, 'number', $unit, $titleAlign);
    }
    
    public function exNumber($title, $subtitle, $id, $unit = null, $titleAlign = 'right'){
        return $this->exField($title, $subtitle, $id, 'number', $unit, $titleAlign);
    }
    
    public function exList($title, $id, $listid, $unit = null, $titleAlign = 'right'){
        $c = new html('div');
        //$c->str("<script>$(function(){var o = $('#$this->type"."_$id'); $('#img_$this->type"."_$id').height(o.height()+4); });</script>");
        
        
        $c->style('background: red; float: left; position: relative; height: 21px; width: calc(98% + 1px); left: -1px;');
        
        $c1 = $c->input("#$this->type"."_$id; .abc1 yellowBox; *text")->width('width: 100%; border: 1px solid black; background: yellow');
        $c2 = $c->img("#img_$this->type"."_$id; @images/icons/edit.png");
        
        $c2->style("position: absolute; z-index: 2; max-height: 100%; top: 0; right: 0");
        
        $this->formList[] = [$title, $c, $unit, 'exlist', $titleAlign, $id];
        return $c1;
    } 
    
    public function checkbox($title, $id, $unit = null, $titleAlign = 'left'){
        return $this->field($title, $id, 'checkbox', $unit, $titleAlign);
    }
    
    public function combo($title, $ida, $list, $unit = null, $titleAlign = 'right'){
        $b = new html('select', "#$this->type"."_$ida; .abc1 yellowBox");
        $b->attr('fieldsetname', $this->type);
        $b->option()->value(0)->selected();
        
        foreach($list as $e){
            $id = array_key_exists('id', $e) ? $e['id'] : (array_key_exists(0, $e) ? $e[0] : null);
            
            if($id != null){
                $na = array_key_exists('name', $e) ? $e['name'] : (array_key_exists(1, $e) ? $e[1] : null);

                if($na != null)
                    $b->option()->value($id)->html($na); 
            }
        }

        $this->formList[] = [$title, $b, $unit, 'combo', $titleAlign, $ida];
        return $b;
    }
    
    public function combo_mod($title, $ida, $list, $fid = 'id', $extends = null, $unit = null, $titleAlign = 'right'){
        $b = new html('select', "#$this->type"."_$ida; .abc1 yellowBox");
        $b->attr('fieldsetname', $this->type);
        $b->option()->value(0)->selected();
        
        foreach($list as $e){
            $id = array_key_exists($fid, $e) ? $e[$fid] : (array_key_exists(0, $e) ? $e[0] : null);
            
            if($id != null){
                $na = array_key_exists('name', $e) ? $e['name'] : (array_key_exists(1, $e) ? $e[1] : null);
                
                if($na === null)
                    continue;
                
                if(is_array($extends))
                    foreach ($extends as $field => $type)
                        if(array_key_exists($field, $e))
                            if($type === 1)
                                $na = $e[$field].' '.$na;
                            else if($type === 2)
                                $na = '('.$e[$field].') '.$na;
                            else if($type === 3)
                                $na .= ' ('.$e[$field].')';
                            else
                                $na .= ' '.$e[$field];

                $b->option()->value($id)->html($na); 
            }
        }

        $this->formList[] = [$title, $b, $unit, 'combo', $titleAlign, $ida];
        return $b;
    }
    
    public function checklist($title, $id, $list, $unit = null, $titleAlign = 'right'){
        $table = new html('table', '#checklist_'.$id.'; .yellowBox');
        $tbody = $table->width('100%')->tbody();
        
        foreach($list as $e){
            $ida = array_key_exists('id', $e) ? $e['id'] : (array_key_exists(0, $e) ? $e[0] : null);
            
            if($ida != null){
                $na = array_key_exists('name', $e) ? $e['name'] : (array_key_exists(1, $e) ? $e[1] : null);

                if($na != null)
                    $r = $tbody->tr();
                    $r->td()->width('1cm')->input("#$this->type"."_$id"."_$ida; .$this->type"."_$id; *checkbox")->attr($id.'ID', $ida); 
                    $r->td()->html($na);
            }
        }
        
        $this->formList[] = [$title, $table, $unit, 'checklist', $titleAlign, $id];
    }
    
    public function subtitle($title = ''){
        $this->formList[] = [$title, null, null, 'subtitle', null, null];
    }
    
    public function label($title, $id, $unit = null, $titleAlign = 'right'){
        $this->formList[] = [$title, null, $unit, 'label', null, $id];
    }
    
    private function field($title, $id, $type, $unit = null, $titleAlign = 'right'){
        $b = new html('input', "#$this->type"."_$id; *$type");
        $b->attr('fieldsetname', $this->type);
        
        if($type !== 'checkbox')
            $b->class('abc1 yellowBox');
        
        $this->formList[] = [$title, $b, $unit, $type, $titleAlign, $id];
        return $b;
    }
    
    public function date($title, $id, $titleAlign = 'right'){
        return $this->field($title, $id, 'date', null, $titleAlign);
    }
    
    public function time($title, $id, $titleAlign = 'right'){
        return $this->field($title, $id, 'time', null, $titleAlign);
    }
    
    public function exField($title, $subtitle, $id, $type, $unit = null, $titleAlign = 'right'){
        $a = new html();
        $a->str($subtitle);
        $b = $a->input("#$this->type"."_$id; *$type");
        $b->attr('fieldsetname', $this->type);
        
        if($type !== 'checkbox')
            $b->class('abc1 yellowBox');
        
        $this->formList[] = [$title, $a, $unit, $type, $titleAlign, $id];
        return $b;
    }
    
    public function textArea($title, $id, $rows, $unit = null, $titleAlign = 'right'){
        $b = new html('textarea', "#$this->type"."_$id; .abc1 yellowBox");
        $b->rows($rows)->attr('fieldsetname', $this->type);
        
        $this->formList[] = [$title, $b, $unit, 'textarea', $titleAlign, $id];
        return $b;
    }
    
    public function tr(){
        $a = new html();
        $this->formList[] = [null, $a, null, 'tr', null, null];
        return $a;
    }
}
