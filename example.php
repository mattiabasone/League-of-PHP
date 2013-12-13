<?php

// Include the library
require 'LeagueOfPHP.class.php';

// Instance the API
$api = new LeagueOfPHP('KEY', 'euw');


// Example request
$api->request('summoner/by-name/Roobre', '1.1');

// If version is 1.1 can be omitted.
$api->request('summoner/by-name/Roobre');

// Print the response
print_r($api->response());


// Store my id
$id = $api->response()->id;


// Another example request
$api->request("league/by-summoner/$id", '2.1');

// Print the response
print_r($api->response());

?>