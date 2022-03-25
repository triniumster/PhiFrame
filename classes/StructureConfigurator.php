<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class StructureConfigurator {
    
    const OBJECTS_LIST_CONTENT = 0; //'ObjectsList';
    const SINGLE_CONFIGURATION_CONTENT = 1; //'SingleConfiguration';
    const SET_AS_DEFAULT_VIEW = true;

    private $options;
    
    public function __construct() {
        $this->options = [];
    }
    
    public function addOption($id, $title, $type = self::OBJECTS_LIST_CONTENT){
        $opt = new StructureOption($id, $title, $type);
        $this->options[] = $opt;
        return $opt;
    }
    
    public function addSeparator(){
        $this->options[] = VerticalCollapsedMenu::MENU_SEPARATOR;
    }
    
    public function getStructureArray(){
        $struct = [];
        
        foreach($this->options as $opt)
            if($opt === VerticalCollapsedMenu::MENU_SEPARATOR)
                $struct[] = VerticalCollapsedMenu::MENU_SEPARATOR;
            else
                $opt->appendStructureToArray($struct);
        
        return $struct;
    }
    
    public function getString(bool $compress = false){
        $str = '';
        $str .= "define('i', FILTER_VALIDATE_INT);\n";
        $str .= "define('f', FILTER_VALIDATE_FLOAT);\n";
        $str .= "define('s', FILTER_SANITIZE_STRING);\n";
        $str .= "define('j', FILTER_UNSAFE_RAW);\n";
        $str .= "define('p', VerticalCollapsedMenu::MENU_SEPARATOR);\n\n";
        $str .= "private \$structure = [";
        $a = false;
        
        foreach($this->options as $opt){
            $str .= ($a ? ',' : '');

            if($opt === VerticalCollapsedMenu::MENU_SEPARATOR)
                $str .= "\n\tp";
            else
                $str .= "\n\t".$opt->getString();

            $a = true;
        }

        $str .= "\n];";

        if($compress)
            $str = preg_replace('/\s+/', '', $str);
        
        return $str;
    }
}

class StructureOption {
    
    private $id;
    private $title;
    private $type;
    private $options;
    
    public function __construct($id, $title, $type) {
        $this->id = $id;
        $this->title = $title;
        $this->type = $type;
        $this->options = [];
    }
    
    public function addOption($id, $title, $defView = false){
        $opt = new StructureOptionOption($id, $title, $this->type, $defView);
        $this->options[] = $opt;
        return $opt;
    }
    
    public function appendstructureToArray(&$structure){
        $struct = [];
        $struct['_name'] = $this->title;
        $struct['_type'] = $this->type;
        
        foreach($this->options as $opt)
            $opt->appendStructureToArray($struct);

        $structure[$this->id] = $struct;
    }
    
    public function getStructureArray(){
        $a = [];
        $this->appendStructureToArray($a);
        return $a;
    }
    
    public function getString(){
        $str = "'$this->id' => ['$this->title', $this->type";

        foreach($this->options as $opt)
            $str .= ",\n\t\t".$opt->getString();

        $str .= "\n\t\t]";
        return $str;
    }
}

class StructureOptionOption {
    
    private $id;
    private $title;
    private $type;
    private $defView;
    private $field;
    private $deleteCheck;
    private $associatedTable;
    private $formConfig;
    
    public function __construct(string $id, string $title, int $type, bool $defView) {
        $this->id = $id;
        $this->title = $title;
        $this->type = $type;
        $this->defView = $defView !== false;
        $this->field = null;
        $this->deleteCheck = [];
        $this->associatedTable = null;
        $this->formConfig = null;
    }
    
    public function setIntField($field){
        $this->setField($field, FILTER_VALIDATE_INT);
    }
    
    public function setFloatField($field){
        $this->setField($field, FILTER_VALIDATE_FLOAT);
    }
    
    public function setStrField($field){
        $this->setField($field, FILTER_SANITIZE_STRING);
    }
    
    public function setJsonField($field){
        $this->setField($field, FILTER_UNSAFE_RAW);
    }
    
    private function setField($field, $type){
        if($field === false || $field === null){
            $this->field = null;
        } elseif(is_array($field) && sizeof($field) > 0) {
            if($this->field === null)
                $this->field = [];
            
            foreach ($field as $value)
                $this->field[$value] = $type;   
        } elseif(is_string($field) && $field !== ''){
            if($this->field === null)
                $this->field = [];
            
            $this->field[$field] = $type;
        }
    }
    
