<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$dcls = '';
$lcls = '';
$no_html = $this->get('no_html', 0);
$mine = ($this->item->get('created_by') == User::get('id'));
$voted = $this->item->ballot(User::get('id'))->get('vote', 0);
$this->url = preg_replace('/\#[^\#]*$/', '', $this->url);
if (!strstr($this->url, '?'))
{
	$this->url .= '?';
}
else
{
	$this->url .= '&';
}

$like_title = '';
$cls = (!$mine ? ' tooltips' : '');
if (!User::isGuest()) {
	if (!$mine) {
		$like_title = (!$voted ? 'Click to like' : 'Click to unlike');
	}
} else {
	$like_title = Lang::txt('PLG_PUBLICATIONS_COMMENTS_VOTE_UP_LOGIN');
}

if (!$no_html) { ?>
<p class="comment-voting voting">
<?php } ?>
	<span class="vote-like<?php echo ($voted ? ' chosen' : ''); ?>">
		<?php if ($mine) { // || !$this->params->get('access-vote-comment')) { ?>
			<span class="vote-button <?php echo ($this->item->get('positive', 0) > 0) ? 'like' : 'neutral'; echo $cls; ?>" title="<?php echo $like_title; ?>">
				<?php echo $this->item->get('positive', 0); ?><span> <?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_VOTE_LIKE'); ?></span>
			</span>
		<?php } else { ?>
			<a class="vote-button <?php echo ($this->item->get('positive', 0) > 0) ? 'like' : 'neutral'; echo $cls; ?>" href="<?php echo Route::url($this->url . 'action=commentvote&' . (!$voted ? 'voteup' : 'votedown') . '=' . $this->item->get('id')); ?>" title="<?php echo $like_title; ?>">
				<?php echo $this->item->get('positive', 0); ?><span> <?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_VOTE_LIKE'); ?></span>
			</a>
		<?php } ?>
	</span>
<?php if (!$no_html) { ?>
</p>
<?php } ?>
