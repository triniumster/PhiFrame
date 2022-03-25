<?php


function json_uni_encode($obj){
    return json_encode($obj, JSON_UNESCAPED_UNICODE);
}

function json_content($obj, $encode = true){
    //ob_start();
    header('Content-Type: application/json');
    echo $encode ? json_encode($obj, JSON_UNESCAPED_UNICODE) : $obj;
    //header('Connection: close');
    //header('Content-Length: '.ob_get_length());
    //ob_end_flush();
    //ob_flush();
    //flush();
}

function json_content_e($obj, $encode = true){
    json_content($obj, $encode);
    exit();
}

function json_error_msg(){
    $json_errors = array(
        JSON_ERROR_NONE => 'Nie wystąpił żaden błąd',
        JSON_ERROR_DEPTH => 'Przekroczono maksymalny poziom zagłębienia',
        JSON_ERROR_CTRL_CHAR => 'Błąd zanku sterującego, prawdopodobnie nieprawidłowo zakodowany',
        JSON_ERROR_SYNTAX => 'Błąd składni',
        JSON_ERROR_STATE_MISMATCH => "Underflow or the modes mismatch",
        JSON_ERROR_UTF8 => "Malformed UTF-8 characters, possibly incorrectly encoded"
    );

    echo $json_errors[json_last_error()];
}

function getPostJson($asArray = true){
    if(!array_key_exists('json', $_POST))
        error('TRY TO GET JSON DATA FROM POST, BUT JSON NOT EXIST (json.php)');
    
    if(($json = json_decode($_POST['json'], $asArray)) === null)
        error("CAN'T DECODE JSON DATA. ERROR ".json_last_error().": ".json_error_msg());
    
    return $json;
}

function message($msg){
    if($msg !== null && $msg !== ''){   
        json_content(['message' =>  $msg]);
        exit; 
    }
}

function error($msg, $exmsg = ''){
    if($msg !== null && $msg !== ''){   
        json_content(['error' =>  $msg.$exmsg]);
        exit; 
    }
}

function done($data = null){
    json_content_e($data == null ? ['status' => 'done'] : $data);
}

