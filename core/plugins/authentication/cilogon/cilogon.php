<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

class plgAuthenticationCILogon extends \Hubzero\Plugin\OauthClient
{
	/**
	 * Affects constructor behavior.
	 * If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Stores the initialized CILogon object.
	 *
	 * @var  object  CILogon
	 */
	private $cilogon = null;

	/**
	 * Get the CILogon object, instantiating it if need be
	 *
	 * @return  object
	 */
	protected function cilogon($redirect = null)
	{
		if (!$this->cilogon)
		{
			if (!$redirect)
			{
				$redirect = self::getRedirectUri('cilogon');
			}

			$this->cilogon = new \CILogon\OAuth2\Client\Provider\CILogon([
				'clientId' => $this->params->get('app_id'),
				'clientSecret' => $this->params->get('app_secret'),
				'redirectUri' => $redirect
			]);
		}

		return $this->cilogon;
	}

	/**
	 * Perform logout (not currently used)
	 *
	 * @return  void
	 */
	public function logout()
	{
		// This is handled by the JS API, and cannot be done server side
		// (at least, it cannot be done server side, given our authentication workflow
		// and the current limitations of the PHP SDK).
	}

	/**
	 * Method to call when redirected back from gc after authentication
	 * Grab the return URL if set and handle denial of app privileges from gc
	 *
	 * @param   object  $credentials
	 * @param   object  $options
	 * @return  void
	 */
	public function login(&$credentials, &$options)
	{
		$b64dreturn = '';
		$return = '';
		$return = Session::get('returnUrl', null, 'cilogon');
		if (!empty($return))
		{
			$b64dreturn = base64_decode($return);
			if (!\Hubzero\Utility\Uri::isInternal($b64dreturn))
			{
				$b64dreturn = '';
			}
		}
		Session::clear('state', 'returnUrl');
		$options['return'] = $b64dreturn;

		// Check to make sure they didn't deny our application permissions
		if (Request::getVar('error', null))
		{
			// User didn't authorize our app or clicked cancel
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . $return),
				Lang::txt('PLG_AUTHENTICATION_CILOGON_MUST_AUTHORIZE_TO_LOGIN', Config::get('sitename')),
				'error'
			);
		}
	}

	public function status()
	{
		// Do nothing as of now
	}

	/**
	 * Method to setup CILogon params and redirect to CILogon auth URL
	 *
	 * @param   object  $view  view object
	 * @param   object  $tpl   template object
	 * @return  void
	 */
	public function display($view, $tpl)
	{
		$returnUrl = Request::getString('return', '');
		$provider = $this->cilogon();
		$loginUrl = $provider->getAuthorizationUrl(array(
			'scope' => ['openid', 'email', 'profile', 'org.cilogon.userinfo']
		));
		Session::set('state', $provider->getState(), 'cilogon');
		Session::set('returnUrl', $returnUrl, 'cilogon');
		// Redirect to the login URL
		App::redirect($loginUrl);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array    $credentials  Array holding the user credentials
	 * @param   array    $options      Array of extra options
	 * @param   object   $response     Authentication response object
	 * @return  boolean
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		$responseArr = array();

		try
		{
			$storedState = Session::get('state', null, 'cilogon');
			$state = Request::getVar('state');
			if (empty($state) || $storedState !== $state)
			{
				throw new Exception('Mismatched state');
			}
			Session::clear('state', 'cilogon');

			// oauth2-client 2.8.0 added requirement to pass scope to getAccessToken.
			// this was reverted in 2.8.1 so keeping this comment here in case it
			// comes up again. 

			//$token = $this->cilogon()->getAccessToken('authorization_code', 
			//array('scope' => ['openid','email','profile','org.cilogon.userinfo'], 
			//'code' => Request::getString('code')));

			$token = $this->cilogon()->getAccessToken('authorization_code', array('code' => Request::getString('code')));
		}
		catch (\Exception $e)
		{
			$response->status = \Hubzero\Auth\Status::FAILURE;
			$response->error_message = Lang::txt('PLG_AUTHENTICATION_CILOGON_ERROR_RETRIEVING_PROFILE', $e->getMessage());
			return;
		}
		// Make sure we have a user_id (gc returns 0 for a non-logged in user)
		if ((isset($user_id) && $user_id > 0) || (isset($token) && $token))
		{
			try
			{
				$cilogonResponse = $this->cilogon()->getResourceOwner($token);
				$responseArr = $cilogonResponse->toArray();
				$id       = $cilogonResponse->getId();
				$firstname =  isset($responseArr['given_name']) ? $responseArr['given_name'] : '';
				$lastname = isset($responseArr['family_name']) ? $responseArr['family_name'] : '';
				$fullname = isset($responseArr['name']) ? $responseArr['name'] : '';
				$email    = isset($responseArr['email']) ? $responseArr['email'] : '';
				$fullname = empty($fullname) ? $firstname . ' ' . $lastname : $fullname;
			}
			catch (\Exception $e)
			{
				// Error message?
				$response->status = \Hubzero\Auth\Status::FAILURE;
				$response->error_message = Lang::txt('PLG_AUTHENTICATION_CILOGON_ERROR_RETRIEVING_PROFILE', $e->getMessage());
				return;
			}
			// Create the hubzero auth link
			$method = (Component::params('com_members')->get('allowUserRegistration', false)) ? 'find_or_create' : 'find';
			$hzal = \Hubzero\Auth\Link::$method('authentication', 'cilogon', null, $id);
			if ($hzal === false)
			{
				$response->status = \Hubzero\Auth\Status::FAILURE;
				$response->error_message = Lang::txt('PLG_AUTHENTICATION_CILOGON_UNKNOWN_USER');
				return;
			}

			$hzal->set('email', $email);

			// Set response variables
			$response->auth_link = $hzal;
			$response->type      = 'cilogon';
			$response->status    = \Hubzero\Auth\Status::SUCCESS;
			$response->fullname  = $fullname;

			if (isset($responseArr['entitlement']))
			{
				$response->entitlement = $responseArr['entitlement'];
			}

			if (isset($responseArr['idp_name']))
			{
				$response->idp_name = $responseArr['idp_name'];
			}

			if ($hzal->user_id)
			{
				$user = User::getInstance($hzal->user_id);

				$response->username = $user->username;
				$response->email    = $user->email;
				$response->fullname = $user->name;
			}
			else
			{
				$response->username = '-' . $hzal->id;
				$response->email    = $response->username . '@invalid';
				// Also set a suggested username for their hub account
				$sub_email    = explode('@', $email == null ? '' : $email, 2);
				$tmp_username = $sub_email[0];
				App::get('session')->set('auth_link.tmp_username', $tmp_username);
			}
			$hzal->update();


			// If we have a real user, drop the authenticator cookie
			if (isset($user) && is_object($user))
			{
				// Set cookie with login preference info
				$prefs = array(
					'user_id'       => $user->get('id'),
					'authenticator' => 'cilogon'
				);

				$namespace = 'authenticator';
				$lifetime  = time() + 365*24*60*60;

				\Hubzero\Utility\Cookie::bake($namespace, $lifetime, $prefs);
			}
		}
		else
		{
			$response->status = \Hubzero\Auth\Status::FAILURE;
			$response->error_message = Lang::txt('PLG_AUTHENTICATION_CILOGON_AUTHENTICATION_FAILED');
		}
	}

	/**
	 * Similar to onAuthenticate, except we already have a logged in user, we're just linking accounts
	 *
	 * @param   array  $options
	 * @return  void
	 */
	public function link($options=array())
	{
		try
		{
			$session = $this->cilogon()->getAccessToken('authorization_code', ['code' => Request::getString('code')]);
		}
		catch (\Exception $ex)
		{
			// When validation fails or other local issues
		}
		// Make sure we have a user_id (cilogon returns 0 for a non-logged in user)
		if ((isset($user_id) && $user_id > 0) || (isset($session) && $session))
		{
			try
			{
				$cilogonResponse = $this->cilogon()->getResourceOwner($session);
				$id       = $cilogonResponse->getId();
				$email    = $cilogonResponse->getEmail();
			}
			catch (\Exception $e)
			{
				// Error message?
				$response->status = \Hubzero\Auth\Status::FAILURE;
				$response->error_message = Lang::txt('PLG_AUTHENTICATION_CILOGON_ERROR_RETRIEVING_PROFILE', $e->getMessage());
				return;
			}

			$hzad = \Hubzero\Auth\Domain::getInstance('authentication', 'cilogon', '');

			// Create the link
			if (\Hubzero\Auth\Link::getInstance($hzad->id, $id))
			{
				// This cilogon account is already linked to another hub account
				App::redirect(
					Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=account'),
					Lang::txt('PLG_AUTHENTICATION_CILOGON_ACCOUNT_ALREADY_LINKED'),
					'error'
				);
			}
			else
			{
				$hzal = \Hubzero\Auth\Link::find_or_create('authentication', 'cilogon', null, $id);
				// if `$hzal` === false, then either:
				//    the authenticator Domain couldn't be found,
				//    no username was provided,
				//    or the Link record failed to be created
				if ($hzal)
				{
					$hzal->set('user_id', User::get('id'));
					$hzal->set('email', $email);
					$hzal->update();
				}
				else
				{
					Log::error(sprintf('Hubzero\Auth\Link::find_or_create("authentication", "cilogon", null, %s) returned false', $id));
				}
			}
		}
		else
		{
			// User didn't authorize our app, or, clicked cancel
			App::redirect(
				Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=account'),
				Lang::txt('PLG_AUTHENTICATION_CILOGON_MUST_AUTHORIZE_TO_LINK', Config::get('sitename')),
				'error'
			);
		}
	}

	/**
	 * Generate return url
	 *
	 * @param   string  $return  url
	 * @param   bool    $encode  whether or not to encode return before using
	 * @return  string  url
	 */
	private static function getReturnUrl($return=null, $encode=false)
	{
		// Get the hub url
		$service = trim(Request::base(), '/');

		if (empty($service))
		{
			$service = $_SERVER['HTTP_HOST'];
		}

		// Check if a return is specified
		$rtrn = '';
		if (isset($return) && !empty($return))
		{
			if ($encode)
			{
				$return = base64_encode($return);
			}
			$rtrn = '&return=' . $return;
		}

		return self::getRedirectUri('cilogon') . $rtrn;
	}
}
