<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class FormCreator {
    public $type = '';
    private $formList = [];
    public $title = '';
    public $useUnit = false;
    private $width = null;

    public function __construct($type = '') {
        $this->type = $type;
    }
    
    public function form($width = null){
        $this->width = $width;
        return $this;
    }
    
    public function htmlNode(){
        $a = new html();
        $table = $a->table()->style('border-collapse: colapse; ');
        
        if($this->width !== null)
            $table->width($this->width);
        
        foreach($this->formList as $field){
            if(!$this->useUnit)
                $this->useUnit |= $field[2] != null;
            else
                break;
        }
        
        $colspan = $this->useUnit ? 3 : 2;
        $b = $table->tbody();
        $b->tr()->td('.phiframe-popup-title')->colspan($colspan);
        $b->tr()->td('.abc0')->html('Nazwa:')->par()->td()->colspan($colspan-1)->input("#name_$this->type; .abc1; *text");
        
        foreach($this->formList as $field){
            $f1 = $b->tr();
            
            if($field[3] === 'tr'){
                $f1->append($field[1]);
            }elseif($field[3] === 'checkbox'){
                $f1->td()->colspan($colspan)->append($field[1])->str($field[0]);
            } elseif($field[3] === 'subtitle'){
                $f1->td()->colspan($colspan)->html($field[0])->style('text-align: center; font-weight: bold; padding-top: 3mm');
            } else {
                $f1->td()->style("text-align: ".$field[4])->html(str_replace(' ', '&nbsp;', $field[0]).":");
                $f1->td()->append($field[1]);

                if($this->useUnit !== null)
                    $f1->td()->html($field[2] === null ? '' : $field[2]);
            }
        }
        
        $f = $b->tr()->td()->colspan($colspan)->style('text-align: center');
        $f->br();
        $f->input('*button')->value('Anuluj')->onclick('closeDialog()');
        $f->input('.submitButton; *button')->value('Wykonaj')->onclick("sendElement('$this->type')");

        
        $a->div('.infoField');
        
        return $table;  
    }
    
    public function __toString() {
        return $this->htmlNode()->__toString();
    }
    
    //--- create form methods
    
    
    
    public function text($title, $id, $unit = null, $titleAlign = 'right'){
        return $this->field($title, $id, 'text', $unit, $titleAlign);
    }
    
    public function number($title, $id, $unit = null, $titleAlign = 'right'){
        return $this->field($title, $id, 'number', $unit, $titleAlign);
    }
    
    public function exNumber($title, $subtitle, $id, $unit = null, $titleAlign = 'right'){
        return $this->exField($title, $subtitle, $id, 'number', $unit, $titleAlign);
    }
    
    public function checkbox($title, $id, $unit = null, $titleAlign = 'left'){
        return $this->field($title, $id, 'checkbox', $unit, $titleAlign);
    }
    
    public function combo($title, $id, $list, $unit = null, $titleAlign = 'right'){
        $b = new html('select', "#$this->type"."_$id; .abc1");
        $b->option()->value(0)->selected();
        
        foreach($list as $e){
            $id = array_key_exists('id', $e) ? $e['id'] : (array_key_exists(0, $e) ? $e[0] : null);
            
            if($id != null){
                $na = array_key_exists('name', $e) ? $e['name'] : (array_key_exists(1, $e) ? $e[1] : null);

                if($na != null)
                    $b->option()->value($id)->html($na); 
            }
        }

        $this->formList[] = [$title, $b, $unit, 'combo', $titleAlign];
        return $b;
    }
    
    public function checklist($title, $id, $list, $unit = null, $titleAlign = 'right'){
        $table = new html('table', '.yellowBox');
        $tbody = $table->width('100%')->tbody();
        
        foreach($list as $e){
            $ida = array_key_exists('id', $e) ? $e['id'] : (array_key_exists(0, $e) ? $e[0] : null);
            
            if($ida != null){
                $na = array_key_exists('name', $e) ? $e['name'] : (array_key_exists(1, $e) ? $e[1] : null);

                if($na != null)
                    $r = $tbody->tr();
                    $r->td()->width('1cm')->input("#$this->type"."_$id"."_$ida; .$this->type"."_$na; *checkbox")->attr($id.'ID', $ida); 
                    $r->td()->html($na);
            }
        }
        
        $this->formList[] = [$title, $table, $unit, 'checklist', $titleAlign];
    }
    
    public function label($title = ''){
        $this->formList[] = [$title, null, null, 'subtitle', null];
    }
    
    private function field($title, $id, $type, $unit = null, $titleAlign = 'right'){
        $b = new html('input', "#$this->type"."_$id; *$type");
        
        if($type !== 'checkbox')
            $b->class('abc1');
        
        $this->formList[] = [$title, $b, $unit, $type, $titleAlign];
        return $b;
    }
    
    public function exField($title, $subtitle, $id, $type, $unit = null, $titleAlign = 'right'){
        $a = new html();
        $a->str($subtitle);
        $b = $a->input("#$this->type"."_$id; *$type");
        
        if($type !== 'checkbox')
            $b->class('abc1');
        
        $this->formList[] = [$title, $a, $unit, $type, $titleAlign];
        return $b;
    }
    
    public function tr(){
        $a = new html();
        $this->formList[] = [null, $a, null, 'tr', null];
        return $a;
    }
}