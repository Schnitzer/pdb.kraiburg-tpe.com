<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Published Componentlanguage class.
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
 * Published Componentlanguage class.
 *
 * @package netzcraftwerk
 */
class Wcms_PublishedComponentlanguage extends Wcms_Componentlanguage
{

	/**
	 * Has many associations
	 *
	 * @var array
	 */
	public $has_many = array(
	   'PublishedComponenttext' => array('foreign_key' => 'componentlanguage_id'),
	   'PublishedComponentshorttext' => array('foreign_key' => 'componentlanguage_id'),
	   'PublishedComponentfile' => array('foreign_key' => 'componentlanguage_id')
	);

	/**
	 * Belongs to associations
	 *
	 * @var array
	 */
	public $belongs_to = array(
	   'PublishedComponent' => array('join_condition' => 'PublishedComponent.id=PublishedComponentlanguage.component_id')
	);

    /**
     * Do nothing
     *
     */
    public function afterSave() {

    }

    /**
     * Do nothing
     *
     */
    public function beforeDelete ()
    {

    }
}
?>
