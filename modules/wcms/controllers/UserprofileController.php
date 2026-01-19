<?php
/* SVN FILE: $Id$ */
/**
 * Contains the UserprofileController class.
 *
 * PHP Version 5
 * Copyright (c) 2011 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Matthias Deinbeck <m.deinbeck@netzcraftwerk.com>
 * @copyright		Copyright 2007-2011, Netzcraftwerk GmbH
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * UserprofileController class.
 *
 * @package netzcraftwerk
 */
class Wcms_UserprofileController extends Wcms_ModuleController
{

	public $has_model = false;
	
    
	public $acl_publics = array(
		'userprofile',
		'updateUser',
    'getUserLatLon',
    'userlogin',
    'userlogout'
	);
	
    
    
    public function getUserLatLon($postcode)
    {
        $this->view = false;
        
        
        // objekt vom Typ Contacts latlon DB erstellen
        $obj_contact_latlon = new Contacts_Latlon();
        // alle auslesen, die die Postleitzahl  haben
        $arr_latlon = $obj_contact_latlon->fetch('all', array('conditions' => array('postcode' => $postcode)));
        if (count($arr_latlon) > 0){
            $user_lat = $arr_latlon[0]->getLat();
            $user_lon=  $arr_latlon[0]->getLon();
            
            // Erdradius (geozentrischer Mittelwert) in Km
            $earth = 6368;
                        
            // Umrechnung von GRAD IN RAD 
            $r_lon = $user_lon / 180 * M_PI;
            $r_lat = $user_lat / 180 * M_PI;
            
        
            $str_query_search_pre = "
                SELECT DISTINCT ncc.name as city, ncc.postcode zip, nca.street street, nco.name name,nco.search, nco.id id,(
                " . $earth . " * SQRT(2*(1-cos(RADIANS(latitude)) * 
                cos(" . $r_lat . ") * (sin(RADIANS(longitude)) *
                sin(" . $r_lon . ") + cos(RADIANS(longitude)) * 
                cos(" . $r_lon . ")) - sin(RADIANS(latitude)) * sin(" . $r_lat . ")))) AS Distance
                FROM ncw_contacts_city ncc
                INNER JOIN ncw_contacts_address nca ON ncc.id = nca.city_id
                INNER JOIN ncw_contacts_contact nco ON nca.contact_id = nco.id
                WHERE nco.search = '1'
                ORDER BY Distance
                LIMIT 0,20
                ";
            //echo $str_query_search_pre . ' <br /><br />';
            //  WHERE nco.search = '1'
            //  HAVING Distance <= '50'
            
            
            $searchquery = $obj_contact_latlon->db->prepare($str_query_search_pre);
            $searchquery->execute();
            $searchresult_pre = $searchquery->fetchAll();
            
            foreach ($searchresult_pre as $haendler) { ?>
                <div id="box-contact-left" style="height:168px;margin-bottom:16px;"class="fx-box-shadow fx-border-radius-0-12">
                    <div class="fx-gradient-grey fx-border-radius-0-12" style="padding:16px;height:138px;position:relative;">
                        <h3 id="ncw-1-component-17-56-30-shorttext-1" class="ncw-wysiwyg-website-admin"><?php echo $haendler['name']; ?></h3><br />
                        <table style="width:100%">
                            <tr>
                                <td style="width:66px"><strong>Straße:</strong></td>
                                <td><?php echo $haendler['street']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>PLZ/Ort:</strong></td>
                                <td><?php echo $haendler['zip'] . ' '. $haendler['city']; ?></td>
                            </tr>
                            <?php 
                            $contact_id = $haendler['id'];
                            $obj_contact_phone = new Contacts_Phone();
                            //$obj_contact_phone->unbindModel('all');
                            $contact_phone = $obj_contact_phone->fetch(
                                'all',
                                array(
                                    'conditions' => array(
                                        'Phone.contact_id' => $contact_id,
                                        'Phone.location' => 'work'
                                    )
                                )
                            );
                            $str_phone = '';
                            if (count($contact_phone) > 0) {
                                $str_phone = $contact_phone[0]->getPhone(); ?>
                                <tr>
                                    <td><strong>Telefon:</strong></td>
                                    <td><?php echo $str_phone; ?></td>
                                </tr>
                            <?php
                            } 
                            $obj_contact_email = new Contacts_Email();
                            $contact_email = $obj_contact_email->fetch(
                                'all',
                                array(
                                    'conditions' => array(
                                        'Email.contact_id' => $contact_id,
                                        'Email.location' => 'work'
                                    )
                                )
                            );
                            $str_email = '';
                            if (count($contact_email) > 0) {
                                $str_email = $contact_email[0]->getEmail(); ?>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?php echo $str_email; ?></td>
                                </tr>
                            <?php
                            } 
                            $obj_contact_website = new Contacts_Website();
                            $contact_website = $obj_contact_website->fetch(
                                'all',
                                array(
                                    'conditions' => array(
                                        'Website.contact_id' => $contact_id,
                                        'Website.location' => 'work'
                                    )
                                )
                            );
                            $str_website = '';
                            //var_dump($contact_website);
                            if (count($contact_website) > 0) {
                                $str_website = $contact_website[0]->getWebsite();
                                if (substr($str_website, 0, 3) != 'htt' && substr($str_website, 0, 3) != 'HTT') {
                                    if (substr($str_website, 0, 3) != 'www' && substr($str_website, 0, 3) != 'WWW') {
                                        $str_website = 'http://www.' . $str_website;
                                    } else {
                                        $str_website = 'http://' . $str_website;
                                    }
                                }
                                ?>
                                <tr>
                                    <td><strong>Website:</strong></td>
                                    <td><a href="<?php echo $str_website; ?>" target="_blank"><?php echo $str_website; ?></a></td>
                                </tr>
                            <?php
                            } ?>
                            
                        </table>
                        <a target="_blank" href="https://maps.google.de/maps?saddr=<?php echo $postcode; ?>&daddr=<?php echo $haendler['street']; ?>,<?php echo $haendler['city']; ?>" >
                            <div style="color: #FFFFFF;font: 700 11px/16px Arial,sans-serif;bottom:12px; right:12px;position:absolute;border-radius: 0 0 7px 0;background: none repeat scroll 0 0 #00B9FF;height: 22px;padding-top: 5px;text-align: center;width: 126px;">
                                Route berechnen
                            </div>
                        </a>
                    </div>
                </div>
                
            <?php
            }
        
        } else {
            echo 'Bitte tragen sie eine korrekte Postleitzahl ein.';
        }
    } 
    
   
	public function userprofileAction ($user_id)
	{
	    
        
		$this->loadModel('Core_User');
		$this->loadModel('Contacts_Contact');
		$this->loadModel('Contacts_Email');
		$this->loadModel('Contacts_Website');
		$this->loadModel('Contacts_Phone');
		$this->loadModel('Contacts_Address');
		$this->loadModel('Contacts_City');
		$this->loadModel('Contacts_Country');
		$this->loadModel('Contacts_Member');
		
    $this->Core_User->unbindModel('all');

        
		//$contact_id = $contact_id[0]->getContactId();
		
    $this->Core_User->setId($user_id);
    $this->Core_User->read();
		$contact_id = $this->Core_User->getContactId();
		//$contact_id = 41;
		
		
		$arr_contact = $this->Contacts_Contact->fetch(
			'all',
			array(
				'conditions' => array(
					'Contact.id' => $contact_id
				)
			)
		);
		
		$contact_email = $this->Contacts_Email->fetch(
            'all',
            array(
                'conditions' => array(
                    'Email.contact_id' => $contact_id,
                    'Email.location' => 'work'
                )
            )
        );
        $str_email = '';
        if (count($contact_email) > 0) {
            $str_email = $contact_email[0]->getEmail();
        }
        
        $contact_website = $this->Contacts_Website->fetch(
            'all',
            array(
                'conditions' => array(
                    'Website.contact_id' => $contact_id,
                    'Website.location' => 'work'
                )
            )
        );
        $str_website = '';
        if (count($contact_website) > 0) {
            $str_website = $contact_website[0]->getWebsite();
        }
                
        $contact_phone = $this->Contacts_Phone->fetch(
            'all',
            array(
                'conditions' => array(
                    'Phone.contact_id' => $contact_id,
                    'Phone.location' => 'work'
                )
            )
        );
        $str_phone = '';
        if (count($contact_phone) > 0) {
            $str_phone = $contact_phone[0]->getPhone();
        }
        
        $contact_fax = $this->Contacts_Phone->fetch(
            'all',
            array(
                'conditions' => array(
                    'Phone.contact_id' => $contact_id,
                    'Phone.location' => 'fax'
                )
            )
        );
        $str_fax = '';
        if (count($contact_fax) > 0) {
            $str_fax = $contact_fax[0]->getPhone();
        }
        
        $contact_address = $this->Contacts_Address->fetch(
            'all',
            array(
                'conditions' => array(
                    'Address.contact_id' => $contact_id,
                    'Address.location' => 'work'
                )
            )
        );
        $str_street = '';
        $str_city_name = '';
        $str_city_postcode = '';
        $str_country_name = '';
        if (count($contact_address) > 0) {
            $str_street = $contact_address[0]->getStreet();
            
            $arr_contact_city = $this->Contacts_City->fetch(
                'all',
                array(
                    'conditions' => array(
                        'City.id' => $contact_address[0]->getCityId()
                    )
                )
            );
            
            
            if (count($arr_contact_city) > 0) {
                $str_city_name = $arr_contact_city[0]->getName();
                $str_city_postcode = $arr_contact_city[0]->getPostcode();
            }
            
            $contact_country = $this->Contacts_Country->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Country.id' => $arr_contact_city[0]->getCountryId()
                    )
                )
            );
            if (count($contact_country) > 0) {
                $str_country_name = $contact_country[0]->getName();
            }
        }
		
		$contact_member = $this->Contacts_Member->fetch(
			'all',
			array(
				'conditions' => array(
					'Member.contact_id' => $contact_id
				)
			)
		);
            // übergabe array rechnungsadresse
            $contact_informations = array();
            foreach ($arr_contact as $contact) {
                $contact_informations[] = array(
                    'contact_id' => $contact_id,
                    'position' => $contact->getInfo(),
                    'firstname' => $contact->getFirstname(),
                    'firm' => $contact->getTitle(),
                    'name' => $contact->getName(),
                    'gender' => $contact->getGender(),
                    'email_standard' => $str_email,
                    'website_standard' => $str_website,
                    'phone_standard' => $str_phone,
                    'fax_standard' => $str_fax,
                    'street_standard' => $str_street,
                    'city_standard' => $str_city_name,
                    'plz_standard' => $str_city_postcode,
                    'country_standard' => $str_country_name
                );
            }
        // deliver informations definieren
        // später kommt die Abfrage ob in dem Array mind. 1 Datensatz vorhanden ist. Wenn nicht werden die normalen Kontaktdaten übergeben
        $deliver_informations = array();
		if (false === empty($contact_member[0])) {
			$member_id = $contact_member[0]->getPersonContactId();
			
			$arr_deliver = $this->Contacts_Contact->fetch(
				'all',
				array(
					'conditions' => array(
						'Contact.id' => $member_id
					)
				)
			);
			
            $deliver_email = $this->Contacts_Email->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Email.contact_id' => $member_id,
                        'Email.location' => 'work'
                    )
                )
            );
            $str_deliver_email = '';
            if (count($deliver_email) > 0) {
                $str_deliver_email = $deliver_email[0]->getEmail();
            }
            
            $deliver_website = $this->Contacts_Website->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Website.contact_id' => $member_id,
                        'Website.location' => 'work'
                    )
                )
            );
            $str_deliver_website = '';
            if (count($deliver_website) > 0) {
                $str_deliver_website = $deliver_website[0]->getWebsite();
            }
                    
            $deliver_phone = $this->Contacts_Phone->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Phone.contact_id' => $member_id,
                        'Phone.location' => 'work'
                    )
                )
            );
            $str_deliver_phone = '';
            if (count($deliver_phone) > 0) {
                $str_deliver_phone = $deliver_phone[0]->getPhone();
            }
            
            $deliver_fax = $this->Contacts_Phone->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Phone.contact_id' => $member_id,
                        'Phone.location' => 'fax'
                    )
                )
            );
            $str_deliver_fax = '';
            if (count($deliver_fax) > 0) {
                $str_deliver_fax = $deliver_fax[0]->getPhone();
            }
            
            $deliver_address = $this->Contacts_Address->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Address.contact_id' => $member_id,
                        'Address.location' => 'work'
                    )
                )
            );
            $str_deliver_street = '';
            $str_deliver_city_name = '';
            $str_deliver_city_postcode = '';
            $str_deliver_country_name = '';
            if (count($deliver_address) > 0) {
                $str_deliver_street = $deliver_address[0]->getStreet();
                
                $arr_deliver_city = $this->Contacts_City->fetch(
                    'all',
                    array(
                        'conditions' => array(
                            'City.id' => $deliver_address[0]->getCityId()
                        )
                    )
                );
                if (count($arr_deliver_city) > 0) {
                    $str_deliver_city_name = $arr_deliver_city[0]->getName();
                    $str_deliver_city_postcode = $arr_deliver_city[0]->getPostcode();
                    $deliver_country = $this->Contacts_Country->fetch(
                        'all',
                        array(
                            'conditions' => array(
                                'Country.id' => $arr_deliver_city[0]->getCountryId()
                            )
                        )
                    );
                    if (count($deliver_country) > 0) {
                        $str_deliver_country_name = $deliver_country[0]->getName();
                    }
                }
            }


            
            // übergabe array lieferadresse
			$deliver_informations = array();
			foreach ($arr_deliver as $deliver) {
				$tmp_arr = array(
					'contact_id' => $contact_id,
					'position' => $deliver->getInfo(),
					'firstname' => $deliver->getFirstname(),
					'firm' => $deliver->getTitle(),
					'name' => $deliver->getName(),
					'gender' => $deliver->getGender(),
					'email_standard' => $str_deliver_email,
					'website_standard' => $str_deliver_website,
					'phone_standard' => $str_deliver_phone,
					'fax_standard' => $str_deliver_fax,
					'street_standard' => $str_deliver_street,
					'city_standard' => $str_deliver_city_name,
					'plz_standard' => $str_deliver_city_postcode,
					'country_standard' => $str_deliver_country_name
				);
				$deliver_informations[] = $tmp_arr;
			}
			$this->view->deliver_informations = $deliver_informations;
		}

        if (count($deliver_informations) < 1) {
            $deliver_informations = $contact_informations;
        }
		$this->view->contact_id = $contact_id;
		$this->view->contact_informations = $contact_informations;
		$this->view->deliver_informations = $deliver_informations;
        
		$this->render();
	}
	
	
	/**
	 * Update the Userprofile
	 *
	 * @param Integer $user_id
	 */
	public function updateUserAction ($contact_id)
	{
		$this->view = false;
		
	/*
		 * Form-Validation
		 */
		$to_replace = array('Firma * ...', 'Firma ...', 'Position ...', 'Name * ...', 'Name ...', 'Vorname * ...', 'Vorname * ...', 'Telefon * ...', 'Telefon ...', 'Fax ...', 'Land ...', 'Ihre PLZ ...', 'Ort ...', 'Hs.-Nr. ...', 'Straße ...', 'E-Mail * ...', 'E-Mail ...', 'Website ...');
		
        foreach ($this->params['form'] As $param => $value) {
            $this->params['form'][$param] = Ncw_Library_Sanitizer::clean($value);
        }
		
		$contact_params = str_replace($to_replace, '', $this->params['form']);

		//var_dump($contact_params);
        
		if (
    		true === isset(
    		    $contact_params['fep-name']
            ) 
    		&& false === empty($contact_params['fep-name'])
            )
		{
			
			/**
			 * Required Models
			 */
			$this->loadModel('Contacts_Contact');
			$this->loadModel('Contacts_Email');
			$this->loadModel('Contacts_City');
			$this->loadModel('Contacts_Address');
			$this->loadModel('Contacts_Phone');
			$this->loadModel('Contacts_Website');
			$this->loadModel('Contacts_Member');
			
			/**
			 * Contact - Maininformation
			 */
			$this->Contacts_Contact->setId($contact_id);
			$this->Contacts_Contact->setType('private');
			$this->Contacts_Contact->setName($contact_params['fep-name']);
			$this->Contacts_Contact->setFirstname($contact_params['fep-forename']);
            
            // Email Content zusammenbauen
            // Userinformationen lesen
            $str_kdnr = '';
            $str_new_addres = '';
            $obj_user = new Core_User();
            // kundennummer ermitteln entspricht username
            $arr_user = $obj_user->fetch('all', array('conditions' => array('contact_id' => $contact_id)));
            if (count($arr_user) > 0) {
                $str_kdnr = $arr_user[0]->getName();
            }
            
            $str_new_addres .= 'Kundennummer: ' . $str_kdnr;
            $str_new_addres .= '<br /><br /><b>Adresse</b>';
            $str_new_addres .= '<br />Name: ' . $contact_params['fep-name'];
            $str_new_addres .= '<br />Position: ' . $contact_params['fep-position'];
            $str_new_addres .= '<br />Strasse: ' . $contact_params['fep-street'];
            $str_new_addres .= '<br />PLZ: ' . $contact_params['fep-zip'];
            $str_new_addres .= '<br />Ort: ' . $contact_params['fep-town'];
            $str_new_addres .= '<br />Tel.: ' . $contact_params['fep-telephone'];
            $str_new_addres .= '<br />Fax: ' . $contact_params['fep-fax'];
            $str_new_addres .= '<br />Email: ' . $contact_params['fep-email'];
            
            $str_new_addres = utf8_decode($str_new_addres);
            
            
			if (true === isset($contact_params['fep-mister'])) {
				$this->Contacts_Contact->setGender('male');
			} else if (true === isset($contact_params['fep-misses'])) {
				$this->Contacts_Contact->setGender('female');
			} else {
				$this->Contacts_Contact->setGender('none');
			}
			$this->Contacts_Contact->setTitle($contact_params['fep-firm']);
			if (true === isset($contact_params['fep-position']) && false === empty($contact_params['fep-position'])) {
				$this->Contacts_Contact->setInfo($contact_params['fep-position']);
			}
			$this->Contacts_Contact->save();
			
			
            
            /**
             * Contact - Email
             */
            if (true === isset($contact_params['fep-email']) && false === empty($contact_params['fep-email'])) {
                
                $contact_params['fep-email'] = str_replace('&#45;', '-', $contact_params['fep-email']);
                
                $obj_email_read = new Contacts_Email();
                $arr_email = $obj_email_read->fetch(
                    'all',
                    array(
                        'conditions' => array(
                            'contact_id' => $contact_id,
                            'location' => 'work',
                            'first' => 1
                        )
                    )
                );
                if (count($arr_email) > 0) {
                    //echo $arr_email[0]->getId();
                    $obj_email = new Contacts_Email();
                    $obj_email->setId($arr_email[0]->getId());
                    $obj_email->setContactId($contact_id);
                    $obj_email->setLocation('work');
                    $obj_email->setEmail($contact_params['fep-email']);
                    $obj_email->setFirst(1);
                    $obj_email->save();
                } else {
                    $obj_email = new Contacts_Email();
                    $obj_email->setContactId($contact_id);
                    $obj_email->setLocation('work');
                    $obj_email->setEmail($contact_params['fep-email']);
                    $obj_email->setFirst(1);
                    $obj_email->create();
                    $obj_email->save();
                }
            }
            
			
			/**
			 * Contact - City
			 */
			$city_id = 0;
			if (true === isset($contact_params['fep-town']) && false === empty($contact_params['fep-town'])) {
				$arr_city = $this->Contacts_City->fetch(
					'all',
					array(
						'conditions' => array(
							'City.name' => $contact_params['fep-town'],
							'City.postcode' => $contact_params['fep-zip']
						)
					)
				);	
				
				if (count($arr_city) < 1) {
					$obj_city = new Contacts_City();
					$obj_city->setCountryId(81);
					$obj_city->setPostcode($contact_params['fep-zip']);
					$obj_city->setName($contact_params['fep-town']);
                    $obj_city->create();
					$obj_city->save();
					$city_id = $obj_city->getId();
				} else {
					$city_id = $arr_city[0]->getId();
				}
			}
			
            
			/**
			 * Contact - Address
			 */
			if (true === isset($contact_params['fep-street']) && false === empty($contact_params['fep-street'])) {
				$arr_address = $this->Contacts_Address->fetch(
					'all',
					array(
						'conditions' => array(
							'Address.contact_id' => $contact_id,
							'Address.location' => 'work'
						)
					)
				);

                if (count($arr_address) > 0) {
                    $obj_adress = new Contacts_Address();
                    $obj_adress->setId($arr_address[0]->getId());
                    $obj_adress->setContactId($contact_id);
                    $obj_adress->setCityId($city_id);
                    $obj_adress->setLocation('work');
                    $obj_adress->setStreet($contact_params['fep-street']);
                    $obj_adress->setFirst(1);
                    $obj_adress->save();
                } else {
                    $obj_adress = new Contacts_Address();
                    $obj_adress->setContactId($contact_id);
                    $obj_adress->setCityId($city_id);
                    $obj_adress->setLocation('work');
                    $obj_adress->setStreet($contact_params['fep-street']);
                    $obj_adress->setFirst(1);
                    $obj_adress->create();
                    $obj_adress->save();
                }
			}
			
			/**
			 * Contact - Phone
			 */
			if (true === isset($contact_params['fep-telephone']) && false === empty($contact_params['fep-telephone'])) {
    			$arr_phone = $this->Contacts_Phone->fetch(
    				'all',
    				array(
    					'conditions' => array(
    						'Phone.contact_id' => $contact_id,
    						'Phone.location' => 'work'
    					)
    				)
    			);
                if (count($arr_phone) > 0) {
                    $obj_phone = new Contacts_Phone();
        			$obj_phone->setId($arr_phone[0]->getId());
        			$obj_phone->setContactId($contact_id);
        			$obj_phone->setLocation('work');
        			$obj_phone->setPhone($contact_params['fep-telephone']);
        			$obj_phone->setFirst(1);
        			$obj_phone->save();
                } else {
                    $obj_phone = new Contacts_Phone();
                    $obj_phone->setContactId($contact_id);
                    $obj_phone->setLocation('work');
                    $obj_phone->setPhone($contact_params['fep-telephone']);
                    $obj_phone->setFirst(1);
                    $obj_phone->create();
                    $obj_phone->save();
                }
            }

			/**
			 * Contact - Fax
			 */
            if (true === isset($contact_params['fep-fax']) && false === empty($contact_params['fep-fax'])) {
                $arr_phone = $this->Contacts_Phone->fetch(
                    'all',
                    array(
                        'conditions' => array(
                            'Phone.contact_id' => $contact_id,
                            'Phone.location' => 'fax'
                        )
                    )
                );
                if (count($arr_phone) > 0) {
                    $obj_phone = new Contacts_Phone();
                    $obj_phone->setId($arr_phone[0]->getId());
                    $obj_phone->setContactId($contact_id);
                    $obj_phone->setLocation('fax');
                    $obj_phone->setPhone($contact_params['fep-fax']);
                    $obj_phone->setFirst(1);
                    $obj_phone->save();
                } else {
                    $obj_phone = new Contacts_Phone();
                    $obj_phone->setContactId($contact_id);
                    $obj_phone->setLocation('fax');
                    $obj_phone->setPhone($contact_params['fep-fax']);
                    $obj_phone->setFirst(1);
                    $obj_phone->create();
                    $obj_phone->save();
                }
            }
			
			
			/**
			 * Contact - Website
			 */
			if (true === isset($contact_params['fep-website']) && false === empty($contact_params['fep-website'])) {
				$arr_website = $this->Contacts_Website->fetch(
					'all',
					array(
						'conditions' => array(
							'Website.contact_id' => $contact_id,
							'Website.location' => 'work'
						)
					)
				);
				if (count($arr_website) < 1) {
				    $obj_website = new Contacts_Website();
					$obj_website->setContactId($contact_id);
					$obj_website->setLocation('work');
					$obj_website->setWebsite($contact_params['fep-website']);
                    $obj_website->create();
					$obj_website->save();
				} else {
				    $obj_website = new Contacts_Website();
					$website_id = $arr_website[0]->getId();
					$obj_website->setId($website_id);
					$obj_website->setContactId($contact_id);
					$obj_website->setLocation('work');
					$obj_website->setWebsite($contact_params['fep-website']);
					$obj_website->save();
				}
			}
            
            
			// ################################################################
            // Lieferkontakt
            // ################################################################
			if (
			    true === isset(
			        $contact_params['fep-delivery-name'],
			        $contact_params['fep-delivery-forename'],
			        $contact_params['fep-delivery-telephone'],
			        $contact_params['fep-delivery-email']
                ) 
            ) {
                
                
            
                $str_new_addres .= '<br /><br /><b>Lieferadresse</b>';
                $str_new_addres .= '<br />Name: ' . $contact_params['fep-delivery-name'];
                $str_new_addres .= '<br />Strasse: ' . $contact_params['fep-delivery-street'];
                $str_new_addres .= '<br />PLZ: ' . $contact_params['fep-delivery-zip'];
                $str_new_addres .= '<br />Ort: ' . $contact_params['fep-delivery-town'];
                $str_new_addres .= '<br />Tel.: ' . $contact_params['fep-delivery-telephone'];
                $str_new_addres .= '<br />Email: ' . $contact_params['fep-delivery-email'];
				
				/**
				 * contact member_id suchen
				 */
				//$this->Contacts_Contact->unbindModel('all');
				$this->Contacts_Member->unbindModel('all');
				$arr_members = $this->Contacts_Member->fetch(
					'all',
					array(
						'conditions' => array(
							'contact_id' => $contact_id
						)
					)
				);
                if (count($arr_members) < 1) {
                // wenn kein Member gefunden wurde
				    $obj_contact_member = new Contacts_Contact();
					$obj_contact_member->setType('private');
					$obj_contact_member->setName($contact_params['fep-delivery-name']);
					$obj_contact_member->setFirstname($contact_params['fep-delivery-forename']);
					if (true === isset($contact_params['fep-delivery-mister'])) {
						$obj_contact_member->setGender('male');
					} else if (true === isset($contact_params['fep-delivery-misses'])) {
						$obj_contact_member->setGender('female');
					} else {
						$obj_contact_member->setGender('none');
					}
					$obj_contact_member->setTitle($contact_params['fep-delivery-firm']);
					if (true === isset($contact_params['fep-delivery-position'])) {
						$obj_contact_member->setInfo($contact_params['fep-delivery-position']);
					}
                    $obj_contact_member->create();
					$obj_contact_member->save();
					
					$member_id = $obj_contact_member->getId();
                    $obj_contacts_member = new Contacts_Member();
					$obj_contacts_member->setContactId($contact_id);
					$obj_contacts_member->setPersonContactId($member_id);
					$obj_contacts_member->setDescription('Lieferadresse für ');
                    $obj_contacts_member->create();
					$obj_contacts_member->save();
				} else {
					$member_id = $arr_members[0]->getPersonContactId();
					$this->Contacts_Contact->setId($member_id);
					$this->Contacts_Contact->setType('private');
					$this->Contacts_Contact->setName($contact_params['fep-delivery-name']);
					$this->Contacts_Contact->setFirstname($contact_params['fep-delivery-forename']);
					if (true === isset($contact_params['fep-delivery-mister'])) {
						$this->Contacts_Contact->setGender('male');
					} else if (true === isset($contact_params['fep-delivery-misses'])) {
						$this->Contacts_Contact->setGender('female');
					} else {
						$this->Contacts_Contact->setGender('none');
					}
					$this->Contacts_Contact->setTitle($contact_params['fep-delivery-firm']);
					if (true === isset($contact_params['fep-delivery-position'])) {
						$this->Contacts_Contact->setInfo($contact_params['fep-delivery-position']);
					}
					$this->Contacts_Member->setDescription('Lieferadresse für ');
					$this->Contacts_Contact->save();
				}
				
				$this->Contacts_Email->unbindModel('all');
				$arr_mail = $this->Contacts_Email->fetch(
					'all',
					array(
						'conditions' => array(
							'Email.contact_id' => $member_id,
							'Email.location' => 'work'
						)
					)
				);
				if (count($arr_mail) < 1) {
				    $obj_mail = new Contacts_Email();
					$obj_mail->setContactId($member_id);
					$obj_mail->setLocation('work');
					$obj_mail->setEmail($contact_params['fep-delivery-email']);
                    $obj_mail->setFirst(1);
                    $obj_mail->create();
					$obj_mail->save();
				} else {
				    $obj_mail = new Contacts_Email();
					$obj_mail->setId($arr_mail[0]->getId());
					$obj_mail->setContactId($member_id);
					$obj_mail->setLocation('work');
					$obj_mail->setEmail($contact_params['fep-delivery-email']);
					$obj_mail->setFirst(1);
					$obj_mail->save();
				}
				
                /**
                 * Contact - City
                 */
                $city_id = 0;
                //if (true === isset($contact_params['fep-delivery-town']) && false === empty($contact_params['fep-delivery-town'])) {
                    $arr_city = $this->Contacts_City->fetch(
                        'all',
                        array(
                            'conditions' => array(
                                'City.name' => $contact_params['fep-delivery-town'],
                                'City.postcode' => $contact_params['fep-delivery-zip']
                            )
                        )
                    );  
                    
                    if (count($arr_city) < 1) {
                        $obj_city = new Contacts_City();
                        $obj_city->setCountryId(81);
                        $obj_city->setPostcode($contact_params['fep-delivery-zip']);
                        $obj_city->setName($contact_params['fep-delivery-town']);
                        $obj_city->create();
                        $obj_city->save();
                        $city_id = $obj_city->getId();
                    } else {
                        $city_id = $arr_city[0]->getId();
                    }
                //}
            
                /**
                 * Contact - Address
                 */
                
                    $arr_address = $this->Contacts_Address->fetch(
                        'all',
                        array(
                            'conditions' => array(
                                'Address.contact_id' => $member_id,
                                'Address.location' => 'work'
                            )
                        )
                    );
                if (true === isset($contact_params['fep-delivery-street']) && false === empty($contact_params['fep-delivery-street'])) {
                    if (count($arr_address) > 0) {
                        $obj_adress = new Contacts_Address();
                        $obj_adress->setId($arr_address[0]->getId());
                        $obj_adress->setContactId($member_id);
                        $obj_adress->setCityId($city_id);
                        $obj_adress->setLocation('work');
                        $obj_adress->setStreet($contact_params['fep-delivery-street']);
                        $obj_adress->setFirst(1);
                        $obj_adress->save();
                    } else {
                        $obj_adress = new Contacts_Address();
                        $obj_adress->setContactId($member_id);
                        $obj_adress->setCityId($city_id);
                        $obj_adress->setLocation('work');
                        $obj_adress->setStreet($contact_params['fep-delivery-street']);
                        $obj_adress->setFirst(1);
                        $obj_adress->create();
                        $obj_adress->save();
                    }
                } else {
                    if (count($arr_address) > 0) {
                        $obj_adress = new Contacts_Address();
                        $obj_adress->setId($arr_address[0]->getId());
                        $obj_adress->delete();
                    }
                }
                
                // Telefon Lieferadresse
				if (true === isset($contact_params['fep-delivery-telephone']) && false === empty($contact_params['fep-delivery-telephone'])) {
    				$phone_id = $this->Contacts_Phone->fetch(
    					'all',
    					array(
    						'conditions' => array(
    							'Phone.contact_id' => $member_id,
    							'Phone.location' => 'work'
    						)
    					)
    				);
    				if ($phone_id[0] == NULL) {
    					$this->Contacts_Phone->create();
    					$this->Contacts_Phone->setContactId($member_id);
    					$this->Contacts_Phone->setLocation('work');
    					$this->Contacts_Phone->setPhone($contact_params['fep-delivery-telephone']);
    					$this->Contacts_Phone->setFirst(1);
    					$this->Contacts_Phone->save();
    				} else {
    					$this->Contacts_Phone->setId($phone_id[0]->getId());
    					$this->Contacts_Phone->setContactId($member_id);
    					$this->Contacts_Phone->setLocation('work');
    					$this->Contacts_Phone->setPhone($contact_params['fep-delivery-telephone']);
    					$this->Contacts_Phone->setFirst(1);
    					$this->Contacts_Phone->save();
    				}
                }
                
                // Fax Lieferadresse
                if (true === isset($contact_params['fep-delivery-fax']) && false === empty($contact_params['fep-delivery-fax'])) {
                    $arr_phone = $this->Contacts_Phone->fetch(
                        'all',
                        array(
                            'conditions' => array(
                                'Phone.contact_id' => $member_id,
                                'Phone.location' => 'fax'
                            )
                        )
                    );
                    if (count($arr_phone) > 0) {
                        $obj_phone = new Contacts_Phone();
                        $obj_phone->setId($arr_phone[0]->getId());
                        $obj_phone->setContactId($member_id);
                        $obj_phone->setLocation('fax');
                        $obj_phone->setPhone($contact_params['fep-delivery-fax']);
                        $obj_phone->setFirst(1);
                        $obj_phone->save();
                    } else {
                        $obj_phone = new Contacts_Phone();
                        $obj_phone->setContactId($member_id);
                        $obj_phone->setLocation('fax');
                        $obj_phone->setPhone($contact_params['fep-delivery-fax']);
                        $obj_phone->setFirst(1);
                        $obj_phone->create();
                        $obj_phone->save();
                    }
                }
				
				if (true === isset($contact_params['fep-delivery-website']) && false === empty($contact_params['fep-delivery-website'])) {
					$website_id = $this->Contacts_Website->fetch(
						'all',
						array(
							'conditions' => array(
								'Website.contact_id' => $member_id,
								'Website.location' => 'work'
							)
						)
					);
					if ($website_id[0] == NULL) {
						$this->Contacts_Website->create();
						$this->Contacts_Website->setContactId($member_id);
						$this->Contacts_Website->setLocation('work');
						$this->Contacts_Website->setWebsite($contact_params['fep-delivery-website']);
						$this->Contacts_Website->save();
					} else {
						$website_id = $website_id[0]->getId();
						$this->Contacts_Website->setId($website_id);
						$this->Contacts_Website->setContactId($member_id);
						$this->Contacts_Website->setLocation('work');
						$this->Contacts_Website->setWebsite($contact_params['fep-delivery-website']);
						$this->Contacts_Website->save();
					}
				}
                
                $email1 = 'info@hagl-s.de';
                //$email1 = 'winfried.weingartner@gmail.com';
                $email2 = 'w.weingartner@netzcraftwerk.com';
                //$this->_mail_att($email,'Kunde Nr. ' . $str_kdnr . ' hat seine Daten verändert',$str_new_addres,'');
                $this->_sendEmails(array($email1, $email2), 'Kunde Nr. ' . $str_kdnr . ' hat seine Daten verändert', $str_new_addres, $str_new_addres);
                
                
                
                
                echo '<h3>Ihre &Auml;nderungen wurden gespeichert und an Simon Hagl GmbH weitergeleitet!</h3>';
			} else {
				echo 'Fehler beim Speichern der Lieferadresse!';
			}
		} else {
			echo 'false';
		}
	}


   /**
    * Mail Attachement hinzufügen und dann die Mail veresenden
    */
   private function _mail_att($to,$subject,$message,$datei_anhang)
   {
       
        $pfad = $datei_anhang;

        if ($datei_anhang != '') {
            $anhang = array();
            $anhang["name"] = basename($pfad);
            $anhang["size"] = filesize($pfad);
            $anhang["data"] = implode("",file($pfad));
    
            if(function_exists("mime_content_type")) {
               $anhang["type"] = mime_content_type($pfad);
            } else {
               $anhang["type"] = "application/octet-stream";
            }
       }
        
       $absender = "Simon Hagl GmbH";
       $absender_mail = "info@hagl-s.de";
       $reply = "info@hagl-s.de";

       $mime_boundary = "-----=" . md5(uniqid(mt_rand(), 1));
    
       $header  ="From:".$absender."<".$absender_mail.">\n";
       $header .= "Reply-To: ".$reply."\n";
    
       $header.= "MIME-Version: 1.0\r\n";
       $header.= "Content-Type: multipart/mixed;\r\n";
       $header.= " boundary=\"".$mime_boundary."\"\r\n";
    
       $content = "This is a multi-part message in MIME format.\r\n\r\n";
       $content.= "--".$mime_boundary."\r\n";
       $content.= "Content-Type: text/html charset=\"iso-8859-1\"\r\n";
       $content.= "Content-Transfer-Encoding: 8bit\r\n\r\n";
       $content.= $message."\r\n";

 
        $send = 'false';
        if (true == is_array($to)) {
            foreach($to As $one_recipient) {
                if(@mail($one_recipient , $subject, $content, $header)) {
                    $send = 'true';
                }
            }
        } else {
            if(@mail($to , $subject, $content, $header)) {
                $send = 'true';
            }
        }
        return $send;
    }

    /**
     * Sendet die Emails
     * 
     * @param array $contacts
     * @param array $replacements
     * 
     * @return array
     */
    protected function _sendEmails ($arr_mail_addresses, $str_subject, $str_body_html, $str_body_txt, $arr_attachement = array(), $arr_attachement_name = array(), $str_optional_sender = '')
    {
                
        $html_body = $str_body_html;
        $text_body = $str_body_txt;
        $urls_to_replace = '';

       
        $replacements = array();

        include_once 'ncw/vendor/swift/swift_required.php';
        
        $obj_email_setting = new Newsletter_Setting();
        $obj_email_setting->setId(1);
        $obj_email_setting->read();
        $transport = Swift_SmtpTransport::newInstance(
            $obj_email_setting->getSmtpHost(), $obj_email_setting->getSmtpPort()
        )
            ->setUsername($obj_email_setting->getSmtpUsername())
            ->setPassword($obj_email_setting->getSmtpPassword());

        $mailer = Swift_Mailer::newInstance($transport);

        $mailer->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
        //Use AntiFlood to re-connect after 100 emails
        $mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100));

        // wenn ein optionaler sender hinterlegt ist wird dieser als absender hergenommen
        if (strlen($str_optional_sender) > 2) {
            $str_sender_email = $str_optional_sender;
        } else {
            $str_sender_email = $obj_email_setting->getSenderEmail();
        }
        // Create the message
        $message = Swift_Message::newInstance()
            ->setSubject($str_subject)
            ->setFrom(array($str_sender_email => $str_sender_email))
            ->setTo($arr_mail_addresses)
            ->setBody($html_body, 'text/html')
            ->addPart($text_body, 'text/plain');
        if (count($arr_attachement) > 0) {
            $message->attach(Swift_Attachment::fromPath($arr_attachement[0])->setFilename($arr_attachement_name[0])); // Attach the generated PDF from earlier
            if (count($arr_attachement) > 1 ){
                $message->attach(Swift_Attachment::fromPath($arr_attachement[1])->setFilename($arr_attachement_name[1])); // Attach the generated PDF from earlier
            }
        }

        $mailer->send($message);

    }
    
    /*
    * Userlogin überprüfen
    */    
    public function userloginAction()
    {
    	$this->view = false;	
   
   		
            $login_error = false;
            if (true === isset($_POST["Login"]["user"], $_POST["Login"]["password"])) {
                
                if (strlen(trim($_POST["Login"]["password"])) > 3) {
                    try {
                        Ncw_Components_Session::regenerate();
    
                        $fields = array(
                            "name" => array("required" => true),
                            "password" => array()
                        );
    
                        $validator = new Ncw_Validator($fields);
                        $data = array();
                        $data["name"] = $_POST["Login"]["user"];
                        $data["password"] = $_POST["Login"]["password"];
                        list($success, $invalid_fields) = $validator->validate($data);
                        if (true === $success) {
                            $data["name"] = $_POST["Login"]["user"];
                            $data["password"] = $_POST["Login"]["password"];
                            list($success, $invalid_fields) = $validator->validate($data);
                        }
                            
                        
                        if (true === $success) {
                            $obj_user = Core_UserController::validateLogin($data["name"], $data["password"]);
                            if (true === $obj_user instanceof Core_User) {
                                Core_UserController::login($obj_user);
                                if (true === Ncw_Configure::read("App.rewrite")) {
                                   // $url = $this->base . "/" . $language_code . "/startseite-16";
                                } else {
                                    //$url = $this->base . "/index.php?url=/" . $language_code . "/startseite-16";
                                }
                                echo 'true';
                                 //var_dump($_SESSION['user']);
                                exit();
                            }
                            
                            $obj_user = Core_UserController::validateLogin($data["name"], strtolower($data["password"]));
                            if (true === $obj_user instanceof Core_User) {
                                Core_UserController::login($obj_user);
                                if (true === Ncw_Configure::read("App.rewrite")) {
                                    //$url = $this->base . "/" . $language_code . "/startseite-16";
                                } else {
                                    //$url = $this->base . "/index.php?url=/" . $language_code . "/startseite-16";
                                }
                                echo 'true';
                                //echo $this->_current_user["id"];
                                exit();
                            }
                            
                        } else {
                            $login_error = true;
                        }
                    } catch (Exception $e) {
                        //print $e->getMessage();
                    }
                }
            }
   					echo 'false';
    }
    
    /*
    * Userlogout
    */
		public function userlogoutAction()
    {
    	$this->view = false;	
    	Core_UserController::logout();
    	echo 'true';
    }

}
?>
