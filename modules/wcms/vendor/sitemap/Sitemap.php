<?php
/* SVN FILE: $Id$ */
/**
 * contains the sitemap class
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
 * Include the Site class.
 */
require_once dirname(__FILE__) . DS . 'Site.php';
/**
 * sitemap class
 * 
 * @package netzcraftwerk
 */
class Sitemap_Sitemap
{

	/**
	 * Array which contains the added sites
	 *
	 * @var Array
	 */
	protected $sites = array();
	
	/**
	 * The site domain
	 *
	 * @var string
	 */
	protected $site_domain = '';
		
	/**
	 * create the sitemap xml
	 *
	 * @return string the xml code
	 */
	public function create ()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		foreach ($this->sites as $site) {
			$xml .= '<url><loc>' . $site->getLoc() . '</loc>';
			
			$lastmod = $site->getLastmod();
			if (false === empty($lastmod)) {
                $xml .= '<lastmod>' . $lastmod . '</lastmod>';
			}

			$changefreq = $site->getChangefreq();
            if (false === empty($changefreq)) {
                $xml .= '<changefreq>' . $changefreq . '</changefreq>';
            }
            
			$priority = $site->getPriority();
            if (false === empty($priority)) {
                $xml .= '<priority>' . $priority . '</priority>';
            }
            
			$xml .= '</url>';
		}
		$xml .= '</urlset>';
		return $xml;
	}
	
	/**
	 * add site 
	 *
	 * @param Sitemap_Site $site
	 */
	public function addSite (Sitemap_Site $site)
	{
		$this->sites[] = $site;
	}
}
?>
