<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$base = 'index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=members' . DS . 'profilesettings';

$this->css('memberprofiles.css')
     ->js('memberprofiles.js');

?>

<?php
	echo $this->form_view;


?>