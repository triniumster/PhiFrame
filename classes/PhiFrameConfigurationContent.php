<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class PhiFrameConfigurationContent extends PhiFrameContent {
    protected $structure = null;
    private $errorEcho = '';
    private $id;
    
    public function loadStyles() {
        Ajax::style();
        ObjectsList::style();
        TabedPane::style();
        parent::loadStyles();
    }
    
    public function loadScripts(){
        Ajax::script();
        FileUpload::script();
        TabedPane::script();
        ObjectsList::script();
        VerticalCollapsedMenu::script();
        
        ?>
            <script>
                singleConfigurationSave = function(prefix, cfg){
                    ScreenDialog.lockScreen('Zapisywanie', 1000);
                    var data = {};

                    $('.prefix_'+prefix+':enabled').each(function(idx, elm){
                        var e = $(elm);
                        data[prefix+'_'+e.attr('subid')] = e.val();
                    });
                    
                    if(Object.keys(data).length > 0){
                        Ajax.send({
                            action: 'singleConfigurationSave',
                            json: JSON.stringify(data),
                            cfg: cfg
                        }, function(){
                            ScreenDialog.unlockScreen();
                        }, function(lvl, result){
                            if(typeof result !== 'object')
                                Ajax.showDialog('', result);
                            else
                                alert(result.error);

                            ScreenDialog.unlockScreen(true);
                        });
                    } else {
                        alert('Żadne pole nie jest aktywne');
                    }
                };
            </script>
        <?php
        
        parent::loadScripts();
    }
  
    public function runAjax(){
        // file upload action
        FileUpload::ajaxServe(function($subaction, $fileinfo){
            $this->__fileUpload($subaction, $fileinfo);
        });
        
        // vertical left menu action
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
        
        // panels tabs actions
        TabedPane::ajaxServe(function($contentID){
            $a = $this->prepareObjectList($contentID);
            
            if($a == null)
                return 'Content not found: '.$contentID;

            
            $a->addList($this->getList($a->getDBTableName(), $a->getSortColumnName(), $a->page, $a->pagesize, $a->lastpage));
            return $a;
        });
        
        // tabs lists refresh action
        ObjectsList::ajaxServe(function($contentID){
            $a = $this->prepareObjectList($contentID);
            
            if($a == null)
                return 'Content not found: '.$contentID;

            $a->addList($this->getList($a->getDBTableName(), $a->getSortColumnName(), $a->page, $a->pagesize, $a->lastpage, $a->getFilter()));
            
            return ['html' => $a->getNewContentHtml()];
        });
                
        // single configuration save event action
        $action = fstr("action");
        
        if($action === 'singleConfigurationSave'){
            $data = getPostJson();
            $cfg = fstr('cfg');
         
            $gcfg = $this->getSingleConfigurationContentData($cfg);
            $q = '';
            $notChange = true;
            
            foreach($data as $key => $value){
                if(!array_key_exists($key, $gcfg))
                        error("ERROR: key `$key` not found in defined location");
                
                if($gcfg[$key] != $value){
                    $notChange &= false;
                
                    if($q != '')
                        $q .= ', ';

                    $q .= "`$key` = '$value'";
                }
            }
            
            if($notChange)
                done();
            
            $a = $this->getSingleConfigurationContentLocation($cfg);
            $this->conn->query("UPDATE `".$a[0]."`.`".$a[1]."` SET ".$q." WHERE `active` = TRUE");
            
            if($this->conn->affected_rows > 1)
                error("ERROR: configLocation contains more then 1 active record, affected records: ".$this->conn->affected_rows);
            elseif($this->conn->affected_rows < 1)
                error("ERROR: settigs not saved. ".$this->conn->error);

            done();
        }
        
        // get, manage and delete event actions
        if(is_array($this->structure) && ($action === 'get' || $action === 'manage' || $action === 'delete')){
            $type = filter_input(INPUT_POST, "type", FILTER_SANITIZE_STRING);
            $this->id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
 
            $struct = null;
            
            foreach($this->structure as $dat)
                if(is_array($dat) && key_exists($type, $dat)){
                    $struct = $dat[$type];
                    break;
                }
            
            if($struct !== null){
                if($action === 'get'){
                    $this->actionGet($type, $struct);
                } elseif($action === 'manage'){
                    $this->actionManage($type, $struct);
                } elseif($action === 'delete'){
                    $this->actionDelete($type, $struct);
                }
            }
        }

        parent::runAjax();
    }
    
    protected function __fileUpload($subaction, $fileinfo){
        // for action in user script
    }

    protected function actionGet($type, $struct){
        $dbtable = key_exists('dbtable', $struct) ? $struct['dbtable'] : $type;
        
        if(($dat = $this->conn->getRecord("SELECT * FROM `{$dbtable}` WHERE `{$dbtable}ID` = $this->id"))){
            if(sizeof($struct[1]) > 0){
                $fields = '';

                foreach(array_keys($struct[1]) as $field)
                    if(substr($field, 0, 1) != '*')
                        $fields .= ($fields !== '' ? ';' : '').$field;

                $dat['fields'] = $fields;
            }

            if(sizeof($struct[3]) > 0)              
                foreach($struct[3] as $set) {
                    $this->mats = [];
                    
                    $this->conn->getRecords("SELECT * FROM `$set[0]` WHERE `{$dbtable}ID` = $this->id", null, function($mat){
                        $this->mats[] = $mat;
                    });

                    $dat['mats_'.$set[0]] = $this->mats;
                }

            echo json_content($dat);
        } 
    }
    
    protected function actionManage($type, $struct){
        $this->errorEcho = null;
        $struct[1]['name'] = s;
        $dbtable = key_exists('dbtable', $struct) ? $struct['dbtable'] : $type;

        if(sizeof($struct[1]) > 0)
            foreach ($struct[1] as $field => $filter) {
                if(substr($field, 0, 1) == '*')
                    $field = substr($field, 1);
                
                $$field = filter_input(INPUT_POST, $field, $filter);
            }

        if($this->id > 0){
            $set = '';

            foreach($struct[1] as $field => $filter){
                if(substr($field, 0, 1) == '*')
                    $field = substr($field, 1);
                
                $set .= ($set !== '' ? ',' : '')."`$field` = '".$$field."'";
            }

            $this->conn->updateRecord("UPDATE `{$dbtable}` SET $set WHERE `{$dbtable}ID` = $this->id");

            if(sizeof($struct[3]) > 0)
                foreach ($struct[3] as $asso){
                    $table = $asso[0];
                    $flds = $asso[1];
                    
                    $this->conn->deleteRecord("DELETE FROM `$table` WHERE `{$dbtable}ID` = '$this->id'");
                    $objs = json_decode($_POST["data_$table"]);

                    if(sizeof($objs) > 0){
                        $query = "INSERT INTO `$table` VALUES ";
                        $first = true;

                        foreach($objs as $obj){
                            $obj->tid = $this->id;
                            $fv = '';

                            foreach($flds as $fld)
                                $fv .= ($fv === '' ? '' : ',')."'".$obj->{$fld}."'";

                            $query .= ($first ? ' ' : ',')."($fv)";
                            $first = false;
                        }

                        $this->conn->insertRecord($query); 
                    }
                }  
        } else {
            $setn = $dbtable.'ID';
            $setv = 'NULL';

            foreach($struct[1] as $field => $filter){
                if(substr($field, 0, 1) == '*')
                    $field = substr($field, 1);
                
                $setn .= ", `$field`";
                $setv .= ", '".$$field."'";
            }

            $this->id = $this->conn->insertRecord("INSERT INTO `{$dbtable}` ($setn) VALUES ($setv)");
        }

        json_content(['isok' => $this->errorEcho === null, 'msg' => $this->errorEcho]);
    }
    
    protected function actionDelete($type, $struct){
        $this->errorEcho = null;
        $dbtable = key_exists('dbtable', $struct) ? $struct['dbtable'] : $type;
        
        if(sizeof($struct[2]) > 0){
            foreach($struct[2] as $set){
                $table = $set[0];
                $field = $set[1];
                $message = '';
                
                if(isset($set[2]))
                    $message = "<br><br>Sprawdź użycie w:<br>".$set[2];

                $chrs = $this->conn->getRecords("SELECT * FROM `$table` WHERE `$field` = '$this->id' LIMIT 1");
                error($this->conn->error);

                if($chrs !== false)
                    json_content_e(['isok' => false, 'msg' => $message]);
            }
        }
             
        $this->conn->deleteRecord("DELETE FROM `{$dbtable}` WHERE `{$dbtable}ID` = '$this->id'");
        error($this->conn->error);
        
        if(sizeof($struct[3]) > 0)           
            foreach($struct[3] as $set)
                $this->conn->deleteRecord("DELETE FROM `$set[0]` WHERE `{$dbtable}ID` = '$this->id'");

        json_content_e(['isok' => true]);
    }
    
    protected function singleConfigurationContentNode($subst, $cfgLoc = false){
        $gcfg = $this->getSingleConfigurationContentData($cfgLoc);
        $node = new html('');

        foreach($subst as $key => $tcfg){
            if(!is_array($tcfg))
                continue;;
            
            $notFail = true;
            
            $table = $node->table();
            $table->class('config');
            $table->tr()->th()->colspan(3)->html($tcfg[0]);

            foreach($tcfg[1] as $set){
                if(!is_array($set))
                    continue;
                
                $thisNotFail = isset($gcfg[$key.'_'.$set[2]]);
                $notFail &= $thisNotFail;
                
                $tr = $table->tr();
                $tr->td()->html($set[0]);
                $td = $tr->td();
                $input = $td->input('.prefix_'.$key.'; *'.$set[1])->attr('subid', $set[2]);
                
                if($thisNotFail)
                    $input->value($gcfg[$key.'_'.$set[2]]);
                else
                    $input->disabled();

                if($set[1] === 'number'){
                    $input->attr('min', $set[5]);
                    $input->attr('max', $set[6]);
                    $input->attr('step', $set[7]);
                }

                $tr->td()->html($set[4]);
            }
            
            $sbt = $table->tr()->td()->colspan(3)->style('padding: 3mm; text-align: center')->input('*button; =Zapisz')->width('3cm')->onclick("singleConfigurationSave('".$key."', '$cfgLoc')");

            if(!$notFail)
                $sbt->disabled();
        }
        
        return $node;
    }
    
    private function getSingleConfigurationContentData($cfgLoc){
        $a = $this->getSingleConfigurationContentLocation($cfgLoc);

        if(($gcfg = $this->conn->getRecordWEE("SELECT * FROM `".$a[0]."`.`".$a[1]."` WHERE `active` = TRUE")) === false)
            error("ERROR: GLOBAL CONFIGURATION RECORD NOT FOUND! (Add record to @#! with value in column `active` = true)");
        
        return $gcfg;
    }
    
    private function getSingleConfigurationContentLocation($cfgLoc){
        if(array_key_exists('configLocation', $this->cfg)){
            $ax = $this->cfg['configLocation'];

            if(is_array($ax)){
                if(array_key_exists($cfgLoc, $ax)){
                    $ay = $ax[$cfgLoc];
                } else
                    error("ERROR: BAD FUNCTION ARGUMENT (cfgLoc for singleConfigurationContent) OR MISSING SETTING (configLocation => cfgLoc)");
            } else
                $ay = $ax;
        } else
            error("ERROR: MISSING SETTING (`this content` => configLocation)");
            
        $a = explode('.', str_replace(' ', '', str_replace('`', '', $ay)));

        if(sizeof($a) != 2)
            error("ERROR: BAD FUNCTION ARGUMENT (configLocation for SingleConfigContent)");
        
        return $a;
    }
    
    protected function prepareObjectList($contentID){
        $struct = null;

        foreach($this->structure as $dat)
            if(is_array($dat) && key_exists($contentID, $dat)){
                $struct = $dat[$contentID];
                break;
            }

        if($struct == null)
            return null;

        $a = new ObjectsList($contentID, $struct[0]);
        $a->enableDeleteObject(key_exists('enableDelete', $struct) ? $struct['enableDelete'] : is_array($struct[1]));
        $a->enableObjectEdit(key_exists('editableObject', $struct) ? $struct['editableObject'] : true);
        $a->enableName(key_exists('enableName', $struct) ? $struct['enableName'] : true);
        $a->setDBTableName(key_exists('dbtable', $struct) ? $struct['dbtable'] : $contentID);
        $a->setSortColumnName(key_exists('sortByColumn', $struct) ? $struct['sortByColumn'] : (isset($struct[4]) && gettype($struct[4]) == 'string' && $struct[4] != '' ? $struct[4] : 'name'));
        
        if(method_exists($this, $contentID."_bar"))
            $this->{$contentID."_bar"}($a->bar);

        if(method_exists($this, $contentID."_form"))
            $this->{$contentID."_form"}($a);
            
        if(method_exists($this, $contentID."_config"))
            $this->{$contentID."_config"}($a, $a);
            
        return $a;
    }

    protected function getList($table, $order = 'name', $page = 0, int $pagesize = 100, int &$lastpage = 0, string $exFilter = ''){
        $orderT = substr($order, 0, 2) == "--" ? "`".substr($order, 2)."` DESC" : "`$order`";
        
        if($page > 0){
            $c = $this->conn->getRecordWEE('settings_c', $table);
            $lastpage = ceil($c['count'] / $pagesize);
            return $this->conn->getRecordsAsArray('settings_b', [$table, $orderT, ($page-1)*$pagesize, $pagesize, $exFilter]);
        } else
            return $this->conn->getRecordsAsArray('settings_a', [$table, $orderT, $exFilter]);
    }
    
    

    
    
    
    
    
    
}
