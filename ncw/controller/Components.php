<?php
/**
 * Contains the Components class.
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * This file is a file from cakephp. It was copied from Netzcraftwerk and restructured for our purposes
 * Redistributions of cakephp files must retain the following copyright notice.
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 1997-2008 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version   SVN: $Id$
 * @link      http://www.netzcraftwerk.com
 * @since     File available since Release 0.1
 * @modby     $LastChangedBy$
 * @lastmod   $LastChangedDate$
 */
/**
 * Components class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Components extends Ncw_Object {

    /**
     * Contains various controller variable information (module_name, name, base).
     *
     * @var object
     */
    private $__controller_vars = array('module_name' => null, 'name' => null, 'base' => null);

    /**
     * List of loaded components.
     *
     * @var object
     */
    protected $_loaded = array();

    /**
     * List of components attached directly to the controller, which callbacks
     * should be executed on.
     *
     * @var object
     */
    protected $_primary = array();

    /**
     * Used to initialize the components for current controller.
     *
     * @param Ncw_Controller $controller Controller with components to load
     *
     * @return void
     */
    public function init (Ncw_Controller &$controller)
    {
        if (false === is_array($controller->components)) {
            return;
        }
        $this->__controller_vars = array(
            'module_name' => $controller->module_name,
            'name' => $controller->name,
            'base' => $controller->base,
        );

        $this->_loadComponents($controller);
    }

    /**
     * Called before the Controller::beforeFilter().
     *
     * @param Ncw_Controller $controller Controller with components to initialize
     *
     * @return void
     */
    public function initialize (Ncw_Controller &$controller)
    {
        foreach (array_keys($this->_loaded) as $name) {
            $component =& $this->_loaded[$name];

            if (true === method_exists($component,'initialize')
                && $component->enabled === true
            ) {
                $settings = array();
                if (isset($this->__settings[$name])) {
                    $settings = $this->__settings[$name];
                }
                $component->initialize($controller, $settings);
            }
        }
    }

    /**
     * Called after the Controller::beforeFilter() and before the controller action
     *
     * @param Ncw_Controller $controller Controller with components to startup
     *
     * @return void
     */
    public function startup (Ncw_Controller &$controller)
    {
        foreach ($this->_primary as $name) {
            $component =& $this->_loaded[$name];
            if ($component->enabled === true
                && true === method_exists($component, 'startup')
            ) {
                $component->startup($controller);
            }
        }
    }

    /**
     * Called after the Controller::beforeRender(), after the view class is loaded, and before the
     * Controller::render()
     *
     * @param Ncw_Controller $controller Controller with components to beforeRender
     *
     * @return void
     */
    public function beforeRender (Ncw_Controller &$controller)
    {
        foreach ($this->_primary as $name) {
            $component =& $this->_loaded[$name];
            if ($component->enabled === true
                && true === method_exists($component,'beforeRender')) {
                $component->beforeRender($controller);
            }
        }
    }

    /**
     * Called before Controller::redirect().
     *
     * @param Ncw_Controller $controller Controller with components to beforeRedirect
     *
     * @return void
     */
    public function beforeRedirect (Ncw_Controller &$controller, $url, $status = null, $exit = true)
    {
        $response = array();

        foreach ($this->_primary as $name) {
            $component =& $this->_loaded[$name];

            if ($component->enabled === true
                && true === method_exists($component, 'beforeRedirect')
            ) {
                $resp = $component->beforeRedirect($controller, $url, $status, $exit);
                if ($resp === false) {
                    return false;
                }
                $response[] = $resp;
            }
        }
        return $response;
    }

    /**
     * Called after Controller::render() and before the output is printed to the browser.
     *
     * @param Ncw_Controller $controller Controller with components to shutdown
     *
     * @return void
     */
    public function shutdown (Ncw_Controller &$controller)
    {
        foreach ($this->_primary as $name) {
            $component =& $this->_loaded[$name];
            if (true === method_exists($component,'shutdown')
                && $component->enabled === true
            ) {
                $component->shutdown($controller);
            }
        }
    }

    /**
     * Loads components used by this component.
     *
     * @param Ncw_Controller $controller the controller
     *
     * @return void
     */
    function _loadComponents (&$controller)
    {
        $ncw_components = array(
           'Acl', 'Captcha', 'Crypter', 'Email',
           'File', 'Folder', 'Header', 'Request', 'Session',
           'RequestHandler'
        );
        foreach ($controller->components as $component_name) {
            if (true === in_array($component_name, $ncw_components)) {
                $class_name = 'Ncw_Components_' . $component_name;
            } else {
                $class_name = $this->__controller_vars['module_name'] . '_Components_' . $component_name;
            }
            $this->_primary[] = $component_name;
            $component = new $class_name();
            $this->_loaded[$component_name] = $component;
        }
    }
}

?>