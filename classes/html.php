<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */


interface HTMLInterface {
    public function __construct($tag, $attr);
    public function html($html);
    public function append($object);
    public function attr($attr, $value);
    public function __toString();
    public function __call($method, $opt);
}

class HTML implements HTMLInterface {
    public $tag;
    private $attr;
    private $obj;
    private $html = '';
    public $parent = null;
    
    private $attributes = ['id', 'class', 'onclick', 'oncontextmenu', 'charset', 'type', 'src', 'rel', 'href', 'style', 'accept',
                            'draggable', 'onkeyup', 'ondragstart', 'ondrop', 'ondragover', 'ondragenter', 'ondragleave', 'placeholder',
                            'onscroll', 'colspan', 'rowspan', 'value', 'title', 'onload', 'name', 'onchange', 'onkeypress', 'for',
                            'onkeydown', 'method', 'onfocus', 'onblur', 'min', 'max', 'step', 'pattern', 'onresize', 'rows', 'cols'];
    private $css_attributes = ['display', 'visibility', 'width', 'overflow', 'height', 'text-align', 'color', 'border', 'background', 'resize'];
    private $spectags = ['img', 'meta', 'link', 'input', 'col', 'br', 'hr'];
    private $shorttag = ['disabled', 'selected', 'checked', 'required', 'multiple'];
    private $translationTags = [
        'textalign' => 'text-align',
        'align' => 'text-align',
        'halign' => 'text-align'
    ];
    
    public function __construct($tag = 'html', $attr = ''){      
        $this->tag = $tag;

        if($tag == 'str'){
            $this->html = $attr;
        } else {
            $this->attr = [];
            $this->css = [];
            $this->obj = [];
            $kv = explode(';', $attr);

            foreach($kv as $par){
                $par = trim($par);
                
                if($par != ''){
                    $begin = substr($par, 0, 1);
                    
                    if($begin == '#'){
                        $this->attr['id'] = substr($par, 1);
                    } else if($begin == '.'){   
                        $this->attr['class'] = substr($par, 1);
                    } else if($begin == '*'){   
                        $this->attr['type'] = substr($par, 1);
                    } else if($begin == '='){
                        $this->attr['value'] = substr($par, 1);
                    } else if($begin == '&'){
                        $this->attr['name'] = substr($par, 1);
                    } else if($begin == '@'){
                        if(substr($par, 1, 1) == '@'){
                            $this->attr['src'] = 'images/'.substr($par, 2);
                        } else {
                            $this->attr['src'] = substr($par, 1);
                        }
                    } else if($begin == '>'){
                        $this->str(substr($par, 1));
                    } else {
                        $y = explode('=', $par);
                        
                        if(!isset($y[1])){
                            var_dump($y);
                            debug_print_backtrace(0, 2);
                        }
                        
                        $this->attr[$y[0]] = $y[1];
                    }
                }
            }
        }
    }
    
    public function html($html){
        $this->obj = [];
        $this->html = $html.'';
        return $this;
    }
    
    public function add($html){
        $this->html .= $html;
        return $this;
    }
    
    public function append($object, $noclear = false){
        if($object == null)
            return $this;
        
        if($noclear){
            if($this->html != null && $this->html != '')
                $this->str($this->html);
        } else {
            $this->html = '';
        }
        
        $object->parent = $this;
        $this->obj[] = $object;
        return $this;
    }
    
    public function addClass($classname){
        $this->appendClass($classname);
    }
    
    public function appendClass($classname){
        if(!isset($this->attr['class']))
            $this->attr['class'] = $classname;
        else
            $this->attr['class'] .= " $classname";
    }
    
    public function attr($attr, $value){
        $this->attr[$attr] = $value;
        return $this;
    }
    
    public function par($lvl = 1){
        if($this->parent == null){
            return $this;
        }
        
        if(is_numeric($lvl)){
            return $lvl > 1 ? $this->parent->par($lvl-1) : $this->parent;
        } else {
            return $this->parent->tag == $lvl ? $this->parent : $this->parent->par($lvl);
        } 
    }
    
    public function dim($w, $h){
        $this->width($w);
        $this->height($h);
        return $this;
    }
    
    public function toString(){
        return $this->__toString();
    }
    
    public function __toString(){
        if($this->tag == 'str'){
            return $this->html;
        }
        
        $p = '';
        $css = '';
        $inn = $this->html;
        
        foreach($this->obj as $o){
            $inn .= $o;
        }
        
        foreach($this->css as $key => $value){
            $css .= "$key: $value;";
        } 
        
        if(array_key_exists('style', $this->attr)){
            $css .= $this->attr['style'];
        }
        
        if($css != ''){
            $this->attr['style'] = $css;
        }
        
        foreach($this->attr as $key => $value){
            if($value != null && $value != ''){
                $p .= " $key=\"$value\"";
            } else if($value == null){
                $p .= " $key";
            }
        }
        
        if($this->tag === ''){
            return $inn;
        }elseif(in_array($this->tag, $this->spectags)){
            return "<$this->tag$p/>";
        }else{
            return "<$this->tag$p>$inn</$this->tag>";
        }
    }
    
    public function __call($m, $opt){
        $method = strtolower($m);
        
        if(array_key_exists($method, $this->translationTags))
            $method = $this->translationTags[$method];
        
        if (!isset($opt[0])) {
            $opt[0] = '';
        }
        
        if(in_array($method, $this->attributes)){
            $this->attr[$method] = $opt[0] === 0 ? '0' : $opt[0];
            return $this;
        }else if(in_array($method, $this->css_attributes)){
            $this->css[$method] = $opt[0];
            return $this;
        }else if(in_array($method, $this->shorttag)){
            if($opt[0] !== false){
                $this->attr[$method] = $method;
            }
            
            return $this;
        }else{
            $o = new HTML($method, $opt[0]);
            $o->parent = $this;
            $this->append($o);
            return $method == "br" ? $this : $o;
        }
    }
    
    public function strow(){
        $row = new html('tr');
        $args = func_get_args();
        
        foreach ($args as $arg) {
            $row->td()->html($arg);
        }
        
        $this->append($row);
        return $this;
    }
}