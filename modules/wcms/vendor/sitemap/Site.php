<?php
/* SVN FILE: $Id$ */
/**
 * contains the sitemap site class
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
 * sitemap site class
 * 
 * @package netzcraftwerk
 */
class Sitemap_Site
{

	/**
	 * the location url
	 *
	 * @var string
	 */
	protected $loc = '';
	
	/**
	 * the last modification
	 *
	 * @var string
	 */
	protected $lastmod = '';
	
	/**
	 * the changefrequenz
	 *
	 * @var string
	 */
	protected $changefreq = '';
	
	/**
	 * the priority
	 *
	 * @var float
	 */
	protected $priority = 0.0;
	
	/**
	 * set the location
	 *
	 * @param string $loc
	 */
	public function setLoc ($loc)
	{
		try {
			if (false === is_string($loc)) {
				throw new Exception('$loc must be of type string');
			}
			$this->loc = $loc;
		} catch (Exception $e) {
			print $e->getMessage();
		}
	}
	
	/**
	 * set the last modification
	 *
	 * @param string $lastmod
	 */
	public function setLastmod ($lastmod)
	{
		try {
			if (false === is_string($lastmod)) {
				throw new Exception('$lastmod must be of type string');
			}
			$this->lastmod = $lastmod;
		} catch (Exception $e) {
			print $e->getMessage();
		}
	}
	
	/**
	 * set the changefrequenz
	 *
	 * @param string $changefreq
	 */
	public function setChangefreq ($changefreq)
	{
		try {
			if (false === is_string($changefreq)) {
				throw new Exception('$changefreq must be of type string');
			}
			$this->changefreq = $changefreq;
		} catch (Exception $e) {
			print $e->getMessage();
		}
	}
	
	/**
	 * set the priority
	 *
	 * @param float $priority
	 */
	public function setPriority ($priority)
	{
		try {
			if (false === is_float($priority)) {
				throw new Exception('$priority must be of type float');
			}
			$this->priority = $priority;
		} catch (Exception $e) {
			print $e->getMessage();
		}
	}

	/**
	 * @return string
	 */
	public function getChangefreq ()
	{
		return $this->changefreq;
	}
	
	/**
	 * @return string
	 */
	public function getLastmod ()
	{
		return $this->lastmod;
	}
	
	/**
	 * @return string
	 */
	public function getLoc ()
	{
		return $this->loc;
	}
	
	/**
	 * @return float
	 */
	public function getPriority ()
	{
		return $this->priority;
	}

}
?>
