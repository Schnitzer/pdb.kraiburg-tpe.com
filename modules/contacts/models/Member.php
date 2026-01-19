<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Member class.
 *
 * PHP Version 5
 * Copyright (c) 2010 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright		Copyright 2007-2010, Netzcraftwerk UG (haftungsbeschränkt)
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * Contacts_Member class.
 *
 * @package netzcraftwerk
 */
class Contacts_Member extends Ncw_Model
{

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
        "contact_id" => array("rules" => array('Integer'), "required" => true),
        "person_contact_id" => array("rules" => array('Integer'), "required" => true),
        'description' => array('rules' => array('MaxLength' => 255)),
	);
}
?>
