<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Contentbox class.
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
 * Contentbox class.
 *
 * @package netzcraftwerk
 */
class Wcms_Contentbox extends Ncw_Model
{

	/**
	 * Has many...
	 *
	 * @var array
	 */
	public $has_many = array(
        'Contentboxlanguage' => array('order' => array('Contentboxlanguage.language_id'))
	);
    
    /**
     * Belongs to
     *
     * @var array
     */
    public $belongs_to = array(
        'Contentboxgroup'
    );

	/**
	 * Validations
	 *
	 * @var array
	 */
	public $validation = array(
	   'name' => array('rules' => array('NotEmpty','MaxLength' => 100), "required" => true)
	);

    /**
     * Before Contentbox set the status
     *
     * @return void
     */
    public function beforeSave ()
    {
        if ($this->getId() > 0) {
            $status = $this->readField('status');
            if (($this->getStatus() === 'published' && $status !== 'published')
                || ($this->getStatus() === 'unpublished' && $status !== 'unpublished')
            ) {
                return;
            }
            if ($status !== 'new' && $status !== 'unpublished') {
                $this->setStatus('modified');
            } else {
                $this->setStatus($status);
            }
        }
    }

    /**
     * Before the Contentbox is deleted check if it is unpublished.
     *
     * @return boolean
     */
    public function beforeDelete ()
    {
        $status = $this->readField('status');
        if ($status === 'unpublished'
            || $status === 'new'
        ) {
            return true;
        }
        return false;
    }
}
?>
