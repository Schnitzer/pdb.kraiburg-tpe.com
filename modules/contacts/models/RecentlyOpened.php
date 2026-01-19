<?php
/* SVN FILE: $Id$ */
/**
 * Contains the RecentlyOpened class.
 *
 * PHP Version 5
 * Copyright (c) 2010 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright		Copyright 2007-2010, Netzcraftwerk UG (haftungsbeschränkt)
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * RecentlyOpened class.
 *
 * @package netzcraftwerk
 */
class Contacts_RecentlyOpened extends Ncw_Model
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
        "user_id" => array("rules" => array('Integer'), "required" => true),
        "contact_id" => array("rules" => array('Integer'), "required" => true),
	);
	
	/**
	 * Add contact to the recently opened list
	 * 
	 * @param int $contact_id
	 */
	public function addContact ($contact_id)
	{
		$user = Ncw_Components_Session::readInAll('user');
		$obj = $this->fetch(
			'first',
			array(
				'conditions' => array(
					'contact_id' => $contact_id,
					'user_id' => $user['id']
				)
			)
		);
		if (false !== $obj) {
			$this->setId($obj->getId());
			$this->delete();
			$this->create();
			$this->setContactId($contact_id);
			$this->setUserId($user['id']);
			$this->save();			
		} else {
			$count = $this->fetch(
				'count',
				array(
					'conditions' => array(
						'RecentlyOpened.user_id' => $user['id']
					)
				)
			);	
			if ($count > 4) {
				$obj = $this->fetch(
					'first',
					array(
						'fields' => array('RecentlyOpened.id'),
						'conditions' => array(
							'user_id' => $user['id']
						),
						'order' => array(
							'RecentlyOpened.created'
						)
					)
				);
				$this->setId($obj->getId());
				$this->delete();
			}		
			$this->create();
			$this->setContactId($contact_id);
			$this->setUserId($user['id']);
			$this->save();		
		}
	}
}
?>
