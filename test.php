<?php

require __DIR__."/vendor/autoload.php";
use GuzzleHttp\Client;

$BIN_KEY = BIN_KEY;

$client = new Client([
    // Base URI is used with relative requests
    // 'base_uri' => 'https://localhost/aza-explorers',
    'base_uri' => 'https://json.extendsclass.com/bin/'.$BIN_KEY,
    'headers' => [
        'Security-key' => SECURITY_KEY,
        'Api-key' => API_KEY,
    ],
    'timeout'  => 2.0,
]);

function print_response($dictionary = [], $error = "ERR"){
    $string = "";
    
    # Convert our dictionary into a JSON string:
    $string = "data: {\"error\" : \"$error\",
                \"command\" : \"$_REQUEST[command]\",
                \"data\" : ". json_encode($dictionary) ."}";
    
    # Print out our json to Godot!
    echo $string;
}

$res = $client->request('GET', '');

// echo $res->getStatusCode()."<br>";
// echo $res->getHeader('content-type')[0]."<br>";
$data = json_decode($res->getBody(), true);

echo json_encode($data);
?>
