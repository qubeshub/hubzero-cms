<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('surveyjs/survey-core.min.css')
    ->css('formForm')
    ->js('surveyjs/survey.core.min.js')
    ->js('surveyjs/survey-js-ui.min.js')
    ->js('formSetup.js')
    ->js('formLibrary.js');

$formId = $this->formId;
$responsePermissions = $this->responsePermissions;
$responseId = $this->responseId;
$formJson = $this->formJson;
$routes = $this->routes;
?>

<script id="surveyjs-popup-json-form" type="application/json">
<?php echo $formJson; ?>
</script>

<div id="surveyjs-popup" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; min-height: 100%; height:100%;"></div>

<form id="surveyjs-popup-submit" class="surveyjs-popup" type="submit" method="post" action="/forms/responses/updatepermissions?response_id=<?php echo $responseId; ?>">
    <input type="hidden" name="surveyjs-popup-json-data" value='<?php echo $responsePermissions; ?>'>
    <input type="hidden" name="responseId" value="<?php echo $responseId; ?>" />
</form>