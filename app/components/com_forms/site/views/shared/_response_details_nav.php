<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formEditNav');

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/formsRouter.php";

use Components\Forms\Helpers\FormsRouter as Routes;

$current = $this->current;
$formId = $this->formId;
$responseId = $this->responseId;
$responsePermissions = $this->responsePermissions;
$routes = new Routes();
$userId = $this->userId;
$userIsAdmin = $this->userIsAdmin;

$steps = [
	'Response' => $routes->formResponseFillUrl($responseId),
	'Feed' => $routes->responseFeedUrl($responseId)
	// 'Steps' => $routes->usersFormPrereqsUrl($formId, $userId),
];

$formJson = '{
  "title": "Response Access Rules",
  "pages": [
    {
      "name": "page1",
      "elements": [
        {
          "type": "dropdown",
          "name": "accessibility",
          "title": "General Access",
          "defaultValueExpression": "restricted",
          "isRequired": true,
          "choices": [
            {
              "value": "anyone",
              "text": "Anyone"
            },
            {
              "value": "qubes",
              "text": "QUBES Members"
            },
            {
              "value": "readonly",
              "text": "Read-only"
            },
            {
              "value": "restricted",
              "text": "Restricted"
            }
          ],
          "allowClear": false
        },
        {
          "type": "matrixdynamic",
          "name": "userAccessibility",
          "visibleIf": "{accessibility} == \'readonly\' || {accessibility} == \'restricted\'",
          "title": "User Access",
          "columns": [
            {
              "name": "user",
              "title": "User",
              "cellType": "dropdown",
              "isRequired": true,
              "isUnique": true,
              "placeholder": "Search for user",
              "choicesLazyLoadEnabled": true,
              "itemComponent": "new-user-item"
            },
            {
              "name": "permission",
              "title": "Permission",
              "cellType": "dropdown",
              "defaultValue": "fill",
              "choices": [
                {
                  "value": "fill",
                  "text": "Fill"
                },
                {
                  "value": "readonly",
                  "text": "Read-only"
                }
              ],
              "searchEnabled": false
            }
          ],
          "rowCount": 0,
          "addRowText": "Add user"
        },
        {
          "type": "matrixdynamic",
          "name": "groupAccessibility",
          "visibleIf": "{accessibility} == \'readonly\' || {accessibility} == \'restricted\'",
          "title": "Group Access",
          "columns": [
            {
              "name": "group",
              "title": "Group",
              "cellType": "dropdown",
              "isRequired": true,
              "placeholder": "Search for group",
              "choicesLazyLoadEnabled": true,
              "itemComponent": "new-group-item"
            },
            {
              "name": "role",
              "title": "Role",
              "cellType": "dropdown",
              "isRequired": true,
              "defaultValue": "members",
              "choicesByUrl": {
                "url": "index.php?option=com_groups&no_html=1&task=autocomplete&id[]={row.group}&roles=1",
                "valueName": "id",
                "titleName": "name"
              },
              "searchEnabled": false
            },
            {
              "name": "permission",
              "title": "Permission",
              "cellType": "dropdown",
              "defaultValue": "fill",
              "choices": [
                {
                  "value": "fill",
                  "text": "Fill"
                },
                {
                  "value": "readonly",
                  "text": "Read-only"
                }
              ],
              "searchEnabled": false
            }
          ],
          "rowCount": 0,
          "addRowText": "Add group/role"
        }
      ]
    }
  ],
  "clearInvisibleValues": "none",
  "widthMode": "static"
}';

$this->view('_ul_nav', 'shared')
	->set('formId', $formId)
	->set('showAccessBtn', true)
	->set('current', $current)
	->set('steps', $steps)
	->display();

$this->view('_surveyjs_popup', 'shared')
	->set('formId', $formId)
	->set('responseId', $responseId)
	->set('responsePermissions', $responsePermissions)
	->set('formJson', $formJson)
	->set('routes', $routes)
	->display();