<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$canDo = \Components\Collections\Helpers\Permissions::getActions('post');

Toolbar::title(Lang::txt('COM_COLLECTIONS') . ': ' . Lang::txt('COM_COLLECTIONS_POSTS'), 'collection.png');
if ($canDo->get('core.create'))
{
	Toolbar::addNew();
}
if ($canDo->get('core.edit'))
{
	Toolbar::editList();
}
if ($canDo->get('core.delete'))
{
	Toolbar::deleteList();
}

?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}
	// do field validation
	submitform(pressbutton);
}
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<label for="filter_search"><?php echo Lang::txt('JSEARCH_FILTER'); ?>:</label>
		<input type="text" name="search" id="filter_search" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo Lang::txt('COM_COLLECTIONS_FILTER_SEARCH_PLACEHOLDER'); ?>" />

		<input type="submit" value="<?php echo Lang::txt('COM_COLLECTIONS_GO'); ?>" />
		<button type="button" onclick="$('#filter_search').val('');this.form.submit();"><?php echo Lang::txt('JSEARCH_FILTER_CLEAR'); ?></button>

		<input type="hidden" name="collection_id" value="<?php echo $this->filters['collection_id']; ?>" />
	</fieldset>

	<table class="adminlist">
		<thead>
			<?php if ($this->filters['collection_id']) { ?>
				<tr>
					<th colspan="7">
						<?php $collection = \Components\Collections\Models\Orm\Collection::oneOrFail($this->filters['collection_id']); ?>
						(<?php echo $this->escape(stripslashes($collection->get('alias'))); ?>)
						<?php echo $this->escape(stripslashes($collection->get('title'))); ?>
					</th>
				</tr>
			<?php } ?>
			<tr>
				<th scope="col"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $this->rows->count(); ?>);" /></th>
				<th scope="col" class="priority-5"><?php echo Html::grid('sort', 'COM_COLLECTIONS_COL_ID', 'id', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo Html::grid('sort', 'COM_COLLECTIONS_COL_DESCRIPTION', 'description', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-5"><?php echo Html::grid('sort', 'COM_COLLECTIONS_COL_POSTED', 'created', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-3"><?php echo Html::grid('sort', 'COM_COLLECTIONS_COL_POSTEDBY', 'created_by', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-2"><?php echo Html::grid('sort', 'COM_COLLECTIONS_COL_ITEM_ID', 'item_id', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<?php if (!$this->filters['collection_id']) { ?>
					<th scope="col" class="priority-2"><?php echo Html::grid('sort', 'COM_COLLECTIONS_COL_COLLECTION_ID', 'collection_id', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<?php } ?>
				<th scope="col" class="priority-4"><?php echo Html::grid('sort', 'COM_COLLECTIONS_COL_ORIGINAL', 'original', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo ($this->filters['collection_id'] ? '7' : '8'); ?>">
					<?php
					echo $this->rows->pagination;
					?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		$i = 0;
		foreach ($this->rows as $row)
		{
			switch ($row->get('original'))
			{
				case 1:
					$class = 'yes';
					$task = 'unoriginal';
					$alt = Lang::txt('COM_COLLECTIONS_IS_ORIGINAL');
				break;

				case 0:
					$class = 'no';
					$task = 'original';
					$alt = Lang::txt('COM_COLLECTIONS_IS_NOT_ORIGINAL');
				break;
			}

			//if (!($content = $row->description('clean', 75)))
			//{
				$content = Lang::txt('COM_COLLECTIONS_NONE');
			//}
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<input type="checkbox" name="id[]" id="cb<?php echo $i; ?>" value="<?php echo $row->get('id'); ?>" onclick="isChecked(this.checked, this);" />
				</td>
				<td class="priority-5">
					<?php echo $row->get('id'); ?>
				</td>
				<td>
					<?php if ($canDo->get('core.edit')) { ?>
						<a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=edit&id=' . $row->get('id')); ?>">
							<span><?php echo $content; ?></span>
						</a>
					<?php } else { ?>
						<span>
							<span><?php echo $content; ?></span>
						</span>
					<?php } ?>
				</td>
				<td class="priority-5">
					<time datetime="<?php echo $row->get('created'); ?>"><?php echo $row->get('created'); ?></time>
				</td>
				<td class="priority-3">
					<span class="glyph member">
						<?php echo $this->escape($row->creator->get('name', Lang::txt('COM_COLLECTIONS_UNKNOWN'))); ?>
					</span>
				</td>
				<td class="priority-2">
					<?php if ($canDo->get('core.edit')) { ?>
						<a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=items&task=edit&id=' . $row->get('item_id')); ?>">
							<span><?php echo $this->escape($row->get('item_id')); ?></span>
						</a>
					<?php } else { ?>
						<span>
							<span><?php echo $this->escape($row->get('item_id')); ?></span>
						</span>
					<?php } ?>
				</td>
				<?php if (!$this->filters['collection_id']) { ?>
					<td class="priority-2">
						<?php if ($canDo->get('core.edit')) { ?>
							<a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=collections&task=edit&id=' . $row->get('collection_id')); ?>">
								<span><?php echo $this->escape($row->get('collection_id')); ?></span>
							</a>
						<?php } else { ?>
							<span>
								<span><?php echo $this->escape($row->get('collection_id')); ?></span>
							</span>
						<?php } ?>
					</td>
				<?php } ?>
				<td class="priority-4">
					<span class="state <?php echo $class; ?>">
						<span><?php echo $alt; ?></span>
					</span>
				</td>
			</tr>
			<?php
			$i++;
			$k = 1 - $k;
		}
		?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="<?php echo $this->task; ?>" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->filters['sort']); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->filters['sort_Dir']); ?>" />

	<?php echo Html::input('token'); ?>
</form>