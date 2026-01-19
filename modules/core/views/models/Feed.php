<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Core_Feed model class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright		Copyright 2007-2009, Netzcraftwerk UG (haftungsbeschränkt)
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * Core_Feed model.
 *
 * @package netzcraftwerk
 */
class Core_Feed extends Ncw_Model
{

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
		'name' => array("rules" => array('NotEmpty', 'MaxLength' => 100), "required" => true),
		'title' => array("rules" => array('NotEmpty', 'MaxLength' => 100), "required" => true),
		'language' => array("rules" => array('MaxLength' => 5)),
		'pub_date' => array("rules" => array('Integer')),
		'generate' => array("rules" => array('MaxLength' => 100)),
		'managing_editor' => array("rules" => array('MaxLength' => 100)),
		'web_master' => array("rules" => array('MaxLength' => 100)),
		'module' => array("rules" => array('MaxLength' => 100)),
		'controller' => array("rules" => array('MaxLength' => 100)),
		'params' => array("rules" => array('MaxLength' => 255)),
	);
}
?>
