<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$base = 'index.php?option=com_groups&cn=' . $this->group->get('cn') . '&active=forum';

$this->css()
     ->js();
?>

<?php if ($this->getError()) { ?>
	<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>

	<form action="<?php echo Route::url($base . '&action=savesettings'); ?>" method="post" id="hubForm" class="full">
		<fieldset class="settings">
			<legend><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_THREADS'); ?></legend>

			<div class="form-group">
				<label for="param-threading">
					<?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_THREADING'); ?>
					<select name="params[threading]" id="param-threading" class="form-control">
						<option value="list"<?php if ($this->config->get('threading', 'list') == 'list') { echo ' selected="selected"'; }?>><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_LIST'); ?></option>
						<option value="tree"<?php if ($this->config->get('threading', 'list') == 'tree') { echo ' selected="selected"'; }?>><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_TREE'); ?></option>
					</select>
					<span class="hint"><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_THREADING_HINT'); ?></span>
				</label>
			</div>

			<div class="form-group">
				<label for="param-sorting">
					<?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_SORTING'); ?>
					<select name="params[sorting]" id="param-sorting" class="form-control">
						<option value="activity"<?php if ($this->config->get('sorting', 'activity') == 'activity') { echo ' selected="selected"'; }?>><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_ACTIVITY'); ?></option>
						<option value="created"<?php if ($this->config->get('sorting', 'activity') == 'created') { echo ' selected="selected"'; }?>><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_CREATED'); ?></option>
						<option value="replies"<?php if ($this->config->get('sorting', 'activity') == 'replies') { echo ' selected="selected"'; }?>><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_REPLIES'); ?></option>
						<option value="title"<?php if ($this->config->get('sorting', 'activity') == 'title') { echo ' selected="selected"'; }?>><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_TITLE'); ?></option>
					</select>
					<span class="hint"><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_SORTING_HINT'); ?></span>
				</label>
			</div>

			<div class="form-group">
				<label for="param-threading_depth">
					<?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_THREADING_DEPTH'); ?>
					<input type="text" class="form-control" name="params[threading_depth]" id="param-threading_depth" value="<?php echo $this->config->get('threading_depth', 3); ?>" />
					<span class="hint"><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_THREADING_DEPTH_HINT'); ?></span>
				</label>
			</div>

			<fieldset class="form-group">
				<legend><?php echo Lang::txt('PLG_GROUPS_FORUM_SETTINGS_ALLOW_ANONYMOUS'); ?></legend>

				<div class="form-check">
					<label for="param-allow_anonymous-no" class="form-check-label">
						<input type="radio" class="form-check-input" name="params[allow_anonymous]" id="param-allow_anonymous-no" value="0" <?php if (!$this->config->get('allow_anonymous')) { echo ' checked="checked"'; } ?> />
						<?php echo Lang::txt('JNO'); ?>
					</label>
				</div>
				<div class="form-check">
					<label for="param-allow_anonymous-yes" class="form-check-label">
						<input type="radio" class="form-check-input" name="params[allow_anonymous]" id="param-allow_anonymous-yes" value="1" <?php if ($this->config->get('allow_anonymous')) { echo ' checked="checked"'; } ?> />
						<?php echo Lang::txt('JYES'); ?>
					</label>
				</div>
			</fieldset>

			<fieldset class="form-group">
				<legend><?php echo Lang::txt('COM_GROUPS_EMAIL_SETTINGS_TITLE'); ?></legend>
				<p><?php echo Lang::txt('COM_GROUPS_EMAIL_SETTINGS_DESC'); ?></p>

				<fieldset>
					<legend><?php echo Lang::txt('COM_GROUPS_EMAIL_SETTING_FORUM_SECTION_LEGEND'); ?> <span class="optional"><?php echo Lang::txt('COM_GROUPS_OPTIONAL'); ?></span></legend>

					<div class="form-group form-check">
						<label for="discussion_email_autosubscribe" class="form-check-label">
							<input type="checkbox" class="option form-check-input" name="params[discussion_email_autosubscribe]" id="discussion_email_autosubscribe" value="1" <?php
								if ($this->config->get('discussion_email_autosubscribe', null) >= 1):
									echo ' checked="checked"';
								endif;
								?> />
							<strong><?php echo Lang::txt('COM_GROUPS_EMAIL_SETTING_FORUM_AUTO_SUBSCRIBE'); ?></strong> <br />
							<span class="indent">
								<?php echo Lang::txt('COM_GROUPS_EMAIL_SETTINGS_FORUM_AUTO_SUBSCRIBE_NOTE'); ?>
							</span>
						</label>
						<label>
							<input type="radio" name="params[discussion_email_autosubscribe]" class="forum-email-digest" value="1"
								<?php if ($this->config->get('discussion_email_autosubscribe', null) == 1) { echo ' checked="checked"'; } ?> 
								<?php if ($this->config->get('discussion_email_autosubscribe', null) == 0) { echo ' disabled="disabled"'; } ?> />
							<?php echo Lang::txt('PLG_GROUPS_FORUM_EMAIL_POSTS_IMMEDIATELY'); ?>

							<br />

							<input type="radio" name="params[discussion_email_autosubscribe]" class="forum-email-digest" value="2"
								<?php if ($this->config->get('discussion_email_autosubscribe', null) >= 2) { echo ' checked="checked"'; } ?>
								<?php if ($this->config->get('discussion_email_autosubscribe', null) == 0) { echo ' disabled="disabled"'; } ?> />
							<?php echo Lang::txt('PLG_GROUPS_FORUM_EMAIL_POSTS_AS_A'); ?>

							<select name="params[discussion_email_autosubscribe]" class="forum-email-digest" <?php if ($this->config->get('discussion_email_autosubscribe', null) < 2) { echo ' disabled="disabled"'; } ?>>
								<option value="2"<?php if ($this->config->get('discussion_email_autosubscribe', null) == 2) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('PLG_GROUPS_FORUM_EMAIL_POSTS_DAILY'); ?></option>
								<option value="3"<?php if ($this->config->get('discussion_email_autosubscribe', null) == 3) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('PLG_GROUPS_FORUM_EMAIL_POSTS_WEEKLY'); ?></option>
								<option value="4"<?php if ($this->config->get('discussion_email_autosubscribe', null) == 4) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('PLG_GROUPS_FORUM_EMAIL_POSTS_MONTHLY'); ?></option>
							</select>
							<?php echo Lang::txt('PLG_GROUPS_FORUM_EMAIL_POSTS_DIGEST'); ?>
						</label>
					</div>
				</fieldset>
			</fieldset>

			<input type="hidden" name="settings[id]" value="<?php echo $this->settings->get('id'); ?>" />
			<input type="hidden" name="settings[object_id]" value="<?php echo $this->group->get('gidNumber'); ?>" />
			<input type="hidden" name="settings[folder]" value="groups" />
			<input type="hidden" name="settings[element]" value="forum" />
		</fieldset>
		<div class="clear"></div>

		<input type="hidden" name="cn" value="<?php echo $this->group->get('cn'); ?>" />
		<input type="hidden" name="process" value="1" />
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="active" value="forum" />
		<input type="hidden" name="action" value="savesettings" />

		<?php echo Html::input('token'); ?>

		<p class="submit">
			<input class="btn btn-success" type="submit" value="<?php echo Lang::txt('PLG_GROUPS_FORUM_SAVE'); ?>" />

			<a class="btn btn-secondary" href="<?php echo Route::url($base); ?>">
				<?php echo Lang::txt('JCANCEL'); ?>
			</a>
		</p>
	</form>
