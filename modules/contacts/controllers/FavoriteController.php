<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Favorite controller class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
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
 * FavoriteController class.
 *
 * @package netzcraftwerk
 */
class Contacts_FavoriteController extends Contacts_ModuleController
{

	/**
	 * Favorites
	 * 
	 */
	public function allAction ()
	{
		// recently opened contacts
		$user = Ncw_Components_Session::readInAll('user');
		$this->loadModel('Files_File');
		$this->loadModel('Contact');
		$favorites = $this->Favorite->fetch(
			'all',
			array(
				'conditions' => array(
					'Favorite.user_id' => $user['id'],
				),
				'order' => array(
					'Favorite.created' => 'desc'
				)
			)
		);
		$arr_favorites = array();
		foreach ($favorites as $contact) {
			$this->Contact->data($contact->Contact->data());
			if ($this->Contact->getFileId() > 0) {
				$image = $this->Files_File->imageUrl($this->Contact->getFileId());
			} else {
	        	if ($this->Contact->getType() == 'business') {
	        		$image = 'building.png';
	        	} else {
	        		$image = 'user.png';
	        	}
	        	$image = $this->base . '/' . THEMES . '/default/web/images/icons/16px/' . $image;				
			}
			$arr_favorites[] = array(
				'id' => $this->Contact->getId(),
				'full_name' => $this->Contact->fullName(),
				'image' => $image
			);
		}
		$this->view->arr_favorites = $arr_favorites;		
	}

	/**
	 * Add favorite
	 *
	 * @param int $contact_id the contact id
	 * 
	 * @return void
	 */
    public function addAction ($contact_id)
    {
        $this->view = false;
        $return = 'false';

		$contact_id = (int) $contact_id;

        if ($contact_id > 0) {
			$user = Ncw_Components_Session::readInAll('user');
			$obj = $this->Favorite->fetch(
				'first',
				array(
					'conditions' => array(
						'contact_id' => $contact_id,
						'user_id' => $user['id']
					)
				)
			);
			if (false === $obj) {
	        	$this->Favorite->setContactId($contact_id);
				$this->Favorite->setUserId($user['id']);
	            if (true === $this->Favorite->save()) {
	                $return = 'true';
	            }		
			}
        }

        print '{ "return_value" : ' . $return . ' }';
    }

    /**
     * Removes a favorite
     *
     * @param int $favorite_id
     *
     * @return void
     */
    public function removeAction ($favorite_id)
    {
        $this->view = false;
        $return = 'false';

		$favorite_id = (int) $favorite_id;

        if ($favorite_id > 0) {
        	
			$user = Ncw_Components_Session::readInAll('user');
			$this->Favorite->setId($favorite_id);
            if (true === $this->Favorite->delete()) {
                $return = 'true';
            }
        }

        print '{ "return_value" : ' . $return . ' }';
    }
}
?>
