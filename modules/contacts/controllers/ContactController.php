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
 * @author             Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright        Copyright 2007-2008, Netzcraftwerk UG (haftungsbeschränkt)
 * @link            http://www.netzcraftwerk.com
 * @package            netzcraftwerk
 * @since            Netzcraftwerk v 3.0.0.1
 * @version            Revision: $LastChangedRevision$
 * @modifiedby        $LastChangedBy$
 * @lastmodified    $LastChangedDate$
 * @license            http://www.netzcraftwerk.com/licenses/
 */
/**
 * ContactController class.
 *
 * @package netzcraftwerk
 */
class Contacts_ContactController extends Contacts_ModuleController
{
    
    /**
     * Layout
     * 
     * @var string
     */
    public $layout = 'blank';    

    /**
     * The page title
     *
     * @var string
     */
    public $page_title = "Contacts";

    /**
     * Pagination options
     *
     * @var array
     */
    public $paginate = array(
        'fields' => array(
            'Contact.id',
            'Contact.type',
            'Contact.name',
            'Contact.firstname',
            'Contact.title',
            'Contact.created',
            'Phone.phone',
            'Email.email',
            'Member.contact_id',
            'Member.description',
            'Contacts_Contact.name',            
        ),
        'order' => array('Contact.type' => 'asc', 'Contact.name' => 'asc')
    );

    /**
     * 
     */
    protected $_main_sql_code = "
        FROM `:prefixcontacts_contact` AS `c`
        
        LEFT JOIN `:prefixcontacts_email` AS `e` 
        ON `c`.id=`e`.contact_id AND e.first=1
        LEFT JOIN `:prefixcontacts_phone` AS `p` 
        ON `c`.id=`p`.contact_id AND p.first=1
        LEFT JOIN `:prefixcontacts_member` AS `me` 
        ON `c`.id=`me`.person_contact_id AND me.first=1    
        LEFT JOIN `:prefixcontacts_contact` AS `co` 
        ON `co`.id=`me`.contact_id 
        LEFT JOIN `:prefixcontacts_group_contact` AS `gr` 
        ON `c`.id=`gr`.contact_id
        
        LEFT JOIN `:prefixcontacts_email` AS `e2` 
        ON `c`.id=`e2`.contact_id
        LEFT JOIN `:prefixcontacts_phone` AS `p2` 
        ON `c`.id=`p2`.contact_id
        LEFT JOIN `:prefixcontacts_address` AS `a2` 
        ON `c`.id=`a2`.contact_id
        LEFT JOIN `:prefixcontacts_city` AS `ci2` 
        ON a2.city_id=ci2.id
        LEFT JOIN `:prefixcontacts_messenger` AS `m2` 
        ON `c`.id=`m2`.contact_id
        LEFT JOIN `:prefixcontacts_website` AS `w2` 
        ON `c`.id=`w2`.contact_id
        LEFT JOIN `:prefixcontacts_note` AS `n2` 
        ON `c`.id=`n2`.contact_id
        LEFT JOIN `:prefixcontacts_date` AS `d2` 
        ON `c`.id=`d2`.contact_id
        LEFT JOIN `:prefixcontacts_bank` AS `b2` 
        ON `c`.id=`b2`.contact_id
        LEFT JOIN `:prefixcontacts_member` AS `me2` 
        ON `c`.id=`me2`.person_contact_id        
        
        WHERE 
        ((concat_ws(' ', c.firstname, c.name) LIKE :search
        OR concat_ws(' ', c.name, c.firstname) LIKE :search
        OR concat_ws(' ', c.owner_name, c.owner_firstname) LIKE :search
        OR concat_ws(' ', c.title, c.name, c.firstname) LIKE :search
        OR concat_ws(' ', c.title, c.firstname, c.name) LIKE :search)
        OR c.info LIKE :search
        OR`e2`.email LIKE :search 
        OR `p2`.phone LIKE :search 
        OR `a2`.street LIKE :search 
        OR `ci2`.name LIKE :search 
        OR `ci2`.postcode LIKE :search
        OR `ci2`.state LIKE :search  
        OR `m2`.messenger LIKE :search 
        OR `w2`.website LIKE :search 
        OR `n2`.body LIKE :search
        OR `d2`.date LIKE :search
        OR `b2`.name LIKE :search
        OR `b2`.bankcode LIKE :search
        OR `b2`.accountnumber LIKE :search
        OR `b2`.iban LIKE :search
        OR `b2`.bic LIKE :search
        OR `me2`.description LIKE :search
        )
    ";

    /**
     * Index action
     * 
     * 
     */
    public function indexAction ()
    {
                    
    }
    
