<?php
/* SVN FILE: $Id$ */
/**
 * contains the Modules model class
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
 * Modules model.
 *
 * @package netzcraftwerk
 */
class Core_Modules extends Ncw_Model
{

	/**
	 * Validation array
	 *
	 * @var array
	 */
	public $validation = array(
		"version" => array("rules" => array("NotEmpty", "MaxLength" => 7), "required" => true),
        "name" => array("rules" => array("NotEmpty", "MaxLength" => 100), "required" => true),
        "permission_name" => array("rules" => array("NotEmpty", "MaxLength" => 100), "required" => true),
        "folder_name" => array("rules" => array("NotEmpty", "MaxLength" => 100), "required" => true),
        "assets_folder_name" => array("rules" => array("MaxLength" => 100)),
        "requirements" => array("rules" => array("MaxLength" => 100)),
        "url" => array("rules" => array("NotEmpty", "MaxLength" => 255), "required" => true)
	);
}
?>
