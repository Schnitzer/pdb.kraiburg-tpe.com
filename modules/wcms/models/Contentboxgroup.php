<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ContentboxGroup class.
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
 * ContentboxGroup class.
 *
 * @package netzcraftwerk
 */
class Wcms_Contentboxgroup extends Ncw_Model
{

	/**
	 * Has many...
	 *
	 * @var array
	 */
	public $has_many = array(
        'ContentboxGroup' => array('foreign_key' => 'parent_id'),
	);

	/**
	 * Validations
	 *
	 * @var array
	 */
	public $validation = array(
	   'name' => array('rules' => array('NotEmpty','MaxLength' => 100), "required" => true)
	);
}
?>
