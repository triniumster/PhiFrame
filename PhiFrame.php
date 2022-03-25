<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

/*******************************************************************************

%. ABOUT 

PhiFrame is very simple side frame with files autoload.



%. PATH TREE

Files tree structure for work with PhiFrame is very static and unconfirugable
for simplicity, most of them is optional, maximum option look like that:
 
main_project_dirictory
    - libs or libraries (One of them must exist. If both exist, the `libraries` directory is used)
        - jquery (optional, if exist links to all .js and .css files will be added to main side content)
        - phiframe (all files in this dir contain orginal elements of PhiFrame)
            - classes (optional)
            - functions (optional)
            - interfaces (optional)
            - scripts (optional)
            - styles (optional)
            - widgets (optional) 
            - PhiFrame.php (Core file of PhiFrame, is not required be here becouse is loaded manualy in index.php before first use, but this is good place for it)
    - classes (optional)
    - contents (optional if you want only run pure side frame without any unique content)
    - functions (optional)
    - scripts (optional)
    - styles (optional)
 
Any extra dirictories not disturb work of PhiFrame.
All files and dirs names must be in lowercase letters.

%. CLASSES AND INTERFACES

   Is two dirs dedicated for user files, it is `classes` and `interfaces`, and
   few next for external libraries. Is no mether to with one you put your classes
   or interfaces, becouse PHP build in loader not say what they serch and call to
   this same method, and going on PhiFrame dont check that. Classes and interfaces
   is loaded when it is used (PhiFrame not load all files on start). PhiFrame
   looking for classes and interfaces in the following order (first found is
   loaded, if is more then one class or interface with that same name):

    `classes`
    `interfaces`
    `libraries\phiframe\classes`
    `libraries\phiframe\interfaces`
    `libraries`

   Off course is able to load file with class or interface or any other manualy
   in content .php

   One word more about looking in `libraries`, it is more complex becouse,
   not only serch one file but also dir with class name and load all files inside.

%. FUNCTIONS

    All functions in `functions` and `libraries\phiframe\functions` are loaded
    on start. (PHP not distribute autoload methods for functions, it is posible
    to make autoload callback but

%. CONTENT DIR

    Is defined two ways to add contents:

    a) Single file content: Simply add .php to `contents` dir. All required
       scripts, styles and actions will be writen inside it.

    b) Multi file content: Create dir inside `contents` and put there all required
       files, like .js, css. and .php. Links to .js and .css will be automaticly
       added to main content. File with content name (that same as parent dirictory)
       will be loaded after load other .php files.






*******************************************************************************/

define('RUN_MODE_DEV', 'DEV');
define('RUN_MODE_TEST', 'TEST');
define('RUN_MODE_PROD', 'PROD');

require_once "config.php";

use function array_key_exists as ake;
define('privilege', 'privilege');



class PhiFrame { 
    protected $ajax;
    protected $command;
    protected $configuration;
    protected $title;
    
    protected $message = "";
    protected $isLogIn;
    protected $currentPlugin;
    protected $currentPluginPath;
    protected $currentPluginClassName;
    
    protected $phiframeDir;
    protected $jqueryDir;
    protected $stylesDir;
    protected $contentsDir;
    protected $scriptsDir;
    protected $widgetsDir;
    protected $functionsDir;
    protected $classesDir;
    protected $interfacesDir;
    
    protected $jscss = [];
    protected $hncls = [];
    
    protected $loadedWidgets;
    public $conn = null;
    
