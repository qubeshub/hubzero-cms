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
		$currentUsersId = User::get('id');
		$isAdmin = $form->isAdmin($currentUsersId); // Future you: This is outside isFillableBy as admin should be able to edit anytime
		$isActive = $form->isActive();
		$isFillable = $form->isFillableBy($currentUsersId);

		$existing = FormResponse::all()
			->whereEquals('form_id', $form->get('id'))
			->whereEquals('user_id', $currentUsersId);
		$isNotFull = !$form->get('max_responses') || ($existing->count() < $form->get('max_responses'));

		$canFill = $isNotFull && ($isAdmin || ($isActive && $isFillable));

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

		$isFormAdmin = $form->isAdmin($currentUsersId);
		$userOwnsForm = $form->isOwnedBy($currentUsersId);

		$canDelete = $isFormAdmin || $userOwnsForm;

		return $canDelete;
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
		$isResponseFillable = $response->isFillableBy($currentUsersId); // If can fill then can view
		$isResponseViewable = $response->isReadonlyBy($currentUsersId);

		$canView = $isResponseFillable || $isResponseViewable;

		return $canView;
	}

	/**
	 * Determines if current user can fill/edit given response
	 * 
	 * Allowed if:
	 * 	- User is admin (or) user is form editor/owner
	 * 	- User is response owner (or) in response fill usergroup AND:
	 * 		- Response is active (in publish window)
	 * 		- Response is not submitted (or) submitted and editing is allowed after submission
	 *
	 * @param    object   $response   Form response instance
	 * @return   bool
	 */
	public function canCurrentUserFillResponse($response)
	{
		$currentUsersId = User::get('id');
		$isResponseDisabled = $response->isDisabled();
		$form = $response->getForm();
		$isFormAdmin = $form->isAdmin($currentUsersId);
		$isResponseFillable = $response->isFillableBy($currentUsersId);
		$isFormActive = $form->isActive();

		$canFill = $isFormAdmin || ($isFormActive && $isResponseFillable && !$isResponseDisabled);

		return $canFill;
	}

	/**
	 * Determines if current user has existing responses
	 *
	 * @param    object   $response   Form response instance
	 * @return   bool
	 */
	public function doesCurrentUserHaveResponses($formId = 0, $filter = '')
	{
		$currentUsersId = User::get('id');

		return FormResponse::allForUser($currentUsersId, $formId, $filter)->count();
	}

}
