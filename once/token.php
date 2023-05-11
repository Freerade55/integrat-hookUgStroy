<?php

const ROOT = __DIR__ . "/..";

require ROOT . "/functions/require.php";

$link = "https://" . $_ENV["SUBDOMAIN"] . ".amocrm.ru/oauth2/access_token";


$data = [
    "client_id" => $_ENV["CLIENT_ID"],
    "client_secret" => $_ENV["CLIENT_SECRET"],
    "grant_type" => "authorization_code",
    "code" => $_ENV["CODE"],
    "redirect_uri" => $_ENV["REDIRECT_URL"]
];


$curl = curl_init();

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-oAuth-client/1.0");
curl_setopt($curl, CURLOPT_URL, $link);
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type:application/json"]);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
$out = curl_exec($curl);
curl_close($curl);


$response = json_decode($out, true);

$start_project[0]["rToken"] = $response["refresh_token"];
$start_project[0]["aToken"] = $response["access_token"];

$jsonData = json_encode($start_project);
file_put_contents(ROOT . "/Tokens.json", $jsonData);