    public function __construct($tlt, $cfg, $autoprint = true) {
        if(!defined('RUN_MODE')){
            die("Not defined RUN_MODE in config.php");
        }
        
        if(!in_array(RUN_MODE, [RUN_MODE_DEV, RUN_MODE_TEST, RUN_MODE_PROD])){
            die("Uncorrect value of RUN_MODE in config.php");
        }
        
        
        
        $this->configuration = $cfg;
        
        $defs = [
            'userPrivileges' => 0,
            'main_color' => '#A00'
        ];
        
        foreach ($defs as $key => $value)
            if(!ake($key, $this->configuration))
                $this->configuration[$key] = $value;

        if(!ake('messagebar', $cfg) || !ake('message', $cfg['messagebar']))
            $this->configuration['messagebar']['message'] = "";
        
        $this->title = $tlt;
        ($this->command = filter_input(INPUT_GET, 'cmd', FILTER_SANITIZE_STRING)) || ($this->command = filter_input(INPUT_POST, 'cmd', FILTER_SANITIZE_STRING)) || ($this->command = '');
        $this->ajax = filter_input(INPUT_GET, "ajax",  FILTER_VALIDATE_BOOLEAN) == true || filter_input(INPUT_POST, "ajax",  FILTER_VALIDATE_BOOLEAN) == true;
        
        // SET TIMEZONE ========================================================
        
        if(ake('timezone', $cfg) && !@date_default_timezone_set($cfg['timezone']))
            die('PhiFrame configuration error: "'.$cfg['timezone'].'" is not supportet timezone.<br>Check <a href=\'http://php.net/manual/en/timezones.php\'>timezones list</a> for select correct one.');
                
        // DEFINE CURRENT CLIENT BROWSER =======================================
        define("MSIE", 'msie');
        define("FIREFOX", 'ff');
        define("CHROME", 'ch');
        define("OPERA_MINI", 'om');
        define("OPERA", 'op');
        define("SAFARI", 'sf');

        foreach(['msie' => MSIE, 'MSIE' => MSIE, 'trident' => MSIE, 'Trident' => MSIE, 'firefox' => FIREFOX, 'chrome' => CHROME, 'opera mini' => OPERA_MINI, 'Opera' => OPERA, 'safari' => SAFARI] as $key => $short)
            if(strpos(filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING), $key) !== false){
                define('BROWSER', $short);
                break;
            }
            
        if(!defined('BROWSER'))
            define('BROWSER', 'unrecognized');
        
        // SETUP CORE DIRECTORIES ==============================================
        
        foreach(['libraries', 'libs'] as $dir)
            if(($this->exLib = $this->setupDir($dir)) != null)
                break;
            
        if($this->exLib == null)
            die('PhiFrame: dir `libs` and `libraries` not found. One of them must exist.');
            
        $this->phiframeDir = $this->setupDir($this->exLib.'/phiframe');
        define('PHIFRAME_LIB_PATH', $this->phiframeDir);
        
        // SETUP USER DIRECTORIES ==============================================
        
        foreach(['styles', 'contents', 'scripts', 'functions', 'classes', 'interfaces', 'widgets'] as $loc){
            $this->{$loc.'Dir'} = $this->setupDir($loc);
        }
        
        // SETUP CLASSES AND INTERFACES AUTOLOADING ============================
        
        spl_autoload_register(function($className){
            foreach([$this->classesDir, $this->interfacesDir, $this->phiframeDir.'/classes', $this->phiframeDir.'/interfaces', $this->exLib] as $dir){  
                
                if($dir == null)
                    continue;
                
                $d = $dir.'/'.$className.'.php';
                    
                if(!file_exists($d) || is_dir($d))
                    continue;
                
                require_once $d;
                return true;
            }
                
            foreach([$this->classesDir, $this->phiframeDir.'/classes', $this->exLib] as $dir){
                if($dir == null)
                    continue;
                
                $d = $dir.'/'.$className;
                    
                if(!file_exists($d) || !is_dir($d))
                    continue;
                    
                foreach(glob("$d/*.php") as $f)
                    require_once $f;

                foreach(glob("$d/*.{js,css}", GLOB_BRACE) as $f)
                    $this->jscss[] = $f;

                return true;    
            }
                
            return false;
        });

        // LOAD FUNCTIONS ======================================================
        
        $this->loadDir("$this->phiframeDir/functions");
        $this->loadDir($this->functionsDir);
        
        // SETUP DEFAULT MYSQL CONNECTION ======================================
        
        if($this->conn == null && ake('db_address', $cfg) && ake('db_login', $cfg) && ake('db_password', $cfg))
            $this->conn = ExtendedMySQLi::open($this->configuration['db_address'], $this->configuration['db_login'], $this->configuration['db_password'], $this->configuration['db_name'], $this->message);

        // LOAD CONTENT CONFIG =================================================
        
        $neededPrivileges = 0;
        $okCmd = false;
        
        if(ake('cmd', $cfg) && ake($this->command, $cfg["cmd"])){
            $cmdCfg = $cfg["cmd"][$this->command];
            
            if(ake(privilege, $cmdCfg))
                 $neededPrivileges = $cmdCfg[privilege];
            
            if(ake('real', $cmdCfg)){
                $this->command = $cmdCfg['real'];
                $okCmd = true;
            } elseif(ake('cmdRealPathes', $cfg) && $cfg['cmdRealPathes'] === true)
                $okCmd = true;
        }

        // PREPARE CONTENT PATH ================================================
        
        $this->currentPluginPath = null;
        $serchPlugin = [];
 
        if($okCmd){
            $serchPlugin[] = $this->command;
            $serchPlugin[] = $this->command.'.php';
        }
        