    public function setDeleteCheck($table = null, string $column = null){
        if($table === null)
            $this->deleteCheck = null;
        elseif(is_array($table) && sizeof($table) > 0){
            if($this->deleteCheck === null)
                $this->deleteCheck = [];
            
            foreach ($table as $key => $value)
                $this->deleteCheck[] = [$key, $value];
        } elseif($column !== null) {
            if($this->deleteCheck === null)
                $this->deleteCheck = [];

            $this->deleteCheck[] = [$table, $column]; 
        }
    }
    
    public function setAssociatedTable($table = null, array $set = null){
        if($table === null)
            $this->associatedTable = null;
        elseif(is_array($table) && sizeof($table) > 0){
            if($this->associatedTable === null)
                $this->associatedTable = [];
            
            foreach ($table as $key => $value)
                $this->associatedTable[] = [$key, $value];
        } elseif($set !== null && sizeof($set) > 0) {
            if($this->associatedTable === null)
                $this->associatedTable = [];

            $this->associatedTable[] = [$table, $set]; 
        }
    }
    
    public function setFormConfiguration(array $cfg = null){
        if($cfg === null)
            $this->formConfig = null;
        elseif(sizeof($cfg) > 0)
            $this->formConfig = $cfg;  
    }
    
    public function appendStructureToArray(&$structure){
        $struct = [];
        $struct['_name'] = $this->title;

        if($this->type === StructureConfigurator::OBJECTS_LIST_CONTENT){
            if($this->field !== null)
                $struct['_field'] = $this->field;
            
            if($this->deleteCheck !== null){
                if(sizeof($this->deleteCheck) > 0)
                    $struct['_deleteCheck'] = $this->deleteCheck;
            } else
                $struct['_deletable'] = false;    
                    
            if($this->associatedTable !== null)
                $struct['_associatedTable'] = $this->associatedTable;
            
        }elseif($this->type == StructureConfigurator::SINGLE_CONFIGURATION_CONTENT && $this->formConfig !== null)
            $struct['_form'] = $this->formConfig;

        $structure[$this->id] = $struct;
    }
    
    public function getStructureArray(){
        $a = [];
        $this->appendStructureToArray($a);
        return $a;
    }
    
    public function getString(){
        $str = "'$this->id' => ['$this->title'";

        if($this->type === StructureConfigurator::OBJECTS_LIST_CONTENT){
            if($this->field !== null){
                $str .= ",[";
                $a = false;
                $v = [FILTER_VALIDATE_INT => 'i', FILTER_VALIDATE_FLOAT => 'f', FILTER_SANITIZE_STRING => 's', FILTER_UNSAFE_RAW => 'j'];
                
                foreach ($this->field as $key => $value) {
                    $str .= ($a ? ',' : '')."\n\t\t\t\t'$key' => $v[$value]";
                    $a = true;
                }
                
                $str .= "\n\t\t\t]";
            } else
                $str .= ",[]";
            
            
            if($this->deleteCheck !== null && sizeof($this->deleteCheck) > 0){
                $str .= ",[";
                $a = false;
                
                foreach ($this->deleteCheck as $set) {
                    $str .= ($a ? ',' : '')."\n\t\t\t\t['$set[0]', '$set[1]']";
                    $a = true; 
                }
                
                $str .= "\n\t\t\t]";
            } else
                $str .= ",[]";
                    
            if($this->associatedTable !== null){
                $str .= ",[";
                $a = false;
                
                foreach ($this->associatedTable as $set) {
                    $str .= ($a ? ',' : '')."\n\t\t\t\t['$set[0]', [";
                    $b = false;
                    
                    foreach ($set[1] as $value) {
                        $str .= ($b ? ',' : '')."'$value'";
                        $b = true;
                    }

                    $str .= "]]";
                    $a = true; 
                }
                
                $str .= "\n\t\t\t]";
            } else
                $str .= ",[]";
            
        }elseif($this->type == StructureConfigurator::SINGLE_CONFIGURATION_CONTENT && $this->formConfig !== null){
            $str .= ",[";
            $a = false;
            
            foreach($this->formConfig as $value){
                    $str .= ($a ? ',' : '')."\n\t\t\t\t[";
                    $str .= "'".$value['label']."', '".$value['type']."', '".$value['subid']."', '".$value['value']."', '".$value['unit']."'";

                    if($value['type'] === 'number')
                        $str .= ", ".$value['min'].", ".($value['max'] === null || $value['max'] === false ? "''" : $value['max']).", ".$value['step'];

                    $str .= "]";
                    $a = true; 
            }
            
            $str .= "\n\t\t\t]";
        }
        
        $str .= "]";
        
        return $str;
    }
}