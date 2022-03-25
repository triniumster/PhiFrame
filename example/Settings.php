<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

define('i', FILTER_VALIDATE_INT);
define('f', FILTER_VALIDATE_FLOAT);
define('s', FILTER_SANITIZE_STRING);
define('j', FILTER_UNSAFE_RAW);
define('p', VerticalCollapsedMenu::MENU_SEPARATOR);

class Settings extends PhiFrameConfigurationContent {
    protected $title = "Ustawienia";
    protected $style = ["styles/Configuration.css"];
    //protected $structure = null;

    //##########################################################################
    
    public function __script(){
        Ajax::script();
        FileUpload::script();
        TabedPane::script();
        ObjectsList::script();
        VerticalCollapsedMenu::script();
    }
    
    public function __style(){
        Ajax::style();
        ObjectsList::style();
        TabedPane::style();
    }
    
    public function __init(){
        if(!$this->hasPrivilege(CONFIG_PANEL_ACCESS_PRIVILEGE))
            return;
        
        if($this->hasPrivilege(GENERAL_RESOURCES_CONFIG_ACCESS_PRIVILEGE)){
            $this->structure['general-resources'] = ['Zasoby podstawowe', 0,
                'team' => ['Zespoły', [], [
                        ['worker', 'teamID'],
                    ], [], ''],
                'worker' => ['Pracownicy', [
                        'code' => s,
                        'teamID' => i,
                        'privileges' => i,
                        'firstname' => s,
                        'surname' => s,
                        'windykatorID' => i
                    ], [], []]
            ];
        
            $this->structure[] = VerticalCollapsedMenu::MENU_SEPARATOR;
        }

        if($this->hasPrivilege(BANKING_CONFIG_ACCESS_PRIVILEGE))
            $this->structure['bookkeeping'] = ['Przetwarzanie przelewów', 0,
                'bankaccount' => ['Konta', [
                        'accountnumber' => s
                    ], [], []],
            ]; 
         
        $this->structure['lettersreturntype'] = ['Zwroty listów', 0,
            'lettersreturntype' => ['Typy zwrotów', [], [], []]
        ];
    }
    
    public function __content(){
        if(!$this->hasPrivilege(CONFIG_PANEL_ACCESS_PRIVILEGE))
            return;
        
        $lm = VerticalCollapsedMenu::htmlNode('cfg', '#fxcontent', $this->structure);
        
        $top = new html('div');
        $top->width('calc(100% - 4mm + 2px)')->style('padding: 0; display: block; margin-left: calc(2mm - 1px)');
        $frame = $top->table('#ftable')->width('100%')->tbody()->tr();
        $frame->td('.f1')->style('vertical-align: top')->append($lm);
        $frame->td('#fxcontent; .f2')->style('vertical-align: top');
       
        echo $top;
    }
    
