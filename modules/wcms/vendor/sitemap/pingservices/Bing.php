<?php
/* SVN FILE: $Id$ */
/**
 * contains the bing sitemap ping service class
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
 * Include the HTTP_Request2 class.
 */
require_once 'ncw/vendor/pear/HTTP/Request2.php';
/**
 * bing sitemap ping service
 *
 * @package netzcraftwerk
 */
class Sitemap_Pingservices_Bing extends Sitemap_PingService
{

	/**
	 * Ping Bing.
	 *
	 */
	public function ping ()
	{
		try {
			$request = new HTTP_Request2();
			$request->setUrl('http://www.bing.com/webmaster/ping.aspx?siteMap=' . urlencode($this->sitemap_url));
			$response = $request->send();
			if ($response->getStatus() !== 200) {
				throw new Exception('Bing ping failed');
			}
		} catch (Exception $e) {
			$e->getMessage();
		}
	}
}
?>