        $serchPlugin[] = 'main';
        $serchPlugin[] = 'main.php';

        foreach($serchPlugin as $contentName){
            if($contentName != '' && file_exists("$this->contentsDir/$contentName")){
                $this->currentPluginPath = "$this->contentsDir/$contentName";
                $currentPluginFilename = $contentName;
                break;
            }
        }
        
        if($this->currentPluginPath == null)
            die("PhiFrame file error: selected content '$this->command' and default 'main' content not exist.<br>Check configuration or create content.");
        
        // LOAD CONTENT PLUGIN =================================================
        
        $this->currentPlugin = null;
        $this->currentPluginClassName = $currentPluginFilename;
        
        if(is_dir($this->currentPluginPath)){
            $this->loadDir($this->currentPluginPath);
        } else {
            require_once $this->currentPluginPath;
            $this->currentPluginClassName = substr($currentPluginFilename, 0, -4);
        }
            
        if(!class_exists($this->currentPluginClassName))
            die("PhiFrame file error: cant't load file '$this->currentPluginPath/$this->currentPluginClassName.php'.<br>Check content file or directory.");
    
        $interfaces = class_implements($this->currentPluginClassName);

        if(!($interfaces && in_array('PhiFrameContentInterface', $interfaces)))
            die("PhiFrame file error: content class '$this->currentPluginClassName' not implemented 'PhiFrameContentInterface' interface.<br>Check content class script.");
              
        $this->currentPlugin = new $this->currentPluginClassName($this->configuration);   
        $this->currentPlugin->setMySqlConnection($this->conn);
                
        if(($key = fstr_null('key', INPUT_GET)) != null && $key == $this->currentPlugin->queryKey){
            $this->currentPlugin->__kwerenda();
            exit();
        }
        
        // LOAD WIDGETS ========================================================
        
        foreach([
            glob("$this->widgetsDir/*.Session.php"),
            glob("$this->widgetsDir/*.session.php"),
            glob("$this->phiframeDir/widgets/*.Session.php"),
            glob("$this->phiframeDir/widgets/*.session.php"),
            glob("$this->widgetsDir/*.php"),
            glob("$this->phiframeDir/widgets/*.php"),
        ] as $fileslist)
            foreach($fileslist as $fileName){
                if(!file_exists($fileName) || is_dir($fileName))
                    continue;

                $fe = explode(".", $fileName);
                $classname = $fe[sizeof($fe)-2];

                if(class_exists($classname, false))
                    continue;

                include_once $fileName;

                $cls = strtolower($classname);

                if((ake($cls.'_enabled', $this->configuration) && $this->configuration[$cls.'_enabled'] == false))
                    continue;

                if(!class_exists($classname))
                    continue;

                $interfaces = class_implements($classname);

                if($interfaces && in_array('PhiFrameWidgetInterface', $interfaces)) {
                    $widget = new $classname($this->configuration);
                    $widget->setCommand($this->command);
                    $widget->initWidget($this->configuration, $this->conn);
                    $this->loadedWidgets[] = $widget;
                } 
            }
        
        // CHECK PRIVILEGES FOR CONTENT ========================================
        
        if(!comparePrivileges($this->configuration['userPrivileges'], $neededPrivileges)){
            header("Location: index.php");
            exit();
        }
        
        // SET MORE CONFIGURATION TO PLUGIN ====================================
        
        $this->currentPlugin->setMySqlConnection($this->conn); // UPDATE DATABASE CONNECTION - BEFOR WAS DEFAULT (SELECTION ONLY)
        $this->currentPlugin->setPrivileges($this->configuration['userPrivileges']);
        $this->currentPlugin->setUserID($this->configuration['userID']);
        $this->currentPlugin->setUserFullName($this->configuration['userFullName']);
        $this->currentPlugin->setCfg(ake($this->command, $cfg) ? $this->configuration[$this->command] : null);
        $this->currentPlugin->__init();
        
        if($this->ajax){
            $this->currentPlugin->runAjax();
            exit();
        }

        $this->configuration['titlebar']['title'] = $this->currentPlugin->title();
        $this->title .= $this->currentPlugin->tabTitle();
        
        // PRINT OUT SIDE CODE    
        
