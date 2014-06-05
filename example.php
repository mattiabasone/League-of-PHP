<?php

// Include the library
require 'LeagueOfPHP.class.php';

// Instance the API
$api = new LeagueOfPHP('KEY', 'euw');


// Example request
$api->request('summoner/by-name/roobre', '1.3');

// Print the response
print_r($api->response());


// Store my id
$id = $api->response()->body->roobre->id;

// Request Teams
$api->request("team/by-summoner/$id", '2.2');

// Print the response
//print_r($api->response());


// Static example request
$api->requestStaticData('summoner-spell');

// Print the response
print_r($api->response());

?>