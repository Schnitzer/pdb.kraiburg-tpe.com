<?php
/* SVN FILE: $Id$ */
/**
 * contains the sitemap ping service composite class
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
 * Include the ping service interface.
 */
require_once dirname(__FILE__) . DS . 'PingService.php';
/**
 * sitemap ping service composite class
 * 
 * @package netzcraftwerk
 */
class Sitemap_PingServiceComposite extends Sitemap_PingService 
{

	/**
	 * The Ping Services
	 *
	 * @var Array
	 */
	protected $ping_services = array();
	
	/**
	 * adds a pingservice to array $ping_services
	 *
	 * @param Sitemap_PingService $ping_service
	 */
	public function addPingService (Sitemap_PingService $ping_service)
	{
		$this->ping_services[] = $ping_service;
	}
	
	/**
	 * calls the ping-method of each pingservice 
	 *
	 */
	public function ping ()
	{
		foreach ($this->ping_services as $ping_service) {
			$ping_service->setSitemapUrl($this->sitemap_url);
			$ping_service->ping();
		}
	}
}
?>
