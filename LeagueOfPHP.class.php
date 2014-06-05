<?php

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

    /** Instances the API
     *
     * @param $key    string Your RIOT API Key. Get one at http://developer.riotgames.com/
     * @param $region string The region to query at.
     *
     */
    public function __construct($key, $region) {
        $this->key = $key;
        $this->region = $region;

        $this->ch = curl_init();

        curl_setopt_array($this->ch, self::$curlOpts);

        $this->autoRetry = array();
        $this->debug = false;
    }

    /** Frees the allocated resources.
     *
     */
    public function __destruct() {
        curl_close($this->ch);
    }

    /** Performs a request
     *
     * @param $key     string Request URL. Region must be ommited.
     * @param $version string The version of the method to use. At the time of writing, only 1.1 and 2.1 are supported.
     * @param $type    string The request type. Currently Riot only offers GET requests.
     *
     */
    public function request($req, $version, $type = 'GET') {
        $this->doRequest($this->buildURL($req, $version), $type);
    }

    /** Performs a request against /api/lol/static-data/
     *
     * @param $req     string Request URL. Region must be ommited.
     * @param $version string The version of the method to use. If it's equal to 1, it can be ommited.
     */
    public function requestStaticData($req, $version = '1') {
        $this->doRequest($this->buildURL($req, $version, true), 'GET');
    }

    /** Returns the request result.
     *
     * @return The last request's result, as StdClass Object.
     *
     */
    public function response() {
        $this->debugPrint(print_r($this->response, true));
        return $this->response;
    }

    /** Returns the response headers for the last request.
     *  @deprecated response()->headers should be used instead.
     *  @return HTTP reponse headers for the last request.
     */
    public function responseHeaders() {
        return $this->response->headers;
    }

    /** Sets the region of the instance
     *
     * @param $region string The new region to perform the requests at.
     *
     */
    public function setRegion($region) {
        $this->region = $region;

        $this->debugPrint("Region changed to '$region'.");
    }

    /** Forces the api to automatically retry failed requests
     *
     * @param $autoRetry array Array of response codes to retry if received. Send an empty array to stop autoretryng. If null is sent, it will default to 429.
     * @param $timeout int Time, in seconds, to wait between requests.
     * @param $tries int Maximum number of tries to do before giving up.
     */
    public function setAutoRetry($autoRetry = null, $timeout = 2, $tries = 5) {
        if ($autoRetry == null)
            $autoRetry = self::$default_autoretry;
        $this->autoRetry = $autoRetry;
        $this->timeout = $timeout;
        $this->tries = $tries;

        $this->debugPrint('Autoretry set for error codes (' . implode(', ', $autoRetry) . ") with timeout $timeout and max $tries tries.");
    }

    /** Enables verbose debug logs
     *
     * @param $debug boolean True to enable debug logs, false to disable.
     * @param $out handle Stream to write the log to. Must be already open. Defaults to STDERR.
     */
    public function debug($debug = true, $out = STDERR) {
        $this->debug = $debug;
        $this->output = $out;
    }

    private function buildURL($req, $version, $static = false) {
        $url = 'http://' . $this->region . '.' . self::$baseHostname;

        if ($static)
            $url .= '/static-data';

        return $url . "/{$this->region}/v$version/$req?api_key={$this->key}";
    }

    private function doRequest($url, $type) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        
        if ($type == 'GET') {
            curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        }

        $tries = 0;

        do {
            ++$tries;

            $this->debugPrint("Requesting $url, try #$tries.");
            
            $response = curl_exec($this->ch);
            $breakpoint = strpos($response, '{');

            $this->response = new stdClass();

            $this->response->code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            $this->response->headers = substr($response, 0, $breakpoint - 1);
            $this->response->sentHeaders = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
            $this->response->body = json_decode(substr($response, $breakpoint));
        } while (in_array($this->response->code, $this->autoRetry) && $tries < $this->tries && !sleep($this->timeout));

        if ($tries == $this->tries)
            $this->debugPrint("Max tries exhausted while requesting $url.");
    }

    private function debugPrint($msg) {
        if ($this->debug)
            fwrite($this->output, 'LOP Debug: ' . $msg . "\n");
    }

}

?>
