<?php

class LeagueOfPHP {
	private static $baseURL = 'http://prod.api.pvp.net/api';

	private $region;
	private $key;

	private $response;

	/** Instances the API
	 *
	 * @param $key    string Your RIOT API Key. Get one at http://developer.riotgames.com/
	 * @param $region string The region to query at.
	 *
	 */
	public function __construct($key, $region) {
		$this->key = $key;
		$this->region = $region;
	}

	/** Performs a request
	 *
	 * @param $key        string Request URL. Region must be ommited, and if the request starts with lol/ it must be included.
	 * @param $version    string The version of the method to use. At the time of writing, only 1.1 and 2.1 are supported.
	 * @param $type       string The request type. Currently Riot only offers GET requests.
	 *
	 */
	public function request($req, $version = '1.1', $type = 'GET') {
		$this->response = json_decode($this->doRequest($this->buildURL($req, $version), $type));
	}

	/** Returns the request result.
	 *
	 * @return The last request's result, as StdClass Object.
	 *
	 */
	public function response() {
		return $this->response;
	}

	/** Sets the region of the instance
	 *
	 * @param $region string The new region to perform the requests at.
	 *
	 */
	public function setRegion($region) {
		$this->region = $region;
	}

	private function buildURL($req, $version) {
		switch ($version) {
			case '1.1':
			$req = 'lol/' . $this->region . '/v1.1/' . $req;
			break;

			case '2.1':
			$req = $this->region . '/v2.1/' . $req;
			break;

			default:
			throw new Exception('Unsupported version.');
		}

		return self::$baseURL . "/$req?api_key={$this->key}";
	}

	private function doRequest($url, $type) {
		if ($type == 'GET') {
			return file_get_contents($url);
		}
		// TODO: If riot implements POST methods, they will be handled here.
	}

}

?>