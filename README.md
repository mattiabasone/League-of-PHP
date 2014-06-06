League of PHP
=============

PHP Implementation of the official League of Legends API

## Documentation
Official documentation of the API can be found here:
http://developer.riotgames.com/api/methods

## Usage
(**Note**: See example.php for more detailed code)

### Requesting data

    require 'LeagueOfPHP.class.php';
    $api = new LeagueOfPHP('KEY', 'euw');

    $api->request('summoner/by-name/roobre', '1.4');

### Reading the response

    $api->response()->code			// Contains the HTTP code of the server's response.
    $api->response()->headers		// Contains the HTTP headers of the server's response.
    $api->response()->sentHeaders	// Contains the HTTP headers sent to the server.
    $api->response()->body			// Contains the HTTP body of the response, json-decoded.

### Note
For simplifying reasons, this implementation accepts requests in a slightly different format. For example.

    /api/lol/{region}/v1.1/champion
    /api/lol/{region}/v1.1/summoner/by-name/{name}

    /api/lol/static-data/{region}/v1/summoner-spell

Becomes

    request('champion', '1.1');
    request('summoner/by-name/{name}', '1.3');

    requestStaticData('summoner-spell');


## Constants
Some methods return human-unreadable constats. You can check them here:
http://developer.riotgames.com/docs/game-constants