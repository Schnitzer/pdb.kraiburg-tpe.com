<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Bank model class.
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
 * Bank model.
 *
 * @package netzcraftwerk
 */
class Contacts_Bank extends Ncw_Model
{

    /**
     * Has many..
     *
     * @var array
     */
	public $belongs_to = array('Contact');

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
		'contact_id' => array('rules' => array('Integer'), 'required' => true),
		'location' => array('rules' => array('InList' => array('work', 'home', 'other')), 'required' => true),
		'name' => array('rules' => array('NotEmpty', 'MaxLength' => 100), 'required' => true),
	  	'bankcode' => array('rules' => array('NotEmpty', 'MaxLength' => 100), 'required' => true),
	  	'accountnumber' => array('rules' => array('NotEmpty', 'MaxLength' => 100), 'required' => true),
	  	'iban' => array('rules' => array('MaxLength' => 255)),
	  	'bic' => array('rules' => array('MaxLength' => 255)),
	  	'first' => array('rules' => array('Boolean')),
	);
}
?>
