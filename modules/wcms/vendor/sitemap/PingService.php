<?php
/* SVN FILE: $Id$ */
/**
 * contains the sitemap ping service class
 * 
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 * 
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is 
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <th@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk GmbH
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * sitemap ping service class
 * 
 * @package netzcraftwerk
 */
abstract class Sitemap_PingService
{
	
	/**
	 * The sitemap url
	 *
	 * @var string
	 */
	protected $sitemap_url = '';
	
	/**
	 * Sets the sitemap url attribute
	 *
	 * @param string $sitemap_url
	 */
	public function setSitemapUrl ($sitemap_url = '')
	{
		try {
			if (false === is_string($sitemap_url)) {
				throw new Exception('$sitemap_url must be of type string');
			}
			$this->sitemap_url = $sitemap_url;
		} catch (Exception $e) {
			print $e->getMessage();
		}
	}
	
	/**
	 * abstract ping method
	 *
	 */
	abstract public function ping ();
}

?>