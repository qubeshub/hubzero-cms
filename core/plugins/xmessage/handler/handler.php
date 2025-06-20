<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * XMessage plugin class for handling message routing
 */
class plgXMessageHandler extends \Hubzero\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Marks action items as completed
	 *
	 * @param   string   $type       Item type
	 * @param   array    $uids       User IDs
	 * @param   string   $component  Item component
	 * @param   integer  $element
	 * @return  boolean  True if no errors
	 */
	public function onTakeAction($type, $uids=array(), $component='', $element=null)
	{
		// Do we have the proper bits?
		if (!$element || !$component || !$type)
		{
			return false;
		}

		// Do we have any user IDs?
		if (count($uids) > 0)
		{
			$database = App::get('db');

			// Loop through each ID
			foreach ($uids as $uid)
			{
				// Find any actions the user needs to take for this $component and $element
				$mids = Hubzero\Message\Action::getActionItems($type, $component, $element, $uid);

				// Check if the user has any action items
				if (count($mids) > 0)
				{
					foreach ($mids as $mid)
					{
						$xseen = Hubzero\Message\Seen::oneByMessageAndUser($mid, $uid);

						if ($xseen->get('whenseen') == ''
						 || $xseen->get('whenseen') == $database->getNullDate()
						 || $xseen->get('whenseen') == null)
						{
							$xseen->set('whenseen', Date::toSql());
							$xseen->save();
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Send a message to one or more users
	 *
	 * @param   string   $type               Message type (maps to #__xmessage_component table)
	 * @param   string   $subject            Message subject
	 * @param   string   $message            Message to send
	 * @param   array    $from               Message 'from' data (e.g., name, address)
	 * @param   array    $to		 List of user IDs
	 * @param   string   $component          Component name
	 * @param   integer  $element            ID of object that needs an action item
	 * @param   string   $description        Action item description
	 * @param   integer  $group_id           Parameter description (if any) ...
	 * @param   boolean  $bypassGroupsCheck  Can bypass checks or not?
	 * @param   integer  $anonymous          Anonymous value in message
	 * @return  mixed    True if no errors else error message
	 */
	public function onSendMessage($type, $subject, $message, $from=array(), $to=array(), $component='', $element=null, $description='', $group_id=0, $bypassGroupsCheck=false, $anonymous=0)
	{
		// Do we have a message?
		if (!$message)
		{
			return false;
		}

		$database = App::get('db');

		// Create the message object
		$xmessage = Hubzero\Message\Message::blank();

		if ($type == 'member_message')
		{
			$time_limit  = intval($this->params->get('time_limit', 30));
			$daily_limit = intval($this->params->get('daily_limit', 100));

			// First, let's see if they've surpassed their daily limit for sending messages
			$filters = array(
				'created_by'  => User::get('id'),
				'daily_limit' => $daily_limit
			);

			$number_sent = $xmessage->getSentMessagesCount($filters);

			if ($number_sent >= $daily_limit)
			{
				return false;
			}

			// Next, we see if they've passed the time limit for sending consecutive messages
			$filters['limit'] = 1;
			$filters['start'] = 0;
			$sent = $xmessage->getSentMessages($filters);
			if ($sent->count() > 0)
			{
				$last_sent = $sent->first();

				$last_time = 0;
				if ($last_sent->created)
				{
					$last_time = Date::of($last_sent->created)->toUnix();
				}
				$time_difference = (Date::toUnix() + $time_limit) - $last_time;

				if ($time_difference < $time_limit)
				{
					return false;
				}
			}
		}

		// Store the message in the database
		$xmessage->set('message', (is_array($message) && isset($message['plaintext'])) ? $message['plaintext'] : $message);

		// Outlook 2016 Windows 10 Desktop client fails to parse some
		// encoded multi-line subject fields. In particular with the
		// line ending of CRLF (or its a bug in  our mailer's encoding,
		// not actually certain). To address this and other potential
		// problems with the subject field we add additional logic
		// here to ensure a valid subject field.
		//
		// If no subject passed use the message text as the subject
		// Truncate subject to 70 characters
		// Append ellipse (...) if truncated
		// Replace any CRLF with LF
		//
		// *njk*

		$subject = trim($subject);

		if (!$subject)
		{
			$subject = trim($xmessage->get('message'));
		}

		if (strlen($subject) >= 70)
		{
			$subject = trim(substr($subject, 0, 70));
			$subject .= '...';
		}

		$subject = trim(str_replace("\r\n", "\n", $subject));

		if (!$subject)
		{
			$subject = "[No Subject]";
		}

		$xmessage->set('subject', $subject);
		$xmessage->set('created', Date::toSql());
		$xmessage->set('created_by', User::get('id'));
		$xmessage->set('component', $component);
		$xmessage->set('type', $type);
		$xmessage->set('group_id', $group_id);
		$xmessage->set('anonymous', (int)$anonymous);

		if (!$xmessage->save())
		{
			return $xmessage->getError();
		}

		if (is_array($message))
		{
			$xmessage->set('message', $message);
		}

		// Do we have any recipients?
		if (count($to) > 0)
		{
			$mconfig = Component::params('com_members');

			// Get all the sender's groups
			if ($mconfig->get('user_messaging', 1) == 1 && !$bypassGroupsCheck)
			{
				$xgroups = User::groups('all');
				$usersgroups = array();
				if (!empty($xgroups))
				{
					foreach ($xgroups as $group)
					{
						if ($group->regconfirmed)
						{
							$usersgroups[] = $group->cn;
						}
					}
				}
			}

			// Loop through each recipient
			foreach ($to as $uid)
			{
				// Create a recipient object that ties a user to a message
				$recipient = Hubzero\Message\Recipient::blank();
				$recipient->set('uid', $uid);
				$recipient->set('mid', $xmessage->get('id'));
				$recipient->set('created', Date::toSql());
				$recipient->set('expires', Date::of(time() + (168 * 24 * 60 * 60))->toSql());
				$recipient->set('actionid', 0); //(is_object($action)) ? $action->id : 0; [zooley] Phasing out action items

				// Get the user's methods for being notified
				$notify = Hubzero\Message\Notify::blank();
				$methods = $notify->getRecords($uid, $type);

				$user = User::getInstance($uid);
				if (!is_object($user) || !$user->get('username'))
				{
					continue;
				}

				if ($mconfig->get('user_messaging', 1) == 1 && ($type == 'member_message' || $type == 'group_message'))
				{
					$pgroups = \Hubzero\User\Helper::getGroups($user->get('id'), 'all', 1);
					$profilesgroups = array();
					if (!empty($pgroups))
					{
						foreach ($pgroups as $group)
						{
							if ($group->regconfirmed)
							{
								$profilesgroups[] = $group->cn;
							}
						}
					}
					// Find the common groups
					if (!$bypassGroupsCheck)
					{
						$common = array_intersect($usersgroups, $profilesgroups);
						if (count($common) <= 0)
						{
							continue;
						}
					}
				}

				// Do we have any methods?
				if ($methods->count())
				{
					// Loop through each method
					foreach ($methods as $method)
					{
						$action = strtolower($method->method);
						if ($action == 'internal')
						{
							if (!$recipient->save())
							{
								$this->setError($recipient->getError());
							}
						}
						else
						{
							if (!Event::trigger('onMessage', array($from, $xmessage, $user, $action)))
							{
								$this->setError(Lang::txt('PLG_XMESSAGE_HANDLER_ERROR_UNABLE_TO_MESSAGE', $uid, $action));
							}
						}
					}
				}
				else
				{
					// First check if they have ANY methods saved (meaning they've changed their default settings)
					// If They do have some methods, then they simply turned off everything for this $type
					$methods = $notify->getRecords($uid);
					if (!$methods || $methods->count() <= 0)
					{
						// Load the default method
						$p = Plugin::byType('members', 'messages');
						$pp = new \Hubzero\Config\Registry((is_object($p) ? $p->params : ''));

						$d = $pp->get('default_method', 'email');

						if (!$recipient->save())
						{
							$this->setError($recipient->getError());
						}

						// Use the Default in the case the user has no methods
						if (!Event::trigger('onMessage', array($from, $xmessage, $user, $d)))
						{
							$this->setError(Lang::txt('PLG_XMESSAGE_HANDLER_ERROR_UNABLE_TO_MESSAGE', $uid, $d));
						}
					}
				}
			}
		}

		return true;
	}
}
