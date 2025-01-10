<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Models;

$componentPath = Component::path('com_forms');

require_once "$componentPath/models/formResponse.php";

use Hubzero\Database\Relational;

class FormResponseJson extends Relational
{
    static $RESPONSE_MODEL_NAME = 'Components\Forms\Models\FormResponse';

	/**
	 * Records table
	 *
	 * @var string
	 */
	protected $table = '#__forms_form_responses_json';

	/**
	 * Attribute validation
	 *
	 * @var  array
	 */
	public $rules = [
        'id' => 'notempty',
		'form_id' => 'positive'
	];

	/**
	 * Returns associated response ID
	 *
	 * @return   object
	 */
	public function getResponseId()
	{
		$form = $this->getResponse();

		return $form->get('id');
	}

	/*
	 * Retrieves associated response record
	 *
	 * @return   object
	 */
	public function getResponse()
	{
		$responseModelName = self::$RESPONSE_MODEL_NAME;
		$foreignKey = 'form_id';

		$response = $this->belongsToOne($responseModelName, $foreignKey)->row();

		return $response;
	}
}
