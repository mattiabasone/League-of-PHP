<?php

class LeagueOfPHPException {
    private $url;

    public function __construct($url, $errorCode) {
        parent::__construct("Error requesting $url. HTTP server returned code $errorCode", $errorCode);
    }
}

?>
