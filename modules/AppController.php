<?php
/* SVN FILE: $Id$ */
/**
 * Contains the AppController class.
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
 * AppController class.
 *
 * @package netzcraftwerk
 */
class AppController extends Ncw_Controller
{

	/**
	 * Use these components
	 *
	 * @var array
	 */
	public $components = array('Acl', 'RequestHandler', 'Session');

	/**
	 * Helpers
	 *
	 * @var array
	 */
	public $helpers = array('Html', 'Javascript', 'Ajax', 'Form', 'Asset', 'Paginator', 'Text');

	/**
	 * The installed modules
	 *
	 * @var array
	 */
	public $installed_modules = array();

	/**
	 * Do not check
	 *
	 * @var boolean
	 */
	public $do_not_check = false;

	/**
	 * Before call check the permissions
	 *
	 * @param Array $params
	 */
	public function beforeFilter ()
	{
		// If ACL is used.
		if (true === isset($this->acl) && true === $this->acl instanceof Ncw_Components_Acl
		    && false === $this->do_not_check
		) {
		    $user = Ncw_Components_Session::readInAll('user');
			// If the ACOS are not read yet then read them if this action is not public
			if (false === $this->acl->getIsAcosRead()
                && false === in_array($this->action, $this->acl_publics)
            ) {
				$this->acl->read($user['id'], '');
			}
			if (true === $this->acl->getIsAcosRead()) {
                $access = $this->checkAccess($this->params, false);
    			// If ACOS are read and the acces is denied then call the noPermission action
    			if (false === $access) {
    				$this->redirect(
                        array(
                            'module' => 'core',
                            'controller' => 'user',
                            'action' => 'denied'
                        )
                    );
    			} else if ($access === -1) {
    			    if ($this->request_handler->responseType() === null) {
        			    $html = new Ncw_Helpers_Html();
        			    print '<script type="text/javascript">
        			    window.location.href="' . $html->url(
                            array(
                                "module" => "core",
                                "controller" => "user",
                                "action" => "login"
                            )
                        ) . '"
                        </script>';
    			    } else {
                        $this->redirect(
                            array(
                                'module' => 'core',
                                'controller' => 'user',
                                'action' => 'login'
                            )
                        );
    			    }
                    exit();
                } else {
                    // set the language
                    Ncw_Configure::write('App.language', $user['language']);
                    $this->setLocale();
                }
			}
		}

		// set the installed modules
	    $this->loadModel('Core_Modules');
        $modules = $this->Core_Modules->fetch(
        	'all',
        	array(
				'order' => array('Modules.no_deinstall', 'Modules.name' => 'DESC'),
			)
        );
        foreach ($modules as $module) {
        	if (Ncw_Configure::read('App.language') == 'en_EN') {
        		$module_name = $module->getName();
			} else {
				$language = str_replace('_', '', Ncw_Configure::read('App.language'));
				$language[0] = strtoupper($language[0]);
				$language[3] = strtolower($language[3]);
				$module_name = $module->{'get' . $language}();
				if (true == empty($module_name)) {
					$module_name = $module->getName();
				}
			}
            $this->installed_modules[$module->getPermissionName()] = array(
                'id' => $module->getId(),
                'name' => $module_name,
                'version' => $module->getVersion(),
                'url' => $module->getUrl(),
                'permission_name' => $module->getPermissionName(),
                'permission' => $this->acl->check('/' . $module->getPermissionName()),
                'requirements' => $module->getRequirements(),
            );
        }
	}

    /**
     * before Render
     *
     */
	public function beforeRender ()
	{
	    $applications = array();
	    foreach ($this->installed_modules as $module) {
	        if (true === $module['permission']) {
	            $applications[] = $module;
	        }
	    }
        $this->view->applications = $applications;
	}

	/**
	 * After filter...
	 *
	 */
	public function afterFilter ()
	{
	    $this->setReferer();
	}

	/**
	 * Sets the referer session
	 *
	 */
	public function setReferer ()
	{
	    /*if (false === isset($this->request_handler)) {
	        $request_handler = new Ncw_Components_RequestHandler();
	        $request_handler->startup($this);
	    }
	    if (false === $this->request_handler->isAjax()
	       && $this->name != 'User'
	       && $this->action != 'login'
	       && $this->action != 'logout'
	    ) {
            Ncw_Components_Session::writeInAll(
                'referer',
                array(
                    "module" => $this->module_name,
                    "controller" => strtolower($this->name),
                    "action" => $this->action,
                    'params' => $this->params
                )
            );
	    }*/
	}

	/**
	 * Redirects to the referer
	 *
	 */
	public function redirectToReferer ()
	{
	    /*if (true === Ncw_Components_Session::checkInAll('referer')) {
	        $goto = Ncw_Components_Session::readInAll('referer');
            $params = array_pop($goto);
            Ncw_Components_Session::deleteInAll('referer');
            $this->redirect($goto, $params);
	    }*/
	}

	/**
	 * Checks if the user is logged in.
	 *
	 * @param boolean $redirect (optional)
	 * @return boolean
	 */
	public final function checkLogin ($redirect = true)
	{
		switch (Core_UserController::checkUserLogin($this)) {
		case 1:
			return true;

		case 0:
			if (true === $redirect) {
				$this->redirect(
				    array(
				        "module" => "core",
				        "controller" => "user",
				        "action" => "login"
				    )
				);
			}
			break;

		case -1:
			$this->redirect(
			    array(
			        "module" => "core",
		            "controller" => "user",
			        "action" => "logout"
			    )
			);
		}
		return false;
	}

	/**
	 * Checks if the user has got the permission to access.
	 *
	 * @param Array $args
	 * @param boolean $redirect
	 *
	 * @return mixed
	 */
	public final function checkAccess ($args, $redirect = true)
	{
		if (true === $this->checkLogin($redirect)) {
			$aco = "/" . $this->module_name . "/" . strtolower($this->name) . "/" . $this->action;
			if (true === is_array($args)) {
				foreach ($args as $arg) {
					$aco .= "/" . $arg;
				}
			}
			return $this->acl->check($aco);
		}
		return -1;
	}

    /**
     * Captcha
     *
     */
    public function captchaAction ()
    {
        $this->layout = 'blank';
        $this->view = false;

        $this->captcha->perturbation = 0.85;
        $this->captcha->multi_text_color = array(
            new Securimage_Color(0x33, 0x99, 0xff),
            new Securimage_Color(0x33, 0, 0xcc),
            new Securimage_Color(0x33, 0x33, 0xcc),
            new Securimage_Color(0x66, 0x66, 0xff),
            new Securimage_Color(0x99, 0xcc, 0xcc)
        );
        $this->captcha->use_multi_text = true;
        $this->captcha->text_angle_minimum = -5;
        $this->captcha->text_angle_maximum = 5;
        $this->captcha->use_transparent_text = true;
        $this->captcha->text_transparency_percentage = 30;
        $this->captcha->num_lines = 5;
        $this->captcha->line_color = new Securimage_Color(0xcc, 0xcc, 0xcc);
        $this->captcha->use_wordlist = true;

        $this->captcha->show();
    }
}
?>
