<?php

require_once 'LeagueOfPHPException.class.php';

class LeagueOfPHP {
    private static $baseHostname = 'api.pvp.net/api/lol';

    private static $curlOpts = array(
        CURLOPT_HEADER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_RETURNTRANSFER => true
        );

    private static $default_autoretry = array(429);

    private $region;
    private $key;

    private $ch;

    private $response;

    private $autoRetry;
    private $timeout;
    private $tries;

    private $debug;
    private $output;

    private $useExceptions;
    private $callback;

    /**
     * Instances the API
     *
     * @param string $key    Your RIOT API Key. Get one at http://developer.riotgames.com/
     * @param string $region The region to query at.
     *
     */
    public function __construct($key, $region) {
        $this->key = $key;
        $this->region = $region;

        $this->ch = curl_init();

        curl_setopt_array($this->ch, self::$curlOpts);

        $this->autoRetry = array();
        $this->debug = false;
        $this->callback = null;
    }

    /**
     * Frees the allocated resources.
     *
     */
    public function __destruct() {
        curl_close($this->ch);
    }

    /**
     * Performs a request
     *
     * @param string $req     Request URL. Region must be ommited.
     * @param string $version The version of the method to use.
     * @param string $type    The request type. Currently Riot only offers GET requests.
     *
     */
    public function request($req, $version, $type = 'GET') {
        $this->doRequest($this->buildURL($req, $version), $type);
    }

    /**
     * Performs a request against /api/lol/static-data/
     *
     * @param string $req     Request URL. Region must be ommited.
     * @param string $version The version of the method to use. If it's equal to 1, it can be ommited.
     */
    public function requestStaticData($req, $version = '1') {
        $this->doRequest($this->buildURL($req, $version, true), 'GET');
    }

    /**
     * Returns the request result.
     *
     * @return stdClass The last request's result, as stdClass Object.
     */
    public function response() {
        $this->debugPrint(print_r($this->response, true), 3);
        return $this->response;
    }

    /**
     * Returns the response headers for the last request.
     * 
     * @deprecated response()->headers should be used instead.
     * @return string HTTP reponse headers for the last request.
     */
    public function responseHeaders() {
        return $this->response->headers;
    }

    /**
     * Sets the region of the instance
     *
     * @param string $region The new region to perform the requests at.
     */
    public function setRegion($region) {
        $this->region = $region;

        $this->debugPrint("Region changed to '$region'.", 1);
    }

    /**
     * Forces the api to automatically retry failed requests
     *
     * @param array $autoRetry Array of response codes to retry if received.
     *     Send an empty array to stop autoretrying. If null is sent, it will default to 429.
     * @param int   $timeout   Time, in seconds, to wait between requests.
     * @param int   $tries     Maximum number of tries to do before giving up.
     */
    public function setAutoRetry($autoRetry = null, $timeout = 2, $tries = 5) {
        if ($autoRetry == null)
            $autoRetry = self::$default_autoretry;
        $this->autoRetry = $autoRetry;
        $this->timeout = $timeout;
        $this->tries = $tries;

        $this->debugPrint('Autoretry set for error codes (' . implode(', ', $autoRetry) .
            ") with timeout $timeout and max $tries tries.", 1);
    }

    /**
     * Sets a callback function to call when a requests fail.
     *
     * @param callable $callback Function that will be called when a request fails. 
     *     The first parameter is the request URL, and the second the HTTP response code.
     */
    public function setCallback($callback = null) {
        $this->callback = $callback;
        $this->debugPrint("Callback on error function set to $callback", 1);
    }

    /**
     * Enables verbose debug logs
     *
     * @param boolean $debug True to enable debug logs, false to disable.
     * @param handle  $out Stream to write the log to. Must be already open. Defaults to STDERR.
     */
    public function debug($debug = 2, $out = STDERR) {
        $this->debug = $debug;
        $this->output = $out;
    }

    public function useExceptions($useExceptions = true) {
        $this->useExceptions = $useExceptions;
        $this->debugPrint('Now exceptions will be thrown.', 1);
    }

    // --# NO TRESPASSING # --    Private section    --# NO TRESPASSING # --

    /**
     * Crafts the full request URL.
     *
     * @param string $req     Base name of the request.
     * @param string $version Method version.
     * @param string $static  True if the method is static.
     */
    private function buildURL($req, $version, $static = false) {
        $url = 'http://' . $this->region . '.' . self::$baseHostname;

        if ($static)
            $url .= '/static-data';

        return $url . "/{$this->region}/v$version/$req?api_key={$this->key}";
    }

    /**
     * Actually performs the request against the given url.
     *
     * @param string $url  URL to request.
     * @param string $tipe HTTP request type (GET or POST)
     */
    private function doRequest($url, $type) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        
        if ($type == 'GET') {
            curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        }

        $tries = 0;

        do {
            $this->debugPrint("Requesting $url, try #" . ($tries + 1), 2);
            
            $response = curl_exec($this->ch);
            $breakpoint = strpos($response, '{');

            $this->response = new stdClass();

            $this->response->code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            $this->response->headers = substr($response, 0, $breakpoint - 1);
            $this->response->sentHeaders = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
            $this->response->body = json_decode(substr($response, $breakpoint));

        } while (in_array($this->response->code, $this->autoRetry) && ++$tries < $this->tries
            && !sleep($this->timeout));

        if ($this->response->code != 200) {
            $this->debugPrint("Max tries exhausted while requesting $url.", 2);
            $this->callback($url, $this->response->code);
            $this->throwException($url, $this->response->code);
        }
    }

    /** 
     * Prints a debug message, if configured to do so.
     *
     * @param string $msg Message to print.
     */
    private function debugPrint($msg, $level) {
        if ($this->debug > $level)
            fwrite($this->output, "\x1b[33;1m LOP Debug: \x1b[39;49m" . $msg . "\x1b[0m \n");
    }

    /**
     * Calls the user-defined function if configured to do so.
     * 
     * @param string $url  URL which was being requested.
     * @param int    $code HTTP response code.
     */
    private function callback($url, $code) {
        if($this->callback != null) {
            call_user_func($this->callback, $url, $code);
        }
    }

    private function throwException($url, $code) {
        if ($this->useExceptions)
            throw new LeagueOfPHPException($url, $this->response->code);
    }

}

?>
