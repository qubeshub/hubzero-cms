<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();
?>
<div id="help-pane">
	<div id="help-container" class="grid">
		<h1><?php echo Lang::txt('MOD_REPORTPROBLEMS_SUPPORT'); ?></h1>

		<div class="col span4">
			<h2><?php echo Lang::txt('MOD_REPORTPROBLEMS_SUPPORT_OPTIONS'); ?></h2>
			<ul>
				<li class="help-kb">
					<h3><a href="<?php echo Route::url('index.php?option=com_kb'); ?>"><?php echo Lang::txt('MOD_REPORTPROBLEMS_OPTION_KB_TITLE'); ?></a></h3>
					<p>Find information on common questions and issues.</p>
				</li>
				<li class="help-tickets">
					<h3><a href="<?php echo Route::url('index.php?option=com_support&controller=tickets&task=display'); ?>">Support Messages</a></h3>
					<p>Check on the status of your correspondences with members of the QUBES team.</p>
				</li>
			</ul>
		</div><!-- / .col span4 -->
		<div class="col span8 omega">
			<h2>
				Contact Us
			</h2>
			<form method="post" action="<?php echo Route::url('index.php?option=com_support&controller=tickets&task=save'); ?>" id="troublereport" enctype="multipart/form-data">
				<fieldset class="reporter">
					<legend><?php echo Lang::txt('MOD_REPORTPROBLEMS_LEGEND_REPORTER'); ?></legend>

					<label<?php if ($this->guestOrTmpAccount) { echo ' for="trLogin"'; } ?>>
						<?php echo Lang::txt('MOD_REPORTPROBLEMS_LABEL_LOGIN'); ?>: <span class="optional"><?php echo Lang::txt('MOD_REPORTPROBLEMS_OPTIONAL'); ?></span>
						<?php if (!$this->guestOrTmpAccount) { ?>
							<input type="hidden" name="reporter[login]" id="trLogin" value="<?php echo $this->escape(User::get('username')); ?>" /><br /><span class="info-block"><?php echo $this->escape(User::get('username')); ?></span>
						<?php } else { ?>
							<input type="text" name="reporter[login]" id="trLogin" value="" />
						<?php } ?>
					</label>

					<label<?php if ($this->guestOrTmpAccount) { echo ' for="trName"'; } ?>>
						<?php echo Lang::txt('MOD_REPORTPROBLEMS_LABEL_NAME'); ?>: <span class="required"><?php echo Lang::txt('MOD_REPORTPROBLEMS_REQUIRED'); ?></span>
						<?php if (!$this->guestOrTmpAccount) {
							$name = trim(User::get('name'));
							$name = $name ?: User::get('username');
							?>
							<input type="hidden" name="reporter[name]" id="trName" value="<?php echo $this->escape($name); ?>" /><br /><span class="info-block"><?php echo $this->escape($name); ?></span>
						<?php } else { ?>
							<input type="text" name="reporter[name]" id="trName" value="" />
						<?php } ?>
					</label>

					<label<?php if ($this->guestOrTmpAccount) { echo ' for="trEmail"'; } ?>>
						<?php echo Lang::txt('MOD_REPORTPROBLEMS_LABEL_EMAIL'); ?>: <span class="required"><?php echo Lang::txt('MOD_REPORTPROBLEMS_REQUIRED'); ?></span>
						<?php if (!$this->guestOrTmpAccount) { ?>
							<input type="hidden" name="reporter[email]" id="trEmail" value="<?php echo $this->escape(User::get('email')); ?>" /><br /><span class="info-block"><?php echo $this->escape(User::get('email')); ?></span>
						<?php } else { ?>
							<input type="email" name="reporter[email]" id="trEmail" value="" />
						<?php } ?>
					</label>

					<?php
						$captchas = Event::trigger('captcha.onDisplay');

						if (count($captchas) > 0)
						{
							foreach ($captchas as $captcha)
							{
								echo $captcha;
							}
						}
					?>

					<label id="trBotcheck-label" for="trBotcheck">
						<?php echo Lang::txt('MOD_REPORTPROBLEMS_LABEL_BOTCHECK'); ?> <span class="required"><?php echo Lang::txt('MOD_REPORTPROBLEMS_REQUIRED'); ?></span>
						<input type="text" name="botcheck" id="trBotcheck" value="" />
					</label>
				</fieldset>
				<fieldset class="reporting">
					<legend><?php echo Lang::txt('MOD_REPORTPROBLEMS_LEGEND_REPORTING'); ?></legend>

					<label for="trProblem">
						Message: <span class="required"><?php echo Lang::txt('MOD_REPORTPROBLEMS_REQUIRED'); ?></span>
						<textarea name="problem[long]" id="trProblem" rows="10" cols="40"></textarea>
					</label>

					<label for="trUpload">
						<?php echo Lang::txt('MOD_REPORTPROBLEMS_LABEL_ATTACH'); ?>: <span class="optional"><?php echo Lang::txt('MOD_REPORTPROBLEMS_OPTIONAL'); ?></span>
						<input type="file" name="upload[]" id="trUpload" multiple="multiple" data-allowed=".<?php echo str_replace(',', ',.', $this->allowed); ?>" />
						<span class="filetypes">(.<?php echo str_replace(',', ', .', $this->allowed); ?>)</span>
					</label>

					<input type="hidden" name="problem[topic]" value="???" />
					<input type="hidden" name="problem[short]" value="" />
					<input type="hidden" name="problem[referer]" value="<?php echo $this->escape($this->referrer); ?>" />
					<input type="hidden" name="problem[tool]" value="" />
					<input type="hidden" name="problem[os]" value="<?php echo $this->escape($this->os); ?>" />
					<input type="hidden" name="problem[browser]" value="<?php echo $this->escape($this->browser); ?>" />
					<input type="hidden" name="verified" value="<?php echo $this->verified; ?>" />
					<input type="hidden" name="reporter[org]" value="<?php echo (!User::isGuest()) ? $this->escape(User::get('org')) : ''; ?>" />
					<input type="hidden" name="option" value="com_support" />
					<input type="hidden" name="controller" value="tickets" />
					<input type="hidden" name="task" value="save" />
					<input type="hidden" name="no_html" value="1" />

					<?php echo Html::input('token'); ?>
				</fieldset>
				<div class="submit">
					<input type="submit" id="send-form" value="<?php echo Lang::txt('MOD_REPORTPROBLEMS_SUBMIT'); ?>" />
					<a class="btn" id="close-form">Close</a>
				</div>
			</form>
			<div id="trSending">
				<!-- Loading animation container -->
				<div class="rp-loading">
					<!-- We make this div spin -->
					<div class="rp-spinner">
						<!-- Mask of the quarter of circle -->
						<div class="rp-mask">
							<!-- Inner masked circle -->
							<div class="rp-masked-circle"></div>
						</div>
					</div>
				</div>
			</div>
			<div id="trSuccess">
			</div>
		</div><!-- / .col span8 omega -->
	</div><!-- / #help-container -->
</div><!-- / #help-pane -->
