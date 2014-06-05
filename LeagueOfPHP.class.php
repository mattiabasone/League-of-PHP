<?php

class LeagueOfPHP {
	private static $baseHostname = 'api.pvp.net/api/lol';

	private static $curlOpts = array(
		CURLOPT_HEADER => true,
		CURLINFO_HEADER_OUT => true,
		CURLOPT_RETURNTRANSFER => true
		);

	private $region;
	private $key;

	private $ch;

	private $response;
	private $responseHeaders;

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

		$response = curl_exec($this->ch);
		$breakpoint = strpos($response, '{');

		$this->response = new stdClass();

		$this->response->code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		$this->response->headers = substr($response, 0, $breakpoint - 1);
		$this->response->sentHeaders = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
		$this->response->body = json_decode(substr($response, $breakpoint));
	}

}

?>
