<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Setting class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
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
 * Setting class.
 *
 * @package netzcraftwerk
 */
class Wcms_Setting extends Ncw_Model
{

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
	   "rewrite" => array("rules" => array("NotEmpty", "MaxLength" => 255), "required" => true),
	   "language_id" => array("rules" => array('Integer'), "required" => true)
	);
}
?>