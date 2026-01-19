<?php
/* SVN FILE: $Id$ */
/**
 * contains the UsergroupUser class
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
 * UsergroupUser model.
 *
 * @package netzcraftwerk
 */
class Core_UsergroupUser extends Ncw_Model
{

	/**
	 * Belongs to assocations
	 *
	 * @var Array
	 */
	public $belongs_to = array("Usergroup", "User");
}
?>
