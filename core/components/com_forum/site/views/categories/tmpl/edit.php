<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

defined('_HZEXEC_') or die();

$this->css();
?>
	<header id="content-header">
		<h2>dfsdf<?php echo Lang::txt('COM_FORUM'); ?></h2>

		<div id="content-header-extra">
			<p>
				<a class="icon-folder categories btn" href="<?php echo Route::url('index.php?option=' . $this->option); ?>">
					<?php echo Lang::txt('COM_FORUM_ALL_CATEGORIES'); ?>
				</a>
			</p>
		</div>
	</header>

	<section class="main section">
		<form action="<?php echo Route::url('index.php?option=' . $this->option); ?>" method="post" id="hubForm">
			<div class="explaination">
				<p><strong><?php echo Lang::txt('COM_FORUM_WHAT_IS_LOCKING'); ?></strong><br />
				<?php echo Lang::txt('COM_FORUM_LOCKING_EXPLANATION'); ?></p>
			</div><!-- / .explaination -->
			<fieldset>
				<legend>
					<?php if ($this->category->get('id')) { ?>
						<?php echo Lang::txt('COM_FORUM_EDIT_CATEGORY'); ?>
					<?php } else { ?>
						<?php echo Lang::txt('COM_FORUM_NEW_CATEGORY'); ?>
					<?php } ?>
				</legend>

				<div class="grid">
					<div class="col span6">
						<div class="form-group">
							<label for="field-closed" id="comment-anonymous-label">
								<input class="option form-control" type="checkbox" name="fields[closed]" id="field-closed" value="3"<?php if ($this->category->get('closed')) { echo ' checked="checked"'; } ?> />
								<?php echo Lang::txt('COM_FORUM_FIELD_CLOSED'); ?>
							</label>
						</div>
					</div>
					<div class="col span6 omega">
						<div class="form-group">
							<label for="field-access">
								<?php echo Lang::txt('COM_FORUM_FIELD_VIEW_ACCESS'); ?>
								<select class="form-control" name="fields[access]" id="field-access">
									<option value="1"<?php if ($this->category->get('access') == 1) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('COM_FORUM_FIELD_READ_ACCESS_OPTION_PUBLIC'); ?></option>
									<option value="2"<?php if ($this->category->get('access') == 2) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('COM_FORUM_FIELD_READ_ACCESS_OPTION_REGISTERED'); ?></option>
								</select>
							</label>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-section_id">
						<?php echo Lang::txt('COM_FORUM_FIELD_SECTION'); ?> <span class="required"><?php echo Lang::txt('COM_FORUM_REQUIRED'); ?></span>
						<select class="form-control" name="fields[section_id]" id="field-section_id">
							<?php foreach ($this->forum->sections(array('state' => 1))->rows() as $section) { ?>
								<option value="<?php echo $section->get('id'); ?>"<?php if ($this->category->get('section_id') == $section->get('id')) { echo ' selected="selected"'; } ?>><?php echo $this->escape(stripslashes($section->get('title'))); ?></option>
							<?php } ?>
						</select>
					</label>
				</div>

				<div class="form-group">
					<label for="field-title">
						<?php echo Lang::txt('COM_FORUM_FIELD_TITLE'); ?> <span class="required"><?php echo Lang::txt('COM_FORUM_REQUIRED'); ?></span>
						<input type="text" class="form-control" name="fields[title]" id="field-title" value="<?php echo $this->escape(stripslashes($this->category->get('title', ''))); ?>" />
					</label>
				</div>

				<div class="form-group">
					<label for="field-description">
						<?php echo Lang::txt('COM_FORUM_FIELD_DESCRIPTION'); ?>
						<textarea class="form-control" name="fields[description]" id="field-description" cols="35" rows="5"><?php echo $this->escape(stripslashes($this->category->get('description', ''))); ?></textarea>
					</label>
				</div>
			</fieldset>
			<div class="clear"></div>

			<p class="submit">
				<input class="btn btn-success" type="submit" value="<?php echo Lang::txt('JSUBMIT'); ?>" />

				<a class="btn btn-secondary" href="<?php echo Route::url('index.php?option=' . $this->option); ?>">
					<?php echo Lang::txt('JCANCEL'); ?>
				</a>
			</p>

			<input type="hidden" name="fields[alias]" value="<?php echo $this->category->get('alias'); ?>" />
			<input type="hidden" name="fields[id]" value="<?php echo $this->category->get('id'); ?>" />
			<input type="hidden" name="fields[state]" value="<?php echo $this->category->get('state', 1); ?>" />
			<input type="hidden" name="fields[scope]" value="site" />
			<input type="hidden" name="fields[scope_id]" value="0" />

			<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
			<input type="hidden" name="controller" value="categories" />
			<input type="hidden" name="task" value="save" />

			<?php echo Html::input('token'); ?>
		</form>
	</section><!-- / .below section -->