    /**
     * show all Contacts
     *
     * @return void
     */
    public function allAction ()
    {
        $group_id = 0; 
        if (true === isset($this->params['url']['g'])) {
            $group_id = (int) $this->params['url']['g'];
        }            
        
        $search = '';
        if (true === isset($this->params['url']['s'])) {
            $search = $this->params['url']['s'];
        }
        $str_search = utf8_encode(trim(Ncw_Library_Sanitizer::escape($search)));            
        
        if ($this->request_handler->responseType() === 'javascript'
            || true === empty($search)) {
                
            if ($this->request_handler->responseType() === 'javascript') {
                $this->layout = 'default';
            }
            $this->view->pagination = true;
        
            $parent_contact_id = 0;
            if (true === isset($this->params['url']['p'])) {
                $parent_contact_id = (int) $this->params['url']['p'];
            }
            $type = 0;
            if (true === isset($this->params['url']['t'])) {
                $type = (int) $this->params['url']['t'];
            }            
        
            $this->view->arr_all_contacts = $contacts = $this->paginate(
                array($this->_searchConditions($group_id, $parent_contact_id, $type))
            );            
        } else {        
            $this->view->pagination = false;
            
            $sql = "
                SELECT `c`.id AS `id`,
                c.type AS `type`, 
                c.name AS `name`,
                c.title AS `title`,
                c.firstname AS `firstname`,
                c.owner_name AS `owner_name`, 
                c.owner_firstname AS `owner_firstname`,
                e.email AS `email`, 
                p.phone AS `phone`,
                me.contact_id AS `parent_id`,
                me.description AS `description`,
                co.name AS `parent_name`";
                
                $sql .= str_replace(':prefix', Ncw_Database::getConfig('prefix'), $this->_main_sql_code);

                if ($group_id > 1) {
                    $sql .= "\nAND gr.group_id = :group_id";
                }
                
                $sql .= "\nGROUP BY `c`.id             
                LIMIT 21";
            $sth = $this->Contact->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            
            $sth->execute(
                array(
                    ':search' => '%' . $str_search . '%',
                    ':group_id' => (int) $group_id,
                    ':prefix' => Ncw_Database::getConfig('prefix')
                )
            );
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            
            $contacts = new Ncw_ModelList();
            foreach ($result as $row) {
                $contact = new Contacts_Contact();
                $email = new Contacts_Email();
                $phone = new Contacts_Phone();
                $member = new Contacts_Member();
    
                $contact->data($row);
                $email->data($row);
                $phone->data($row);
                
                $member->setContactId($row['parent_id']);
                $member->setDescription($row['description']);
                $parent_contact = new Contacts_Contact();
                $parent_contact->setId($row['parent_id']);
                $parent_contact->setName($row['parent_name']);
                $member->addAssociatedModel($parent_contact);
                
                $contact->addAssociatedModel($email);
                $contact->addAssociatedModel($phone);
                $contact->addAssociatedModel($member);
                $contacts->addModel($contact);
            }        
            
            $this->view->arr_all_contacts = $contacts;              
        }        

        $permissions = array();
        foreach ($contacts as $contact) {
            $contact_id = $contact->getId();
            $permissions['/contacts/contact/objects/' . $contact_id] = $this->acl->check('/contacts/contact/objects/' . $contact_id);
        }
        $this->view->contact_permissions = $permissions;
        unset($permissions, $contact_id);    

        if (true === isset($str_search)) {
          $this->view->search_str = $str_search;
        } else {
            $this->view->search_str = '';
        }
        if (true === isset($group_id)) {
           $this->view->group_id = $group_id;
        } else {
            $this->view->group_id = '';
        }
        $this->loadModel('Group');
        $this->Group->setId($group_id);
        $this->view->group_name = $this->Group->readField('name');
        
        $this->view->permissions = array(
            '/contacts/group/edit' => $this->acl->check('/contacts/group/edit'),
        );    
    }

    /**
     * Search action
     */
    public function searchAction () 
    {
        $this->view = false;
        
        $group_id = 0;
        if (true === isset($this->params['url']['g'])) {
            $group_id = (int) $this->params['url']['g'];
        }
        $parent_contact_id = 0;
        if (true === isset($this->params['url']['p'])) {
            $parent_contact_id = (int) $this->params['url']['p'];
        }
        
        $sql = "
            SELECT count(1) AS num, c.id
            " . str_replace(':prefix', Ncw_Database::getConfig('prefix'), $this->_main_sql_code) . "            
            GROUP BY `c`.id 
            LIMIT 2        
            ";
        $sth = $this->Contact->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        
        $search = '';
        if (true === isset($this->params['url']['s'])) {
            $search = $this->params['url']['s'];
        }
        $str_search = utf8_encode(trim(Ncw_Library_Sanitizer::escape($search)));        
        
        $sth->execute(
            array(
                ':search' => '%' . $str_search . '%'
            )
        );
        $result = $sth->fetchAll();
        $contact_num = count($result);
        
        
        /*$contact_num = $this->Contact->fetch(
            'count',
            array(
                'conditions' => array($this->_searchConditions($group_id, $parent_contact_id))
            )
        );*/    
        if ($contact_num == 1) {
            $contact_id = $result[0]['id'];
            /*$contact = $this->Contact->fetch(
                'first',
                array(
                    'fields' => array('Contact.id'),
                    'conditions' => array($this->_searchConditions($group_id, $parent_contact_id))
                )
            );*/
            print '{"contact_id" : ' . $contact_id . '}';
        } else {
            print '{"contact_id" : 0}';
        }
    }

