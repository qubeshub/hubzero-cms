<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Members\Helpers;

/**
 * Helper class for registration.
 * Use primarily for input validation.
 */
class Utility
{
	/**
	 * Validate organization type
	 *
	 * @param   string   $org
	 * @return  boolean
	 */
	public static function validateOrgType($org)
	{
		$orgtypes = array('university','precollege','nationallab','industry','government','military','unemployed');

		if (in_array($org, $orgtypes))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check validity of login
	 *
	 * @param   string  $login                       Login name to check
	 * @param   bool    $allowNumericFirstCharacter  Whether or not to allow first character as number (used for grandfathered accounts)
	 * @return  integer
	 */
	public static function validlogin($login, $allowNumericFirstCharacter=false)
	{
		$firstCharClass = ($allowNumericFirstCharacter) ? 'a-z0-9' : 'a-z';

		if (preg_match("/^[" . $firstCharClass . "][_.a-z0-9]{1,31}$/", $login))
		{
			if (self::is_positiveint($login))
			{
				return 0;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Check if an integer is positive
	 *
	 * @param   integer  $x
	 * @return  boolean
	 */
	public static function is_positiveint($x)
	{
		if (is_numeric($x) && intval($x) == $x && $x >= 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Validate a password
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public static function validpassword($password)
	{
		if (preg_match("/^[_\`\~\!\@\#\$\%\^\&\*\(\)\=\+\{\}\:\;\"\'\<\>\,\.\?\/0-9a-zA-Z-]+$/", $password))
		{
			return true;
		}
		return false;
	}

	/**
	 * Validate an email address
	 *
	 * @param   string  $email
	 * @return  boolean
	 */
	public static function validemail($email)
	{
		if (preg_match("/^[_\+\.\%0-9a-zA-Z-]+@([0-9a-zA-Z-]+\.)+[a-zA-Z]{2,63}$/", $email))
		{
			return true;
		}
		return false;
	}

	/**
	 * Validate a URL
	 *
	 * @param   string  $url
	 * @return  integer
	 */
	public static function validurl($url)
	{
		$ptrn = '/([a-z0-9_\-]{1,5}:\/\/)?(([a-z0-9_\-]{1,}):([a-z0-9_\-]{1,})\@)?((www\.)|([a-z0-9_\-]{1,}\.)+)?([a-z0-9_\-]{2,})(\.[a-z]{2,4})(\/([a-z0-9_\-]{1,}\/)+)?([a-z0-9_\-]{1,})?(\.[a-z]{2,})?(\?)?(((\&)?[a-z0-9_\-]{1,}(\=[a-z0-9_\-]{1,})?)+)?/';
		if (preg_match($ptrn, $url))
		{
			return 1;
		}
		return 0;
	}

	/**
	 * Validate a phone number
	 *
	 * @param   string  $phone
	 * @return  integer
	 */
	public static function validphone($phone)
	{
		if (preg_match("/^[\ \#\*\+\:\,\.0-9-]*$/", $phone))
		{
			return 1;
		}
		return 0;
	}

	/**
	 * Validate text
	 *
	 * @param   string   $text  Text to validate
	 * @return  integer
	 */
	public static function validtext($text)
	{
		if (!strchr($text, "	"))
		{
			return 1;
		}
		return 0;
	}

	/**
	 * Validate name
	 *
	 * @param   string   $name  the name to validate
	 * @return  boolean
	 */
	public static function validname($name)
	{
		return \Hubzero\Utility\Validate::properName($name);
	}

	/**
	 * Validate ORCID
	 *
	 * @param   string   $orcid  ORCID
	 * @return  integer  1 = valid, 0 = invalid
	 */
	public static function validorcid($orcid)
	{
		if (preg_match("/^[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}$/", $orcid))
		{
			return 1;
		}
		return 0;
	}

	/**
	 * Generate a confirmation number
	 *
	 * @return  integer
	 */
	public static function genemailconfirm()
	{
		return (-rand(1, pow(2, 31)-1)); // php5 in debian etch returns negative values if i don't subtract 1 from this max
	}

	/**
	 * Send a confirmation email to a user
	 * 
	 * @param   object  $user
	 * @param   object  $xregistration
	 * @return  bool
	 */
	public static function sendConfirmEmail($user, $xregistration, $new = true)
	{
		$baseURL = rtrim(Request::root(), '/');

		$subject  = Config::get('sitename').' '.Lang::txt('COM_MEMBERS_REGISTER_EMAIL_CONFIRMATION');

		$eview = new \Hubzero\Mail\View(array(
			'name'   => 'emails',
			'layout' => 'create',
			'base_path' => Component::path('members') . '/site'
		));
		$eview->option        = 'com_members';//$this->_option; //com_members
		$eview->controller    = 'register'; //$this->_controller; //register
		$eview->sitename      = Config::get('sitename');
		$eview->xprofile      = $user;
		$eview->baseURL       = $baseURL;
		$eview->xregistration = $xregistration;
		$eview->new           = $new;

		$msg = new \Hubzero\Mail\Message();
		$msg->setSubject($subject)
		    ->addTo($user->get('email'), $user->get('name'))
		    ->addFrom(Config::get('mailfrom'), Config::get('sitename') . ' Administrator')
		    ->addHeader('X-Component', 'com_members');

		$message = $eview->loadTemplate(false);
		$message = str_replace("\n", "\r\n", $message);

		$msg->addPart($message, 'text/plain');

		$eview->setLayout('create_html');
		$message = $eview->loadTemplate();
		$message = str_replace("\n", "\r\n", $message);
		$msg->addPart($message, 'text/html');

		if (!$msg->send())
		{
			//$this->setError(Lang::txt('COM_MEMBERS_REGISTER_ERROR_EMAILING_CONFIRMATION'/*, $hubMonitorEmail*/));
			// @FIXME: LOG ERROR SOMEWHERE
			return false;
		}

		return true;
	}

	/**
	 * Generate a random password
	 *
	 * @param   integer  $length  Length of the password
	 * @return  string
	 */
	public static function userpassgen($length = 8)
	{
		$genpass = '';
		$salt = "abchefghjkmnpqrstuvwxyz0123456789";
		srand((double)microtime()*1000000);
		$i = 0;
		while ($i < $length)
		{
			$num = rand() % 33;
			$tmp = substr($salt, $num, 1);
			$genpass = $genpass . $tmp;
			$i++;
		}
		return $genpass;
	}

	/**
	 * Check to see if the email confirmation code is still an active code
	 *
	 * @param   integer  $code  email confirmation code
	 * @return  bool
	 */
	public static function isActiveCode($code)
	{
		$db = \App::get('db');

		$query = "SELECT `id` FROM `#__users` WHERE `activation` = " . $db->quote('-' . $code) . " LIMIT 1";
		$db->setQuery($query);
		$result = $db->loadResult();

		return ($result) ? true : false;
	}
	
	/**
	 * Escape the special characters that might exist in the organization names on Research Organization Registry 
	 *
	 * @param   string  $term
	 * @return  string
	 */
	public static function escapeSpecialChars($term)
	{
		$special_chars = [
			"+" => "\+", 
			"-" => "\-", 
			"=" => "\=", 
			"&&" => "\&&", 
			"||" => "\||", 
			">" => "\>", 
			"<" => "\<", 
			"!" => "\!", 
			"(" => "\(", 
			")" => "\)", 
			"{" => "\{", 
			"}" => "\}", 
			"[" => "\[", 
			"]" => "\]", 
			"^" => "\^", 
			'"' => '\"', 
			"~" => "\~", 
			"*" => "\*", 
			"?" => "\?", 
			":" => "\:", 
			"\\" => "\\\\", 
			"/" => "\/"
		];
    
		return str_replace(array_keys($special_chars), array_values($special_chars), $term);
	}
}
