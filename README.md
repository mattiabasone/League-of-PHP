League of PHP
=============

PHP Implementation of the official League of Legends API

## Documentation
Official documentation of the API can be found here:
http://developer.riotgames.com/api/methods

### Note
For simplifying reasons, this implementation accepts requests in a slightly different format. For example.

    /api/lol/{region}/v1.1/champion
    /api/lol/{region}/v1.1/summoner/by-name/{name}

    /api/lol/static-data/{region}/v1/summoner-spell

Becomes

    request('champion', 1.1);
    request('summoner/by-name/{name}', 1.3);

    requestStaticData('summoner-spell');


## Constants
Some methods return human-unreadable constats. You can check them here:
http://developer.riotgames.com/docs/game-constants