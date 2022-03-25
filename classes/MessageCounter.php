<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class MessageCounter {
    private $messagesList;
    private $prefix;

    public function __construct($prefix = ''){
        $this->messagesList = [];
        $this->prefix = $prefix;
    }
    
    public function getHtmlList(){
        return (string)$this;
    }
    
    public function __toString(){
        $wl = '';

        foreach($this->messagesList as $key => $val){
            $wl .= $this->prefix.'&nbsp;'.str_replace('_', '&nbsp;', $key).":&nbsp;".$val['count']."<br>";
            
            if(array_key_exists('messageCounter', $val))
                $wl .= (string)$val['messageCounter'];
        }
        
        return $wl;
    }
    
    public function __call($method, $args){
        if(array_key_exists($method, $this->messagesList))
            $this->messagesList[$method]['count']++;
        else {
            $this->messagesList[$method] = [];
            $this->messagesList[$method]['count'] = 1;
        }

        if(isset($args[0])){
            if(!array_key_exists('messageCounter', $this->messagesList[$method]))
                $this->messagesList[$method]['messageCounter'] = new MessageCounter();
            
            $this->messagesList[$method]['messageCounter']->{str_replace(' ', '_', $args[0])}();  
        }
    }
}