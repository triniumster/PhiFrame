<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any waranty 
 * 
 */

class Session extends PhiFrameWidget {
    protected $isSession = false;
    protected $isLogin = false;
    protected $visibleLoginForm = false;
    protected $message = "";

    public function __init(&$cfg, &$conn){
        $conn != null || die("Critical error: Database connection is null.");
            
        $sm = isset($cfg['session']['db_table']) ? new MySqlSession($cfg['session']) : new PhpSession();

        if(fstr_null('session_logout') == null &&
            $sm->is('userid') &&
            $sm->is('ip', sha1(fip('REMOTE_ADDR'))) &&
            $sm->is('browser', sha1(fstr('HTTP_USER_AGENT', INPUT_SERVER))) &&
            $conn->getRecordSE('session_get_user_data', $sm->userid()) !== false)
                $this->isSession = true;
        elseif(($login = fstr('username')) && $login != '' && ($pass = fstr('password')) && $pass != ''){
            if(($user = $conn->getRecordSE('session_sign_user_by_login_and_hash', [$login, sha1($pass)])) !== false){
                $sm->userid($user['id']);
                $sm->ip(sha1(fip('REMOTE_ADDR')));
                $sm->browser(sha1(fstr('HTTP_USER_AGENT', INPUT_SERVER)));
                $sm->setLifeTime(fstr('rememberme') == 'on' ? 60*60*24*7 : 0);
                $this->isSession = true;
            } else {
                $this->visibleLoginForm = true;
                $this->message = "Nieprawidłowy login lub hasło";
            }
        }

        if($this->isSession && 
          ($conn = ExtendedMySQLi::open($cfg['session']['db_address'], $cfg['session']['db_login'], $cfg['session']['db_password'], $cfg['session']['db_name'], $this->message))){
            $sm->regenerateId();

            $user = $conn->getRecordSE('session_get_user_data', $sm->get('userid'));
            $cfg['userPrivileges'] = $user['privileges'];
            $cfg['userID'] = $sm->get('userid');
            $cfg['userFullName'] = $user['name'];
            
            if(fstr('session_pass') == 'pass'){
                if(($npa = fstr('npa')) && ($npb = fstr('npb'))){
                    if(trim($npa) == '' || trim($npb) == '')
                        message('Jedno z pól jest puste');

                    if($npa != $npb)
                        message('Pola nie są identyczne');

                    if($conn->squery('session_update_user_password', [$sm->get('userid'), sha1($npa)]) === true)
                        done();
                    else
                        error("Błąd przy zmianie hasła: ".$conn->error);
                } else
                    message('Jedno z pól jest puste');
            }
        } else {
            $this->isSession = false;
            $sm->destroy();
            $cfg['userPrivileges'] = 0;
            $cfg['userID'] = -1;
            $cfg['userFullName'] = '';
            $this->printLoginForm();
            exit();
        }

        $cfg['islogin'] = $this->isSession;
        $cfg['messagebar']['message'] .= $this->message;

        if(array_key_exists('menubar', $cfg)){
            if($this->isSession){
                $cfg['menubar'][] = ['title' => $cfg['userFullName'], 'list' => [
                    ['title' => 'Hasło', 'onclick' => "showDialog('#phiframe-passform')"],
                    ['title' => 'Wyloguj', 'onclick' => "document.forms['logout'].submit(); return null;"]
                ]];
            } else {
                $cfg['menubar'][] = ['title' => 'Zaloguj', 'onclick' => "showDialog('#phiframe-loginform')"];
            }
        }
    }
    
    private function printLoginForm(){
        $h = new html();
        $h->head()->link()->href(PHIFRAME_LIB_PATH.'/styles/style.PhiFrame.Session.css')->rel('stylesheet')->type('text/css');
        $loginFormDiv = $h->body()->div('#phiframe-loginform; .phiframe-loginform');
        $loginForm = $loginFormDiv->center()->form()->method('post')->table('.phiframe-session-formtable');
        $loginForm->tr()->th()->colspan(2)->html('LOGOWANIE');
        $loginForm->tr()->td('.label top')->html('ID:')->par()->td('.field top')->input('*text')->name('username')->required();
        $loginForm->tr()->td('.label')->html('Hasło:')->par()->td('.field')->input('*password')->name('password')->required();
        $loginForm->tr('.remember')->td('.cb top')->colspan(2)->input('*checkbox')->name('rememberme')->par()->str('Pamiętaj logowanie');
        $loginForm->tr()->td('.cb btn')->colspan(2)->input('*submit; =Zaloguj');
        echo $h;
    }
    
    protected function __code(){   
        ?>
        <div id="phiframe-session" style="display: none">
            <script> 
                function changePassword(){
                    Ajax.send({session_pass: 'pass', npa: $("#passA").val(), npb: $("#passB").val()}, function(){
                        alert("Hasło zostało zmienione");
                        hideDialog();
                    });  
                }  
            </script>
        </div>
        <?php
        $mode = ''; // -light

        $passFormDiv = new html('div', '#phiframe-passform; .phiframe-passform'.$mode);
        $passFormDiv->display('none');
        $passForm = $passFormDiv->center()->table('.phiframe-session-formtable'.$mode);
        $passForm->tr()->th()->colspan(2)->html('ZMIANA HASŁA');
        $passForm->tr()->td('.label top')->html('Hasło:')->par()->td('.field top')->input('#passA; *password');
        $passForm->tr()->td('.label')->html('Powtórz hasło:')->par()->td('.field')->input('#passB; *password');
        $passForm->tr()->td('.cb btn')->colspan(2)->input('*button; =Zmień')->onclick("changePassword()")->par()->input('*button; =Anuluj')->onclick("hideDialog()");
        echo $passFormDiv;

        $logoutForm = new html('form');
        $logoutForm->method('POST')->name('logout')->input('*hidden; =logout')->name('session_logout');
        echo $logoutForm;
    }
}


        

