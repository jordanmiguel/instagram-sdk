<?php
/**
 * Instagram SDK
 * A simple PHP SDK for Instagram API. Provides a wrapper for making authenticated requests.
 *
 * Instagram API Oficial Docs: https://instagram.com/developer/
 *
 * @package instagram-sdk
 * @author Daniel Trolezi <danieltrolezi@outlook.com>
 * @version 3.0
 */

class Instagram
{
	/**
	 * @var string
	 */
	private $base_url = 'https://graph.instagram.com/';

	/**
	 * @var string
	 */
	private $auth_url = 'https://api.instagram.com/oauth/authorize/';

	/**
	 * @var string
	 */
	private $token_url = 'https://api.instagram.com/oauth/access_token/';

	/**
	 * @var string
	 */
	protected $app_id;

	/**
	 * @var string
	 */
	protected $app_secret;

	/**
	 * @var string
	 */
	protected $redirect_uri;

	/**
	 * @var string
	 */
	protected $access_token;

	/**
	 * Constructor for the API.
	 *
	 * @param string $app_id
	 * @param string $app_secret
	 */
	function __construct($app_id, $app_secret)
	{
		$this->app_id = $app_id;
		$this->app_secret = $app_secret;
	}

	/**
	 * Set the redirect uri, which is required to get
	 * the login url and get the access token.
	 *
	 * @param $redirect_uri
	 */
	public function setRedirectUri($redirect_uri)
	{
		$this->redirect_uri = $redirect_uri;
	}

	/**
	 * Set the Access Token.
	 *
	 * @param string $access_token
	 */
	public function setAccessToken($access_token)
	{
		$this->access_token = $access_token;
	}

	/**
	 * Return a valid Access Token or the one that is being used by the object.
	 *
	 * @param string $code
	 * @return string
	 * @throws Exception
	 * @throws InstagramException
	 */
	public function getAccessToken($code = null)
	{
		if($code){
			$data = [
				'app_id' => $this->app_id,
				'app_secret' => $this->app_secret,
				'grant_type' => 'authorization_code',
				'redirect_uri' => $this->redirect_uri,
				'code' => $code
			];

			$result = $this->curl($this->token_url, $data);
			$result = json_decode($result, true);

			if(isset($result['error_message'])) {
				throw new InstagramException($result['error_message'], $result['error_type'], $result['code']);
			}

			$this->access_token = $result['access_token'];
			return $this->access_token;
		} else if($this->access_token){
			return $this->access_token;
		}

		throw new Exception('You must provide the "code" resulting from the login webflow.', 400);
	}

	/**
	 * Returns the login URL.
	 *
	 * @param string $scope
	 * @return string
	 * @throws Exception
	 */
	public function getLoginURL($scope = 'user_profile,user_media')
	{
		if(!$this->redirect_uri) {
			throw new Exception('You must provide a "redirect_uri".', 400);
		}

		return $this->auth_url.'?app_id='.$this->app_id.'&redirect_uri='. urlencode($this->redirect_uri) .'&response_type=code&scope='.$scope;
	}

	/**
	 * Make authenticated requests to the API.
	 *
	 * @param string $endpoint - e.g: /users/{user-id|username|self}/media/recent
	 * @param array $params
	 * @return mixed
	 * @throws InstagramException
	 */
	public function call($endpoint, $params = array())
	{
		$url = $this->base_url . trim($endpoint, "/");

		if($this->access_token) {
			$params['access_token'] = $this->access_token;
		}

		$result = $this->curl($url.'?'.http_build_query($params));
		$result = json_decode($result, true);

		if(isset($result['meta']['error_message'])) {
			throw new InstagramException($result['meta']['error_message'], $result['meta']['error_type'], $result['meta']['code']);
		}

		return $result;
	}

	/**
	 * @param string $url
	 * @param array $data
	 * @return mixed
	 */
	private function curl($url, $data = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

		if(isset($_SERVER["HTTP_USER_AGENT"])){
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
		}

		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);

		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		$result = curl_exec($ch);
		//var_dump(curl_getinfo($ch));
		curl_close($ch);
		return $result;
	}
}

class InstagramException extends Exception
{
	/**
	 * @var string
	 */
	public $type;

	public function __construct($message, $type, $code, \Exception $previous = null)
	{
		$this->type = $type;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Gets the Exception error type
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
}
