<?php
/* SVN FILE: $Id$ */
/**
 * Contains the LiteratureLanguage class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk UG (haftungsbeschränkt)
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * TPE LiteratureLanguage class.
 *
 * @package netzcraftwerk
 */
class Tpe_LiteratureLanguage extends Ncw_Model
{

	/**
	 * Belongs to...
	 *
	 * @var array
	 */
	public $belongs_to = array('Literature', 'Language', 'Files_File');

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
		//"file_id" => array("rules" => array('NotEmpty'), "required" => true),
        //"title" => array("rules" => array('NotEmpty', 'MaxLength' => 100), "required" => true),
        //"description" => array("rules" => array('NotEmpty', 'MaxLength' => 100), "required" => true),
	);

}
?>
