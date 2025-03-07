<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Helpers;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/componentAuth.php";
require_once "$componentPath/models/formResponse.php";

use Components\Forms\Helpers\ComponentAuth;
use Components\Forms\Models\FormResponse;

class FormsAuth extends ComponentAuth
{

	/**
	 * Constructs FormsAuth instance
	 *
	 * @param    array   $args   Instantiation state
	 * @return   void
	 */
	public function __construct($args = [])
	{
		$args['component'] = 'com_forms';

		parent::__construct($args);
	}

	/**
	 * Determines if current user can edit given form
	 *
	 * @param    object   $form     Form instance
	 * @return   bool
	 */
	public function canCurrentUserEditForm($form)
	{
		$canEdit = $form->isEditableBy(User::get('id'));

		return $canEdit;
	}

	/**
	 * Determines if current user can fill given form
	 *
	 * @param    object   $form     Form instance
	 * @return   bool
	 */
	public function canCurrentUserFillForm($form)
	{
		$existing = FormResponse::all()
			->whereEquals('form_id', $form->get('id'))
			->whereEquals('user_id', User::get('id'));
		
		$canFill = $form->isFillableBy(User::get('id'));
		$canFill = $canFill && (!$form->get('max_responses') || ($existing->count() < $form->get('max_responses')));

		return $canFill;
	}

	/**
	 * Determines if current user can delete given form
	 *
	 * @param    object   $form     Form instance
	 * @return   bool
	 */
	public function canCurrentUserDeleteForm($form)
	{
		$currentUsersId = User::get('id');

		$userIsAdmin = $this->currentIsAuthorized('core.admin');
		$userCanDelete = $this->currentIsAuthorized('core.delete');
		$userOwnsForm = $form->isOwnedBy($userId);

		$canEdit = $userIsAdmin || ($userCanDelete && $userOwnsForm);

		return $canEdit;
	}

	/**
	 * Determines if current user can view given response
	 *
	 * @param    object   $response   Form response instance
	 * @return   bool
	 */
	public function canCurrentUserViewResponse($response)
	{
		$currentUsersId = User::get('id');
		$userIsAdmin = $this->currentIsAuthorized('core.admin');

		$canView = $response->isOwnedBy($currentUsersId) || $userIsAdmin;

		return $canView;
	}

	/**
	 * Determines if current user can fill/edit given response
	 * 
	 * Allowed if:
	 * 	- User is admin (or) user is form editor/owner
	 * 	- User is response owner (or) in response fill usergroup AND:
	 * 		- Response is open
	 * 		- Response is not submitted (or) submitted and editing is allowed after submission
	 *
	 * @param    object   $response   Form response instance
	 * @return   bool
	 */
	public function canCurrentUserFillResponse($response)
	{
		$currentUsersId = User::get('id');
		$userIsAdmin = $this->currentIsAuthorized('core.admin');

		$canView = $response->isOwnedBy($currentUsersId) || $userIsAdmin;

		return $canView;
	}

}
