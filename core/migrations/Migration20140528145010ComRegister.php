<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for merging com_register data into com_members
 * and removing com_register component entry
 **/
class Migration20140528145010ComRegister extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$rparams = $this->getParams('com_register');

		if (!empty($rparams))
		{
			$values = $rparams->toArray();

			$this->db->setQuery("SELECT * FROM `#__extensions` WHERE `type`='component' AND `element`='com_members' LIMIT 1");
			if ($data = $this->db->loadAssoc())
			{
				$mparams = new \Hubzero\Config\Registry($data['params']);
				foreach ($values as $key => $value)
				{
					$mparams->set($key, $value);
				}

				$data['params'] = $mparams->toString();

				$vals = array();
				foreach ($data as $key => $val)
				{
					if ($key == 'extension_id')
					{
						continue;
					}
					$vals[] = "`$key`=" . $this->db->quote($val);
				}
				$vals = implode(', ', $vals);

				$query = "UPDATE `#__extensions` SET $vals WHERE `extension_id`=" . $this->db->quote($data['extension_id']);

				$this->db->setQuery($query);
				$this->db->query();
			}
		}

		// Get the default menu identifier
		//$this->db->setQuery("SELECT menutype FROM `#__menu` WHERE home='1' LIMIT 1;");
		//$menutype = $this->db->loadResult();
		$this->db->setQuery("SELECT extension_id FROM `#__extensions` WHERE `type`='component' AND `element`='com_members'");
		$component = $this->db->loadResult();

		// Check if there's a menu item for com_register
		$this->db->setQuery("SELECT id FROM `#__menu` WHERE `alias`='register' AND `path`='register'"); //" AND menutype=" . $this->db->quote($menutype));
		if ($id = $this->db->loadResult())
		{
			// There is!
			// So, just update its link
			$this->db->setQuery("UPDATE `#__menu` SET `link`='index.php?option=com_members&view=register&layout=create', `component_id`=" . $this->db->quote($component) . " WHERE `id`=" . $this->db->quote($id));
			$this->db->query();
		}
		else
		{
			$this->db->setQuery("SELECT menutype FROM `#__menu` WHERE `home`='1' LIMIT 1;");
			$menutype = $this->db->loadResult();

			$this->db->setQuery("SELECT ordering FROM `#__menu` WHERE `menutype`=" . $this->db->quote($menutype) . " ORDER BY ordering DESC LIMIT 1");
			$ordering = intval($this->db->loadResult());
			$ordering++;

			// No menu item for com_register so we need to create one for the new com_members controler
			$query  = "INSERT INTO `#__menu` (`id`, `menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `ordering`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`)\n";
			$query .= "VALUES ('', '$menutype', 'Register', 'register', '', 'register', 'index.php?option=com_members&view=register&layout=create', 'component', 1, 1, 1, $component, $ordering, 0, '0000-00-00 00:00:00', 0, 1, '', 0, '', 0, 0, 0, '*', 0);";
			$this->db->setQuery($query);
			$this->db->query();

			require_once PATH_CORE . '/components/com_menus/models/menu.php';
			$table = Components\Menus\Models\Menu::blank();
			$table->rebuild();
		}

		$this->deleteComponentEntry('register');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->addComponentEntry('register');

		$rparams = $this->getParams('com_members');
		$values = $rparams->toArray();

		$this->db->setQuery("SELECT * FROM `#__extensions` WHERE `type`='component' AND `element`='com_register' LIMIT 1");
		if ($data = $this->db->loadAssoc())
		{
			$mparams = new \Hubzero\Config\Registry($data['params']);
			foreach ($values as $key => $value)
			{
				$mparams->set($key, $value);
			}

			$data['params'] = $mparams->toString();

			$vals = array();
			foreach ($data as $key => $val)
			{
				if ($key == 'extension_id')
				{
					continue;
				}
				$vals[] = "`$key`=" . $this->db->quote($val);
			}
			$vals = implode(', ', $vals);

			$query = "UPDATE `#__extensions` SET $vals WHERE `extension_id`=" . $this->db->quote($data['extension_id']);

			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}
