<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$data = $this->data;
$pub  = $data->get('pub');

$details = $data->get('localPath');
$details.= $data->getSize() ? ' | ' . $data->getSize('formatted') : '';
if ($data->get('viewer') != 'freeze')
{
	$details.= !$data->exists() ? ' | ' . Lang::txt('PLG_PROJECTS_PUBLICATIONS_MISSING_FILE') : '';
}
?>
	<li class="reorder pick" id="pick-<?php echo $data->get('id'); ?>">
		<span class="item-options">
			<?php if ($data->get('viewer') == 'edit') { ?>
			<span class="item-options">
				<?php if ($data->total > 1) { ?>
				<a href="#" class="item-reorder handle" title="<?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_REORDER'); ?>">&nbsp;</a>
				<?php } ?>
				<?php if ($data->exists()) { ?>
				<a href="<?php echo $data->get('downloadUrl'); ?>" class="item-download" title="<?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_DOWNLOAD'); ?>">&nbsp;</a>
				<?php } ?>
				<a href="<?php echo Route::url($pub->link('editversion') . '&action=edititem&aid=' . $data->get('id') . '&p=' . $data->get('props')); ?>" class="showinbox item-edit" title="<?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_RELABEL'); ?>">&nbsp;</a>
				<a href="<?php echo Route::url($pub->link('editversion') . '&action=deleteitem&aid=' . $data->get('id') . '&p=' . $data->get('props')); ?>" class="item-remove" title="<?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_REMOVE'); ?>">&nbsp;</a>
			</span>
			<?php } elseif ($data->exists()) { ?>
				<span><a href="<?php echo $data->get('downloadUrl'); ?>" class="item-download" title="<?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_DOWNLOAD'); ?>">&nbsp;</a></span>
			<?php } ?>
		</span>
		<?php if ($data->total > 1) { ?>
		<span class="item-order"><?php echo $data->get('ordering'); ?></span>
		<?php } ?>
		<span class="item-title" id="<?php echo 'file-'.$data->get('id'); ?>">
			<?php echo $data::drawIcon($data->get('ext')); ?> <?php echo $data->get('title'); ?>
		</span>
		<span class="item-details"><?php echo $details; ?></span>
		<span class="item-access<?php echo (Component::params('com_publications')->get('instructor_only') ? '' : ' hidden'); ?>"><?php echo $data->get('access') ? Lang::txt('PLG_PROJECTS_PUBLICATIONS_FILE_ACCESS') : ''; ?></span>
	</li>
