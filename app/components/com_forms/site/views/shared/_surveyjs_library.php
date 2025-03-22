<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('surveyjs/survey-core.min.css')
    ->js('surveyjs/survey.core.min.js')
    ->js('surveyjs/survey-js-ui.min.js')
    ->js('formSetup.js')
    ->js('formLibrary.js');
?>

<form id="surveyjsForm" style="margin: 0;">
    <div id="formLibrary" style="height: 100vh;"></div>
    <input type="hidden" name="form_id" value="<?php echo $this->form->get('id'); ?>">
    <input type="hidden" name="response_id" value="<?php echo $this->response->get('id'); ?>">
</form>