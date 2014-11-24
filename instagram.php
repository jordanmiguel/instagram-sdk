<?php
/* 
** Instagram PHP SDK 1.0.0.2
** Autor: Daniel Trolezi
*/

class Instagram {
	
	public $client_id;
	public $client_secret;
	public $redirect_uri;
	public $access_token;
	
	function __construct($config){		
		if(isset($config['client_id'])) $this->client_id = $config['client_id'];
		if(isset($config['client_secret'])) $this->client_secret = $config['client_secret'];
		if(isset($config['redirect_uri'])) $this->redirect_uri = $config['redirect_uri'];
		if(isset($config['access_token'])) $this->access_token = $config['access_token'];
	}
	
	public function setAccessToken($access_token){
		$this->access_token = $access_token;	
	}
	
	// API FUNCTIONS 
	
	// Retorna um Access Token válido
	public function getAccessToken($code){		
		$url = 'https://api.instagram.com/oauth/access_token';
		
		$data = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'authorization_code',
			'redirect_uri' => $this->redirect_uri,
			'code' => $code
		);			
		
		$result = $this->curl($url, $data);
		$result = json_decode($result, true);		
		return $result;	
	}
	
	/* Redireciona o usuário para autorizar o app
	** @param string $scope */
	public function authorizeUser($scope = null){
		$url = 'https://api.instagram.com/oauth/authorize/?client_id='.$this->client_id.'&redirect_uri='.$this->redirect_uri.'&response_type=code';	
		if($scope != null) $url .= '&scope='.$scope;
		header('Location: '.$url);
	}
	
	/* Retorna informação sobre o usuário 
	** @param string $user_id */
	public function getUser($user_id){
		$endpoint = 'https://api.instagram.com/v1/users/'.$user_id.'/?access_token='.$this->access_token;
		$result = $this->curl($endpoint);
		return json_decode($result, true);	
	}
	
	/* Retorna os posts recentes do usuário
	** @param string $user_id
	** @param int $count
	** @param string $min_timestamp retorna os posts posteriores a unix timestamp
	** @param string $min_id retorna os posts posteriores ao id */
	public function getMediaRecent($params = array()){		
		$config = array(
			'user_id' => '',
			'count' => '',
			'min_timestamp' => '',
			'min_id' => ''
		);
		
		if(count($params) > 0){
			$config = array_merge($config, $params);
			extract($config, EXTR_OVERWRITE);	
		}
 	
		$endpoint = 'https://api.instagram.com/v1/users/'.$user_id.'/media/recent/?access_token='.$this->access_token;
		if(!empty($count)) $endpoint .= '&count='.$count;
		if(!empty($min_timestamp)) $endpoint .= '&min_timestamp='.$min_timestamp;
		if(!empty($min_id)) $endpoint .= '&min_id='.$min_id;
				
		$result = $this->curl($endpoint);
		return json_decode($result, true);
	}
	
	/* Retorna os likes do usuário
	** @param int $count
	** @param string $max_like_id retorna os likes anteriores ao id */
	public function getUsersLike($params = array()){
		$config = array(
			'count' => '',
			'max_like_id' => ''
		);
		
		if(count($params) > 0){
			$config = array_merge($config, $params);
			extract($config, EXTR_OVERWRITE);	
		}
		
		$endpoint = 'https://api.instagram.com/v1/users/self/media/liked?access_token='.$this->access_token;
		if($count != null) $endpoint .= '&count='.$count;
		if($max_like_id != null) $endpoint .= '&max_like_id='.$max_like_id;
	
		$result = $this->curl($endpoint);
		return json_decode($result, true);	
	}
	
	/* Retorna a lista de mídias recentes relacionadas a tag
	** @param string $tag_name
	** @param string $min_tag_id */
	public function getSearch($params = array()){
		$config = array(
			'count' => '',
			'tag_name' => '',
			'min_tag_id' => ''
		);
		
		if(count($params) > 0){
			$config = array_merge($config, $params);
			extract($config, EXTR_OVERWRITE);	
		}
		
		$endpoint = 'https://api.instagram.com/v1/tags/'.$tag_name.'/media/recent';
		
		if($this->access_token != null) $endpoint .= '?access_token='.$this->access_token;
		else $endpoint .= '?client_id='.$this->client_id;
		
		if($count != null) $endpoint .= '&count='.$count;
		if($min_tag_id != null) $endpoint .= '&min_tag_id='.$min_tag_id;
		
		$result = $this->curl($endpoint);
		return json_decode($result, true);	
	}
	
	/* Retorna a informação sobre um post
	** (Instagram API Documentation) Note: if you authenticate with an OAuth Token, you will
	** receive the user_has_liked key which quickly tells you whether the current user has liked this media item.
	** @param string $media_id  */
	public function getMedia($media_id){
		$endpoint = 'https://api.instagram.com/v1/media/'.$media_id.'?client_id='.$this->client_id;
		if($this->access_token != null) $endpoint .= '&access_token='.$this->access_token;
		$result = $this->curl($endpoint);
		return json_decode($result, true);
	}
	
	// STREAMING API FUNCTIONS
	
	/* Realiza uma inscrição no Instagram	
	** @param string $object 
	** @param string $object_id */
	public function newSubscription($object, $object_id = null){
		$endpoint = 'https://api.instagram.com/v1/subscriptions/';
		
		$data = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'object' => $object,
			'aspect' => 'media',
			//'verify_token' => ,
			'callback_url' => $this->redirect_uri
		);
		if($object_id != null) $data['object_id'] = $object_id;
		
		$result = $this->curl($endpoint, $data);
		return json_decode($result, true);
	}
	
	// Retorna a lista de inscrições
	public function listSubscriptions(){
		$endpoint = 'https://api.instagram.com/v1/subscriptions?client_secret='.$this->client_secret.'&client_id='.$this->client_id;
		$result = $this->curl($endpoint);
		return json_decode($result, true);
	}
	
	/* Deleta as inscrições
	** @param array $config
	** $config['object'] values: all, tag, user
	** $config['id']: id da inscrição */
	public function deleteSubscriptions($config = null){
		$endpoint = 'https://api.instagram.com/v1/subscriptions?client_id='.$this->client_id.'&client_secret='.$this->client_secret;
		if(isset($config['object'])) $endpoint .= '&object='.$config['object'];
		if(isset($config['id'])) $endpoint .= '&id='.$config['id'];
		
		$result = $this->curl($endpoint, null, 'DELETE');
		return json_decode($result, true);	
	}
	
	// PRIVATE FUNCTIONS
	
	/* cURL
	** @param string $url
	** @param array $data
	** @param string $custom_request */
	private function curl($url, $data = null, $custom_request = null){
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
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
		
		if($data != null){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		if($custom_request != null){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom_request);	
		}
	
		$result = curl_exec($ch);
		//var_dump(curl_getinfo($ch));
		curl_close($ch);
		return $result;
	}
	
}

?>