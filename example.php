<?php

// Include the library
require 'LeagueOfPHP.class.php';

// Instance the API
$api = new LeagueOfPHP('KEY', 'euw');

// The summoner we are looking for. Lowercase is recommended.
$SUMMONER_NAME = 'roobre';



// Example request
$api->request("summoner/by-name/$SUMMONER_NAME", '1.4');

// Print the response
print_r($api->response());

if ($api->response()->code != '200') {
    die('An error occurred while performing the request (status code: ' . $api->response()->code . ")\n");
}

// Store my id
$id = $api->response()->body->{$SUMMONER_NAME}->id;


// Request Teams
$api->request("team/by-summoner/$id", '2.2');

if ($api->response()->code != '200') {
    die('An error occurred while performing the request (status code: ' . $api->response()->code . ")\n");
}


// Static example request
$api->requestStaticData('summoner-spell');

// Set auto retry
$api->setAutoRetry(array(429, 401));
// This tells the API to automatically retry on the given array of status codes.
// By default, it is set to 429 (Rate limit) only. But for testing purposes we will add 401 (Forbidden) too.

echo "I will stay for 5 tries * 2 seconds each (10s)\n";
$api->request("i/dont/exist", '0'); // Yes, RITO throws a 401 for a non-existen method...
echo "I'm done! Enjoy your error!\n\n";

// Print the response
print_r($api->response());


// Disable auto retry
$api->setAutoRetry(array());


// /*Seek and*/ Destroy
unset($api);

?>
