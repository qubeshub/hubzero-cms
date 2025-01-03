<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
?>

<div class="landing-header">
    <?php
        $this->view('_form_create_link')
            ->set('classes', 'icon-add btn')
            ->display();
            
        $this->view('_link_lang', 'shared')
            ->set('textKey', 'COM_FORMS_LINKS_MY_RESPONSES')
            ->set('urlFunction', 'usersResponsesUrl')
            ->set('urlFunctionArgs', [])
            ->set('classes', 'icon-list btn')
            ->display();
    ?>
</div>