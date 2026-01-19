<?php
/* SVN FILE: $Id$ */
/**
 * contains the Language class
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschraenkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcrafrtwerk.com>
 * @copyright		Copyright 2007-2009, Netzcraftwerk UG (haftungsbeschraenkt)
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * Language model.
 *
 * @package netzcraftwerk
 */
class Core_Language extends Ncw_Model
{

	/**
	 * Validation array
	 *
	 * @var array
	 */
	public $validation = array(
        "name" => array("rules" => array("NotEmpty", "Letter", "MaxLength" => 25), "required" => true),
        "shortcut" => array("rules" => array("ExactLength" => 5, "RegExp" => '/^[a-z]{2}[_]{1}[A-Z]{2}$/'), "required" => true)
	);
}
?>
