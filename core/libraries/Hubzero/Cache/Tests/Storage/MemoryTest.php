<?php
/**
 * @package    framework
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Hubzero\Cache\Tests\Storage;

/**
 * MemoryTest
 */
class MemoryTest extends AbstractCache
{
	/**
	 * Test setup
	 *
	 * @return  void
	 */
	public function setUp(): void
	{
		parent::setup();

		$this->cache->setDefaultDriver('memory');
	}
}
