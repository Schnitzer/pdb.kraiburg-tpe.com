<?php
/* SVN FILE: $Id$ */

/**
 * Contains the Published Site class.
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
 * Published Site class.
 *
 * @package netzcraftwerk
 */
class Wcms_PublishedSite extends Wcms_Site
{
    /**
     * Has many associations
     *
     * @var array
     */
    public $has_many = array('PublishedSitelanguage', 'PublishedComponent', 'PublishedNewssite');

    /**
     * @return void
     */
    public function beforeSave() {}

    /**
     * @return void
     */
    public function beforeDelete() {}

    /**
     * @return void
     */
    public function afterDelete($deleted = null) {}
}
?>
