<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die;

/**
 * Pagenavigation plugin class.
 */
class plgContentPagenavigation extends \Hubzero\Plugin\Plugin
{
	/**
	 * Prepare the content for display
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $row      The article object.  Note $article->text is also available
	 * @param   object   $params   The article params
	 * @param   integer  $page     The 'page' number
	 * @return  void
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{
		$view  = Request::getCmd('view');
		$print = Request::getBool('print');

		if ($print)
		{
			return false;
		}

		if ($params->get('show_item_navigation') && ($context == 'com_content.article') && ($view == 'article'))
		{
			$html = '';
			$db   = App::get('db');

			$nullDate = $db->getNullDate();

			$date = Date::of('now');
			$now  = $date->toSql();

			$uid = $row->id;
			$option = 'com_content';
			$canPublish = User::authorise('core.edit.state', $option . '.article.' . $row->id);

			// The following is needed as different menu items types utilise a different param to control ordering.
			// For Blogs the `orderby_sec` param is the order controlling param.
			// For Table and List views it is the `orderby` param.
			$params_list = $params->toArray();
			if (array_key_exists('orderby_sec', $params_list))
			{
				$order_method = $params->get('orderby_sec', '');
			}
			else
			{
				$order_method = $params->get('orderby', '');
			}
			// Additional check for invalid sort ordering.
			if ($order_method == 'front')
			{
				$order_method = '';
			}

			// Determine sort order.
			switch ($order_method)
			{
				case 'date' :
					$orderby = 'a.created';
					break;
				case 'rdate' :
					$orderby = 'a.created DESC';
					break;
				case 'alpha' :
					$orderby = 'a.title';
					break;
				case 'ralpha' :
					$orderby = 'a.title DESC';
					break;
				case 'hits' :
					$orderby = 'a.hits';
					break;
				case 'rhits' :
					$orderby = 'a.hits DESC';
					break;
				case 'order' :
					$orderby = 'a.ordering';
					break;
				case 'author' :
					$orderby = 'a.created_by_alias, u.name';
					break;
				case 'rauthor' :
					$orderby = 'a.created_by_alias DESC, u.name DESC';
					break;
				case 'front' :
					$orderby = 'f.ordering';
					break;
				default :
					$orderby = 'a.ordering';
					break;
			}

			$xwhere = ' AND (a.state = 1 OR a.state = -1)' .
				' AND (publish_up IS NULL OR publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($now) . ')' .
				' AND (publish_down IS NULL OR publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($now) . ')';

			// Array of articles in same category correctly ordered.
			$query = $db->getQuery();
			//sqlsrv changes
			$case_when = ' CASE WHEN ';
			$case_when .= $query->charLength('a.alias');
			$case_when .= ' THEN ';
			$a_id = $query->castAsChar('a.id');
			$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
			$case_when .= ' ELSE ';
			$case_when .= $a_id.' END as slug';

			$case_when1 = ' CASE WHEN ';
			$case_when1 .= $query->charLength('cc.alias');
			$case_when1 .= ' THEN ';
			$c_id = $query->castAsChar('cc.id');
			$case_when1 .= $query->concatenate(array($c_id, 'cc.alias'), ':');
			$case_when1 .= ' ELSE ';
			$case_when1 .= $c_id.' END as catslug';
			$query->select('a.id, a.language,' . $case_when . ',' . $case_when1);
			$query->from('#__content AS a');
			$query->leftJoin('#__categories AS cc ON cc.id = a.catid');
			// TODO: Fix where clause
			$query->where('a.catid = ' . (int)$row->catid . ' AND a.state = '. (int)$row->state . ($canPublish ? '' : ' AND a.access = ' .(int)$row->access) . $xwhere);
			$query->order($orderby);
			if (App::isSite() && App::get('language.filter'))
			{
				$query->where('a.language', 'in', '(' . $db->quote(Lang::getTag()) . ',' . $db->quote('*') . ')');
			}

			$db->setQuery($query);
			$list = $db->loadObjectList('id');

			// This check needed if incorrect Itemid is given resulting in an incorrect result.
			if (!is_array($list))
			{
				$list = array();
			}

			reset($list);

			// Location of current content item in array list.
			$location = array_search($uid, array_keys($list));

			$rows = array_values($list);

			$row->prev = null;
			$row->next = null;

			if ($location -1 >= 0)
			{
				// The previous content item cannot be in the array position -1.
				$row->prev = $rows[$location -1];
			}

			if (($location +1) < count($rows))
			{
				// The next content item cannot be in an array position greater than the number of array postions.
				$row->next = $rows[$location +1];
			}

			$pnSpace = '';
			if (Lang::txt('JGLOBAL_LT') || Lang::txt('JGLOBAL_GT'))
			{
				$pnSpace = ' ';
			}

			if ($row->prev)
			{
				$row->prev = Route::url(Components\Content\Site\Helpers\Route::getArticleRoute($row->prev->slug, $row->prev->catslug, $row->prev->language));
			}
			else
			{
				$row->prev = '';
			}

			if ($row->next)
			{
				$row->next = Route::url(Components\Content\Site\Helpers\Route::getArticleRoute($row->next->slug, $row->next->catslug, $row->next->language));
			}
			else
			{
				$row->next = '';
			}

			// Output.
			if ($row->prev || $row->next)
			{
				$html = '<ul class="pagenav">';
				if ($row->prev)
				{
					$html .= '
						<li class="pagenav-prev">
							<a href="' . $row->prev . '" rel="prev">' . Lang::txt('JGLOBAL_LT') . $pnSpace . Lang::txt('JPREV') . '</a>
						</li>';
				}

				if ($row->next)
				{
					$html .= '
						<li class="pagenav-next">
							<a href="' . $row->next . '" rel="next">' . Lang::txt('JNEXT') . $pnSpace . Lang::txt('JGLOBAL_GT') . '</a>
						</li>';
				}
				$html .= '</ul>';

				$row->pagination = $html;
				$row->paginationposition = $this->params->get('position', 1);
				// This will default to the 1.5 and 1.6-1.7 behavior.
				$row->paginationrelative = $this->params->get('relative', 0);
			}
		}
	}
}
