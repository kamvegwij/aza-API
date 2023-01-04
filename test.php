<?php

require __DIR__."/vendor/autoload.php";
use GuzzleHttp\Client;

$BIN_KEY = "4acde40497d4";

$client = new Client([
    // Base URI is used with relative requests
    // 'base_uri' => 'https://localhost/aza-explorers',
    'base_uri' => 'https://json.extendsclass.com/bin/'.$BIN_KEY,
    'headers' => [
        'Security-key' => '12345',
        'Api-key' => '3347f9ef-70a6-11ed-8b32-0242ac110002',
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
