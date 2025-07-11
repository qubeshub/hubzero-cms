<?php
/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * The first PHP Library to support OAuth for Twitter's REST API.
 */

/* Load OAuth lib. You can find it at http://oauth.net */
require_once __DIR__ . '/OAuth.php';

/**
 * Twitter OAuth class
 */
class TwitterOAuth
{
	/* Contains the last HTTP status code returned. */
	public $http_code;
	/* Contains the last API call. */
	public $url;
	/* Set up the API root URL. */
	public $host = "https://api.twitter.com/1.1/";
	/* Set timeout default. */
	public $timeout = 30;
	/* Set connect timeout. */
	public $connecttimeout = 30;
	/* Verify SSL Cert. */
	public $ssl_verifypeer = false;
	/* Respons format. */
	public $format = 'json';
	/* Decode returned json data. */
	public $decode_json = true;
	/* Contains the last HTTP headers returned. */
	public $http_info;
	/* Set the useragnet. */
	public $useragent = 'TwitterOAuth v0.2.0-beta2';
	/**/
	public $sha1_method = null;
	/**/
	public $consumer = null;
	/**/
	public $token = null;
	/**/
	public $http_header = array();

	/* Immediately retry the API call if the response was not successful. */
	//public $retry = true;

	/**
	 * Set API URLS
	 */
	public function accessTokenURL()
	{
		return 'https://api.twitter.com/oauth/access_token';
	}

	public function authenticateURL()
	{
		return 'https://api.twitter.com/oauth/authenticate';
	}

	public function authorizeURL()
	{
		return 'https://api.twitter.com/oauth/authorize';
	}

	public function requestTokenURL()
	{
		return 'https://api.twitter.com/oauth/request_token';
	}

	/**
	 * Debug helpers
	 */
	public function lastStatusCode()
	{
		return $this->http_status;
	}

	public function lastAPICall()
	{
		return $this->last_api_call;
	}

	/**
	 * construct TwitterOAuth object
	 */
	public function __construct($consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null)
	{
		$this->sha1_method = new TwitterOAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new TwitterOAuthConsumer($consumer_key, $consumer_secret);
		if (!empty($oauth_token) && !empty($oauth_token_secret))
		{
			$this->token = new TwitterOAuthConsumer($oauth_token, $oauth_token_secret);
		}
		else
		{
			$this->token = null;
		}
	}


	/**
	 * Get a request_token from Twitter
	 *
	 * @returns a key/value array containing oauth_token and oauth_token_secret
	 */
	public function getRequestToken($oauth_callback)
	{
		$parameters = array();
		$parameters['oauth_callback'] = $oauth_callback;
		$request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
		$token = TwitterOAuthUtil::parse_parameters($request);
		$this->token = new TwitterOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * Get the authorize URL
	 *
	 * @returns a string
	 */
	public function getAuthorizeURL($token, $sign_in_with_twitter = true)
	{
		if (is_array($token))
		{
			$token = $token['oauth_token'];
		}
		if (empty($sign_in_with_twitter))
		{
			return $this->authorizeURL() . "?oauth_token={$token}";
		}
		else
		{
			 return $this->authenticateURL() . "?oauth_token={$token}";
		}
	}

	/**
	 * Exchange request token and secret for an access token and
	 * secret, to sign API calls.
	 *
	 * @returns array("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham")
	 */
	public function getAccessToken($oauth_verifier)
	{
		$parameters = array();
		$parameters['oauth_verifier'] = $oauth_verifier;
		$request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
		$token = TwitterOAuthUtil::parse_parameters($request);
		$this->token = new TwitterOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * One time exchange of username and password for access token and secret.
	 *
	 * @returns array("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham",
	 *                "x_auth_expires" => "0")
	 */
	public function getXAuthToken($username, $password)
	{
		$parameters = array();
		$parameters['x_auth_username'] = $username;
		$parameters['x_auth_password'] = $password;
		$parameters['x_auth_mode'] = 'client_auth';
		$request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
		$token = TwitterOAuthUtil::parse_parameters($request);
		$this->token = new TwitterOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * GET wrapper for oAuthRequest.
	 */
	public function get($url, $parameters = array())
	{
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json)
		{
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * POST wrapper for oAuthRequest.
	 */
	public function post($url, $parameters = array())
	{
		$response = $this->oAuthRequest($url, 'POST', $parameters);
		if ($this->format === 'json' && $this->decode_json)
		{
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * DELETE wrapper for oAuthReqeust.
	 */
	public function delete($url, $parameters = array())
	{
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json)
		{
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * Format and sign an OAuth / API request
	 */
	public function oAuthRequest($url, $method, $parameters)
	{
		if (strrpos($url, 'https://') !== 0
		 && strrpos($url, 'http://') !== 0)
		{
			$url = "{$this->host}{$url}.{$this->format}";
		}
		$request = TwitterOAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method)
		{
			case 'GET':
				return $this->http($request->to_url(), 'GET');
			default:
				return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
		}
	}

	/**
	 * Make an HTTP request
	 *
	 * @return API results
	 */
	function http($url, $method, $postfields = null)
	{
		$this->http_info = array();
		$ci = curl_init();

		// Curl settings
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, false);

		switch ($method)
		{
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, true);
				if (!empty($postfields))
				{
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields))
				{
					$url = "{$url}?{$postfields}";
				}
		}

		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		curl_close ($ci);
		return $response;
	}

	/**
	 * Get the header info to store.
	 */
	public function getHeader($ch, $header)
	{
		$i = strpos($header, ':');
		if (!empty($i))
		{
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
}
