<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Transfer class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Matthias Deinbeck <m.deinbeck@netzcraftwerk.com>
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
 * Transfer class.
 *
 * @package netzcraftwerk
 */
class Contacts_Transfer extends Ncw_Model
{
	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
		'name' => array('rules' => array('NotEmpty'), 'required' => true),
		'separator' => array('rules' => array('NotEmpty'), 'requierd' => true),
		'firstname' => array('rules' => array('Integer')),
		'lastname' => array('rules' => array('Integer')),
		'gender' => array('rules' => array('Integer')),
		'company' => array('rules' => array('Integer')),
		'street' => array('rules' => array('Integer')),
		'postcode' => array('rules' => array('Integer')),
		'city' => array('rules' => array('Integer')),
		'state' => array('rules' => array('Integer')),
		'country' => array('rules' => array('Integer')),
		'telephone' => array('rules' => array('Integer')),
		'mobile' => array('rules' => array('Integer')),
		'email' => array('rules' => array('Integer'))
	);
}
?>