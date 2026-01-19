<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Componenttemplate class.
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
 * Componenttemplate class.
 *
 * @package netzcraftwerk
 */
class Wcms_Componenttemplate extends Ncw_Model
{

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
        "name" => array("rules" => array("NotEmpty", "MaxLength" => 100), "required" => true),
        "in_list" => array("rules" => array("Boolean")),
	);
}
?>