        if($autoprint)
            $this->echoSide();
    } 
     
    private function setupDir($dir, $required = false){
        if(file_exists("./$dir") && is_dir("./$dir"))
            return "./$dir";
        elseif(!$required)
            return null;

        die("PhiFrame: path './$dir' not exist.");
    }
    
    private function loadDir($dir){
        if(file_exists($dir) && is_dir($dir))
            foreach (glob("$dir/*.php") as $filename)
                require_once $filename;
    }
  

    public function echoSide(){
        ?>
        <!DOCTYPE HTML>
        <html> 
            <head>
                <meta charset="UTF-8">
                <?php 
                    echo "<title>$this->title</title>";
                    $this->loadStylesScripts($this->exLib.'/jquery');
                    Ajax::script();
                ?>
                <script>
                    $(function(){
                        window.onResizeListeners = [];
                        
                        window.appendOnResizeListener = function(index, callback){
                            if((typeof index !== 'number' && typeof index !== 'string') || typeof callback !== 'function')
                                return false;
                            
                            window.onResizeListeners[index] = callback;
                            return true;
                        };

                        window.removeOnResizeListener = function(index){
                            if((typeof index !== 'number' && typeof index !== 'string') || !window.onResizeListeners.hasOwnProperty(index))
                                return false;
                            
                            delete window.onResizeListeners[index];
                            return true;    
                        };

                        window.onresize = function(event){
                            for(var listener in window.onResizeListeners)
                                if(typeof window.onResizeListeners[listener] === 'function')
                                    window.onResizeListeners[listener](event);
                        };

                        window.onClickListeners = {};
                        
                        window.appendOnClickListener = function(index, callback){
                            if((typeof index !== 'number' && typeof index !== 'string') || typeof callback !== 'function')
                                return false;
                            
                            window.onClickListeners[index] = callback;
                            return true;
                        };

                        window.removeOnClickListener = function(index){
                            if((typeof index !== 'number' && typeof index !== 'string') || !window.onClickListeners.hasOwnProperty(index))
                                return false;
                            
                            delete window.onClickListeners[index];
                            return true;    
                        };

                        window.onclick = function(event){
                            for(var listener in window.onClickListeners)
                                if(typeof window.onClickListeners[listener] === 'function')
                                    window.onClickListeners[listener](event);
                            
                            event.stopPropagation();
                        };
                    });
                </script>
                <?php
                    foreach(["$this->phiframeDir/styles", "$this->phiframeDir/scripts"] as $loc)
                        $this->loadStylesScripts($loc);
                    
                    foreach(['Scripts', 'Styles'] as $t)
                        if(is_array(($s = $this->currentPlugin->{"load$t"}())))
                            foreach($s as $file)
                                $this->printFileLink($file);
                        
                    $this->loadStylesScripts($this->currentPluginPath);
                ?>
                <script>
                    $(function(){
            
                        
                        window.onresize();
                    });
                </script>
            </head>
            <body>
                <?php
                if($this->currentPlugin == null){
                    echo "PhiFrame: Can't load content class.";
                }elseif($this->currentPlugin->plain()){
                    if($this->currentPlugin){
                        $this->currentPlugin->__content();
                        $this->currentPlugin->updateMessage($this->message);
                        echo "<center style='color: red'>$this->message</center>";
                    }
                } else { ?>
                    <!--<div id='phiframe-wrapper'>-->
                        <div id="phiframe-content" <?php  echo "class='".BROWSER."'"; ?>>
                            <?php
                                if($this->currentPlugin){
                                    $this->currentPlugin->__content();
                                    $this->currentPlugin->updateMessage($this->message);
                                    $this->configuration['messagebar']['message'] .= $this->message;
                                }
                            ?>
                        </div>
                        <?php
                            foreach($this->loadedWidgets as $widget){
                                $cls = strtolower(get_class($widget));
                                $widget->updateConfig(ake($cls, $this->configuration) ? $this->configuration[$cls] : []);
                                $widget->echoWidget();
                            } 
                            
                            Ajax::content();
                        ?>
                    <!--</div>-->
                <?php }?>
            </body> 
        </html>
        <?php
    }
    
    function loadStylesScripts($path){
        if(!file_exists($path))
            return false;
        
        if(is_dir($path)){
            if(!(BROWSER && $this->loadStylesScripts("$path/".BROWSER)))
                foreach(scandir($path) as $file)
                    $this->printFileLink("$path/$file");  
            
            return true;
        } else {
            $classname = get_class($this->currentPlugin);
            
            foreach ([$this->scriptsDir, $this->stylesDir] as $dir)    
                foreach (glob("$dir/*$classname.*") as $file)
                    $this->printFileLink($file);
        }

        return false;
    }
    
    function printFileLink($path){
        $rnd = randomKey();

        switch(substr(strrchr($path, "."), 1)){
            case 'css': echo "<link href='$path?rnd=$rnd' rel='stylesheet' type='text/css'>"; break;
            case 'js': echo "<script type='text/javascript' src='$path?rnd=$rnd'></script>";
        }
    }
}