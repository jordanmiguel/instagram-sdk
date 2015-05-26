<?php
/**
 * Instagram SDK
 * A simple PHP SDK for Instagram API. Provides a wrapper for making both 
 * public and authenticated requests.
 * 
 * @package instagram-sdk
 * @author Daniel Trolezi <danieltrolezi@outlook.com>
 * @version 2.0.1
 */
 
class InstagramException extends Exception {}

class Instagram {
	
	/**
	 * @var string
	 */
	private $base_url = 'https://api.instagram.com/v1/';

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
	public $client_id;
	
	/**
	 * @var string
	 */
	public $client_secret;
	
	/**
	 * @var string 
	 */
	public $redirect_uri;
	
	/**
	 * @var string
	 */
	public $access_token;
	
	/**
	 * Constructor for the API
	 * @param string $client_id
	 * @param string $client_secret
	 */
	function __construct($client_id, $client_secret)
	{		
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
	}
	
	/**
	 * Set the Access Token
	 * @param string $access_token
	 */
	public function setAccessToken($access_token)
	{
		$this->access_token = $access_token;	
	}
	
	/**
	 * Return a valid Access Token or the one that is being used by the object
	 * @param string $code
	 * @param string $redirect_uri
	 */
	public function getAccessToken($code = false)
	{
		if($code){	
			$data = array(
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'grant_type' => 'authorization_code',
				'redirect_uri' => $this->redirect_uri,
				'code' => $code
			);			
			
			$result = $this->curl($this->token_url, $data);
			$result = json_decode($result, true);		
			$this->access_token = $result['access_token'];
			
			return $this->access_token;	
		} else if($this->access_token){
			return $this->access_token;	
		} else {
			throw new InstagramException( 'Missing parameter \'code\'.' );	
		}
	}
	
	/**
	 * Returns the login URL
	 * @param string $scope
	 * @return string
	 */
	public function getLoginURL($redirect_uri, $scope = 'basic')
	{
		$this->redirect_uri = $redirect_uri;
		return $this->auth_url.'?client_id='.$this->client_id.'&redirect_uri='.$this->redirect_uri.'&response_type=code&scope='.$scope;	
	}
	
	/**
	 * Make both public and authenticated requests to the API
	 * @param string $endpoint ex: /users/{user-id|username|self}/media/recent
	 * @param array $params
	 * @return array
	 */
	public function call($endpoint, $params = array())
	{
		$url = $this->base_url . trim($endpoint, "/");
		
		if(!isset($params['access_token']))
			$params['client_id'] = $this->client_id;
			
		$result = $this->curl($url.'?'.http_build_query($params));
		return json_decode($result, true);
	}
	
	/** 
	 * cURL
	 * @param string $url
	 * @param array $data
	 */
	private function curl($url, $data = false){
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		//curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		//curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
		
		if($data){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
	
		$result = curl_exec($ch);
		//var_dump(curl_getinfo($ch));
		curl_close($ch);
		return $result;
	}
	
}
?>