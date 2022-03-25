<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */


class jqplot {
    private $containerId = '';
    private $data = '';
    private $configuration = '';
    private $objects = '';
    private $embended = false;
    
    public function __construct($cid, $cfg, $embended = false){
        $this->containerId = $cid;
        $this->configuration = $this->subConfig($cfg);
        $this->embended = $embended;
    }
    
    public function addDataSeries($array){
        $seriesData = '';

        foreach($array as $x => $y) {
            $seriesData .= ($seriesData == '' ? '' : ',')."[$x,$y]";
        }

        $this->data .= ($this->data == '' ? '' : ',')."[$seriesData]";
    }
    
    private function subConfig($array){
        $config = '';
        
        foreach ($array as $key => $val) {
            $config .= ($config == '' ? '' : ',')."$key:";
            
            if(is_array($val)){
                $config .= "{".$this->subConfig($val)."}";
            } else {
                $config .= $val;
            }
        }
        
        return $config;
    }
            
    public function addObjects($array){
        foreach ($array as $type => $conf) {
            $cfg = '';
            
            foreach ($conf as $key => $value) {
                $cfg .= ($cfg == '' ? '' : ',')."$key:$value";        
            }
             
            $this->objects .= ($this->objects == '' ? '' : ',')."{".$type.":{".$cfg."}}";        
        }
    }
    
    public function __toString() {
        if($this->objects != ''){
            $this->configuration .= ($this->configuration == '' ? '' : ',')."canvasOverlay:{show:true,objects:[$this->objects]}";
        }
        
        $script = "$.jqplot('$this->containerId', [$this->data], {".$this->configuration."});";
        return $this->embended ? "<script>$script</script>" : $script;
    }
}

