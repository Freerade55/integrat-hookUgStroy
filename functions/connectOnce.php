<?php


function connectOnce(string $link, string $method = 'GET', array $queryData = [], int $try = 0): string
{

    $tokens = file_get_contents(ROOT . "/Tokens.json");
    $tokens = json_decode($tokens, true);

    $headers = [
        "Authorization: Bearer " . $tokens[0]["aToken"]
    ];


    $curl = curl_init();


    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_HEADER, false);


    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($queryData));

    } else if ($method === 'PATCH') {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($queryData));

    }



    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);


    $code = (int)$code;
    $errors = [
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        500 => 'Internal server error',
        501 => 'Not Implemented',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        2002 => 'Nothing found by your request',

    ];


    if ($try === 0) {

        try {

            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            } else {

                return $out;
            }
        } catch (Exception $e) {


            return getTokens($link, $method, $queryData, $try);


        }

    } else {

        if ($code < 200 || $code > 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
        } else {


            return $out;
        }


    }


}




