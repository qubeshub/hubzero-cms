<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

// Get block/element properties
$props    = $this->pub->curation('blocks', $this->master->blockId, 'props') . '-' . $this->elementId;
$complete = $this->pub->curation('blocks', $this->master->blockId, 'elementStatus', $this->elementId);

// Element required status depends on the requirement of each focus area
$required = count(array_filter($this->fas->copy()->rows()->fieldsByKey('mandatory_depth'),
                  function($required) {
                    return $required;
                  }));

$elName   = 'tagsPick';

// Get side text
$aboutText = $this->manifest->about ? $this->manifest->about : null;
if ($this->pub->_project->isProvisioned() && isset($this->manifest->aboutProv))
{
	$aboutText = $this->manifest->aboutProv;
}

// Get curator status
$curatorStatus = $this->pub->_curationModel->getCurationStatus($this->pub, $this->master->blockId, $this->elementId, 'author');

$complete = $curatorStatus->status == 1 ? $curatorStatus->status : $complete;
$updated  = $curatorStatus->updated && (($curatorStatus->status == 3 && !$complete)
		|| $curatorStatus->status == 1 || $curatorStatus->status == 0) ? true : false;

?>

<div id="<?php echo $elName; ?>" class="blockelement <?php
	echo $required ? ' el-required' : ' el-optional';
    echo $complete ? ' el-complete' : ' el-incomplete';
    echo $curatorStatus->status == 1 ? ' el-passed' : '';
    echo $curatorStatus->status == 0 ? ' el-failed' : '';
    echo $curatorStatus->updated && $curatorStatus->status != 2 ? ' el-updated' : '';
    ?>">
    <div class="element_editing">
		<div class="pane-wrapper">
			<span class="checker">&nbsp;</span>
			<label id="<?php echo $elName; ?>-lbl">
                <?php if ($required) { ?><span class="required"><?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_REQUIRED'); ?></span>
				<?php } else { ?><span class="optional"><?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_OPTIONAL'); ?></span><?php } ?>
				<?php echo $this->manifest->label; ?>
			</label>
		  <?php echo $this->pub->_curationModel->drawCurationNotice($curatorStatus, $props, 'author', $elName); ?>
          <?php echo strip_tags($aboutText); ?>

			<fieldset class="focus-areas">
				<?php
					if (count($this->fas) > 0):
						foreach ($this->fas as $fa):
				?>
							<fieldset value="<?php echo ($fa->mandatory_depth ? $fa->mandatory_depth : 0) ?>">
								<legend>
									<span class="tooltips" title="<?php echo $fa->about; ?>"><?php echo $fa->label; ?></span>
									<?php echo ($fa->mandatory_depth ? '<span class="required">required</span>' : '<span class="optional">optional</span>'); ?>
								</legend>
								<?php echo $fa->render('select', array('selected' => $this->selected, 'multiple_depth' => $fa->multiple_depth)); ?>
							</fieldset>
						<?php
						endforeach;
					endif;
				?>
			</fieldset>
        </div>
	</div>
</div>