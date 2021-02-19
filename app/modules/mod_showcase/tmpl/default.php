<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
?>

<?php 
$boards = array();
foreach ($this->items as $item) {
	if ($item["type"] === "static") {
		require $this->getLayoutPath('_billboards');
	} elseif ($item["type"] === "dynamic") {
		switch ($item["content"])
		{
			case 'publications':
				$item_pubs = $this->_getPublications($item);
				require $this->getLayoutPath('_publications');
			break;

			case 'groups':
				$item_groups = $this->_getGroups($item);
				require $this->getLayoutPath('_groups');
			break;

			case 'partners':
				$item_partners = $this->_getPartners($item);
				require $this->getLayoutPath('_partners');
			break;

			case 'blogs':
				$item_blogs = $this->_getBlogs($item);
				require $this->getLayoutPath('_blogs');
			break;
			
			case 'newsletters':
				$item_newsletters = $this->_getNewsletters($item);
				require $this->getLayoutPath('_newsletters');
			break;
			
			case 'fmns':
				$item_fmns = $this->_getFmns($item);
				require $this->getLayoutPath('_fmns');
			break;

			default:
				echo 'Showcase Module Error: Unknown dynamic type "' . $item["content"] . '".  Possible values include "publications", "groups", "partners", "blogs", "newsletters", or "fmns".';
			break;
		}
	} else {
		echo 'Showcase Module Error: Unknown type "' . $item["type"] . '".  Possible values include "static" or "dynamic".';
	}
}
?>
