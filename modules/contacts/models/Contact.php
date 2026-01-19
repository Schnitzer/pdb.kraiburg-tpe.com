<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Contact class.
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
 * Contact class.
 *
 * @package netzcraftwerk
 */
class Contacts_Contact extends Ncw_Model
{

    /**
     * Has many...
     *
     * @var array
     */
	public $has_many = array(
	   'GroupContact',
       'Phone' => array(
           'order' => array('Phone.first' => 'desc', 'Phone.id')
       ),
       'Email' => array(
           'order' => array('Email.first' => 'desc', 'Email.id')
       ),
       'Messenger' => array(
           'order' => array('Messenger.first' => 'desc', 'Messenger.id')
       ),
       'Website' => array(
           'order' => array('Website.first' => 'desc', 'Website.id')
       ),
       'Bank' => array(
           'order' => array('Bank.first' => 'desc', 'Bank.id')
       ),
       'Address' => array(
           'order' => array('Address.first' => 'desc', 'Address.id')
       ),
       'Date' => array(
           'order' => array('Date.first' => 'desc', 'Date.id')
       ),   
	   'Note' => array(
	       'order' => array('Note.created' => 'desc')
	   ),
       'File' => array(
           'order' => array('File.id')
       ),
       'RecentlyOpened',
       'Favorite',
	);

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
		'name' => array('rules' => array('NotEmpty', 'MaxLength' => 100), 'required' => true),
		'type' => array('rules' => array('InList' => array('private', 'business'))),
		'firstname' => array('rules' => array('NotEmpty', 'MaxLength' => 100)),
		'gender' => array('rules' => array('InList' => array('none', 'male', 'female'))),
		'title' => array('rules' => array('NotEmpty', 'MaxLength' => 100)),
	);
	
	/**
	 * Returns the full name of the contact
	 * 
	 * @return string
	 */
	public function fullName ()
	{
        if ($this->getId() > 0) {
            if ($this->getType() == 'private') {        	
				$full_name = '';
            	$title = $this->getTitle();
				if (false === empty($title)) {
					$full_name .= $title . ' ';
				}
                $full_name .= $this->getFirstname() . ' ' . $this->getName();
				return $full_name;
            } else {
                return $this->getName();
            }
        }
		return false;	
	}
}
?>
