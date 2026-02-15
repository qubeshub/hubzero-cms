<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$description = $this->publication->describe('parsed');

// Consider moving this to controller
if (($this->publication->params->get('show_citation'))) {
	if ($this->publication->params->get('show_citation') == 1
	|| $this->publication->params->get('show_citation') == 2)
	{
		// Build our citation object
		$cite = new stdClass();
		$cite->title     = $this->publication->title;
		$cite->year      = $this->publication->published_up && $this->publication->published_up != '0000-00-00 00:00:00' ? Date::of($this->publication->published_up)->toLocal('Y') : Date::of('now')->toLocal('Y');

		$cite->location  = '';
		$cite->date      = '';

		$cite->doi       = $this->publication->doi ? $this->publication->doi : '';
		$cite->url       = $cite->doi ? trim($this->config->get('doi_resolve', 'https://doi.org/'), '/') . '/' . $cite->doi : null;
		$cite->type      = '';
		$cite->pages     = '';
		$cite->author    = $this->publication->getUnlinkedContributors();
		$cite->publisher = $this->config->get('doi_publisher', '');
		if ($this->publication->groupOwner())
		{
			$cite->organization = $this->publication->groupOwner('description');
			$cite->location = '/groups/' . $this->publication->groupOwner('cn');
		}
		if ($this->publication->version_label > 1)
		{
			$cite->version = $this->publication->version_label;
		}
	}
	else
	{
		$cite = null;
	}
    // Using \Templates\Bmc\Publications\Helpers\Html was throwing an error
    $citeinstruct  = \Components\Publications\Helpers\Html::citation($cite, $this->publication, '');
}

// Show version notes
$notes = null;
if (($this->publication->params->get('show_notes')) && $this->publication->get('release_notes'))
{
	$notes = $this->publication->notes('parsed');
}
?>

<h4><?php echo Lang::txt('COM_PUBLICATIONS_DESCRIPTION'); ?></h4>
<?php echo $description; ?>

<?php if ($notes) { ?>
<h4><?php echo Lang::txt('COM_PUBLICATIONS_NOTES'); ?></h4>
<div class="pub-content">
    <?php echo $notes; ?>
</div>
<?php } ?>

<h4 id="citethis"><?php echo Lang::txt('COM_PUBLICATIONS_CITE_THIS'); ?></h4>
<div class="pub-content">
    <?php echo $citeinstruct; ?>
</div>