<?php
/**
 * Contains the Cache helper class
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  1997-2008 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version    SVN: $Id$
 * @link       http://www.netzcraftwerk.com
 * @since      File available since Release 0.1
 * @modby      $LastChangedBy$
 * @lastmod    $LastChangedDate$
 */
/**
 * Include the Cache class.
 */
require_once 'ncw/vendor/pear/Cache.php';
/**
 * The Cache Helper
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Helpers_Cache extends Ncw_Helper
{

    /**
     * The cache object
     *
     * @var Cache
     */
    public $object = null;

	/**
	 * Construct
	 *
	 */
	public function __construct ()
	{
		$cache_dir = Ncw_Configure::read('Cache.dir');
		// Ensure cache_dir is set and not empty
		if (empty($cache_dir)) {
			$cache_dir = './tmp/cache';
		}
		// Convert relative paths to absolute based on document root
		if ($cache_dir[0] !== '/') {
			// Remove leading ./ if present
			$cache_dir = ltrim($cache_dir, './');
			// Use dirname of index.php (project root) as base
			$project_root = dirname(dirname(dirname(__FILE__)));
			$cache_dir = $project_root . DS . $cache_dir;
		}
		
		$this->object = new Cache(
            'file',
            array(
                'cache_dir' => $cache_dir . DS . 'views',
                'filename_prefix' => 'cache_'
            )
        );
	}

    /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    public function startup (Ncw_View &$view)
    {
        $view->cache = $this->object;
    }
}
?>
