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
    /api/{region}/v2.1/team/by-summoner/{summonerId}

Becomes

    champion
    summoner/by-name/{name}
    team/by-summoner/{summonerId}

## Constants
Some methods return human-unreadable constats. You can check them here:
http://developer.riotgames.com/docs/game-constants