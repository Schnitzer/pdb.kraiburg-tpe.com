<?php
/* SVN FILE: $Id$ */
/**
 * Contains the TransferController class.
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
 * TransferController class.
 *
 * @package netzcraftwerk
 */
class Contacts_TransferController extends Contacts_ModuleController
{

    /**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Import/Export :: Contacts";
	
    /**
	 * Paginate
	 */
    public $paginate = array(
		'limit' => '30',
		'order' => array('Transfer.name' => 'asc')
	);
    
	/**
	 * Lists all Contact-Import-Profiles
	 *
	 */
    public function allAction()
    {
    	$this->view->transfersettings = $this->paginate();
    }
    
    /**
     * Creates a new Contact-Import-Profile
     *
     */
    public function newAction()
    {
    	
    }
	
    /**
     * Creates a new Contact-Import-Profile
     *
     */
    public function saveAction()
    {
    	if (true === isset($this->data['Transfer'])) {
    		$this->Transfer->data($this->data['Transfer']);
    		if (true === $this->Transfer->save()) {
    			print '{ "return_value" :  false }';
				return;
    		}
    	}

		print '{ "return_value" :  false }';
    }	
    
    /**
     * edit Contact-Import-Profile
     * 
     *
     */
    public function editAction($transfer_id)
    {
    	$transfer_id = (int) $transfer_id;
		
    	$this->Transfer->setId($transfer_id);
    	
    	$this->Transfer->read();
    	$this->data['Transfer'] = $this->Transfer->data();
			
    	$this->view->types = array('business', 'private');
    	$this->view->transfersetting_id = $transfer_id;
    }
    
    /**
     * Update Setting
     *
     */
    public function updateAction()
    {
    	$this->view = false;
    	$return_value = 'false';
    	if (true === isset($this->data['Transfer'])) {
    		
			if ($this->data['Transfer']['default'] == 1) {
				$profiles = $this->Transfer->fetch(
					'id', 
					array('conditions' => array('Transfer.default' => 1))
				);
				foreach ($profiles as $profile) {
					$this->Transfer->setId($profile->getId());
					$this->Transfer->setDefault(0);
					$this->Transfer->saveField('default');
				}
			}
			
			$this->Transfer->data($this->data['Transfer']);
			if (true === $this->Transfer->save()) {
    			$return_value = 'true';
    		}
    	}
		echo '{"return_value" : ' . $return_value . '}';
    }
    
    /**
     * delete Setting
     *
     * @param int $setting_id
     */
    public function deleteAction($transfer_id)
    {
    	$this->view = false;
    	$this->Transfer->setId((int) $transfer_id);
    	$this->Transfer->delete();
    	$this->redirect(array('action' => 'all'));
    }

    /**
     * Import CSV
     *
     * @return void
     */
    public function importCsvAction ()
    {
    	$this->layout = 'default';
		
        $this->loadModel('Transfer');
		$this->view->profiles = $this->Transfer->fetch(
			'list',
			array(
				'fields' => array(
					'Transfer.name',
					'Transfer.id'
				),
			array(
			'order' => array(
						'Transfer.default' => 'desc'
					)
				)
			)
		);
    	
		// groups
		$list = array();
		foreach ($this->_allGroups() as $group) {
			$list[str_repeat('--', $group['level']) . ' ' . $group['name']] = $group['id'];
		}	
		$this->view->arr_groups = $list;
		
        $this->loadModel('Group');
        $this->Group->unbindModel(
            array(
                'belongs_to' => array('Groupcategory'),
                'has_many' => array('GroupContact')
            )
        );
        $arr_all_groups = $this->Group->fetch(
            'all', array('order' => array('Group.name'))
        );
        $this->view->arr_all_groups = $arr_all_groups;

        $this->registerCss('contact.less');
        $this->registerJs(
            array(
                'ncw.contacts.transfer',
                'ncw.contacts.contact'
            )
        );	
    }
    
