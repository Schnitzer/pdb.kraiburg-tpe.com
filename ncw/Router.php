<?php

/**
 * Contains the Router class.
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
 * Router class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Router
{
    /**
     * The connected routes
     *
     * @var Array
     */
    protected static $_routes = array();

    /**
     * The current route
     *
     * @var array
     */
    private static $__current_route = array();

    /**
     * Connect a route
     *
     * @param string $url   the url to connect
     * @param Array  $route the route
     *
     * @return void
     */
    public static function connect($url, $route)
    {
        $route = array_merge(
            array(
                'prefix' => '',
                'module' => '',
                'controller' => 'index',
                'action' => 'index',
                'params' => array()
            ),
            $route
        );
        if (true === is_array($route['params'])) {
            $route['params'] = array_merge(
                array('pass' => array(), 'named' => array()),
                $route['params']
            );
        }
        self::$_routes[$url] = $route;
    }

    /**
     * Parses the route of the given url
     *
     * @param string $url  the url to check
     *
     * @return Array
     */
    public static function parse($url)
    {
        if (ini_get('magic_quotes_gpc') === '1') {
            $url = stripslashes_deep($url);
        }

        $result = self::_checkRoutes($url);
        // If not route was found.
        if (false !== $result) {
            $route = $result;
        } else {
            $route = self::_noRoute($url);
        }

        // set params
        if (true === Ncw_Configure::check('Router.prefix') &&
                false !== Ncw_Configure::read('Router.prefix')) {
            $route['params']['prefix'] = Ncw_Configure::read('Router.prefix');
        } else {
            $route['params']['prefix'] = false;
        }
        $route['params']['module'] = $route['module'];
        $route['params']['controller'] = $route['controller'];

        $extension = '';
        if (false !== strpos($route['action'], '.')) {
            $splitted_action = explode('.', $route['action']);
            $extension = array_pop($splitted_action);
            $route['action'] = implode('.', $splitted_action);
        }

        $route['params']['action'] = $route['action'];

        $route['params']['url'] = array(
            'url' => $url,
        );
        if (false === empty($extension)) {
            $route['params']['url']['ext'] = $extension;
        }
        /*foreach ($_GET as $key => $value) {
            $route['params']['url'][$key] = $value;
        }*/

        self::$__current_route[] = $route;

        return $route['params'];
    }

    /**
     * Checks it a route for the given url is set.
     *
     * @param string $url the url to check
     *
     * @return mixed
     */
    protected static function _checkRoutes($url)
    {
        // check if a route matches
        foreach (self::$_routes as $route_regex => $route) {
            $matches = array();
            $route_regex = str_replace('*', '([-0-9a-zA-Z_/.:]*)', $route_regex);
            if (true == preg_match('=^' . $route_regex . '[/]?$=', $url, $matches)) {
                if (true === isset($matches[1])) {
                    $route_elements = explode('/', $matches[1]);
                    $num = count($route_elements);
                    if (true === empty($route_elements[$num - 1])) {
                        array_pop($route_elements);
                        --$num;
                    }

                    if (true === isset($route_elements[0])) {
                        if (false === isset($route_elements[1])) {
                            $route_elements[1] = 'index';
                        }
                        if (false === isset($route_elements[2])) {
                            $route_elements[2] = 'index';
                        }
                        array_walk_recursive(
                            $route,
                            array('Ncw_Router', '_replaceTags'),
                            array(
                                'value' => array(
                                    $route_elements[0],
                                    $route_elements[1],
                                    $route_elements[2],
                                    $matches[1]
                                ),
                                'tags' => array(
                                    ':module',
                                    ':controller',
                                    ':action',
                                    ':url'
                                )
                            )
                        );
                        if ($route['params'] === ':params') {
                            $route['params'] = self::_getParams($route_elements);
                        }
                    } else {
                        array_walk_recursive(
                            $route,
                            array('Ncw_Router', '_replaceTags'),
                            array(
                                'value' => '',
                                'tags' => ':url'
                            )
                        );
                    }
                }
                if (true === empty($route['controller'])) {
                    $route['controller'] = 'index';
                }
                if (true === empty($route['action'])) {
                    $route['action'] = 'index';
                }
                return array(
                    'module' => $route['module'],
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'params' => $route['params']
                );
            }
        }
        return false;
    }

    /**
     * Replaces the tags within a route
     *
     * @param array $route_value
     * @param array $key
     * @param array $value
     *
     * @return array
     */
    protected static function _replaceTags(&$route_value, $key, $userdata = null)
    {
        extract($userdata);
        $route_value = str_replace($tags, $value, $route_value);
    }

    /**
     * When no routes matches.
     *
     * @param string $url the url to build the route from
     *
     * @return Array
     */
    protected static function _noRoute($url)
    {
        $controller = '';
        $action = '';

        $route = explode('/', trim($url, '/'));
        $index_start = 0;
        if (true === Ncw_Configure::check('Router.prefix') &&
                false !== Ncw_Configure::read('Router.prefix')) {
            $index_start = 1;
        }
        $module = $route[$index_start++];
        if (true === isset($route[$index_start]) &&
                false === empty($route[$index_start])) {
            $controller = $route[$index_start++];
        }
        if (true === isset($route[$index_start]) &&
                false === empty($route[$index_start])) {
            $action = $route[$index_start++];
        }
        $params = self::_getParams($route);

        if (true === empty($controller)) {
            $controller = 'index';
        }
        if (true === empty($action)) {
            $action = 'index';
        }

        return array(
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'params' => $params
        );
    }

    /**
     * Reads the params
     *
     * @param array $route the route
     *
     * @return array
     */
    protected static function _getParams($route)
    {
        $params = array('pass' => array(), 'named' => array());
        if (true === isset($route[3])) {
            $num = count($route);
            for ($count = 3; $count < $num; ++$count) {
                if (false === strpos($route[$count], ':')) {
                    $params['pass'][] = $route[$count];
                } else {
                    $param = explode(':', $route[$count]);
                    $params['named'][$param[0]] = $param[1];
                }
            }
        }
        return $params;
    }

    /**
     * Finds URL for specified action.
     *
     * Returns an URL pointing to a combination of controller and action. Param
     * $url can be:
     *
     * - Empty - the method will find adress to actuall controller/action.
     * - '/' - the method will find base URL of application.
     * - A combination of controller/action - the method will find url for it.
     *
     * @param mixed $url like "/products/edit/92"
     * @param mixed $full If (bool) true, the full base URL will be prepended to the result.
     *
     * @return string Full translated URL with base path.
     */
    public static function url($url = null, $full = false)
    {
        if (true === is_null($url) || true === empty($url)) {
            $url = array();
        }

        $extension = $q = null;

        if (true === is_array($url)) {
            $request_route = self::requestRoute();
            if (true === Ncw_Configure::check('Router.prefix') &&
                    false !== Ncw_Configure::check('Router.prefix')) {
                $prefix = Ncw_Configure::read('Router.prefix');
            } else {
                $prefix = false;
            }
            $url = array_merge(
                array(
                    'prefix' => $prefix,
                    'module' => $request_route['module'],
                    'controller' => '',
                    'action' => '',
                    'id' => null
                ),
                $url
            );
            if (false === empty($url['action']) &&
                    true === empty($url['controller']) &&
                    $url['module'] === $request_route['module']) {
                $url['controller'] = $request_route['controller'];
            }

            $url_string = '';
            if (false === empty($url['prefix'])) {
                $url_string .= $url['prefix'] . '/';
            }

            if (true === isset($url['ext'])) {
                $extension = '.' . $url['ext'];
                unset($url['ext']);
            }

            if (false === empty($url['module'])) {
                $url_string .= $url['module'] . '/';
                if (false === empty($url['controller'])) {
                    $url_string .= $url['controller'] . '/';
                    if (false === empty($url['action'])) {
                        $url_string .= $url['action'] . $extension;
                        if (false == is_null($url['id'])) {
                            $url_string .= '/' . $url['id'];
                        }
                    }
                }
            }

            if (true === isset($url['?'])) {
                $q = $url['?'];
                unset($url['?']);
            }

            unset(
                $url['prefix'],
                $url['module'],
                $url['controller'],
                $url['action'],
                $url['id'],
                $url['ext'],
                $url['?']
            );

            foreach ($url as $key => $value) {
                if (true === is_array($value) ||
                        (is_string($value) && strlen($value) == 0)) {
                    continue;
                }
                $url_string .= '/';
                if (true === is_string($key)) {
                    $url_string .= $key . ':';
                }
                $url_string .= $value;
            }
        } else {
            if ($url === '/') {
                return Ncw_Configure::read('Project.url');
            } else {
                $url_string = '';
                if (true === Ncw_Configure::check('Router.prefix') &&
                        false !== Ncw_Configure::check('Router.prefix')) {
                    $url_string .= Ncw_Configure::read('Router.prefix') . '/';
                }
                if (strpos($url, '/') === 0) {
                    $url = substr($url, 1, strlen($url));
                }
                $url_string .= $url;
            }
        }

        /*$url = self::getRoute($url_string);
        $url_string = $url['params']['url']['url'];*/

        if (false === Ncw_Configure::read('App.rewrite')) {
            $url_string = 'index.php?url=' . $url_string;
        }

        if (true === $full) {
            $url_string = Ncw_Configure::read('Project.url') . '/' . $url_string;
        }

        return $url_string . Ncw_Router::queryString($q);
    }

    /**
     * Strip escape characters from parameter values.
     *
     * @param mixed $param Either an array, or a string
     *
     * @return mixed Array or string escaped
     */
    public static function stripEscape($param)
    {
        if (false === is_array($param) || true === empty($param)) {
            if (true === is_bool($param)) {
                return $param;
            }

            return preg_replace('/^(?:[\t ]*(?:-!)+)/', '', $param);
        }

        foreach ($param as $key => $value) {
            if (true === is_string($value)) {
                $return[$key] = preg_replace('/^(?:[\t ]*(?:-!)+)/', '', $value);
            } else {
                foreach ($value as $array => $string) {
                    $return[$key][$array] = self::stripEscape($string);
                }
            }
        }
        return $return;
    }

    /**
     * Returns the route matching the current request URL.
     *
     * @return array Matching route
     */
    public static function requestRoute()
    {
        return self::$__current_route[0];
    }

    /**
     * Returns the route matching the current request (useful for requestAction traces)
     *
     * @return array Matching route
     */
    public static function currentRoute()
    {
        return self::$__current_route[count(self::$__current_route) - 1];
    }

    /**
     * Generates a well-formed querystring from $q
     *
     * @param mixed $q Query string
     * @param array $extra Extra querystring parameters.
     * @param bool $escape Whether or not to use escaped &
     *
     * @return array
     */
    public static function queryString($q, $extra = array(), $escape = false)
    {
        if (true === empty($q) && true === empty($extra)) {
            return null;
        }
        $join = '&';
        if ($escape === true) {
            $join = '&amp;';
        }
        $out = '';

        if (is_array($q)) {
            $q = array_merge($extra, $q);
        } else {
            $out = $q;
            $q = $extra;
        }
        $out .= http_build_query($q, '', $join);
        if (true === isset($out[0]) && $out[0] != '?') {
            if (true === Ncw_Configure::read('App.rewrite')) {
                $out = '?' . $out;
            } else {
                $out = $join . $out;
            }
        }
        return $out;
    }
}
?>
