<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class CSVFile {
    private $filename;
    private $convertEncoding;
    private $delimiter = ';';
    private $enclosure = '"';
    
    
    public function __construct(string $filename = '', bool $convertEncoding = true){
        $this->filename = $filename;
        $this->convertEncoding = $convertEncoding;
    }
    
    public function setDelimiter(string $delimiter){
        if(empty($delimiter))
            return;
        
        $this->delimiter = $delimiter;
    }
    
    public function setEnclosure(string $enclosure){
        if(empty($enclosure))
            return;
        
        $this->enclosure = $enclosure;
    }
    
    public function getArrayByLine($callback){
        if(($file = fopen($this->filename, "r"))){
            if($this->convertEncoding)
                $plEnc = new PolishEncode();
            
            while(!feof($file)) {
                $line = fgets($file);
                
                if($this->convertEncoding)
                    $plEnc->convertToUTF($line);

                $arr = str_getcsv($line, $this->delimiter, $this->enclosure);

                if(is_callable($callback) && $callback($arr, $line) === false)
                    break;
            }

            fclose($file);
        }  
    }
    
    public function getArray(){
        $arr = [];
        
        if(($file = fopen($this->filename, "r"))){
            if($this->convertEncoding)
                $plEnc = new PolishEncode();
            
            while(!feof($file)) {
                $line = fgets($file);
                
                if($this->convertEncoding)
                    $plEnc->convertToUTF($line);

                $arr[] = str_getcsv($line, $this->delimiter, $this->enclosure);
            }

            fclose($file);
        }
        
        return $arr;
    }
}