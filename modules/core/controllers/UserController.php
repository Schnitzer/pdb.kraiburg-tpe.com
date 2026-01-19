<?php
/* SVN FILE: $Id$ */
/**
 * Contains the UsersController class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk UG
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * UsersController class.
 *
 * @package netzcraftwerk
 */
class Core_UserController extends Core_ModuleController
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
	public $page_title = 'Users :: Core';

    /**
     * Use these components
     *
     * @var array
     */
    public $components = array('Acl', 'RequestHandler', 'Session', 'Crypter');

	/**
	 * ACL publics
	 *
	 * @var array
	 */
	public $acl_publics = array('login', 'logout', 'denied');

	/**
	 * The user configurations
	 *
	 * @var Array
	 */
	public $configs = array('user.config');

	/**
	 * Paginate
	 *
	 * @var array
	 */
	public $paginate = array(
        'limit' => 25,
        'order' => 'User.name'
	);

	/**
	 * beforeFiler
	 *
	 */
	public function beforeFilter ()
	{
	    parent::beforeFilter();

	    $this->crypter->key = USER_PW_KEY;

        if (false === in_array($this->action, array("update", "checkUsernameAvailability", "removeUsergroup"))) {
            $this->session->delete("edit_user_id");
        }
	}

	/**
	 * show all Users
	 *
	 * @param int $usergroup_id the usergroup id
	 *
	 * @return void
	 */
	public function allAction ($usergroup_id = 1)
	{
        if ($this->request_handler->responseType() == 'javascript') {
        	$this->layout = 'default';
        }		
		
		$this->User->unbindModel(
			array(
				'has_many' => array('UsergroupUser')
			)
		);
		
        if (true === isset($this->installed_modules['contacts'])) {
            $contact_module = true;
			$this->User->bindModel(
				array(
					'belongs_to' => array('Contacts_Contact')
				)
			);			
        } else {
            $contact_module = false;
        }
        $this->view->contact_module = $contact_module;						
        
		$condition = array();
		
		$str_search = '';
    	if (true === isset($this->params['url']['s'])) {
    		$str_search = Ncw_Library_Sanitizer::escape($this->params['url']['s']);
    		$condition[] = 'User.name LIKE \'%' . $str_search . '%\'';
    	}		
		
		if ($usergroup_id > 1) {
			$this->User->bindModel(array('has_one' => array('UsergroupUser')));
			$condition['UsergroupUser.usergroup_id'] = $usergroup_id;
		}
		
        $this->view->arr_all_users = $users = $this->paginate($condition);
		if (true === $contact_module) {
			$this->loadModel('Contacts_Contact');
			$arr_contacts = array();
			foreach ($users as $user) {
				$this->Contacts_Contact->data($user->Contacts_Contact->data());
				$arr_contacts[$user->getId()] = $this->Contacts_Contact->fullName();
			}
			$this->view->arr_contacts = $arr_contacts;
		}

	    $this->view->usergroup_id = $usergroup_id;
	    $this->loadModel('Usergroup');
		$this->Usergroup->setId($usergroup_id);
		$this->view->usergroup_name = $this->Usergroup->readField('name');
		$this->view->search_value = $str_search;
	}

	/**
	 * new User
	 *
	 * @return void
	 */
	public function newAction ()
	{
        $this->registerJs(
            array(
                'ncw.core.user',
            )
        );

	    if (true === isset($this->installed_modules['contacts'])) {
            $this->view->contact_module = true;
        } else {
            $this->view->contact_module = false;
        }
		
		$this->view->usergroup_id = $usergroup_id;

        // languages
        $language = new Core_Language();
        $this->view->languages = $language->fetch(
            'list',
            array(
                'fields' => array(
                    'Language.name',
                    'Language.id'
                )
            )
        );
	}

	/**
	 * save new user
	 *
	 * @return void
	 */
	public function saveAction ()
	{
	    $this->view = false;

	    $return = 'false';
	    if (isset($this->data['User'])) {
            $this->User->data($this->data['User']);
				
            if ($this->data['User']['password'] !== $this->data['User']['password_repeat']) {
							$this->User->invalidateField("password", T_("Check your Password(s)"));
							$this->User->invalidateField("password2", T_("Check your Password(s)"));
							$this->User->invalidateField("password3", T_("Check your Password(s)"));
            }
            if (true === $this->User->validate()) {
                $this->User->setPassword(
                    $this->crypter->encrypt($this->User->getPassword()), false
                );
                $this->User->save(false);
                $return = 'true';
            }
        }

        print '{ "return_value" : ' . $return . ', "invalid_fields" : ' . json_encode($this->User->invalidFields()) . '}';
	}

	/**
	 * edit a user
	 *
	 */
	public function editAction ($id)
	{
        $this->registerJs(
            array(
                'ncw.core.user',
            )
        );

        if (true === isset($this->installed_modules['contacts'])) {
            $contact_module = true;
        } else {
            $contact_module = false;
        }
        $this->view->contact_module = $contact_module;

        $fields = array(
            'User.id',
            'User.name',
						'User.email',
            'User.activated',
            'User.language_id',
            'User.contact_id',
            'User.entry_point',
        );

        if (true === $contact_module) {
            $this->User->bindModel(
                array(
                    'belongs_to' => array(
                        'Contacts_Contact'
                    )
                )
            
			);
			$fields[] = 'Contacts_Contact.id';
            $fields[] = 'Contacts_Contact.type';
            $fields[] = 'Contacts_Contact.name';
            $fields[] = 'Contacts_Contact.firstname';
        }
		$this->User->setId($id);
		$this->User->read(
            array(
                'fields' => $fields
            )
        );
		$this->session->write("edit_user_id", $id);

		$this->data['User'] = $this->User->data();
		$this->data['User']['password'] = "";

		$this->view->user_id = $id;
		$this->view->user_name = $this->User->getNameEncoded();

		// languages
		$language = new Core_Language();
		$this->view->languages = $language->fetch(
            'list',
            array(
                'fields' => array('Language.name', 'Language.id')
            )
        );

        if (true === $contact_module) {
            // contact
            if ($this->User->getContactId() > 0) {
            	$this->loadModel('Contacts_Contact');
                $this->view->contact_id = $this->User->getContactId();
				$this->Contacts_Contact->data($this->User->Contacts_Contact->data());
				$this->view->contact_name = $this->Contacts_Contact->fullName();
            } else {
                $this->view->contact_id = 0;
                $this->view->contact_name = '';
            }
        }

        // usergroups
        $this->User->setId($id);
        $this->view->usergroups = $this->User->UsergroupUser;

        // Creat a list of all usergroups
        $usergroups = new Core_Usergroup();
        $usergroups_list = $usergroups->fetch(
            "all",
            array(
                "conditions" => array("Usergroup.id !=" => 1),
                "fields" => array(
                    "Usergroup.name",
                    "Usergroup.id",
                    'Usergroup.level' => 'COUNT(`p`.`id`)-1'
                )
            )
        );
        $this->view->usergroups_list = $usergroups_list;
	}

	/**
	 * to edit one user
	 * save to database when validation is ok
	 *
	 */
	public function updateAction ()
	{
		$this->view = false;

		$arr_state = array("return_value" => true);
		if (true === isset($this->data['User'])
		    && true === $this->session->check("edit_user_id"))
		{
			$this->User->setId($this->session->read("edit_user_id"));
			$this->User->data($this->data['User']);
			
			// Save the user name if it has changed.
			if ($this->data['User']['name'] !== $this->User->readField('name')) {
                if (true === $this->User->saveField('name')) {
                    $arr_state['return_value'] = true;
                } else {
                	$arr_state['return_value'] = false;
                    $arr_state['invalid_fields'] = $this->User->invalidFields();
                }
			}
			
			if (true === $arr_state['return_value']) {
    			// If password must not be set.
    			if (   true === empty($this->data['User']['password'])
    			    && true === empty($this->data['User']['password2'])
    			    && true === empty($this->data['User']['password3']))
    			{
    				$result = $this->User->saveFields(
    					array(
    						'activated', 
    						'language_id',
								'email',
    						'contact_id', 
    						'entry_point'
    					)
					);
    				if (true === $result) {
    					$arr_state['return_value'] = true;
    				} else {
    					$arr_state['return_value'] = false;
    					$arr_state['invalid_fields'] = $this->User->invalidFields();
    				}
    			} else {
    				// Check if the value of the password fields are equal. If not then invalidate the password field.
    				if ($this->data['User']['password'] != $this->data['User']['password2']
    				    || false === self::validatePassword($this->User->getId(), $this->data['User']['password3'])
					) {
						$arr_state['return_value'] = false;
    					$this->User->invalidateField("password", T_("Check your Password(s)!"));
						$this->User->invalidateField("password2", T_("Check your Password(s)!"));
						$this->User->invalidateField("password3", T_("Check your Password(s)!"));
    				}
    				if (true === $this->User->validateField('password')
    				    && true === $this->User->validateField('activated')
    				    && true === $this->User->validateField('language_id'))
    				    {
    					$this->User->setPassword($this->crypter->encrypt($this->User->getPassword()), false);
    					$result = $this->User->saveFields(
                            array(
                                'password',
                                'activated',
                                'language_id',
																'email',
                                'contact_id',
                                'entry_point'
                            ),
                            false
                        );
						if (true === $result) {
    						$arr_state['return_value'] = true;
						}
    				} else {
    					$arr_state['return_value'] = false;
    					$arr_state['invalid_fields'] = $this->User->invalidFields();
    				}
    			}
			}
		}

		$user = Ncw_Components_Session::readInAll('user');
		if (true === isset($arr_state['return_value'])
		    && true === $arr_state['return_value']
		    && $user['id'] === $this->session->read("edit_user_id")
		    && true === isset($this->data['User']['language_id'])
		) {
		    $this->loadModel('Language');
		    $this->Language->setId($this->data['User']['language_id']);
		    $shortcut = $this->Language->readField('shortcut');
		    if ($shortcut !== $user['language']) {
                $user = array_merge(
                    $user,
                    array('language' => $shortcut)
                );
                Ncw_Components_Session::writeInAll('user', $user);
		    }
		}

		print json_encode($arr_state);
	}

    /**
     * The profile action
     *
     */
    public function profileAction ()
    {
    	$this->layout = 'default';
		
        if (true === Ncw_Components_Session::checkInAll('user')) {
            $user = Ncw_Components_Session::readInAll('user');
            $this->User->setId($user['id']);
            if (true === isset($this->data['User'])) {
                $this->User->data($this->data['User']);
                if (true === empty($this->data['User']['password'])
                    && true === empty($this->data['User']['password2'])
                    && true === empty($this->data['User']['password3'])
                ) {
                    $this->User->getLanguageId();
                    $this->User->saveFields(array('language_id'));
                } else {
                    // Check if the value of the password fields are equal. If not then invalidate the password field.
                    if ($this->data['User']['password'] != $this->data['User']['password2']
                       || false === self::validatePassword($this->User->getId(), $this->data['User']['password3'])
                    ) {
                        $this->User->invalidateField("password");
                    }
                    if (true === $this->User->setPassword($this->data['User']['password'])) {
                        $this->User->setPassword($this->crypter->encrypt($this->User->getPassword()), false);
                        $this->User->saveFields(array('password', 'language_id'), false);
                    }
                }
            }
			
	        if (true === isset($this->installed_modules['contacts'])) {
	            $contact_module = true;
	        } else {
	            $contact_module = false;
	        }
	        $this->view->contact_module = $contact_module;

            $this->User->unbindModel(array("has_many" => array("UsergroupUser")));

			$fields = array(
				'User.id', 
				'User.name',
				'User.email', 
				'User.language_id',
				'User.contact_id',
			);

	        if (true === $contact_module) {
	            $this->User->bindModel(
	                array(
	                    'belongs_to' => array(
	                        'Contacts_Contact'
	                    )
	                )
	            );
	            $fields[] = 'Contacts_Contact.type';
	            $fields[] = 'Contacts_Contact.name';
	            $fields[] = 'Contacts_Contact.firstname';
	        }
		
            $this->User->read(
            	array(
            		'fields' => $fields
				)
			);


            $this->data['User'] = $this->User->data();
            $this->data['User']['password'] = '';
            $this->view->username = $this->User->getName();

            $language = new Core_Language();
            $this->view->languages = $language->fetch('list', array('fields' => array('Language.name', 'Language.id')));
			
	        if (true === $contact_module) {
	            // contact
	            if ($this->User->getContactId() > 0) {
	                $this->view->contact_id = $this->User->getContactId();
	                if ($this->User->Contacts_Contact->getType() == 'private') {
	                    $this->view->contact_name = $this->User->Contacts_Contact->getFirstname() . ' ' . $this->User->Contacts_Contact->getName();
	                } else {
	                    $this->view->contact_name = $this->User->Contacts_Contact->getName();
	                }
	            } else {
	                $this->view->contact_id = 0;
	                $this->view->contact_name = '';
	            }
	        }			
						
        } else {
            $this->redirect();
        }
    }

	/**
	 * Delete user
	 *
	 */
	public function deleteAction ($id)
	{
	    $this->view = false;

		$this->User->setId($id);
		$this->User->delete();

		print '{"return_value" : true}';
	}

	/**
	 * validate the input
	 *
	 */
	public function validateAction ()
	{
		$this->view = false;
		$invalid_fields = array();
		if (isset($this->data['User'])) {
			$this->User->data($this->data['User']);
			if (false === $this->User->validate()) {
				$invalid_fields['User'] = $this->User->invalidFields();
			}
		}
		print json_encode($invalid_fields);
	}

	/**
	 * add a usergroup to the user
	 *
	 * @param int $id           the user id
	 * @param int $usergroup_id the usergroup id
	 *
	 * @return void
	 */
	public function addUsergroupAction ($id, $usergroup_id)
	{
        $this->view = false;

        $usergroup_user = new Core_UsergroupUser();
        $usergroup_user->setUsergroupId($usergroup_id);
        $usergroup_user->setUserId($id);
        $usergroup_user->save();

        $usergroup = new Core_Usergroup();
        $usergroup->setId($usergroup_id);
        $name = $usergroup->readField('name');

        print '{"return_value" : true, "usergroup" : { "name" : "' . $name . '" } }';
	}

	/**
	 * Remove a user to usergroup assocation
	 *
	 */
	public function removeUsergroupAction ($id)
	{
        $this->view = false;

		$usergroup_user = new Core_UsergroupUser();
		$usergroup_user->setId($id);
		$usergroup_user->delete();

		print '{"return_value" : true}';
	}

	/**
	 * Check if the given username is available and validated
	 *
	 */
	public function checkUsernameAvailabilityAction ()
	{
		$this->view = false;
		$not_changed = false;
		$invalid_fields = array("user" => false);
		if (true === isset($_POST['username'])) {
			if (true === $this->session->check("edit_user_id")) {
				$this->User->setId($this->session->read("edit_user_id"));
				if ($_POST['username'] === $this->User->readField('name')) {
				    $not_changed = true;
				}
			}
			if (false === $not_changed) {
    			if (true === $this->User->setName($_POST['username'])) {
    				$invalid_fields = true;
    			} else {
    				$invalid_fields = $this->User->invalidFields();
    			}
			}
		}
		print json_encode($invalid_fields);
	}

	/**
	 * Login action
	 *
	 */
	public function loginAction ()
	{
	    if (true === Ncw_Configure::read('App.ssl')
            && false === $this->request_handler->isSSL()
        ) {
            $base = str_replace('http', 'https', $this->base);
            if (false !== $this->prefix) {
                $base .= DS . $this->prefix;
            }
            header('Location: '. $base);
        }

		$this->layout = 'login';
		if (true === isset($this->data['User']['name'], $this->data['User']['password'])) {
			$user = self::validateLogin(
                $this->data['User']['name'],
                $this->data['User']['password']
            );
			if (true === $user instanceof Core_User && true === self::login($user)) {
			    // redirect
			    $entry_point = $user->getEntryPoint();
			    /*if (true === Ncw_Components_Session::checkInAll('referer')) {
			        $this->redirectToReferer();
			    } else*/ if (false === empty($entry_point)) {
                    $goto = $entry_point;
		        } else {
                    $goto = array('action' => 'profile');
		        }
				$this->redirect($goto);
			} else {
				$this->User->invalidateField('name');
			}
		}
	}

	/**
	 * Logout action
	 *
	 */
	public function logoutAction ()
	{
		$this->layout = 'login';
		self::logout();
		$this->view->document_root = Ncw_Configure::read('Logout.url');
	}

	/**
	 * Normally if a user have not got the permission
	 * to access any object then this action is called.
	 *
	 */
	public function deniedAction ()
	{
	    $this->header->sendStatusCode(403);
	    $this->header->setHeader('Connection', 'close');
		$this->layout = "login";
	}

   /**
     * Logs in the user
     *
     * @param Core_User $user the user to login
     *
     * @return boolean
     */
    public static function login (Core_User $user)
    {
        $user->setLastLogin(time());
        $user->saveField('last_login', false);
        // create the user array
        $user = array(
            'id' => $user->getId(),
            'name' => $user->getName(),
            'client_ip' => $_SERVER['REMOTE_ADDR'],
            'client_browser' => $_SERVER['HTTP_USER_AGENT'],
            'language' => $user->Language->getShortcut()
        );
        // start the session
        if (false === Ncw_Components_Session::checkInAll('SERVER_GENERATED_SID')) {
           session_destroy();
        }
        self::logout();
        Ncw_Components_Session::writeInAll('SERVER_GENERATED_SID', true);
        // Write the user array session.
        Ncw_Components_Session::writeInAll("user", $user);
        self::setLoginCookie();
        return true;
    }

    /**
     * Logs out the user
     *
     * @return void
     */
    public static function logout ()
    {
        // Delete the session
        Ncw_Components_Session::deleteInAll("user");
        Ncw_Components_Session::regenerate();
        // delete the login cookie
        setcookie(LOGIN_COOKIE_NAME, '', 0, '/');
        unset($_COOKIE[LOGIN_COOKIE_NAME]);
    }

	/**
	 * Validates the Login.
	 *
	 * @param string $username the username
	 * @param string $password the password
	 *
	 * @return mixed User object or false
	 */
	public static function validateLogin ($username, $password)
	{
	    include_once MODULES . DS . 'core' . DS . 'config' . DS . 'user.config.php';
		$user = new Core_User();
		$user->unbindModel('all');
		$user->bindModel(array('belongs_to' => array('Language')));
		$data_user = $user->findBy(
		    "name",
		    $username,
		    array(
		        "conditions" => array(
                    "User.virtual" => false,
                    "User.activated" => true
                ),
		        'fields' => array(
		            'User.id',
		            'User.name',
		            'User.password',
		            'User.entry_point',
		            'Language.shortcut'
                )
            )
        );
		if (true === $data_user instanceof Ncw_DataModel) {
			// Decrypt the password
			$crypter = new Ncw_Components_Crypter(USER_PW_KEY);
			$decrypted_password = $crypter->decrypt($data_user->getPassword());
			// Check if the given password matches the password in the database
			if ($password === $decrypted_password) {
				$user->data($data_user->data());
				$user->associatedModels($data_user->associatedModels());
				return $user;
			}
		}
		return false;
	}

	/**
	 * Checks if the login is still valid.
	 *
	 * @param Ncw_Controller $controller
	 *
	 * @return int
	 */
	public static function checkUserLogin (Ncw_Controller $controller = null)
	{
		if (true === Ncw_Components_Session::checkInAll("user")) {
			$user = Ncw_Components_Session::readInAll("user");
			$referer = '';
			if (false === is_null($controller)) {
                $referer = $controller->referer();
			} else if (true === isset($_SERVER['HTTP_REFERER'])) {
			    $referer = $_SERVER['HTTP_REFERER'];
			}
			/*if (// strpos($referer, Ncw_Configure::read('Project.url')) !== 0 ||
			    $_SERVER['REMOTE_ADDR'] != $user['client_ip']
			    || $_SERVER['HTTP_USER_AGENT'] != $user['client_browser']
			    //|| false === self::validateLoginCookie()
			) {
			    Ncw_Components_Session::destroy();
				return -1;
			}*/
			return 1;
		}
		return 0;
	}

	/**
	 * Sets the login cookie
	 *
	 */
	public static function setLoginCookie ()
	{
	    return true;
		$user = Ncw_Components_Session::readInAll("user");
		$value = '1|' . time() . '|' . $user['id'];
		$crypter = new Ncw_Components_Crypter(USER_PW_KEY);
		$value = $crypter->encrypt($value);

		$domain = '';
		if (Ncw_Configure::read('Project.domain') !== 'localhost') {
		    $domain = "." . Ncw_Configure::read('Project.domain');
		}

		// set the login cookie
		setcookie(
            LOGIN_COOKIE_NAME,
            $value,
            time() + LOGIN_COOKIE_LIFETIME,
            Ncw_Configure::read('Session.cookie_path'),
            $domain
        );
	}

	/**
	 * Validates the login cookie
	 *
	 * @return boolean
	 */
	public static function validateLoginCookie ()
	{
	    include_once MODULES . DS . 'core' . DS . 'config' . DS . 'user.config.php';
		if (true === isset($_COOKIE[LOGIN_COOKIE_NAME])) {
		    $crypter = new Ncw_Components_Crypter(USER_PW_KEY);
			$value =$crypter->decrypt($_COOKIE[LOGIN_COOKIE_NAME]);
			$values = explode('|', $value);
			if (true === isset($values[0], $values[1], $values[2])
                && (int) $values[0] === 1
                && (int) $values[1] > 0
                && (int) $values[2] > 0
            ) {
				if ((time() - (int) $values[1]) > LOGIN_COOKIE_RESET) {
					self::setLoginCookie();
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Validates the Password.
	 *
	 * @static
	 * @param int $id
	 * @param string $password the password
	 * @return true or false
	*/
	public static function validatePassword ($id, $password)
	{
		$user = new Core_User();
		$user = $user->findBy("id", $id, array("fields" => array("User.password")));
		if (true === $user instanceof Ncw_DataModel) {
			// Decrypt the password
			// is the password crypted in the database?
			if (strlen($user->getPassword()) >= 32) {
			    $crypter = new Ncw_Components_Crypter(USER_PW_KEY);
				$decrypted_password = $crypter->decrypt($user->getPassword());
				// Check if the given password matches the password in the database
				if ($password === $decrypted_password) {
					return true;
				}
			}
		}
		return false;
	}
}
?>
