<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ModuleController class.
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
 * ModuleController class.
 *
 * @package netzcraftwerk
 */
class Contacts_ModuleController extends AppController
{

    /**
     * General translations. only for translation tool
     *
     * @return void
     */
    private function __generalTranslations ()
    {
        T_('Applications');
        T_('Other');
        T_('Logout');
        T_('Profile');

        T_('Alert');
        T_('Choose your options!');
        T_('Confirm');
        T_('Make a decision!');
        T_('Do you really want to delete this object?');
        T_('Saved');
        T_('Your data has been saved successfully.');
        T_('Error');
        T_('An error has occurred!');
        T_('Notify');
        T_('Be aware that...');
        T_('Custom');
        T_('Saving...');
        T_('Deleting...');
        T_('The item has been deleted successfully');
    }

    /**
     * Before filter
     *
     */
    public function beforeFilter ()
    {
        parent::beforeFilter();
        if (Ncw_Configure::read('App.language') != 'en_EN') {
            $this->registerJs('locale/' . Ncw_Configure::read('App.language') . '/LC_MESSAGES/default');
        }
        $this->registerJs('ncw.contacts');
    }

    /**
     * before Render
     *
     */
    public function beforeRender ()
    {
        if ($this->layout === 'default') {
            parent::beforeRender();

            $html = new Ncw_Helpers_Html();
            $html->startup($this->view);

            $this->view->menu = array(
                $html->link(T_('Contacts'), array('controller' => 'group', 'action' => 'all')),
                $html->link(T_('Overview'), array('controller' => 'group', 'action' => 'all'), array('class' => (($this->name == 'Contact') ? 'opened' : ''))),
            );
            
			if (true === $this->acl->check('/contacts/transfer')) {
	            $this->view->extras_menu = array(
	            	$html->link(T_('Import/Export'), array('controller' => 'transfer', 'action' => 'importCsv')),
	            );
			}
        }
    }

    /**
     * read all contact groups
     *
	 * @param int $id
	 * @param mixed $conditions
	 * @param boolean $nots_rekursiv
	 * 
	 * @return array
     */
    public function _allGroups ($id = 1, $not_this_group = null, $nots_rekursiv = false)
    {
        $this->loadModel('Group');
		return $this->Group->fetchAsList($id, $not_this_group, $nots_rekursiv);
    }

    /**
     * validate and save city data
     * if there is no city with the given data it will be created
     *
     * @return city_id
     */
    protected function _city ($data)
    {
    	$city_id = false;
		
        $this->loadModel('City');
        $city = $this->City->fetch(
            'first',
            array(
                'fields' => array('City.id'),
                'conditions' => array(
                    'City.name' => $data['name'],
                    'City.state' => $data['state'],
                    'City.postcode' => $data['postcode'],
                    'City.country_id' => $data['country_id']
                )
            )
        );
        if (true == strstr($data['state'], 'Bundesland')) {
            $data['state'] = '';
        }

        if (true === is_object($city)) {
            $city_id = (int) $city->getId();
        } else {
            $this->City->data($data);
            $this->City->setName(ucfirst ($data['name']));
            $this->City->setState($data['state']);
            
            if (true === $this->City->save()){
            	$city_id = (int) $this->City->getId();
            } else {
            	$city_id = $this->City->invalidFields();
            }
            
        }

        return $city_id;
    }
}
?>
