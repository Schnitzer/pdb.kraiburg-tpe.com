<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Componenttext class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
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
 * Componenttext class.
 *
 * @package netzcraftwerk
 */
class Wcms_Componenttext extends Ncw_Model
{

	/**
	 * Belongs to..
	 *
	 * @var array
	 */
	public $belongs_to = array('Componentlanguage');

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array("componentlanguage_id" => array("rules" => array("Integer"), "required" => true), "position" => array("rules" => array("Integer"), "required" => true));

    /**
     * After save update the sitelanguage status to modified
     *
     * @return void
     */
    public function afterSave  ()
    {
        $this->read(array('fields' => array('Componentlanguage.id')));
        Wcms_Componentlanguage::setSitelanguageStatus(
            $this->Componentlanguage->getId()
        );
    }

    /**
     * Before delete update the sitelanguage status to modified
     *
     * @return void
     */
    public function beforeDelete ()
    {
        $this->read(array('fields' => array('Componentlanguage.id')));
        Wcms_Componentlanguage::setSitelanguageStatus(
            $this->Componentlanguage->getId()
        );
    }
}
?>
