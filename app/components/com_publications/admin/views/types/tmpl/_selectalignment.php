<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Components\Tags\Models\FocusArea;

// No direct access
defined('_HZEXEC_') or die();

$fas = FocusArea::fromObject($this->row->id);
$fas_ids = $fas->copy()->rows()->fieldsByKey('id');
$fas_all = FocusArea::roots();
$depth = [];
foreach($fas_all as $fa) {
    $depth[$fa->id] = $fa->maxdepth();
}
if ($fas != null)
{
	$html = '<ul id="alignment-list" style="position:relative;overflow:auto;">'."\n";
	foreach ($fas as $fa)
	{
		$label = $fa->label;
        $id = $fa->id;

		$html .= "\t".'<li class="pick reorder" data-id="' . $id . '" data-depth="' . $depth[$id] . '">'
			. $label . ' (' . $id . ')';
        $html .= ' <input type="hidden" name="curation[blocks][' . $this->blockId . '][elements][' . $this->elementId . '][params][typeParams][view][alignment][' . $id . '][faid]" value="' . $id . '">';
		$html .= ' <span class="editalignment" onclick="HUB.PublicationsCuration.deleteAlignment(this)" > ' . Lang::txt('COM_PUBLICATIONS_DELETE') . '</span> ';
        $html .= ' <span class="editalignment">';
        $html .= '   <label for="multiple-' . $id . '">Radio depth</label>';
        $html .= '   <input class="numeric-input" type="number" id="multiple-' . $id . '" name="curation[blocks][' . $this->blockId . '][elements][' . $this->elementId . '][params][typeParams][view][alignment][' . $id . '][multiple_depth]" value="' . $fa->multiple_depth . '" min="0" max="' . $depth[$id] . '">';
        $html .= ' </span>';
        $html .= ' <span class="editalignment">';
        $html .= '   <label for="mandatory-' . $id . '">Mandatory depth</label>';
        $html .= '   <input class="numeric-input" type="number" id="mandatory-' . $id . '" name="curation[blocks][' . $this->blockId . '][elements][' . $this->elementId . '][params][typeParams][view][alignment][' . $id . '][mandatory_depth]" value="' . $fa->mandatory_depth . '" min="0" max="' . $depth[$id] . '">';
        $html .= ' </span>';
		$html .= '</li>' . "\n";
	}
	$html.= '</ul>';
}
else
{
	$html.= '<p class="notice">' . Lang::txt('COM_PUBLICATIONS_NO_AUTHORS') . '</p>';
}
?>

<fieldset class="adminform">
    <legend><span>Alignment</span></legend>
    <select id="select-alignment" class="sidenote" style="width:unset;margin-bottom:10px;" onchange="HUB.PublicationsCuration.addAlignment(this)">
        <?php foreach($fas_all as $fa) { 
            $selected = in_array($fa->id, $fas_ids); ?>
            <option value="<?php echo $fa->id; ?>" data-depth="<?php echo $depth[$fa->id]; ?>" <?php echo ($selected ? 'disabled selected' : '')?>><?php echo $fa->label . ' (' . $fa->id . ')';?></option>
        <?php } ?>
    </select>
    <label for="select-alignment" class="sidenote add">Add Alignment</label>
    <fieldset style="clear:both;">
		<div class="input-wrap" id="master-type-alignment">
            <?php echo $html; ?>
        </div>
    </fieldset>
</fieldset>