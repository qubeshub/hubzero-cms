<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css();

$pageTitle = ($this->author->id) ? Lang::txt('COM_PUBLICATIONS_EDIT_AUTHOR_INFO') : Lang::txt('COM_PUBLICATIONS_ADD_AUTHOR');

$tmpl = Request::getCmd('tmpl', '');

if ($tmpl != 'component')
{
	Toolbar::title(Lang::txt('COM_PUBLICATIONS') . ': ' . $pageTitle . ' ' . Lang::txt('COM_PUBLICATIONS_FOR_PUB') . ' #' . $this->pub->id . ' (v.' . $this->row->version_label . ')', 'publications');
	Toolbar::save('saveauthor');
	Toolbar::cancel();
}

$name      = $this->author->name ? $this->author->name : null;
$firstname = null;
$lastname  = null;

if (trim($name))
{
	$nameParts = explode(' ', $name);
	$lastname  = end($nameParts);
	$firstname = count($nameParts) > 1 ? $nameParts[0] : '';
}

$firstname = $this->author->firstName ? htmlspecialchars($this->author->firstName) : $firstname;
$lastname  = $this->author->lastName ? htmlspecialchars($this->author->lastName) : $lastname;

?>

<?php if ($this->getError()) { ?>
	<p class="error"><?php echo implode('<br />', $this->getError()); ?></p>
<?php } ?>
<p class="crumbs"><?php echo Lang::txt('COM_PUBLICATIONS_PUBLICATION_MANAGER'); ?> &raquo; <a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=edit&id[]=' . $this->pub->id); ?>"><?php echo Lang::txt('COM_PUBLICATIONS_PUBLICATION') . ' #' . $this->pub->id; ?></a> &raquo; <?php echo $pageTitle; ?></p>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" id="item-form">
<?php if ($tmpl == 'component') { ?>
	<fieldset>
		<div class="configuration">
			<div class="configuration-options">
				<button type="button" onclick="submitbutton('addusers');"><?php echo Lang::txt( 'JSAVE' );?></button>
				<button type="button" onclick="window.parent.document.getElementById('sbox-window').close();"><?php echo Lang::txt( 'Cancel' );?></button>
			</div>

			<?php echo Lang::txt('COM_PUBLICATIONS_EDIT_AUTHOR') ?>
		</div>
	</fieldset>
<?php } ?>

	<fieldset class="adminform">
		<legend><span><?php echo $pageTitle; ?></span></legend>

		<input type="hidden" name="author" value="<?php echo $this->author->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="controller" value="<?php echo $this->controller; ?>">
		<input type="hidden" name="no_html" value="<?php echo ($tmpl == 'component') ? '1' : '0'; ?>">
		<input type="hidden" name="task" value="saveauthor" />
		<input type="hidden" name="id" value="<?php echo $this->pub->id; ?>" />
		<input type="hidden" name="version" value="<?php echo $this->row->version_number; ?>" />

		<table class="admintable">
			<tbody>
				<?php if (!$this->author->id) { ?>
					<tr>
						<td class="key"><label><?php echo Lang::txt('COM_PUBLICATIONS_FIELD_EMAIL'); ?>:</label></td>
						<td>
							<input type="text" name="email" value="" />
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td class="key"><label><?php echo Lang::txt('COM_PUBLICATIONS_FIELD_USER_ID'); ?>:</label></td>
					<td>
						<?php if (!$this->author->id || !$this->author->user_id) { ?>
							<input type="text" name="uid" value="<?php echo $this->author->user_id; ?>" size="25" />
						<?php } else { ?>
							<input type="hidden" name="uid" value="<?php echo $this->author->user_id; ?>" />
							<span><?php echo $this->author->user_id; ?></span>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td class="key"><label><?php echo Lang::txt('COM_PUBLICATIONS_FIELD_AUTHOR_NAME_FIRST_AND_MIDDLE'); ?>: <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label></td>
					<td>
						<input type="text" name="firstName" value="<?php echo $firstname; ?>" size="25" />
					</td>
				</tr>
				<tr>
					<td class="key"><label><?php echo Lang::txt('COM_PUBLICATIONS_FIELD_AUTHOR_NAME_LAST'); ?>: <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label></td>
					<td>
						<input type="text" name="lastName" value="<?php echo $lastname; ?>" size="25" />
					</td>
				</tr>
				<tr>
					<td class="key"><label><?php echo Lang::txt('COM_PUBLICATIONS_FIELD_AUTHOR_ORGANIZATION'); ?>:</label></td>
					<td>
						<input type="text" name="organization" value="<?php echo $this->escape($this->author->organization); ?>" size="25" />
					</td>
				</tr>
				<tr>
					<td class="key"><label><?php echo Lang::txt('COM_PUBLICATIONS_FIELD_AUTHOR_ORCID'); ?>:</label></td>
					<td>
						<input type="text" name="orcid" placeholder="####-####-####-####" value="<?php echo $this->escape($this->author->orcid); ?>" size="25" />
						<p><?php echo Lang::txt('COM_PUBLICATIONS_FIELD_AUTHOR_ORCID_ID_DESC'); ?></p>
					</td>
				</tr>
				<tr>
					<td class="key"><label><?php echo Lang::txt('COM_PUBLICATIONS_FIELD_AUTHOR_CREDIT'); ?>:</label></td>
					<td>
						<input type="text" name="credit" value="<?php echo $this->escape($this->author->credit); ?>" size="25" />
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>

	<?php echo Html::input('token'); ?>
</form>
