<?php

function saveHook(array $request) {



    $t = explode(" ",microtime());
    $requestTime = date("Y-m-d H:i:s", $t[1]).substr((string)$t[0],1,4);

    $hook[] = $request;
    $mdTime = md5($requestTime);


    $hook = json_encode($hook, JSON_UNESCAPED_UNICODE);
    file_put_contents(ROOT . "/hooks/{$requestTime}_$mdTime.json", $hook);
}







