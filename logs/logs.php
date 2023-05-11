<?php
//
////// логи
//
function logs()
{
    if (file_exists(ROOT . "/hook.json")) {
        $log = file_get_contents(ROOT . "/hook.json");
        $log = json_decode($log, true);
    } else {
        $log = [];
    }

    $t = explode(" ",microtime());
    $log[date("Y-m-d H:i:s", $t[1]).substr((string)$t[0],1,4)] = $_REQUEST;
    $log = json_encode($log, JSON_UNESCAPED_UNICODE);
    file_put_contents(ROOT . "/hook.json", $log);
}

function json_print($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
