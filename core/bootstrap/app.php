<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

define('_HZEXEC_', 1);

if (!defined('PATH_ROOT'))
{
        define('PATH_ROOT', dirname(dirname(__DIR__)));
}

if (!defined('PATH_CORE'))
{
        define('PATH_CORE', PATH_ROOT . '/core');
}

define('DS', DIRECTORY_SEPARATOR);
define('PATH_APP', PATH_ROOT . '/app');
define('JPATH_BASE', PATH_ROOT);
define('JPATH_ROOT', PATH_ROOT);
define('JPATH_SITE', PATH_ROOT);
define('JPATH_CONFIGURATION', PATH_APP . '/config');
define('JPATH_ADMINISTRATOR', PATH_ROOT . '/administrator');
define('JPATH_LIBRARIES', PATH_CORE . '/libraries');
define('JPATH_PLUGINS', PATH_CORE . '/plugins');
define('JPATH_INSTALLATION', PATH_ROOT . '/installation');
define('JPATH_THEMES', PATH_APP . '/templates');
define('JPATH_CACHE', PATH_APP . '/cache');
define('JPATH_MANIFESTS', PATH_CORE . '/manifests');
define('JPATH_API', PATH_ROOT . '/api');

define('HVERSION', '2.2.30');

error_reporting(-1);
ini_set('display_errors', 0);

date_default_timezone_set('UTC');

mb_internal_encoding('UTF-8');

// Work around for issues with SCRIPT_NAME and PHP_SELF set incorrectly by php-fpm
// GH-12996 https://github.com/php/php-src/issues/12996 fixed in 8.2.16
// GH-10869 https://github.com/php/php-src/issues/10869 fixed in 8.1.18
// Don't overrite ORIG_SCRIPT_NAME if already set (e.g. between above versions)

if (PHP_VERSION_ID < 80216 && strpos($_SERVER['PATH_INFO'], '%') !== false)
{
	if (!isset($_SERVER['ORIG_SCRIPT_NAME']))
	{
		$_SERVER['ORIG_SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];
	}

	$_SERVER['SCRIPT_NAME'] = str_replace(rawurldecode($_SERVER['PATH_INFO']), '', $_SERVER['SCRIPT_NAME']);
	$_SERVER['PHP_SELF'] = str_replace(rawurldecode($_SERVER['PATH_INFO']), '', $_SERVER['PHP_SELF']);
}

Hubzero\Base\ClassLoader::addDirectories(array(	PATH_APP,	PATH_CORE));
Hubzero\Base\ClassLoader::register();

require PATH_CORE . '/libraries/Hubzero/Base/helpers.php';