    /*
     * Create Preview of Import
     * 
     * @return void
     */
    public function previewCsvAction ()
    {
    	$this->layout = 'default';
		
    	$this->loadModel('Transfer');
		
		// groups
		$list = array();
		foreach ($this->_allGroups() as $group) {
			$list[str_repeat('--', $group['level']) . ' ' . $group['name']] = $group['id'];
		}	
		$this->view->arr_groups = $list;
		
    	$this->view->profiles = $this->Transfer->fetch(
			'list',
			array(
				'fields' => array(
					'Transfer.name',
					'Transfer.id'
				)
			)
		);
		$setting_id = $this->data['Transfer']['profile_id'];
		$import_settings = $this->Transfer->fetch(
			'all',
			array(
				'conditions' => array(
					'Setting.id' => $setting_id
				)
			)
		);

    	$filename = $_FILES['data']['name']['Contact']['upload'];
        $temp_file = $_FILES['data']['tmp_name']['Contact']['upload'];
        $arr_filename = @explode('.', $filename);
        $filetype = array_pop($arr_filename);
        $filename = implode('.', $arr_filename);
        
        if ($filetype === 'csv' || $filetype === 'CSV') {
        	$filerows = file($temp_file);
        	foreach ($import_settings as $setting) {	
        		$ct = 0;
	        	$html = '<table class="ncw-table">';
	        	$html .= '<thead>';
        		//Creates the Tablehead
	        	foreach ($filerows as $row) {
        			$row = str_replace(array('"', "'"), '', $row);
        			$cells = explode($setting->getSeparator(), $row);
        			if ($ct == 0) {
	        			foreach ($cells as $cell) {
	        				$html .= '<th>' . $cell . '</th>';
	        			}
        			}
        			$ct++;
        		}
	        	$ct = 0;
	        	$html .= '</thead><tbody>';
	        	//Creates the Tablebody
	        	foreach ($filerows as $row) {
	        		$row = str_replace(array('"', "'"), '', $row);
        			$cells = explode($setting->getSeparator(), $row);
	        		if ($ct > 0 && $ct < 4) {
	        			$html .= '<tr>';
	        				foreach ($cells as $cell) {
	        					$html .= '<td>' . $cell . '</td>';
	        				}
	        			$html .= '</tr>';
	        		}
	        		$ct++;
	        	}
	        	
	        	$rowcheck = str_replace(array('"', "'"), '', $filerows[1]);
	        	$rowcheck = @explode($setting->getSeparator(), $rowcheck);
	        	$this->view->rowcheck = $rowcheck;
	        	$this->view->profilesetting = $setting;
        	}	
        	$html .= '</tbody></table>';
        	$this->view->contactCounter = $ct;
        	$this->view->previewTable = $html;
        }
    	
    }
    
     /**
     * Upload CSV action and import the contacts
     *
     * @return void
     */
    public function uploadCsvAction ()
    {
        $this->loadModel('Contact');
		$this->loadModel('Phone');
		$this->loadModel('Email');
		$this->loadModel('Address');
        $this->loadModel('City');
        $this->loadModel('Country');
        $this->loadModel('GroupContact');
        $this->loadModel('Member');
		$this->loadModel('Transfer');
		
		// load profile
		$profile_id = $this->data['Transfer']['profile_id'];
		$profiles = $this->Transfer->fetch(
			'all',
			array(
				'conditions' => array(
					'Transfer.id' => $profile_id
				)
			)
		);
	    
        $filename = $_FILES['data']['name']['Contact']['upload'];
        $temp_file = $_FILES['data']['tmp_name']['Contact']['upload'];
        $arr_filename = @explode('.', $filename);
        $filetype = array_pop($arr_filename);
        $filename = implode('.', $arr_filename);
        
        if ($filetype == 'csv' || $filetype == 'CSV') {
            $filerows = file($temp_file);
            foreach ($profiles as $profile) {
            	$ct = 0;
	            foreach ($filerows As $row) {

	                if ($ct > 0) {
	                    
	                    $row = str_replace(array('"', "'"), '', $row);
	                    
	                    //echo $row . '<br />';
	                    
	            		$cell = explode($profile->getSeparator(), $row);
						array_unshift($cell, '');

						$this->Contact->setType(trim($cell[$profile->getType()]));
	                    
	                    $this->Contact->setTitle(trim($cell[$profile->getTitle()]));
	                    $this->Contact->setFirstname(trim($cell[$profile->getFirstname()]));
	                    if (strlen(trim($cell[$profile->getLastname()])) > 0) {
	                        $this->Contact->setName(trim($cell[$profile->getLastname()]));
	                    } else {
	                    	$this->Contact->setName(trim($cell[$profile->getCompany()]));
	                    }
	
	                    // set gender
	                    $gender = str_replace('.', '', $cell[$profile->getGender()]);
	                    $gender = trim($gender);
						if (true === in_array($gender, array('male', 'female', 'none'))) {
							$this->Contact->setGender($gender);
						} else {
		                    if (strtoupper($gender) === 'HERR' || strtoupper($gender) === 'MR') {
		                    	$this->Contact->setGender('male');
		                    } else if (strtoupper($gender) === 'FRAU' || strtoupper($gender) === 'MRS') {
		                        $this->Contact->setGender('female');
		                    } else {
		                    	$this->Contact->setGender('none');
		                    }
						}
	                    
	                    // set Info (company)
	                    $this->Contact->setInfo(trim($cell[$profile->getInfo()]));
	
	                    // Country
	                    $allCountries = $this->Country->findBy(
	                        'code',
	                        strtoupper(trim($cell[$profile->getCountry()]))
	                    );
	                    $country_id = 81;
	                    if ($allCountries != false) {
	                        $country_id = $allCountries->getId();
	                    }

	                    // data city array
	                    $arrCity = array(
	                        'country_id' => $country_id,
	                        'postcode' => $cell[$profile->getPostcode()],
	                        'state' => $cell[$profile->getState()],
	                        'name' => $cell[$profile->getCity()]
	                    );
	                    $city_id = $this->_city($arrCity);
	
	                    // save contact
	                    $this->Contact->create();
	                    $this->Contact->save();
	                    $contact_id = $this->Contact->getId();
	
	                    // save the Address
	                    $this->Address->setContactId($contact_id);
	                    $this->Address->setCityId($city_id);
	                    $this->Address->setStreet($cell[$profile->getStreet()]);
	                    if ($profile->getType() == 'business') {
	                    	$this->Address->setLocation('work');
	                    } else {
	                    	$this->Address->setLocation('home');
	                    }
	                    $this->Address->setFirst(1);
	                    $this->Address->create();
	                    $this->Address->save();
	                    
	                    // save email
	                    if ($profile->getEmail() != 0) {
		                    $this->Email->setContactId($contact_id);
		                    $this->Email->setEmail($cell[$profile->getEmail()]);
		                    $this->Email->setFirst(1);
		                    $this->Email->create();
		                    $this->Email->save();
	                    }
	                    
	                    //save telephone
	                    if ($profile->getPhone() != 0) {
		                    $this->Phone->setContactId($contact_id);
		                    $this->Phone->setPhone($cell[$profile->getPhone()]);
		                    $this->Phone->setFirst(1);
		                    if ($profile->getType() == 'business') {
		                    	$this->Phone->setLocation('work');
		                	} else {
		                		$this->Phone->setLocation('home');
		                	}
		                    $this->Phone->create();
		                    $this->Phone->save();
	                    }
	                    
	                    //save mobile
	                    if ($profile->getMobile() != 0) {
		                    $this->Phone->setContactId($contact_id);
		                    $this->Phone->setPhone($cell[$profile->getMobile()]);
		                    $this->Phone->setLocation('mobile');
		                    $this->Phone->create();
		                    $this->Phone->save();
	                    }
						
	                    //save Member Contact => Child, Company => Parent
	                    $company_name = $cell[$profile->getCompany()];
	                    if (false === empty($company_name) && $profile->getMember() === 1) {
	                    	$companies = $this->Contact->fetch('id', array('conditions' => array('Contact.name' => $company_name)));
	                    	foreach ($companies as $company) {
	                    		$parent_id = $company->getId();
		                    	$this->Member->setPersonContactId($contact_id);
		                    	$this->Member->setContactId($parent_id);
		                    	$this->Member->create();
								$this->Member->save();
	                    	}
	                    }
	
	                    // add contact to group
	                    if (true === isset($this->data['Group'])) {
	                        foreach ($this->data['Group'] As $unit => $group) {
	                            $this->GroupContact->setContactId($contact_id);
	                            $this->GroupContact->setGroupId($unit);
	                            if ($contact_id != $save_contact_id) {
	                                $this->GroupContact->create();
	                                $save_contact_id = $contact_id;
	                            }
	                            $this->GroupContact->save();
	                        }
	                    }
	                    
	                }
	                $ct++;
	            }
            }
        }
    }

