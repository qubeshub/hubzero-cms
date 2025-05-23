<?php

/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2024 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

define('PATH_ROOT', isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] ? $_SERVER['DOCUMENT_ROOT'] : __DIR__);
define('PATH_CORE', isset($_ENV['PATH_CORE']) && $_ENV['PATH_CORE'] ? $_ENV['PATH_CORE'] : PATH_ROOT . '/core');

require PATH_CORE . '/vendor/autoload.php';

(new Hubzero\Base\Application())->run();

