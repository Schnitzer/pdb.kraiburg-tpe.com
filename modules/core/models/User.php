<?php
/* SVN FILE: $Id$ */
/**
 * contains the User class
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
 * User model.
 *
 * @package netzcraftwerk
 */
class Core_User extends Ncw_Model
{

	/**
	 * Has many...
	 *
	 * @var array
	 */
	public $has_many = array("UsergroupUser");
	
	/**
	 * Belongs to...
	 *
	 * @var array
	 */
	public $belongs_to = array("Language");	

	/**
	 * Validation array
	 *
	 * @var array
	 */
    public $validation = array(
        "name" => array("rules" => array("Username", "Unique"), "required" => true),
        "password" => array("rules" => array("Password"), "required" => true),
        'activated' => array('rules' => array('Boolean')),
				'email' => array('rules' => array('MaxLength' => 100, 'Email')),
        'entry_point' => array('rules' => array('Url', 'MaxLength' => 255)),
        "client_ip" => array("rules" => array("Ip")),
        "client_browser" => array("rules" => array("NotEmpty")),
        "language_id" => array("rules" => array("Integer"), "required" => true),
        "contact_id" => array("rules" => array("Integer")),
    );
}
?>