    /**
     * Export contacts as CSV-File
     *
     * @return Csv-File
     */
    public function exportCsvAction ()
    {
		$list = array();
		foreach ($this->_allGroups(0) as $group) {
			if ($group['name'] == 'root') {
				$group['name'] = T_('All contacts');
			}
			$list[str_repeat('--', $group['level']) . ' ' . $group['name']] = $group['id'];
		}	
		$this->view->arr_groups = $list;
		
        $this->registerCss('contact.less');		
    }
	
	/**
	 * Creates the csv file for the export
	 */
	public function createCsvAction ()
	{
		$this->view = false;
		
		if (true === isset($this->data['Transfer']['group_id'])) {
			$group_id = (int) $this->data['Transfer']['group_id'];
			
			$this->loadModel('Contact');
			$this->Contact->unbindModel('all');
							
			if ($group_id == 1) {
				$contacts = $this->Contact->fetch('all');
			} else {
				$this->Contact->unbindModel(
					array(
						'has_one' => array('GroupContact')
					)
				);				
				$contacts = $this->Contact->fetch(
					'all',
					array(
						'conditions' => array(
							'GroupContact.id' => $group_id
						)
					)
				);
			}
			
			$rows = array();
			$rows[] = 
			implode(
				';',
				array(
					'id',
					'type',
					'name',
					'firstname',
					'gender',
					'title',
					'info',
				)
			);			
			
			foreach ($contacts as $contact) {
				$row = array(
					'id' => '"' . $contact->getId() . '"',
					'type' => '"' . $contact->getType() . '"',
					'name' => '"' . $contact->getName() . '"',
					'firstname' => '"' . $contact->getFirstname() . '"',
					'gender' => '"' . $contact->getGender() . '"',
					'title' => '"' . $contact->getTitle() . '"',
					'info' => '"' . $contact->getInfo() . '"',
				);
				$rows[] = implode(';', $row);
			}

			header( "Content-Type: text/csv" ); 
			header( "Content-Disposition: attachment; filename=contacts.csv"); 
			header( "Content-Description: contacts" ); 
			header( "Pragma: no-cache" ); 
			header( "Expires: 0" );
			
			print implode("\n", $rows);
		}
	}
}
?>
