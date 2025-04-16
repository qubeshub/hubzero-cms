<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$componentPath = Component::path('com_forms');

require_once "$componentPath/models/formResponse.php";

use Components\Forms\Models\FormResponse;
?>

<div class="landing-header">
    <?php
        $this->view('_form_create_link')
            ->set('classes', 'icon-add btn')
            ->display();
            
        $this->view('_protected_link', 'shared')
            ->set('authMethod', 'doesCurrentUserHaveResponses')
            ->set('textKey', 'COM_FORMS_LINKS_MY_RESPONSES')
            ->set('urlFunction', 'usersResponsesUrl')
            ->set('urlFunctionArgs', [])
            ->set('classes', 'icon-list btn')
            ->display();

        $this->view('_protected_link', 'shared')
            ->set('authArgs', [0, 'shared'])
            ->set('authMethod', 'doesCurrentUserHaveResponses')
            ->set('textKey', 'COM_FORMS_LINKS_SHARED_RESPONSES')
            ->set('urlFunction', 'usersSharedResponsesUrl')
            ->set('urlFunctionArgs', [])
            ->set('classes', 'icon-group btn')
            ->display();
    ?>
</div>