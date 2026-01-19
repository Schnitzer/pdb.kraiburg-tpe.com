<?php
/**
 * Contains the Dispatcher class.
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
 * Dispatcher
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Dispatcher extends Ncw_Object
{

    /**
     * Base URL
     *
     * @var string
     */
    public $base = false;

    /**
     * webroot path
     *
     * @var string
     */
    public $webroot = '/';

    /**
     * Current URL
     *
     * @var string
     */
    public $here = false;

    /**
     * Admin route (if on it)
     *
     * @var string
     */
    public $admin = false;

    /**
     * Module being used
     *
     * @var string
     */
    public $module_name = null;

    /**
     * the params for this request
     *
     * @var string
     */
    public $params = null;

    /**
     * Construct the dispatcher
     *
     * @return void
     */
    public function __construct ()
    {
        // Include the database configurations.
        include_once 'config/database.php';
    }

    /**
     * Dispatches and invokes the given url
     *
     * @param string $url               the url to dispatch
     * @param array  $additional_params the params array
     *
     * @return void
     */
    public function dispatch ($url = null, $additional_params = array())
    {
        if ($this->base === false) {
            $this->base = $this->baseUrl();
        }

        if (true === is_array($url)) {
            $url = $this->_extractParams($url, $additional_params);
        } else {
            if ($url) {
                $_GET['url'] = $url;
            }
            $url = $this->getUrl();
            $this->params = array_merge(
                $this->parseParams($url), $additional_params
            );
        }

        if (true === Ncw_Configure::read('App.rewrite')) {
            $this->here = $this->base . '/' . $url;
        } else {
            $this->here = $this->base . '/index.php?url=' . $url;
        }

        // if the view is cached
        if (true === $this->_cached($url)) {
            $this->_stop();
        }

        // Load the controller
        $controller = $this->_getController();

        // set attributest
        $controller->base = $this->base;
        $controller->here = $this->here;
        $controller->module_name = $this->module_name;
        $controller->name = ucfirst($this->params['controller']);
        $controller->view_path = strtolower($controller->name);
        $controller->prefix = $this->params['prefix'];
        $controller->webroot = MODULES . '/' . $this->params['module'] . '/'
            . 'web';
        $controller->params =& $this->params;
        $controller->action =& $this->params['action'];
        $controller->passed_args = array_merge(
            $this->params['pass'],
            $this->params['named']
        );

        if (false === empty($this->params['data'])) {
            $controller->data =& $this->params['data'];
        } else {
            $controller->data = null;
        }
        if (true === array_key_exists('return', $this->params)
            && $this->params['return'] == 1
        ) {
            $controller->auto_render = false;
        }
        if (false === empty($this->params['bare'])) {
            $controller->auto_layout = false;
        }
        if (true === array_key_exists('layout', $this->params)) {
            if (true === empty($this->params['layout'])) {
                $controller->auto_layout = false;
            } else {
                $controller->layout = $this->params['layout'];
            }
        }
        if (true === isset($this->params['viewPath'])) {
            $controller->view_path = $this->params['viewPath'];
        }

        return $this->_invoke($controller, $this->params['action']);
    }

    /**
     * Sets the params when $url is passed as an array to Object::requestAction();
     *
     * @param array $url
     * @param array $additional_params
     *
     * @return string $url
     */
    protected function _extractParams ($url, $additional_params = array())
    {
        $defaults = array(
            'pass' => array(),
            'named' => array(),
            'form' => array(),
            'prefix' => Ncw_Configure::read('Router.prefix'),
        );
        $this->params = array_merge($defaults, Ncw_Router::requestRoute(), $url, $additional_params);
        return Ncw_Router::url($url);
    }

    /**
     * Returns array of GET and POST parameters. GET parameters are taken from given URL.
     *
     * @param string $from_url URL to mine for parameter information.
     *
     * @return array Parameters found in POST and GET.
     */
    public function parseParams ($from_url)
    {
        $params = array();

        if (isset($_POST)) {
            $params['form'] = $_POST;
            if (ini_get('magic_quotes_gpc') === '1') {
                $params['form'] = stripslashes_deep($params['form']);
            }
            if (env('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $params['form']['_method'] = env('HTTP_X_HTTP_METHOD_OVERRIDE');
            }
            if (true === isset($params['form']['_method'])) {
                if (false === empty($_SERVER)) {
                    $_SERVER['REQUEST_METHOD'] = $params['form']['_method'];
                } else {
                    $_ENV['REQUEST_METHOD'] = $params['form']['_method'];
                }
                unset($params['form']['_method']);
            }
        }
        /*$namedExpressions = Ncw_Router::getNamedExpressions();
        extract($namedExpressions);*/
        include CONFIG . DS . 'routes.php';
        $params = array_merge(Ncw_Router::parse($from_url), $params);

        if (strlen($params['action']) === 0) {
            $params['action'] = 'index';
        }
        if (true === isset($params['form']['data'])) {
            $params['data'] = Ncw_Router::stripEscape($params['form']['data']);
            unset($params['form']['data']);
        }
        if (true === isset($_GET)) {
            if (ini_get('magic_quotes_gpc') === '1') {
                $url = stripslashes_deep($_GET);
            } else {
                $url = $_GET;
            }
            if (true === isset($params['url'])) {
                $params['url'] = array_merge($params['url'], $url);
            } else {
                $params['url'] = $url;
            }
        }

        foreach ($_FILES as $name => $data) {
            if ($name != 'data') {
                $params['form'][$name] = $data;
            }
        }

        if (isset($_FILES['data'])) {
            foreach ($_FILES['data'] as $key => $data) {
                foreach ($data as $model => $fields) {
                    if (true === is_array($fields)) {
                        foreach ($fields as $field => $value) {
                            if (true === is_array($value)) {
                                foreach ($value as $k => $v) {
                                    $params['data'][$model][$field][$k][$key] = $v;
                                }
                            } else {
                                $params['data'][$model][$field][$key] = $value;
                            }
                        }
                    } else {
                        $params['data'][$model][$key] = $fields;
                    }
                }
            }
        }
        return $params;
    }

    /**
     * Returns a base URL and sets the proper webroot
     *
     * @return string Base URL
     */
    public function baseUrl() {
        $this->webroot = Ncw_Configure::read('Project.url') . '/';
        return $this->base = Ncw_Configure::read('Project.url');
        /*$dir = $webroot = null;
        $config = Configure::read('App');
        extract($config);

        if (false === Ncw_Configure::read('Project.url')) {
            $base = $this->base;
        }
        if (Ncw_Configure::read('Project.url') !== false) {
            $this->webroot = Ncw_Configure::read('Project.url') . '/';
            return $this->base = Ncw_Configure::read('Project.url');
        }*/
        /*if (!$baseUrl) {
            $replace = array('<', '>', '*', '\'', '"');
            $base = str_replace($replace, '', dirname(env('PHP_SELF')));

            if ($webroot === 'web' && $webroot === basename($base)) {
                $base = dirname($base);
            }
            if ($dir === 'modules' && $dir === basename($base)) {
                $base = dirname($base);
            }

            if ($base === DS || $base === '.') {
                $base = '';
            }

            $this->webroot = $base .'/';
            return $base;
        }

        $file = '/' . basename($baseUrl);
        $base = dirname($baseUrl);

        if ($base === DS || $base === '.') {
            $base = '';
        }
        $this->webroot = $base .'/';

        if (!empty($base)) {
            if (strpos($this->webroot, $dir) === false) {
                $this->webroot .= $dir . '/' ;
            }
            if (strpos($this->webroot, $webroot) === false) {
                $this->webroot .= $webroot . '/';
            }
        }
        return $base . $file;*/
    }

    /**
     * Invokes given controller's render action if auto render is set
     *
     * @param Ncw_Controller &$controller the controller
     * @param string         $action_name the action to call
     *
     * @return void
     */
    protected function _invoke (Ncw_Controller &$controller, $action_name)
    {
        try {
            $controller->constructClasses();
            $controller->components_object->initialize($controller);
            $controller->components_object->startup($controller);
            $controller->beforeFilter();

            // call the action
            $output = $controller->dispatchMethod(
                $action_name . 'Action',
                $this->params['pass']
            );
            if (true === $controller->auto_render) {
                $controller->output = $controller->render();
            } else if (true === empty($controller->output)) {
                $controller->output = $output;
            }

            $controller->components_object->shutdown($controller);
            $controller->afterFilter();

            if (true === isset($this->params['return'])) {
                return $controller->output;
            }
            print $controller->output;
        } catch (Ncw_Exception $e) {
            if (Ncw_Configure::read('debug_mode') > 0) {
                $e->exitWithMessage();
            }
        }
    }

    /**
     * Get controller to use, either plugin controller or application controller
     *
     * @param array $params Array of parameters
     *
     * @return mixed name of controller if not loaded, or object if loaded
     */
    protected function &_getController ($params = null)
    {
        if (false === is_array($params)) {
            $params = $this->params;
        }
        $controller_object = $this->__loadController($params);
        return $controller_object;
    }

    /**
     * Prepares the controller to be used and calls the action.
     *
     * @param string $params the params
     *
     * @return void
     */
    private function __loadController ($params)
    {
        try {
            $module_name = $controller = null;
            if (false == empty($params['module'])) {
                $module_name = $this->module_name = $params['module'];
                $controller = $this->params['controller'] = $module_name;
            }
            if (false === empty($params['controller'])) {
                $controller = $this->params['controller'] = $params['controller'];
            }
            if ($module_name . $controller) {
                if (Ncw_Configure::read('debug_mode') > 0) {
                    Ncw_Bootstrap::$timer->setMarker('dispatched');
                }
                $controller_class = $module_name . '_' . ucfirst($controller) . 'Controller';
                return new $controller_class();
            }
        } catch (Ncw_Exception $e) {
            if (Ncw_Configure::read('debug_mode') > 0) {
                $e->exitWithMessage();
            }
        }
        return false;
    }

    /**
     * Returns the REQUEST_URI from the server environment, or, failing that,
     * constructs a new one, using the PHP_SELF constant and other variables.
     *
     * @return string URI
     */
    public function uri ()
    {
        foreach (array('HTTP_X_REWRITE_URL', 'REQUEST_URI', 'argv') as $var) {
            if ($uri = env($var)) {
                if ($var == 'argv') {
                    $uri = $uri[0];
                }
                break;
            }
        }
        $base = preg_replace('/^\//', '', '' . Ncw_Configure::read('Project.url'));

        if ($base) {
            $uri = preg_replace('/^(?:\/)?(?:' . preg_quote($base, '/') . ')?(?:url=)?/', '', $uri);
        }
        if (PHP_SAPI == 'isapi') {
            $uri = preg_replace('/^(?:\/)?(?:\/)?(?:\?)?(?:url=)?/', '', $uri);
        }
        if (!empty($uri)) {
            if (key($_GET) && strpos(key($_GET), '?') !== false) {
                unset($_GET[key($_GET)]);
            }
            $uri = explode('?', $uri, 2);

            if (isset($uri[1])) {
                parse_str($uri[1], $_GET);
            }
            $uri = $uri[0];
        } else {
            $uri = env('QUERY_STRING');
        }
        if (is_string($uri) && strpos($uri, 'index.php') !== false) {
            list(, $uri) = explode('index.php', $uri, 2);
        }
        if (empty($uri) || $uri == '/' || $uri == '//') {
            return '';
        }
        return str_replace('//', '/', '/' . $uri);
    }

    /**
     * Returns and sets the $_GET[url] derived from the REQUEST_URI
     *
     * @param string $uri Request URI
     * @param string $base Base path
     *
     * @return string URL
     */
    public function getUrl ($uri = null, $base = null)
    {
        if (true === empty($_GET['url'])) {
            return '';

            /*if ($uri == null) {
                $uri = $this->uri();
            }
            if ($base == null) {
                $base = $this->base;
            }
            $url = null;
            $tmp_ori = preg_replace('/^(?:\?)?(?:\/)?/', '', $uri);
            $base_dir = preg_replace('/^\//', '', dirname($base)) . '/';

            if ($tmp_ori === '/' || $tmp_ori == $base_dir || $tmp_ori == $base) {
                $url = $_GET['url'] = '/';
            } else {
                if ($base && strpos($uri, $base) !== false) {
                    $elements = explode($base, $uri);
                } elseif (preg_match('/^[\/\?\/|\/\?|\?\/]/', $uri)) {
                    $elements = array(1 => preg_replace('/^[\/\?\/|\/\?|\?\/]/', '', $uri));
                } else {
                    $elements = array();
                }

                if (!empty($elements[1])) {
                    $_GET['url'] = $elements[1];
                    $url = $elements[1];
                } else {
                    $url = $_GET['url'] = '/';
                }

                if (strpos($url, '/') === 0 && $url != '/') {
                    $url = $_GET['url'] = substr($url, 1);
                }
            }*/
        } else {
            $url = $_GET['url'];
        }
        if ($url{0} == '/') {
            $url = substr($url, 1);
        }
        return $url;
    }

    /**
     * Check if the view is cached and show it if it is.
     *
     * @param string $url the file url
     *
     * @return boolean
     */
    protected function _cached ($url)
    {
        if (false !== strpos($url, 'css/')
            || false !== strpos($url, 'javascript/')
            || false !== strpos($url, 'images/')
        ) {
            $is_asset = false;
            $assets = array(
                'js' => 'text/javascript',
                'css' => 'text/css',
                'gif' => 'image/gif',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
            );
            $extension = explode('.', $url);
            $extension = array_pop($extension);
            $pos = 0;
            foreach ($assets as $type => $content_type) {
                if ($type === $extension) {
                    $parts = explode('/', $url);
                    $with_assets = false;
                    if ($parts[0] === 'css'
                        || $parts[0] === 'javascript'
                        || $parts[0] === 'images'
                    ) {
                        $pos = 0;
                    } else if (($parts[1] === 'css'
                        || $parts[1] === 'javascript'
                        || $parts[1] === 'images')
                        && $parts[0] === ASSETS
                    ) {
                        $with_assets = true;
                        $pos = 0;
                    } else if (($parts[1] === 'css'
                        || $parts[1] === 'javascript'
                        || $parts[1] === 'images')
                    ) {
                        $pos = strlen($parts[0]);
                    } else {
                        $with_assets = true;
                        $pos = strlen($parts[1]);
                    }
                    $is_asset = true;
                    break;
                }
            }
            // if js, css, image
            if (true === $is_asset) {
                $asset_file = null;
                $path = '';
                if (true === $with_assets) {
                    $module = substr($url, 7, $pos);
                    if (false === empty($module)) {
                        $url = preg_replace(
                            '/^' . preg_quote(ASSETS . '/' . $module, '/') . '\//i', '',
                            $url
                        );
                        $path .= ASSETS . '/' . $module . '/';
                    } else {
                        $url = preg_replace(
                            '/^' . preg_quote(ASSETS, '/') . '\//i', '',
                            $url
                        );
                        $path = ASSETS . '/';
                    }
                } else {
                    $module = substr($url, 0, $pos);
                    $url = preg_replace('/^' . preg_quote($module, '/') . '\//i', '', $url);
                    $path = ASSETS . '/' . $module . '/';
                }
                //print $path . $url;
                if (is_file($path . $url) && file_exists($path . $url)) {
                    $asset_file = $path . $url;
                }
                if ($asset_file !== null) {
                    $cache_expires = Ncw_Configure::read('Cache.expires');
                    header('Content-type: ' . $content_type);
                    header("Vary: Accept-Encoding");
                    header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($asset_file)));
                    header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cache_expires) . " GMT");
                    header("Cache-Control: public, max-age=" . $cache_expires);
                    header("Pragma: cache");
                    if ($type === 'css' || $type === 'javascript') {
                        include($asset_file);
                    } else {
                        readfile($asset_file);
                    }
                    return true;
                }
            }
        }

        if (false === Ncw_Configure::read('Cache')) {
            $group = $this->params['module'];
            if (false === file_exists(TMP . DS . 'cache' . DS . 'views' . DS . $group)) {
                return false;
            }
            $cache = new Ncw_Helpers_Cache();
            $cache_id = $cache->object->generateID(implode('/', $this->params['url']));
            // check if the action is already cached
            if (true === $cache->object->isCached($cache_id, $group)
                && false === $cache->object->isExpired($cache_id, $group)
            ) {
                $cached_view = $cache->object->get($cache_id, $group);
                if (true === Cache::isError($cached_view)) {
                    throw new Ncw_Exception($cached_view->getMessage());
                }
                print $cached_view;
                return true;
            }
        }
        return false;
    }
}
?>
