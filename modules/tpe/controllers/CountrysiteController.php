<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Countrysite class.
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
 * CountrysiteController class.
 *
 * @package netzcraftwerk
 */
class Tpe_CountrysiteController extends Tpe_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "TPE :: Countrysite";

	/**
	 * edit a countrysite
	 *
	 */
	public function editAction ($country_id, $region_id)
	{
    	if (true === isset($this->data['Countrysite'])) {
    	    
            $arr_countrysite = $this->Countrysite->findBy('country_id', $country_id);
            //var_dump($arr_countrysite);
    		if (count($arr_countrysite) > 0) {
    		    if ($arr_countrysite != false) {
    			    $this->Countrysite->setId($arr_countrysite->getId());
                }
    		}
    		$this->Countrysite->data($this->data['Countrysite']);
    		$this->Countrysite->setCountryId($country_id);
    		$this->Countrysite->setSiteId($this->data['Countrysite']['site_id']);
    		if (true === $this->Countrysite->save()) {
    			$this->redirect(
                    array(
                        'controller' => 'region',
                        'action' => 'edit',
                        'id' => $region_id
                    )
                );
    		}
    	}

    	$obj_country = new Contacts_Country();
    	$country = $obj_country->findBy('id', $country_id);

    	// read sites
    	$obj_site = new Wcms_Site();
    	$obj_site->unbindModel('all');
    	$this->view->arr_options = $obj_site->fetch(
            'all',
            array(
                'fields' => array(
                    'Site.id',
                    'Site.name'
                ),
                'conditions' => array('Site.id != 1', 'Site.parent_id = 15')
            )
        );

    	$this->view->country_name = $country->getName();
    	$this->view->region_id = $region_id;

    	if (true == ($countrysite = $this->Countrysite->findBy('country_id', $country_id))) {
    		$this->view->countrysite_siteId = $countrysite->getSiteId();
    	} else {
    		$this->view->countrysite_siteId = false;
    	}
	}
}
?>
