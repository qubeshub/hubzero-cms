<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('surveyjs/survey-core.min.css')
    ->css('surveyjs/survey-creator-core.min.css')
    ->js('surveyjs/survey.core.min.js')
    ->js('surveyjs/survey-js-ui.min.js')
    ->js('surveyjs/survey-creator-core.min.js')
    ->js('surveyjs/survey-creator-js.min.js')
    ->js('formSetup.js')
    ->js('formCreator.js');
?>

<div style="margin: 0;">
    <div id="formCreator" style="height: 100vh;"></div>
</div>