<?php
/* SVN FILE: $Id$ */
/**
 * Contains the PublishedNews class.
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
 * PublishedNews class.
 *
 * @package netzcraftwerk
 */
class Wcms_PublishedNews extends Wcms_News
{
    /**
     * Has many...
     *
     * @var array
     */
    public $has_many = array(
        'PublishedNewssite' => array('foreign_key' => 'news_id')
    );

    /**
     * Do nothing
     *
     */
    public function beforeSave() {

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
