<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

$this->row->fulltxt = ($this->row->fulltxt) ? stripslashes($this->row->fulltxt ? $this->row->fulltxt : ""): stripslashes($this->row->introtext ? $this->row->introtext : "");

$type = $this->row->type;

$data = array();
preg_match_all("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", $this->row->fulltxt, $matches, PREG_SET_ORDER);
if (count($matches) > 0)
{
	foreach ($matches as $match)
	{
		$data[$match[1]] = trim($match[2]);
	}
}

$this->row->fulltxt = preg_replace("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", '', $this->row->fulltxt);
$this->row->fulltxt = trim($this->row->fulltxt);

include_once Component::path('com_resources') . DS . 'models' . DS . 'elements.php';

$elements = new \Components\Resources\Models\Elements($data, $type->get('customFields'));
$fields = $elements->render();

$this->css('create.css')
     ->js('create.js');
?>
<header id="content-header">
	<h2><?php echo $this->title; ?></h2>

	<div id="content-header-extra">
		<p>
			<a class="icon-add add btn" href="<?php echo Route::url('index.php?option=' . $this->option . '&task=draft'); ?>">
				<?php echo Lang::txt('COM_CONTRIBUTE_NEW_SUBMISSION'); ?>
			</a>
		</p>
	</div><!-- / #content-header -->
</header><!-- / #content-header -->

<section class="main section">
	<?php
		$this->group_cn = Request::getString('group','');
		$this->view('steps')
		     ->set('option', $this->option)
		     ->set('step', $this->step)
		     ->set('steps', $this->steps)
		     ->set('id', $this->id)
			 ->set('group_cn', $this->group_cn)
		     ->set('resource', $this->row)
		     ->set('progress', $this->progress)
		     ->display();
	?>

	<?php if ($this->getError()) { ?>
		<p class="error"><?php echo implode('<br />', $this->getErrors()); ?></p>
	<?php } ?>

	<form action="<?php echo Route::url('index.php?option=' . $this->option . '&task=draft&step=' . $this->next_step . '&group=' . $this->group_cn . '&id=' . $this->id); ?>" method="post" id="hubForm" accept-charset="utf-8">
		<div class="explaination">
			<p><?php echo Lang::txt('COM_CONTRIBUTE_COMPOSE_EXPLANATION'); ?></p>

			<p><?php echo Lang::txt('COM_CONTRIBUTE_COMPOSE_ABSTRACT_HINT'); ?></p>
		</div>
		<fieldset>
			<legend><?php echo Lang::txt('COM_CONTRIBUTE_COMPOSE_ABOUT'); ?></legend>

			<label for="field-title">
				<?php echo Lang::txt('COM_CONTRIBUTE_COMPOSE_TITLE'); ?>: <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span>
				<input type="text" name="fields[title]" id="field-title" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->title ? $this->row->title : "")); ?>" />
			</label>

			<label for="field-fulltxt">
				<?php echo Lang::txt('COM_CONTRIBUTE_COMPOSE_ABSTRACT'); ?>:
				<span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span>
				<?php echo $this->editor('fields[fulltxt]', $this->escape(stripslashes($this->row->fulltxt)), 50, 20, 'field-fulltxt'); ?>
			</label>

			<fieldset>
				<legend><?php echo Lang::txt('COM_CONTRIBUTE_MEDIA_MANAGER'); ?></legend>
				<p><?php echo Lang::txt('COM_CONTRIBUTE_MEDIA_EXPLANATION'); ?></p>
				<div class="field-wrap">
					<iframe width="100%" height="160" name="filer" id="filer" src="<?php echo Request::base(true); ?>/index.php?option=<?php echo $this->option; ?>&amp;controller=media&amp;tmpl=component&amp;resource=<?php echo $this->row->id; ?>"></iframe>
				</div>
			</fieldset>
		</fieldset><div class="clear"></div>

		<?php if ($fields) { ?>
			<div class="explaination">
				<p><?php echo Lang::txt('COM_CONTRIBUTE_COMPOSE_CUSTOM_FIELDS_EXPLANATION'); ?></p>
			</div>
			<fieldset>
				<legend><?php echo Lang::txt('COM_CONTRIBUTE_COMPOSE_DETAILS'); ?></legend>
				<?php
				echo $fields;
				?>
			</fieldset><div class="clear"></div>
		<?php } ?>

		<input type="hidden" name="fields[published]" value="<?php echo $this->row->get('published'); ?>" />
		<input type="hidden" name="fields[standalone]" value="1" />
		<input type="hidden" name="fields[id]" value="<?php echo $this->row->get('id'); ?>" />
		<input type="hidden" name="id" value="<?php echo $this->row->get('id'); ?>" />
		<input type="hidden" name="fields[type]" value="<?php echo $this->row->get('type'); ?>" />
		<input type="hidden" name="fields[created]" value="<?php echo $this->row->get('created'); ?>" />
		<input type="hidden" name="fields[created_by]" value="<?php echo $this->row->get('created_by'); ?>" />
		<input type="hidden" name="fields[publish_up]" value="<?php echo $this->row->get('publish_up'); ?>" />
		<input type="hidden" name="fields[publish_down]" value="<?php echo $this->row->get('publish_down'); ?>" />
		<input type="hidden" name="fields[group_owner]" value="<?php echo $this->row->get('group_owner'); ?>" />

		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
		<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
		<input type="hidden" name="step" value="<?php echo $this->next_step; ?>" />

		<?php echo Html::input('token'); ?>

		<p class="submit">
			<input class="btn btn-success" type="submit" value="<?php echo Lang::txt('COM_CONTRIBUTE_NEXT'); ?>" />
		</p>
	</form>
</section><!-- / .main section -->