    public function __ajax(){ 
        FileUpload::ajaxServe(function($subaction){
            
            
        });
        
        VerticalCollapsedMenu::ajaxServe(function($contentID){
            if(!array_key_exists($contentID, $this->structure))
                return "Content not exist: $contentID";
            
            $subst = $this->structure[$contentID];
                
            if(!array_key_exists(1, $subst))    
                return "Content ID exist, but not found content object type";
            
            if($subst[1] === StructureConfigurator::OBJECTS_LIST_CONTENT){
                $currentName = null;

                if(($cn = fstr_null('currenttab')) != null || ($cn = fstr_null('currenttab', INPUT_GET)) != null)
                    $currentName = $cn;

                $tabedPane = new TabedPane();

                foreach ($subst as $key => $struct)
                    if($key !== 0 && $key !== 1)
                        $tabedPane->addTab($struct[0], "#$key", $currentName == $key || (key_exists('defaultView', $struct) && $struct['defaultView']));

                return $tabedPane;
            }
            
            if($subst[1] === StructureConfigurator::SINGLE_CONFIGURATION_CONTENT)
                return $this->singleConfigurationContentNode($subst);
                
            return 'Content object type not recognized: '.$subst['_type'];
        });
        
        TabedPane::ajaxServe(function($contentID){
            $struct = null;
            
            foreach($this->structure as $dat)
                if(is_array($dat) && key_exists($contentID, $dat)){
                    $struct = $dat[$contentID];
                    break;
                }
            
            if($struct == null)
                return 'Content not found: '.$contentID;

            $a = new ObjectsList($contentID, $struct[0], is_array($struct[1]));
            
            if(method_exists($this, $contentID."_bar"))
                $this->{$contentID."_bar"}($a->bar);

            if(method_exists($this, $contentID."_form"))
                $this->{$contentID."_form"}($a);
                
            $a->addList($this->getList($a->type, isset($struct[4]) && gettype($struct[4]) == 'string' && $struct[4] != '' ? $struct[4] : 'name', $a->page, $a->pagesize, $a->lastpage));
            return $a;
        });
        
        ObjectsList::ajaxServe(function($contentID){
            $struct = null;
            
            foreach($this->structure as $dat)
                if(is_array($dat) && key_exists($contentID, $dat)){
                    $struct = $dat[$contentID];
                    break;
                }
            
            if($struct == null)
                return 'Content not found: '.$contentID;

            $a = new ObjectsList($contentID, $struct[0], is_array($struct[1]));
            
            if(method_exists($this, $contentID."_bar"))
                $this->{$contentID."_bar"}($a->bar);

            if(method_exists($this, $contentID."_form"))
                $this->{$contentID."_form"}($a);
                
            $a->addList($this->getList($a->type, isset($struct[4]) && gettype($struct[4]) == 'string' && $struct[4] != '' ? $struct[4] : 'name', $a->page, $a->pagesize, $a->lastpage, $a->getFilter()));
            
            return ['html' => $a->getNewContentHtml()];
        });
    }
    
// |----------------------------------------------------------------------------
// | FORMS & BARS
// |----------------------------------------------------------------------------
    
    //--- general resources forms ----------------------------------------------
    
    protected function worker_form(&$b){
        $b->text('WID', 'wID');
        $b->text('Imię', 'firstname');
        $b->text('Nazwisko', 'surname');
        $b->text('ID', 'code');
        $b->combo('Zespół', 'teamID', $this->getList('team'));
        $b->subtitle('UPRAWNIENIA PODSTAWOWE');
        
        foreach([
            CONFIG_PANEL_ACCESS_PRIVILEGE => 'Dostęp do panelu ustawień',
            BANKING_ACCESS_PRIVILEGE => 'Dostęp do przetwarzania przelewów',
            DBRAPORTS_ACCESS_PRIVILEGE => 'Dostęp do katalogu raportów głównych',
            LETTHER_RETURN_ACCESS_PRIVILEGE => 'Dostęp do wprowadzania zwrotów poczty'
        ] as $index => $label)
            $b->checkbox($label, "p$index")->class("worker_p")->value($index);
        
        $b->subtitle('UPRAWNIENIA USTAWIEŃ');
        
        foreach([
            GENERAL_RESOURCES_CONFIG_ACCESS_PRIVILEGE => 'Zasoby podstawowe',
            BANKING_CONFIG_ACCESS_PRIVILEGE => 'Przetwarzanie przelewów'
        ] as $index => $label)
            $b->checkbox($label, "p$index")->class("worker_p")->value($index);
    }
    
    //--- bookkeeping forms ----------------------------------------------------
    
    protected function transfertype_form(&$b){
        $b->combo('Typ', 'transferdirection', $this->getList('transferdirection'));
    }
    
    protected function transferregex_form(&$b){
        $b->text('Wyrażenie regularne', 'regex')->width('15cm');
        $b->text('Format końcowy', 'format')->width('15cm');
        $b->number('Priorytet', 'priority')->min(0);
    }
    
    protected function bankaccount_form(&$b){
        $b->text('Numer konta', 'accountnumber')->width('15cm');
    }

}