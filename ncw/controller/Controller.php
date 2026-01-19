<?php
/**
 * Contains the controller class.
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
 * Gettext functions
 */
include_once VENDOR . DS . 'php-gettext' . DS . 'gettext.inc';
/**
 * The controller class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
abstract class Ncw_Controller extends Ncw_Object
{

    /**
     * The controller name
     *
     * @var string
     */
    public $name = '';

    /**
     * The module name
     *
     * @var string
     */
    public $module_name = '';

    /**
     * The action/method which is called last.
     *
     * @var string
     */
    public $action = '';

    /**
     * The given parameters
     *
     * @var array
     */
    public $params = array();

    /**
     * The passed args
     *
     * @var array
     */
    public $passed_args = array();

    /**
     * Layout
     *
     * @var string
     */
    public $layout = 'default';

    /**
     * The page title.
     *
     * @var string
     */
    public $page_title = '';

    /**
     * The view object.
     *
     * @var Ncw_View
     */
    public $view = null;

    /**
     * As default the controller is assigned to a model.
     * Set to false if not.
     *
     * @var boolean
     */
    public $has_model = true;

    /**
     * Set to false if you don't want the views
     * to be auto rendered
     *
     * @var boolean
     */
    public $auto_render = true;

    /**
     * Set to true to automatically render the layout around views.
     *
     * @var boolean
     */
    public $auto_layout = true;

    /**
     * The output
     *
     * @var string
     */
    public $output = '';

    /**
     * The configs which must be included
     *
     * @var array
     */
    public $configs = array();

    /**
     * The components which must be included
     *
     * @var array
     */
    public $components = array();

    /**
     * The components object
     *
     * @var Ncw_Component
     */
    public $components_object = null;

    /**
     * The helpers which must be included
     *
     * @var array
     */
    public $helpers = array('Html');

    /**
     * The ACL public array.
     *
     * @var array
     */
    public $acl_publics = array();

    /**
     * Defines which actions must be cached.
     *
     * @var Array
     */
    public $cache_action = array();

    /**
     * Pagination options
     *
     * @var array
     */
    public $paginate = array(
        'limit' => 20,
        'page' => 1
    );

    /**
     * The post data
     *
     * @var Array
     */
    public $data = array();

    /**
     * The base path
     *
     * @var string
     */
    public $base = '';

    /**
     * The prefix
     *
     * @var string
     */
    public $prefix = '';

    /**
     * The requested controller/action
     *
     * @var string
     */
    public $here = '';

    /**
     * The view subfolder path
     *
     * @var mixed
     */
    public $view_path = null;

    /**
     * The layout path
     *
     * @var mixed
     */
    public $layout_path = null;

    /**
     * The theme path
     *
     * @var mixed
     */
    public $theme_path = null;

    /**
     * The webroot path
     *
     * @var string
     */
    public $webroot = '';

    /**
     * Initializes the view object and
     * sets the method which is called first.
     *
     */
    public final function __construct ()
    {
        if (true === empty($this->name)) {
            $class_name = get_class($this);
            $arr_class_name = explode('_', $class_name);
            $this->module_name = strtolower($arr_class_name[0]);
            $this->name = str_replace('Controller', '', $arr_class_name[1]);
        }

        $this->setLocale();

        // Initialize the view object.
        $this->view = new Ncw_View($this);

        $this->theme_path = THEMES . '/' . Ncw_Configure::read('App.theme');

        // Include the configs
        $this->_includeConfigs();

        $this->components_object = new Ncw_Components();

        if (Ncw_Configure::read('debug_mode') > 0) {
            Ncw_Bootstrap::$timer->setMarker('before action');
        }
    }

    /**
     * Loads a model
     *
     * @param string $model_class the model class
     * @param int $id             the id
     *
     * @return void
     */
    public final function loadModel ($model_class, $id = 0)
    {
        $class = $model_class;
        if (false === strpos($model_class, '_')) {
            $class = $this->module_name . "_" . $model_class;
        }
        $this->$model_class = new $class();
        if ($id > 0) {
            $this->$model_class->setId($id);
        }
    }

    /**
     * Construct the classes
     *
     */
    public final function constructClasses ()
    {
        $this->components_object->init($this);

        if (true === $this->has_model) {
            // Initialize the model object.
            $this->loadModel($this->name);
        }
    }

    /**
     * Sets the gettext locale and domain
     *
     */
    public function setLocale ()
    {
        $locale = Ncw_Configure::read('App.language');
        $domain = 'default';
        $encoding = Ncw_Configure::read('App.encoding');

        T_setlocale(LC_MESSAGES, $locale);
        T_bindtextdomain(
            $domain,
            MODULES . DS . $this->module_name . DS . 'locale'
        );
        T_bind_textdomain_codeset($domain, $encoding);
        T_textdomain($domain);
    }

    /**
     * Include the configs
     *
     * @return void
     */
    protected final function _includeConfigs ()
    {
        foreach ($this->configs as $config) {
            $path_to_module_config = MODULES . DS . $this->module_name . DS . "config" . DS . $config . ".php";
            if (true === is_file($path_to_module_config)) {
                include_once $path_to_module_config;
            } else {
                throw new Ncw_Exception("Config " . $config . " does not exist!");
            }
        }
    }

    /**
     * before filter
     *
     * @return void
     */
    public function beforeFilter ()
    {

    }

    /**
     * after filter
     *
     * @return void
     */
    public function afterFilter ()
    {

    }

    /**
     * Renders the view of the controller method.
     *
     * @param string $view   set a view to render (optional)
     * @param mixed  $layout set the layout (optional)
     *
     * @return mixed
     */
    public final function render ($view = '', $layout = null)
    {
        if (false !== $this->view) {

            $this->beforeRender();

            if (true === empty($view)) {
                $view = $this->action;
            }
            if (false === is_null($layout)) {
                $this->layout = $layout;
            }
            $this->output .= $this->view->render($view);
            $this->auto_render = false;

            return $this->output;
        }
        return false;
    }

    /**
     * Before any view of the controller is rendered
     * this method is called.
     *
     * @return void
     */
    public function beforeRender ()
    {

    }

    /**
     * Is used to call further actions.
     *
     * @param string $action the action to call
     * @param mixed  $params the parameters (optional)
     *
     * @return void
     */
    public final function call ($action, $params = null)
    {
        if (false === is_string($action)) {
            throw new Ncw_Exception('$action must be of type string!');
        }
        if (true === empty($action)) {
            throw new Ncw_Exception('$action must not be empty!');
        }
        $this->action = $action;
        if (true === is_array($params)) {
            $this->params = array_merge($this->params, $params);
        }
        $action_name = $this->action . "Action";
        if (true === method_exists($this, $action_name)) {
            $this->dispatchMethod($action_name, $this->params['pass']);
        } else {
            throw new Ncw_Exception("Action " . $this->action . " does not exist!");
        }
    }

    /**
     * Redirects to the given path (module, controller, action). If no path is given then it
     * redirects to the index action. If $path is a string, then the string will be used
     * as redirect url.
     *
     * @param mixed   $path       (optional)
     * @param int     $status     the status code
     * @param boolean $exit       if exit after redirect
     *
     * @return void
     */
    public final function redirect ($path = array(), $status = 301, $exit = true)
    {
        $header = new Ncw_Components_Header();
        $header->object->sendStatusCode($status);

        if (true === is_string($path)) {
            $this->components_object->beforeRedirect($this, $path, $status, $exit);

            if (false === $header->object->redirect($path, $exit)) {
                throw new Ncw_Exception('Header was alreay sent');
            }
        }

        $path = array_merge(
            array(
                "module" => $this->module_name,
                "controller" => strtolower($this->name),
            ),
            $path
        );
        $html = new Ncw_Helpers_Html();
        $url = $html->url($path, true);

        $this->components_object->beforeRedirect($this, $url, $status, $exit);

        if (false === $header->object->redirect($url, $exit)) {
            throw new Ncw_Exception('Header was alreay sent');
        }
    }

    /**
     * Returns the referring URL for this request.
     *
     * @param string $default Default URL to use if HTTP_REFERER cannot be read from headers
     * @param boolean $local If true, restrict referring URLs to local server
     *
     * @return string Referring URL
     */
    public function referer ($default = null, $local = false)
    {
        $ref = env('HTTP_REFERER');
        if (false === empty($ref) && true === Ncw_Configure::check('Project.url')) {
            $base = Ncw_Configure::check('Project.url') . $this->webroot;
            if (strpos($ref, $base) === 0) {
                $return =  substr($ref, strlen($base));
                if ($return[0] != '/') {
                    $return = '/'.$return;
                }
                return $return;
            } elseif (!$local) {
                return $ref;
            }
        }

        if ($default != null) {
            return $default;
        }
        return '/';
    }

    /**
     * Forces the user's browser not to cache the results of the current request.
     *
     * @return void
     * @access public
     */
    public function disableCache ()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    /**
     * Use to register a css file to be included.
     *
     * @param mixed $file the css file name (without '.css')
     *
     * @return boolean
     */
    public final function registerCss ($file)
    {
        if (false === is_string($file) && false === is_array($file)) {
            throw new Ncw_Exception('$file must be either of type string or array!');
        }
        if (true === is_array($file)) {
            $return = true;
            foreach ($file as $node) {
                if (false === $this->_registerFile('css', $node)) {
                    $return = false;
                }
            }
            return $return;
        } else {
            return $this->_registerFile('css', $file);
        }
    }

    /**
     * Use to register a js file to be included.
     *
     * @param mixed $file the js file name (without '.js')
     *
     * @return boolean
     */
    public final function registerJs ($file)
    {
        if (false === is_string($file) && false === is_array($file)) {
            throw new Ncw_Exception('$file must be either of type string or array!');
        }
        if (true === is_array($file)) {
            $return = true;
            foreach ($file as $node) {
                if (false === $this->_registerFile('javascript', $node)) {
                    $return = false;
                }
            }
            return $return;
        } else {
            return $this->_registerFile('javascript', $file);
        }
    }

    /**
     * Registers a file.
     *
     * @param string $type the file type (javascript, css)
     * @param string $file the file name without extension
     *
     * @return boolean
     */
    protected final function _registerFile ($type, $file)
    {
        $file_name = $file;
        $type_shortcuts = array('javascript' => 'js', 'css' => 'css', 'less' => 'css');

        if ($type === 'less') {
            $path = MODULES . '/' . $this->module_name . '/' . 'web'
                . '/css/';
            $file .= '.less';
        } else {
            $path = MODULES . '/' . $this->module_name . '/' . 'web'
                . '/' . $type  . '/' ;
        }
        $file .= '.' . $type_shortcuts[$type];

        if (true === is_file($path . $file)) {
            $url = $this->base . '/' . $path . $file;
            switch ($type) {
            case 'css': case 'less':
                $this->view->css[] = array(
                    'path' => $path,
                    'file' => $file,
                    'tag' => '<link rel="stylesheet" type="text/css" href="'
                    . $url . '" media="screen" />'
                );
                break;
            case 'javascript':
                $this->view->js[] = array(
                    'path' => $path,
                    'file' => $file,
                    'tag' => '<script type="text/javascript" src="'
                    . $url . '"></script>'
                );
                break;
            }
            return true;
        } else {
            if ($type === 'css') {
                return $this->_registerFile('less', $file_name);
            }
            if (Ncw_Configure::read('debug_mode') > 0) {
                throw new Ncw_Exception('File ' . $file . ' does not exist.');
            }
        }
        return false;
    }

    /**
     * Paginate
     *
     * @param string $model the model
     * @param array $scope
     * @param array $whitelist
     *
     * @return array
     */
    public final function paginate ($model = null, $scope = array(), $whitelist = array())
    {
        if (true === is_array($model)) {
            $whitelist = $scope;
            $scope = $model;
            $model = null;
        }
        if (true === empty($model)) {
            $model = $this->name;
        }
        if (false === isset($this->{$model})) {
            $this->loadModel($model);
        }

        $options = $this->params['named'];

        if (true === isset($this->paginate[$model])) {
            $defaults = $this->paginate[$model];
        } else {
            $defaults = $this->paginate;
        }

        if (true === isset($options['show'])) {
            $options['limit'] = (int) $options['show'];
        }
        if (true === isset($options['sort'])) {
            $direction = '';
            if (true === isset($options['direction'])) {
                $direction = Ncw_Library_Sanitizer::clean($options['direction']);
            }
            $sort = Ncw_Library_Sanitizer::clean($options['sort']);
            if (false === empty($direction)) {
                $options['order'] = array(
                    $sort => $direction
                );
            } else {
                $options['order'] = $sort;
            }
        }

        $vars = array('fields', 'order', 'limit', 'page');
        $keys = array_keys($options);
        $count = count($keys);
        for ($i = 0; $i < $count; $i++) {
            if (false === in_array($keys[$i], $vars, true)) {
                unset($options[$keys[$i]]);
            }
            if (empty($whitelist) && ($keys[$i] === 'fields')) {
                unset($options[$keys[$i]]);
            } elseif (!empty($whitelist) && !in_array($keys[$i], $whitelist)) {
                unset($options[$keys[$i]]);
            }
        }
        $conditions = $fields = $order = $limit = $page = $recursive = null;

        if (false === isset($defaults['conditions'])) {
            $defaults['conditions'] = array();
        }
        if (true === isset($defaults['order'])
            && false === is_array($defaults['order'])
        ) {
            $defaults['order'] = array($defaults['order'] => 'asc');
        }

        $options = array_merge(
            array('page' => 1, 'limit' => 20),
            $defaults,
            $options
        );
        $options['limit'] = (empty($options['limit']) || !is_numeric($options['limit'])) ? 1 : (int) $options['limit'];
        extract($options);

        if (true === is_array($scope) && false === empty($scope)) {
            $conditions = array_merge($conditions, $scope);
        } elseif (true === is_string($scope)) {
            $conditions = array($conditions, $scope);
        }

        $extra = array_diff_key(
            $defaults,
            compact(
                'conditions', 'fields', 'order', 'limit', 'page'
            )
        );

        $parameters = compact('conditions');
        $count = $this->{$model}->fetch(
            'count',
            array_merge($parameters, $extra)
        );
        $page_count = intval(ceil($count / $limit));

        if ($page === 'last' || $page >= $page_count) {
            $options['page'] = $page = $page_count;
        } elseif (intval($page) < 1) {
            $options['page'] = $page = 1;
        }
        $page = $options['page'] = (integer) $page;

        $parameters = compact('conditions', 'fields', 'order', 'limit');
        if ($page > 0) {
            $parameters['limit'] = (int) $parameters['limit'] * ($page - 1) . ', ' . $parameters['limit'];
        }

        $results = $this->{$model}->fetch(
            'all',
            array_merge($parameters, $extra)
        );

        unset($defaults['conditions'], $defaults['fields'], $options['conditions'], $options['fields']);
        $paging = array(
            'page'      => $page,
            'current'   => count($results),
            'count'     => $count,
            'prevPage'  => ($page > 1),
            'nextPage'  => ($count > ($page * $limit)),
            'pageCount' => $page_count,
            'defaults'  => array_merge(array('limit' => 20, 'step' => 1), $defaults),
            'options'   => $options
        );
        $this->params['paging'][$model] = $paging;

        if (false === in_array('Paginator', $this->helpers)
            && false === array_key_exists('Paginator', $this->helpers)
        ) {
            $this->helpers[] = 'Paginator';
        }

        return $results;
    }
}
?>
