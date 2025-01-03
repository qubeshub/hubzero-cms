<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Models;

$componentPath = Component::path('com_forms');

require_once "$componentPath/models/form.php";

use Hubzero\Database\Relational;

class FormJson extends Relational
{
	static $FORM_MODEL_NAME = 'Components\Forms\Models\Form';

	/**
	 * Records table
	 *
	 * @var string
	 */
	protected $table = '#__forms_form_json';

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
	 * Returns associated page's form's ID
	 *
	 * @return   object
	 */
	public function getFormId()
	{
		$form = $this->getForm();

		return $form->get('id');
	}

	/*
	 * Retrieves associated form record
	 *
	 * @return   object
	 */
	public function getForm()
	{
		$formModelName = self::$FORM_MODEL_NAME;
		$foreignKey = 'form_id';

		$form = $this->belongsToOne($formModelName, $foreignKey)->row();

		return $form;
	}
}
