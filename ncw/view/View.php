<?php
/**
 * Contains the View class.
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * Parts of this file are from cakephp. They were copied from Netzcraftwerk and restructured for our purposes
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
 * The View class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
#[AllowDynamicProperties]
class Ncw_View extends Ncw_Object
{

    /**
     * The controller
     *
     * @var Ncw_controller
     */
    public $controller = null;

    /**
     * The controller name
     *
     * @var string
     */
    public $controller_name = '';

    /**
     * The module name
     *
     * @var string
     */
    public $module_name = '';

    /**
     * The view type
     *
     * @var string
     */
    public $type = '';

    /**
     * The view content
     *
     * @var string
     */
    public $view = '';

    /**
     * Registered css files
     *
     * @var array
     */
    public $css = array();

    /**
     * Registered js files
     *
     * @var array
     */
    public $js = array();

    /**
     * The page title
     *
     * @var string
     */
    public $page_title = '';

    /**
     * The layout
     *
     * @var string
     */
    public $layout = '';

    /**
     * The base path
     *
     * @var string
     */
    public $base = '';

    /**
     * The url prefix
     *
     * @var string
     */
    public $prefix = '';

    /**
     * The view path
     *
     * @var string
     */
    public $view_path = null;

    /**
     * The layout path
     *
     * @var string
     */
    public $layout_path = null;

    /**
     * The theme path
     *
     * @var string
     */
    public $theme_path = '';

    /**
     * The webroot
     *
     * @var string
     */
    public $webroot = '';

    /**
     * Set to true to automatically render the layout around views.
     *
     * @var boolean
     */
    public $auto_layout = true;

    /**
     * The helpers to load
     *
     * @var array
     */
    public $helpers = array();

    /**
     * The loaded view helpers
     *
     * @var array
     */
    public $loaded = array();

    /**
     * List of generated DOM UUIDs
     *
     * @var array
     */
    public $uuids = array();

    /**
     * The view vars
     *
     * @var array
     */
    public $view_vars = array();

    /**
     * The params
     *
     * @var array
     */
    public $params = array();

    /**
     * Sets the path to the view
     *
     * @param Ncw_controller &$controller the controller
     */
    public function __construct (Ncw_Controller &$controller)
    {
        $this->controller = $controller;
        $this->__setAttributes();
    }

    /**
     * Sets the attributes
     *
     */
    private function __setAttributes ()
    {
        $this->page_title = $this->controller->page_title;
        $this->controller_name = $this->controller->name;
        $this->module_name = $this->controller->module_name;
        $this->params = $this->controller->params;

        $this->helpers = $this->controller->helpers;
        $this->base = $this->controller->base;
        $this->prefix = $this->controller->prefix;
        $this->webroot = $this->controller->webroot;
        $this->theme_path = $this->controller->theme_path;
        $this->page_title = $this->controller->page_title;
        $this->auto_layout = $this->controller->auto_layout;
        $this->layout = $this->controller->layout;
        $this->view_path = $this->controller->view_path;
        $this->layout_path = $this->controller->layout_path;
    }

    /**
     * Fire a callback on all loaded Helpers
     *
     * @param string $callback name of callback fire.
     *
     * @return void
     */
    protected function _triggerHelpers ($callback)
    {
        if (empty($this->loaded)) {
            return false;
        }
        $helpers = array_keys($this->loaded);
        foreach ($helpers as $helper_name) {
            $helper =& $this->loaded[$helper_name];
            if (true === is_object($helper)) {
                if (is_subclass_of($helper, 'Ncw_Helper')) {
                    $helper->{$callback}($this);
                }
            }
        }
    }

    /**
     * Renders a element
     *
     * @param string $name  the element to render
     * @param mixed $module (optional)
     *
     * @return string
     */
    public function element ($name, $module = null)
    {
        if (false !== $module) {
            if (true === is_null($module)) {
                $module = $this->module_name;
            }
            $path = MODULES . DS . $module . DS . 'views' . DS
                . 'elements' . DS . $name . '.phtml';
        } else {
            $path = $this->theme_path . DS . 'elements' . DS . $name . '.phtml';
        }
        if (true === file_exists($path)) {
            ob_start();
            include_once $path;
            return ob_get_clean();
        } else {
            throw new Ncw_Exception("Element " . $path . " does not exist");
        }
    }

    /**
     * Renders a partial view
     *
     * @param string $name       the partial to render
     * @param mixed  $module     the module name or
     *        false if partial is located in the theme (optional)
     * @param mixed  $controller the controller (optional)
     *
     * @return string the file path
     */
    public function partial ($name, $module = null, $controller = null)
    {
        if (false !== $module) {
            if (true === is_null($module)) {
                $module = $this->module_name;
            }
            if (true === is_null($controller)) {
                $controller = $this->view_path;
            }
            $path = MODULES . DS . $module . DS
                . 'views' . DS;
            if (false === is_null($controller)) {
                $path .= $controller . DS;
            }
            $path .= '_' . $name . '.phtml';

        } else {
            $path = $this->theme_path . DS . 'layouts' . DS . '_' . $name . '.phtml';
        }
        if (true === file_exists($path)) {
            return $path;
        } else {
            throw new Ncw_Exception("Partial " . $path . " does not exist");
        }
    }

    /**
     * Renders the view.
     *
     * @param mixed   $view         the view to render
     * @param boolean $load_helpers (boolean)
     *
     * @return void
     */
    public function render ($view = null, $load_helpers = true)
    {
        $this->__setAttributes();
        if (true === is_null($view)) {
            $view = $this->controller->action;
        }
        $path = MODULES . DS . $this->module_name . DS
            . 'views' . DS;
        if (false === is_null($this->view_path)) {
            $path .= $this->view_path . DS;
        }
        $path .= $view . '.phtml';

        if ($this->helpers != false && true === $load_helpers) {
            // Include the Helpers
            $this->_loadHelpers();

            $this->_triggerHelpers('beforeRender');
        }

        ob_start();
        // Include the view.
        if (false === include_once $path) {
            throw new Ncw_Exception("View " . $path . " does not exist");
        }
        $output = ob_get_clean();

        if ($this->helpers != false && true === $load_helpers) {
            $this->_triggerHelpers('afterRender');
        }

        if (true === $this->auto_layout) {
            $output = $this->renderLayout($output, $this->layout);
        }

        $this->_cache($output);
        return $output;
    }

    /**
     * Renders the layout
     *
     * @param string $content the view content
     * @param string $layout  the layout to render
     *
     * @return string
     */
    public function renderLayout ($content, $layout = null)
    {
        if (false !== strpos($layout, '/')) {
            $layout_path = explode('/', $layout);
            $path = MODULES . DS . $layout_path[0] . DS . 'views' . DS . 'layouts' . DS;
            if (false === is_null($this->layout_path)) {
                $path .= $this->layout_path . DS;
            }
            $path .= $layout_path[1] . '.phtml';
        } else {
            $path = THEMES . DS . Ncw_Configure::read('App.theme') . DS . "layouts"
                . DS;
            if (false === is_null($this->layout_path)) {
                $path .= $this->layout_path . DS;
            }
            $path .= $layout . '.phtml';
        }

        $this->view = $content;

        ob_start();

        $this->_triggerHelpers('beforeLayout');

        // include the layout.
        if (false === include $path) {
            throw new Ncw_Exception("Layout " . $path . " does not exist");
        }

        $this->_triggerHelpers('afterLayout');

        return ob_get_clean();
    }

    /**
     * Returns the registered css files
     *
     * @return string
     */
    public function css ()
    {
        $output = '';
        foreach ($this->css as $css) {
            $output .= $css['tag'];
        }
        return $output;
    }

    /**
     * Returns the registered css files
     *
     * @return string
     */
    public function js ()
    {
        $output = '';
        foreach ($this->js as $js) {
            $output .= $js['tag'];
        }
        return $output;
    }

    /**
     * Generates a unique, non-random DOM ID for an object, based on the object type and the target URL.
     *
     * @param string $object Type of object, i.e. 'form' or 'link'
     * @param string $url The object's target URL
     *
     * @return string
     */
    public function uuid ($object, $url)
    {
        $c = 1;
        $url = Ncw_Router::url($url);
        $hash = $object . substr(md5($object . $url), 0, 10);
        while (in_array($hash, $this->uuids)) {
            $hash = $object . substr(md5($object . $url . $c), 0, 10);
            $c++;
        }
        $this->uuids[] = $hash;
        return $hash;
    }

    /**
     * Cache the view if needed
     *
     * @param string $output the output
     *
     * @return void
     */
    protected final function _cache ($output)
    {
        if (false === Ncw_Configure::read('Cache')) {
            return;
        }
        // if the action must be cached
        $id = implode('/', $this->controller->params['url']);
        if (true === array_key_exists($id, $this->controller->cache_action)) {
            $cache_id = $this->cache->generateID($id);
            // cache the output
            $result = $this->cache->save(
                $cache_id,
                $output,
                $this->controller->cache_action[$id],
                $this->controller->module_name
            );
            if (true === Cache::isError($result)) {
                throw new Ncw_Exception($result);
            }
        }
    }

    /**
     * Include the helpers
     *
     * @return void
     */
    protected final function _loadHelpers ()
    {
        $ncw_helpers = array(
           'Cache', 'Form', 'Html', 'Javascript', 'Text', 'Paginator', 'Asset',
           'Ajax',
        );
        foreach ($this->helpers as $helper_name) {
            if (true === in_array($helper_name, $ncw_helpers)) {
                $class_name = 'Ncw_Helpers_' . $helper_name;
            } else {
                $class_name = $this->controller->module_name . '_Helpers_' . $helper_name;
            }
            $helper = new $class_name();
            $helper->startup($this);
            $this->loaded[$helper_name] = $helper;
        }
    }

    /**
     * Allows a template or element to set a variable that will be available in
     * a layout or other element
     *
     * @param mixed $one A string or an array of data.
     * @param mixed $two Value in case $one is a string (which then works as the key).
     *              Unused if $one is an associative array, otherwise serves as the
     *              values to $one's keys.
     *
     * @return void
     */
    public function set ($one, $two = null)
    {
        $data = null;
        if (true === is_array($one)) {
            if (true === is_array($two)) {
                $data = array_combine($one, $two);
            } else {
                $data = $one;
            }
        } else {
            $data = array($one => $two);
        }

        if ($data == null) {
            return false;
        }

        foreach ($data as $name => $value) {
            if ($name == 'title') {
                $this->page_title = $value;
            } else {
                $this->view_vars[$name] = $value;
            }
        }
    }

    /**
     * Sets the view vars
     *
     * @param array $vars the view vars
     *
     * @return void
     */
    public function setVars ($vars = array())
    {
        $this->view_vars = $vars;
    }

    /**
     * Returns the view vars
     *
     * @return array
     */
    public function getVars ()
    {
        return $this->view_vars;
    }

    /**
     * Set a view variable.
     * If the variable is of type Ncw_Model
     * then the model(s) must be weakened before they can be
     * used in the views. This is necessary to prevent
     * the abuse functions like save, delete, fetch in the view.
     * A model list needs to be set as an array.
     *
     * @param string $name  the name of the variable
     * @param mixed  $value the value to set
     *
     * @return void
     */
    public function __set ($name, $value)
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string!');
        }
        if (true === empty($name)) {
            throw new Ncw_Exception('$name must not be empty');
        }
        if (true === $value instanceof Ncw_ModelList) {
            $value = $value->toArray();
        } else if (true === $value instanceof Ncw_Model) {
            $value = new Ncw_DataModel($value);
        }
        $this->{$name} = $value;
        $this->view_vars[$name] = $value;
    }

    /**
     * Get a view variable.
     *
     * @param string $name the value of the variable
     *
     * @return mixed
     */
    public function __get ($name)
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string!');
        }
        if (true === empty($name)) {
            throw new Ncw_Exception('$name must not be empty');
        }
        if (true === isset($this->{$name})) {
            return $this->{$name};
        } else if (true === isset($this->view_vars[$name])) {
            return $this->view_vars[$name];
        } else {
            throw new Ncw_Exception('Variable ' . $name . ' is not set');
        }
    }
}
?>