    /**
     * Bereite search conditions vor.
     * 
     * @param $group_id
     * @param $parent_contact_id
     * @param $type
     * 
     * @return string 
     */
    protected function _searchConditions ($group_id = 0, $parent_contact_id = 0, $type = 0)
    {
        $this->Contact->unbindModel('all');
        $this->Contact->bindModel(
            array(
                'has_one' => array(
                    'Phone' => array(
                        'fields' => array(
                            'Phone.phone',
                        ),
                        'join_condition' => 'Contact.id=Phone.contact_id && Phone.first = 1'
                    ),
                    'Email' => array(
                        'fields' => array(
                            'Email.email',
                        ),
                        'join_condition' => 'Contact.id=Email.contact_id && Email.first = 1'
                   ),
                   'Member' => array(
                        'fields' => array(
                            'Member.contact_id',
                            'Member.description',
                        ),
                        'join_condition' => 'Contact.id=Member.person_contact_id && Member.first = 1'
                   ), 
                   'Contacts_Contact' => array(
                       'fields' => array(
                            'Contacts_Contact.name',
                        ),                   
                        'join_condition' => 'Member.contact_id=Contacts_Contact.id'
                   ),                    
                )
            )
        );

        $search_conditions = '';
        $str_search = '';

        if ($this->request_handler->responseType() === 'javascript') {
            $this->layout = 'default';
        }

        if ($this->request_handler->responseType() === 'javascript'
            && true === isset($this->data['search'])
        ) {
            $arr_conditions = array();
            foreach ($this->data['search'] as $field => $value) {
                $arr_conditions[] = "" . $field . " LIKE '%" . trim(Ncw_Library_Sanitizer::escape($value)) . "%'";
            }
            $search_conditions = implode(' AND ' , $arr_conditions);
        } else {
            $search = '';
            if (true === isset($this->params['url']['s'])) {
                $search = $this->params['url']['s'];
            }
            $str_search = utf8_encode(trim(Ncw_Library_Sanitizer::escape($search)));
            $search_conditions = '(concat_ws(\' \', Contact.firstname, Contact.name, Contact.owner_name, Contact.owner_firstname) LIKE \'%' . $str_search . '%\'
            || concat_ws(\' \', Contact.name, Contact.firstname, Contact.owner_name, Contact.owner_firstname) LIKE \'%' . $str_search . '%\')';
            
        }        
        
        switch ((int) $type) {
            case 1:
                $search_conditions .= ' AND Contact.type = "private" ';
                break;
            case 2:
                $search_conditions .= ' AND Contact.type = "business" ';
                break;
        }
        
        
        if ($group_id > 1) {
            // search in one group
            $this->Contact->bindModel(
                array(
                    'has_one' => array('GroupContact')
                )
            );
            $search_conditions .= ' AND GroupContact.group_id = ' . $group_id;
        }

        if ($parent_contact_id > 0) {
            /*$this->Contact->bindModel(
                array(
                    'has_one' => array(                   
                        'Member' => array(
                            'join_condition' => 'Contact.id=Member.person_contact_id'
                           )
                   )
                )
            );*/
            
            $search_conditions .= ' && Member.contact_id = ' . $parent_contact_id;
        }
        return $search_conditions;    
    }

    /**
     * Search dialog action
     *
     */
    public function searchDialogAction ()
    {
        $this->layout = 'default';
    
        $list = array();
        foreach ($this->_allGroups() as $group) {
            $list[str_repeat('--', $group['level']) . ' ' . $group['name']] = $group['id'];
        }    
        $this->view->arr_groups = $list;    
    }

    /**
     * Reads a specific contact
     *
     * @param int $contact_id
     *
     * @return void
     */
    public function readAction ($contact_id)
    {
        if ($this->request_handler->responseType() === null) {
            $this->_stop();
        }

        $this->Contact->setId($contact_id);
        $this->Contact->read();

        $emails = array();
        foreach ($this->Contact->Email as $item) {
            $emails[] = $item->data();
        }
        $phones = array();
        foreach ($this->Contact->Phone as $item) {
            $phones[] = $item->data();
        }
        $messengers = array();
        foreach ($this->Contact->Messenger as $item) {
            $messengers[] = $item->data();
        }
        $address = array();
        foreach ($this->Contact->Address as $item) {
            $address[] = array(
                'address' => $item->data(),
                'city' => $item->City->data(),
                //'country' => $item->City->Country->data(),
            );
        }
        $dates = array();
        foreach ($this->Contact->Date as $item) {
            $dates[] = $item->data();
        }
        $websites = array();
        foreach ($this->Contact->Website as $item) {
            $websites[] = $item->data();
        }
        $banks = array();
        foreach ($this->Contact->Bank as $item) {
            $banks[] = $item->data();
        }
        $data = array(
           'contact' => array(
               $this->Contact->data()
           ),
           'email' => array(
                $emails
           ),
           'phone' => array(
                $phones
           ),
           'messenger' => array(
                $messengers
           ),
           'bank' => array(
                $banks
           ),
           'address' => array(
                $address
           ),
           'date' => array(
                $dates
           ),
           'website' => array(
                $websites
           )
        );

        $this->view->data = $data;
    }

    /**
     * New contact
     *
     * @param string $type the contact type
     * @param int $related_company
     *
     * @return void
     */
    public function newAction ($type = 'private', $related_company_id = 0)
    {
        if ($type == 'private') {
            $this->Contact->setType('private');
            $this->view->contact_type = 'private';
        } else {
            $this->Contact->setType('business');
            $this->view->contact_type = 'business';
        }

        // city
        $this->loadModel('City');
        $this->City->setCountryId(81);
        $this->data['City'] = $this->City->data();

        // country
        $this->loadModel('Country');
        $this->Country->unbindModel('all');
        $countries = $this->Country->fetch(
            'all', array(
                'fields' => array('Country.id', 'Country.code', 'Country.name')
            )
        );

        include 'I18Nv2/Country.php';
        $I18Nv2_country = new I18Nv2_Country(
            substr(Ncw_Configure::read('App.language'), 0, 2),
            Ncw_Configure::read('App.encoding')
        );

        $arr_countries = array();
        foreach ($countries as $country) {
            $translated_country_name = $I18Nv2_country->getName(strtolower($country->getCode()));
            $arr_countries[$translated_country_name] = $country->getId();
        }
        $this->view->countries = $arr_countries;

        // groups
         $list = array();
        foreach ($this->_allGroups() as $group) {
            $list[str_repeat('--', $group['level']) . ' ' . $group['name']] = $group['id'];
        }    
        $this->view->arr_groups = $list;
        
        // related company
        if ($related_company_id > 0) {
            $this->view->has_related_company = true;
            $this->_readRelatedCompany($related_company_id);
        } else {
            $this->view->has_related_company = false;
        }
        
        // preview image
        if ($this->Contact->getType() == 'business') {
            $image = 'building.png';
        } else {
            $image = 'user.png';
        }
        $this->view->preview_image = $this->base . '/themes/default/web/images/icons/16px/' . $image;        
    }

    /**
     * 
     */
    protected function _readRelatedCompany ($related_company_id)
    {
        $this->Contact->unbindModel('all');
        $this->Contact->bindModel(
            array(
                'has_one' => array(
                    'Phone' => array(
                        'fields' => array(
                            'Phone.phone',
                        ),
                        'join_condition' => 'Contact.id=Phone.contact_id && Phone.first = 1'
                    ),
                    'Email' => array(
                        'fields' => array(
                            'Email.phone',
                        ),
                        'join_condition' => 'Contact.id=Email.contact_id && Email.first = 1'
                    ),
                    'Address' => array(
                        'fields' => array(
                            'Address.city_id',
                            'Address.street',
                        ),
                        'join_condition' => 'Contact.id=Address.contact_id && Address.first = 1'
                    ),
                    'City' => array(
                        'fields' => array(
                            'City.country_id',        
                            'City.state',
                            'City.postcode',
                            'City.name',
                        ),
                        'join_condition' => 'Address.city_id=City.id'
                    )                    
                )
            )
        );
        $related_company = $this->Contact->fetch(
            'first',
            array(
                'fields' => array(
                    'Contact.name',
                    'Phone.phone',
                    'Email.email',
                    'Address.city_id',
                    'Address.street',        
                    'City.country_id',        
                    'City.state',
                    'City.postcode',
                    'City.name',            
                ),
                'conditions' => array(
                    'Contact.id' => $related_company_id
                )
            )
        );
        
        $this->data['Member'][0] = array();
        $this->data['Member'][0]['contact_id'] = $related_company_id;
        
        $phone = $related_company->Phone->getPhone();
        if (false === empty($phone)) {
            $this->view->related_company_phone = true;
            $this->data['Phone'][0]['phone'] = $phone;
            $this->data['Phone'][0]['location'] = 'work';
        } else {
            $this->view->related_company_phone = false;
        }

        $email = $related_company->Email->getEmail();
        if (false === empty($email)) {
            $this->view->related_company_email = true;
            $this->data['Email'][0]['email'] = $email;
            $this->data['Email'][0]['location'] = 'work';    
        } else {
            $this->view->related_company_email = false;
        }
        
        $address = $related_company->Address->getCityId();
        if (false === empty($address)) {
            $this->view->related_company_address = true;
            $this->data['Address'][0] = $related_company->Address->data();
            $this->data['Address'][0]['location'] = 'work';    
            
            $this->data['City'][0] = $related_company->City->data();    
        } else {
            $this->view->related_company_address = false;
        }        

        $this->view->related_company_name = $related_company->getName();
        
        $this->view->locations = array(
            T_('Work') => 'work',
            T_('Home') => 'home',
            T_('Other') => 'other',
        );        
        $this->view->phone_locations = array(
            T_('Work') => 'work',
            T_('Mobile') => 'mobile',
            T_('Fax') => 'fax',
            T_('Pager') => 'pager',
            T_('Home') => 'home',
            T_('Skype') => 'skype',
            T_('Other') => 'other',
        );                
    }

    /**
     * Save the contact
     * 
     * @return void
     */
    public function saveAction ()
    {
        $this->view = false;

        $return = array('return_value' => true);        

        if (true === isset($this->data['Contact'])) {
            
            $this->loadModel('Phone');
            $this->loadModel('Email');
            $this->loadModel('Messenger');
            $this->loadModel('Website');
            $this->loadModel('Address');
            $this->loadModel('Bank');
            $this->loadModel('Member');
            
            $related_data = array('Phone', 'Email', 'Messenger', 'Website', 'Address', 'Bank');

            // contact data
            if (true === isset($this->data['Contact']['title'])
                && $this->data['Contact']['title'] == T_('Title')
            ) {
                $this->data['Contact']['title'] = '';
            }
            
            if (true === isset($this->data['Contact']['firstname'])
                && $this->data['Contact']['firstname'] == T_('Firstname')
            ) {
                $this->data['Contact']['firstname'] = '';
            }        
            
            if (true === isset($this->data['Contact']['name'])
                && $this->data['Contact']['name'] == T_('Lastname')
            ) {
                $this->data['Contact']['name'] = '';
            }

            $this->Contact->data($this->data['Contact']);
            
            // validations
            $return['invalid_fields'] = array();
            if (false === $this->Contact->validate()) {
                $return['return_value'] = false;
                $return['invalid_fields'] = $this->Contact->invalidFields();
            }
            
            $invalid_fields = array();    
            foreach ($related_data as $data_name) {
                if (true === isset($this->data[$data_name])) {
                    foreach ($this->data[$data_name] as $count => $item) {
                        $count = (int) $count;
                        $this->{$data_name}->create();
                        if ($data_name == 'Address') {
                            $city_id = $this->_city($this->data['City'][$count]);
                            if (true === is_integer($city_id)) {
                                $this->data['Address'][$count]['city_id'] = $item['city_id'] = $city_id;
                            } else {
                                $invalid_fields['City']['n_' . $count] = $this->City->invalidFields();
                            }                            
                        }
                        $this->{$data_name}->data($item);
                        $this->{$data_name}->setContactId(1);                        
                        if (false === $this->{$data_name}->validate()) {
                            $return['return_value'] = false;
                            $invalid_fields[$data_name]['n_' . $count] = $this->{$data_name}->invalidFields();
                        }
                    }
                }
            }
            
            $return['invalid_fields'] = array_merge(
                $return['invalid_fields'],
                $invalid_fields
            );

            // if save redirect
            if (true === $return['return_value']) {
                $this->Contact->save();
                $return['contact_id'] = $contact_id = $this->Contact->getId();
                
                $name = $this->Contact->getName();
                if ($this->Contact->getType() == 'private') {
                    $name = $this->Contact->getFirstname() . ' ' . $name;
                }
                /*$this->acl->addACO(
                    '/contacts/contact/objects/' . $contact_id,
                     $name . ' (' . $this->Contact->getType() . ')'
                );*/                
                
                // save related data
                foreach ($related_data as $data_name) {
                    if (true === isset($this->data[$data_name])) {
                        $first = true;
                        foreach ($this->data[$data_name] as $item) {
                            $this->{$data_name}->create();
                            $this->{$data_name}->data($item);
                            $this->{$data_name}->setContactId($contact_id);
                            if (false === $first) {
                                $this->{$data_name}->setFirst(false);
                            }                        
                            if (true === (boolean) $this->{$data_name}->getFirst()) {
                                $first = false;    
                            }                            
                            $this->{$data_name}->save();                    
                        }
                    }
                }
                
                // Related Companies
                if (true === isset($this->data['Member'])) {
                    foreach ($this->data['Member'] as $member) {
                        $this->Member->create();
                        $this->Member->data($member);
                        $this->Member->setPersonContactId($this->Contact->getId());
                        $this->Member->save();
                    }
                }    
                            
            }
        }

        print json_encode($return);
    }

    /**
     * Leitet auf groups/all/?c_id=<contact_id> weiter
     * 
     * @param $contact_id
     */
    public function gotoAction ($contact_id)
    {
        $this->redirect(
            array(
                'controller' => 'group',
                'action' => 'all',
                '?' => 'c_id=' . $contact_id
            )
        );
    }

    /**
     * Leitet auf groups/all/?c_id=<contact_id> weiter
     * 
     * @param $contact_id
     */
    public function editAction ($contact_id)
    {
        $this->redirect(
            array(
                'controller' => 'group',
                'action' => 'all',
                '?' => 'c_id=' . $contact_id
            )
        );
    }

    /**
     * Edit contact
     *
     * @param int $contact_id the contact id
     *
     * @return void
     */
    public function editContactAction ($contact_id)
    {
        
        $this->_prepareEdit(
            $contact_id,
            array(),
            true
        );        

        // phones
        $num = count($this->Contact->Phone);
        for ($count = 0; $count < $num; $count++) {
            $this->data['Phone'][$count] = $this->Contact->Phone[$count]->data();
        }
        $this->view->phone_num = $num;

        // emails
        $num = count($this->Contact->Email);
        for ($count = 0; $count < $num; $count++) {
            $this->data['Email'][$count] = $this->Contact->Email[$count]->data();
        }
        $this->view->email_num = $num;

        // messengers
        $num = count($this->Contact->Messenger);
        for ($count = 0; $count < $num; $count++) {
            $this->data['Messenger'][$count] = $this->Contact->Messenger[$count]->data();
        }
        $this->view->messenger_num = $num;

        // websites
        $num = count($this->Contact->Website);
        for ($count = 0; $count < $num; $count++) {
            $this->data['Website'][$count] = $this->Contact->Website[$count]->data();
        }
        $this->view->website_num = $num;

        // banks
        $num = count($this->Contact->Bank);
        for ($count = 0; $count < $num; $count++) {
            $this->data['Bank'][$count] = $this->Contact->Bank[$count]->data();
        }
        $this->view->bank_num = $num;

        // addresses
        $this->loadModel('City');
        $num = count($this->Contact->Address);
        for ($count = 0; $count < $num; $count++) {
            $this->data['Address'][$count] = $this->Contact->Address[$count]->data();

            if ($this->Contact->Address[$count]->getCityId() > 0) {
                $this->data['City'][$count] = $this->Contact->Address[$count]->City->data();
            }
        }
        $this->view->address_num = $num;

        // related companies
        if ($this->Contact->getType() == 'private') {
            $this->loadModel('Member');
            $this->Member->unbindModel('all');
            $this->Member->bindModel(
                array(
                    'belongs_to' => array(
                        'Contact'
                    )
                )
            );
            $related_companies = $this->Member->fetch(
                'all',
                array(
                    'fields' => array(
                        'Member.id',
                        'Member.contact_id',
                        'Member.person_contact_id',
                        'Member.description',
                        'Member.first',
                        'Contact.name',
                    ),
                    'conditions' => array(
                        'Member.person_contact_id' => $this->Contact->getId()
                    )
                )
            );
            $num = count($related_companies);
            $arr_related_companies = array();
            for ($count = 0; $count < $num; $count++) {
                $this->data['Member'][$count] = $related_companies[$count]->data();
                $arr_related_companies[$count] = $related_companies[$count]->Contact->getName();
            }
            $this->view->arr_related_companies = $arr_related_companies;
            $this->view->related_companies_num = $num;
        }

        // countries
        $obj_country = new Contacts_Country();
        $obj_country->unbindModel('all');
        $countries = $obj_country->fetch(
            'all', array(
                'fields' => array(
                    'Country.id',
                    'Country.code',
                    'Country.name'
                )
            )
        );
        
        include 'I18Nv2/Country.php';
        $I18Nv2_country = new I18Nv2_Country(
            substr(Ncw_Configure::read('App.language'), 0, 2),
            Ncw_Configure::read('App.encoding')
        );
        $arr_countries = array();
        foreach ($countries as $country) {
            $translated_country_name = $I18Nv2_country->getName(strtolower($country->getCode()));
            $arr_countries[$translated_country_name] = $country->getId();
        }
        $this->view->countries = $arr_countries;

        // preview image
        if ($this->Contact->getType() == 'business') {
            $image = 'building.png';
        } else {
            $image = 'user.png';
        }
        $image = $this->base . '/themes/default/web/images/icons/16px/' . $image;        
        if ($this->Contact->getFileId() > 0) {
            $image = Files_File::previewImageUrl($this->Contact->getFileId(), 96);
        }
        $this->view->preview_image = $image;

        // locations
        $this->view->locations = array(
            T_('Work') => 'work',
            T_('Home') => 'home',
            T_('Other') => 'other',
        );
        $this->view->phone_locations = array(
            T_('Work') => 'work',
            T_('Mobile') => 'mobile',
            T_('Fax') => 'fax',
            T_('Pager') => 'pager',
            T_('Home') => 'home',
            T_('Skype') => 'skype',
            T_('Other') => 'other',
        );
        $this->view->messenger_locations = array(
            T_('Work') => 'work',
            T_('Personal') => 'personal',
            T_('Other') => 'other',
        );
        $this->view->bank_locations = array(
            T_('Business') => 'work',
            T_('Private') => 'home',
            T_('Other') => 'other',
        );
        $this->view->messenger_protocols = array(
            T_('AIM') => 'aim',
            T_('MSN') => 'msn',
            T_('ICQ') => 'icq',
            T_('Habber') => 'habber',
            T_('Yahoo') => 'yahoo',
            T_('Skype') => 'skype',
            T_('QQ') => 'qq',
            T_('Sametime') => 'sametime',
            T_('Gadu-Gadu') => 'gadu-gadu',
            T_('Google Talk') => 'google talk',
            T_('Other') => 'other'
        );
        
        // permissions
        $this->view->permissions = array(
            '/contacts/contact/delete' => $this->acl->check('/contacts/contact/delete'),
        );        
    }

    /**
     * Action zum Bearbeiten der Kontakt-Info
     * 
     * @params int $contact_id
     */
    public function editInfoAction ($contact_id)
    {
        $this->_prepareEdit(
            $contact_id        
        );
    }

    /**
     * Action zum Bearbeiten der Daten welche zu einem Kontakt zugeordnet wurden.
     * 
     * @params int $contact_id
     */
    public function editDatesAction ($contact_id)
    {
        $this->_prepareEdit(
            $contact_id,
            array('Date')        
        );

        // dates
        $num = count($this->Contact->Date);
        for ($count = 0; $count < $num; $count++) {
            $this->data['Date'][$count] = $this->Contact->Date[$count]->data();
        }
        $this->view->dates = $num;        
        
        $this->view->date_descriptions = array(
            T_('Birthday') => 'birthday',
            T_('Anniversary') => 'anniversary',
            T_('First met') => 'first met',
            T_('Hired') => 'hired',
            T_('Fired') => 'fired',
            T_('Other') => 'other'
        );
    }

    /**
     * Action zum Bearbeiten der Gruppen welche zu einem Kontakt zugeordnet wurden.
     * 
     * @params int $contact_id
     */
    public function editGroupsAction ($contact_id)
    {
        $this->_prepareEdit(
            $contact_id,
            array('GroupContact')        
        );    
        
        // contact groups
        $this->view->arr_all_contact_groups = $assigend_groups = $this->Contact->GroupContact;
        
        if (true === isset($assigend_groups[0])) {
            $not_this_groups = array();
            foreach ($assigend_groups as $assigend_group) {
                $not_this_groups[] = $assigend_group->Group->getId();
            }
            $groups = $this->_allGroups(1, $not_this_groups);
        } else {
            $groups = $this->_allGroups(1);
        }
        
        $list = array();
        foreach ($groups as $group) {
            $list[str_repeat('--', $group['level']) . ' ' . $group['name']] = $group['id'];
        }
        
        $this->view->arr_options = $list;
    }

    /**
     * Bereitet die Edit Actions vor.
     * 
     * @param int $contact_id
     * @param array $has_many
     * @param boolean $no_unbind
     */
    protected function _prepareEdit ($contact_id, $has_many = array(), $no_unbind = false)
    {
        $this->Contact->setId((int) $contact_id);
        
        if (false === $no_unbind) {
            $this->Contact->unbindModel('all');
            $this->Contact->bindModel(
                array(
                    'has_many' => $has_many
                )
            );
        }
        $this->Contact->read();
        
        $this->data['Contact'] = $this->Contact->data();
        $this->view->contact = $this->Contact->data();
        $this->view->contact_id = $this->Contact->getId();
        $this->view->contact_type = $this->Contact->getType();            
    }

    /**
     * Update private and business contacts
     *
     * @param $id
     *
     * @return void
     */
    public function updateAction ()
    {
        $this->view = false;

        if (true === isset($this->data['Contact']['update'])) {
            switch ($this->data['Contact']['update']) {
                case 'contact':
                    $this->_updateContact();
                    break;                    
                case 'info':
                    $this->_updateInfo();
                    break;                
                case 'dates':
                    $this->_updateDates();
                    break;
            }
            return;
        }

    }

    protected function _updateContact ()
    {
        $return = array('return_value' => true);    
        
        if (true === isset($this->data['Contact'])) {
                
            $this->loadModel('Phone');
            $this->loadModel('Email');
            $this->loadModel('Messenger');
            $this->loadModel('Website');
            $this->loadModel('Address');
            $this->loadModel('Bank');
            $this->loadModel('Member');
            
            $related_data = array('Phone', 'Email', 'Messenger', 'Website', 'Address', 'Bank');            
            
            $this->Contact->data($this->data['Contact']);

            // validations
            $return['invalid_fields'] = array();            
            if (false === $this->Contact->validate()) {
                $return['return_value'] = false;
                $return['invalid_fields'] = $this->Contact->invalidFields();
            }
            
            $invalid_fields = array();
            foreach ($related_data as $data_name) {
                if (true === isset($this->data[$data_name])) {
                    foreach ($this->data[$data_name] as $count => $item) {
                        $count = (int) $count;
                        $this->{$data_name}->create();
                        if ($data_name == 'Address') {
                            $city_id = $this->_city($this->data['City'][$count]);
                            if (true === is_integer($city_id)) {
                                $this->data['Address'][$count]['city_id'] = $item['city_id'] = $city_id;
                            } else {
                                $invalid_fields['City']['n_' . $count] = $this->City->invalidFields();
                            }                                
                        }
                        $this->{$data_name}->data($item);
                        $this->{$data_name}->setContactId(1);                        
                        if (false === $this->{$data_name}->validate()) {
                            $return['return_value'] = false;
                            $invalid_fields[$data_name]['n_' . $count] = $this->{$data_name}->invalidFields();
                        }
                    }
                }
            }
            
            $return['invalid_fields'] = array_merge(
                $return['invalid_fields'],
                $invalid_fields
            );

            // if save redirect
            if (true === $return['return_value']) {
                $this->Contact->saveFields(
                    array(
                        'file_id',
                        'name',
                        'firstname',
                        'owner_name',
                        'owner_firstname',
                        'gender',
                        'title'    
                    )
                );                
                $contact_id = $this->Contact->getId();

                // save related data
                foreach ($related_data as $data_name) {
                    $this->loadModel($data_name);
                    $this->{$data_name}->deleteAll(
                        array(
                            $data_name . '.contact_id' => $contact_id
                        )
                    );                    
                    if (true === isset($this->data[$data_name])) {
                        $first = true;
                        foreach ($this->data[$data_name] as $item) {
                            $this->{$data_name}->create();                        
                            $this->{$data_name}->data($item);
                            $this->{$data_name}->setContactId($contact_id);
                            if (false === $first) {
                                $this->{$data_name}->setFirst(false);
                            }                        
                            if (true === (boolean) $this->{$data_name}->getFirst()) {
                                $first = false;    
                            }                            
                            $this->{$data_name}->save();                    
                        }
                    }
                }
                
                // Related Companies
                $this->Member->deleteAll(
                    array(
                        'Member.person_contact_id' => $this->Contact->getId()
                    )
                );                
                if (true === isset($this->data['Member'])) {
                    $first = true;
                    foreach ($this->data['Member'] as $member) {
                        $this->Member->create();
                        $this->Member->data($member);
                        $this->Member->setPersonContactId($this->Contact->getId());
                        if (false === $first) {
                            $this->Member->setFirst(false);
                        }                        
                        if (true === (boolean) $this->Member->getFirst()) {
                            $first = false;    
                        }                            
                        $this->Member->save();
                    }
                }                    
            }
        }        

        print json_encode($return);
    }

    /**
     * Aktualisiert die Kontakt-Info (Dates)
     */
    protected function _updateInfo ()
    {
        $return_value = 'false';

        if (true === isset($this->data['Contact'])) {
            $this->Contact->data($this->data['Contact']);
            if (true === $this->Contact->saveField('info')) {
                $return_value = 'true';
            }
        }
        
        echo '{"return_value" : ' . $return_value . '}';
    }

    /**
     * Aktualisiert die Kontakt-Daten (Dates)
     */
    protected function _updateDates ()
    {
        $return = array('return_value' => true);

        if (true === isset($this->data['Contact'])) {
            $this->Contact->data($this->data['Contact']);
        
            $this->loadModel('Date');
            $this->Date->deleteAll(
                array(
                    'Date.contact_id' => $this->Contact->getId()
                )
            );
            
            if (true === isset($this->data['Date'])) {
                
                foreach ($this->data['Date'] as $count => $date) {
                    $this->Date->create();
                    $this->Date->data($date);
                    $this->Date->setContactId($this->Contact->getId());
                    if (false === $this->Date->validate()) {
                        $return['return_value'] = false;
                        $return['invalid_fields']['Date']['n_' . $count] = $this->Date->invalidFields();
                    }
                }
                
                $first = true;
                foreach ($this->data['Date'] as $date) {
                    $this->Date->create();
                    $this->Date->data($date);
                    $this->Date->setContactId($this->Contact->getId());
                    if (false === $first) {
                        $this->Date->setFirst(false);
                    }                        
                    if (true === (boolean) $this->Date->getFirst()) {
                        $first = false;    
                    }
                    $this->Date->save();
                }
            }
        }
        
        print json_encode($return);
    }

    /**
     * contact preview
     *
     * @param int $id
     *
     * @return void
     */
    public function showContactAction ($contact_id)
    {
        $contact_id = (int) $contact_id;
        if ($contact_id <= 0) {
            return;
        }
        
        if (false === $this->acl->check('/contacts/contact/objects/' . $contact_id)) {
            $this->view = false;
            return;
        }        
        
        $this->Contact->setId($contact_id);
        $this->Contact->unbindModel(
            array(
                'has_many' => array('Note')
            )
        );
        $this->Contact->read();

        // contact
        $this->view->contact = $this->Contact->data();

        $image = false;
        if ($this->Contact->getFileId() > 0) {
            $image = Files_File::imageUrl($this->Contact->getFileId(), 96);
        } else {
            if ($this->Contact->getType() == 'business') {
                $image = 'building.png';
            } else {
                $image = 'user.png';
            }
            $image = $this->base . '/themes/default/web/images/icons/16px/' . $image;
        }
        $this->view->image = $image;

        $this->view->parents = $this->_readParents($this->Contact->getId());

        // notes
        $this->paginate['Note'] = array(
            'order' => array('Note.created' => 'desc'),
            'limit' => 5,
        );     
        $this->view->notes = $this->paginate(
            'Note',
            array(
                'Note.contact_id' => $this->Contact->getId()
            ),
            array(
                
            )            
        );

        // phones
        $items = array();
        $num = count($this->Contact->Phone);
        for ($count = 0; $count < $num; $count++) {
            $items[] = $this->Contact->Phone[$count]->data();
        }
        $this->view->phones = $items;

        // emails
        $items = array();
        $num = count($this->Contact->Email);
        for ($count = 0; $count < $num; $count++) {
            $items[] = $this->Contact->Email[$count]->data();
        }
        $this->view->emails = $items;

        // messengers
        $items = array();
        $num = count($this->Contact->Messenger);
        for ($count = 0; $count < $num; $count++) {
            $items[] = $this->Contact->Messenger[$count]->data();
        }
        $this->view->messengers = $items;

        // websites
        $items = array();
        $num = count($this->Contact->Website);
        for ($count = 0; $count < $num; $count++) {
            $items[] = $this->Contact->Website[$count]->data();
        }
        $this->view->websites = $items;

        // bank account
        $items = array();
        $num = count($this->Contact->Bank);
        for ($count = 0; $count < $num; $count++) {
            $items[] = $this->Contact->Bank[$count]->data();
        }
        $this->view->banks = $items;

        include 'I18Nv2/Country.php';
        $I18Nv2_country = new I18Nv2_Country(
            substr(Ncw_Configure::read('App.language'), 0, 2),
            Ncw_Configure::read('App.encoding')
        );

        // addresses
        $items = array();
        $this->loadModel('City');
        $num = count($this->Contact->Address);
        for ($count = 0; $count < $num; $count++) {
            if ($this->Contact->Address[$count]->getCityId() > 0) {
                $this->City->setId($this->Contact->Address[$count]->getCityId());
                $this->City->read();
                $city = $this->City->data();
            }

            $items[] = array(
                'address' => $this->Contact->Address[$count]->data(),
                'city' => $city,
                'country' => $I18Nv2_country->getName(strtolower($this->City->Country->getCode())),
            );
        }
        $this->view->addresses = $items;

        // dates
        $items = array();
        $num = count($this->Contact->Date);
        for ($count = 0; $count < $num; $count++) {
            $date = explode('-', $this->Contact->Date[$count]->getDate());
            $date = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
            $items[] = array(
                'description' => $this->Contact->Date[$count]->getDescription(),
                'date' => $date,
            );
        }
        $this->view->dates = $items;

        // files
        $items = array();
        $num = count($this->Contact->File);
        for ($count = 0; $count < $num; $count++) {
            $items[] = array(
                'file' => $this->Contact->File[$count]->data(),
                'real_file' => $this->Contact->File[$count]->Files_File->data(),
            );
        }
        $this->view->files = $items;

        // groups
        $items = array();
        $num = count($this->Contact->GroupContact);
        for ($count = 0; $count < $num; $count++) {
            $items[] = $this->Contact->GroupContact[$count]->Group->data();
        }
        $this->view->groups = $items;

        // Member
        if ($this->Contact->getType() == 'business') {
            $this->loadModel('Member');
            $this->Member->bindModel(
                array(
                    'belongs_to' => array(
                        'Contact' => array(
                            'foreign_key' => 'person_contact_id'
                        ),
                        'Email' => array(
                            'join_condition' => 'Contact.id=Email.contact_id && Email.first=1'
                        ),
                        'Phone' => array(
                            'join_condition' => 'Contact.id=Phone.contact_id && Phone.first=1'
                        ),
                    ),
                )
            );
            $this->paginate['Member'] = array(
                'fields' => array(
                    'Member.id',
                    'Member.description',
                    'Contact.id',
                    'Contact.name',
                    'Contact.firstname',
                    'Contact.title',
                    'Contact.created',
                    'Phone.phone',
                    'Email.email',
                ),
                'order' => array('Contact.name' => 'asc'),            
                'limit' => 25,
            );
            $this->view->members = $this->paginate(
                'Member',
                array(
                    'Member.contact_id' => $this->Contact->getId()
                )
            );        
        }
        
        // update recently opened contacts
        $this->loadModel('RecentlyOpened');
        $this->RecentlyOpened->addContact($this->Contact->getId());
         
        // favorites
        $this->loadModel('Favorite');
        $user = Ncw_Components_Session::readInAll('user');
        $is_favorite = $this->Favorite->fetch(
            'first',
            array(
                'fields' => array('Favorite.id'),
                'conditions' => array(
                    'Favorite.user_id' => $user['id'],
                    'Favorite.contact_id' => $this->Contact->getId(),
                )
            )
        );
        if (false !== $is_favorite) {
            $this->view->favorite_id = $is_favorite->getId();
            $this->view->is_favorite = true;
        } else {
            $this->view->is_favorite = false;
        }
         
        // locations
        $this->view->locations = array(
            'work' => T_('Work'),
            'home' => T_('Home'),
            'other' => T_('Other'),
        );
        // phone locations
        $this->view->phone_locations = array(
            'work' => T_('Work'),
            'mobile' => T_('Mobile'),
            'fax' => T_('Fax'),
            'pager' => T_('Pager'),
            'home' => T_('Home'),
            'skype' => T_('Skype'),
            'other' => T_('Other'),
        );
        // bank locations
        $this->view->bank_locations = array(
            'work' => T_('Business'),
            'home' => T_('Private'),
            'other' => T_('Other'),
        );
        // messenger locations
        $this->view->messenger_locations = array(
            'work' => T_('Work'),
            'personal' => T_('Personal'),
            'other' => T_('Other'),
        );
        $this->view->messenger_protocols = array(
            'aim' => T_('AIM'),
            'msn' => T_('MSN'),
            'icq' => T_('ICQ'),
            'habber' => T_('Habber'),
            'yahoo' => T_('Yahoo'),
            'skype' => T_('Skype'),
            'qq' => T_('QQ'),
            'sametime' => T_('Sametime'),
            'gadu-gadu' => T_('Gadu-Gadu'),
            'google talk' => T_('Google Talk'),
            'other' => T_('Other'),
        );
        $this->view->date_descriptions = array(
            'birthday' => T_('Birthday'),
            'anniversary' => T_('Anniversary'),
            'first met' => T_('First met'),
            'hired' => T_('Hired'),
            'fired' => T_('Fired'),
            'other' => T_('Other'),
        );
        
        // permissions
        $this->view->permissions = array(
            '/contacts/contact/edit' => $this->acl->check('/contacts/contact/edit'),
            '/contacts/contact/file' => $this->acl->check('/contacts/contact/file'),
            '/contacts/contact/note' => $this->acl->check('/contacts/contact/note'),
        );
    }

    /**
     * Recently opened contacts
     * 
     */
    public function recentlyOpenedAction ()
    {
        // recently opened contacts
        $user = Ncw_Components_Session::readInAll('user');
        $this->loadModel('Files_File');
        $this->loadModel('RecentlyOpened');
        $recently_opened_contacts = $this->RecentlyOpened->fetch(
            'all',
            array(
                'conditions' => array(
                    'RecentlyOpened.user_id' => $user['id'],
                ),
                'order' => array(
                    'RecentlyOpened.created' => 'desc'
                )
            )
        );
        $arr_recently_opened = array();
        foreach ($recently_opened_contacts as $contact) {
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
            $arr_recently_opened[] = array(
                'id' => $this->Contact->getId(),
                'full_name' => $this->Contact->fullName(),
                'image' => $image
            );
        }
        $this->view->recently_opened_contacts = $arr_recently_opened;        
    }

    /**
     * Recently added contacts
     * 
     */
    public function recentlyAddedAction ()
    {
        // recently opened contacts
        $user = Ncw_Components_Session::readInAll('user');
        $this->loadModel('Files_File');
        $recently_added_contacts = $this->Contact->fetch(
            'all',
            array(
                'order' => array(
                    'Contact.created' => 'desc'
                ),
                'limit' => '5'
            )
        );
        $arr_recently_added = array();
        foreach ($recently_added_contacts as $contact) {
            $this->Contact->data($contact->data());
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
            $arr_recently_added[] = array(
                'id' => $this->Contact->getId(),
                'full_name' => $this->Contact->fullName(),
                'image' => $image
            );
        }
        $this->view->recently_added_contacts = $arr_recently_added;        
    }

    /**
     * Delete contact
     *
     */
    public function deleteAction ($contact_id)
    {
        $this->view = false;
        $contact_id = (int) $contact_id;
    
        $this->Contact->setId($contact_id);
        $contact_type = $this->Contact->readField('type');
        if ($contact_type == 'private') {
            $this->Contact->bindModel(
                array(
                    'has_many' => array(
                        'Member' => array(
                            'foreign_key' => 'person_contact_id'
                        )
                    )
                )
            );
        } else {
            $this->Contact->bindModel(
                array(
                    'has_many' => array(
                        'Member',
                    )
                )
            );
        }
        //$this->acl->removeACO('/contacts/contact/objects/' . $contact_id);
        $this->Contact->delete();

        print '{"return_value" : true}';
    }

    /**
     * add contact to group
     *
     * @param int $id the contact id
     * @param int $group_id the group id
     *
     * @return void
     */
    public function addContactgroupAction($id, $group_id)
    {
        $this->view = false;

        $this->loadModel('GroupContact');
        $this->GroupContact->setContactId($id);
        $this->GroupContact->setGroupId($group_id);
        $this->GroupContact->save();

        $this->loadModel('Group');
        $this->Group->setId($group_id);
        $name = $this->Group->readField('name');

        print '{"return_value" : true, "contactgroup" : { "id" : "' . $this->GroupContact->getId() . '" , "name" : "' . $name . '" } }';
    }

    /**
     * Delete group_contact
     *
     * @param int $id the contact id
     *
     * @return void
     */
    public function removeContactgroupAction ($id)
    {
        $this->view = false;

        $this->loadModel('GroupContact');
        $this->GroupContact->setId($id);
        $this->GroupContact->delete();

        print '{"return_value" : true}';
    }

    /**
     * add a contact to contact
     *
     * @param int $id the contact id
     * @param int $child-contact_id
     *
     * @return void
     */
    public function addMemberAction($person_contact_id, $parent_contact_id)
    {
        $this->view = false;

        $person_contact_id = (int) $person_contact_id;
        $parent_contact_id = (int) $parent_contact_id;

        $this->loadModel('Member');
        
        $first_member = $this->Member->fetch(
            'first',
            array(
                'conditions' => array(
                    'Member.person_contact_id' => $person_contact_id,
                    'Member.first' => 1,
                )
            )
        );
        $first = true;
        if (false !== $first_member) {
            $first = false;
        }
        
        $this->Member->setContactId($parent_contact_id);
        $this->Member->setPersonContactId($person_contact_id);
        $this->Member->setFirst($first);
        $this->Member->save();

        print '{"return_value" : true }';
    }

    /**
     * delete Member
     *
     * @param int $id
     *
     * @return void
     */
    public function removeMemberAction($id)
    {
        $this->view = false;
        
        $this->loadModel('Member');
        $this->Member->setId($id);
        $this->Member->delete();

        print '{"return_value" : true }';
    }   

    /**
     * Liest alle übergeordneten Kontakte aus.
     *
     * @param int $contact_id
     *
     * @return array
     */
    protected function _readParents ($contact_id)
    {
        $this->loadModel('Member');

        $this->Member->unbindModel('all');
        $parents = $this->Member->fetch(
            'all',
            array(
                'conditions' => array(
                    'Member.person_contact_id' => $contact_id
                ),
                'order' => array('Member.first' => 'desc')
             )
        );

        $arr_parents = array();
        foreach ($parents as $parent) {
            $this->Contact->unbindModel('all');
            $contact = $this->Contact->findBy('id', $parent->getContactId());

            $arr_contact = array(
                'id' => $contact->getId(),
                'type' => $contact->getType(),
                'name' => $contact->getName(),
                'member_id' => $parent->getId(),
                'member_description' => $parent->getDescription(),
            );

            $arr_parents[] = $arr_contact;
        }

        return $arr_parents;
    }
}
?>
