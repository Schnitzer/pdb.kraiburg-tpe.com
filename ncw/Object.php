<?php
/**
 * Contains the Object class.
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
 * Object class
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Object
{

    /**
     * Calls a controller's method from any location.
     *
     * @param mixed $url String or array-based url.
     * @param array $extra if array includes the key "return" it sets the Auto Render to true.
     *
     * @return mixed Boolean true or false on success/failure, or contents
     *               of rendered action if 'return' is set in $extra.
     */
    public function requestAction ($url, $extra = array())
    {
        if (true == empty($url)) {
            return false;
        }
        if (true === in_array('return', $extra, true)) {
            $extra = array_merge(
                $extra,
                array(
                    'return' => 0,
                    'auto_render' => 1
                )
            );
        }
        if (true === is_array($url) && false === isset($extra['url'])) {
            $extra['url'] = array();
        }
        $params = array_merge(
            array(
                'auto_render' => 0,
                'return' => 1,
                'bare' => 1,
                'requested' => 1
            ),
            $extra
        );
        $dispatcher = new Ncw_Dispatcher();
        return $dispatcher->dispatch($url, $params);
    }

    /**
     * Calls a method on this object with the given parameters. Provides an OO wrapper
     * for call_user_func_array, and improves performance by using straight method calls
     * in most cases.
     *
     * @param string $method  Name of the method to call
     * @param array $params  Parameter list to use when calling $method
     *
     * @return mixed  Returns the result of the method call
     */
    public function dispatchMethod ($method, $params = array())
    {
        switch (count($params)) {
            case 0:
                return $this->{$method}();
            case 1:
                return $this->{$method}($params[0]);
            case 2:
                return $this->{$method}($params[0], $params[1]);
            case 3:
                return $this->{$method}($params[0], $params[1], $params[2]);
            case 4:
                return $this->{$method}($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return $this->{$method}($params[0], $params[1], $params[2], $params[3], $params[4]);
            default:
                return call_user_func_array(array(&$this, $method), $params);
            break;
        }
    }

    /**
     * Stop execution of the current script
     *
     * @param $status see http://php.net/exit for values
     *
     * @return void
     */
    protected function _stop ($status = 0)
    {
        exit($status);
    }

    /**
     * Log method
     *
     * @param string $message  the message to log
     * @param string $priority the priority of the message
     *
     * @return void
     */
    public final function log ($message, $priority = 'error')
    {
        if (false === is_string($message)) {
            throw new Ncw_Exception('$message must be of type string!');
        }
        if (false === is_string($priority)) {
            throw new Ncw_Exception('$priority must be of type string!');
        }
        if (true === LOG) {
            $log = Ncw_Library_LogFactory::factory('Composite');
            $log->addLogger(new Ncw_Library_Log_Logger_File($this->module_name . '.log'));
            $log->log($message, $priority);
        }
    }

    /**
     * Allows setting of multiple properties of the object in a single line of code.
     *
     * @param array $properties An associative array containing properties and corresponding values.
     *
     * @return void
     */
    protected function _set ($properties = array())
    {
        if (true === is_array($properties) && false === empty($properties)) {
            $vars = get_object_vars($this);
            foreach ($properties as $key => $val) {
                if (array_key_exists($key, $vars)) {
                    $this->{$key} = $val;
                }
            }
        }
    }

    /**
     * To string method
     *
     * @return string
     */
    public function __toString ()
    {
        $class = get_class($this);
        return $class;
    }
}

?>