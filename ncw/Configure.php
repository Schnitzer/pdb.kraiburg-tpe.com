<?php
/**
 * Contains the Configure class.
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
 * Configure class
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Configure extends Ncw_Object
{

    /**
     * The configures
     *
     * @var array
     */
    protected static $_configures = array();

    /**
     * Writes a configure
     *
     * @param string $name  the configure name
     * @param mixed  $value the configure value
     *
     * @return void
     */
    public static function write ($name, $value = null)
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string');
        }
        self::$_configures[$name] = $value;
    }

    /**
     * Read the configure value
     *
     * @param string $name the configure to read
     *
     * @return mixed
     */
    public static function read ($name)
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string');
        }
        if (false === self::check($name)) {
            throw new Ncw_Exception($name . ' is not set');
        }
        return self::$_configures[$name];
    }

    /**
     * Delete a configure
     *
     * @param string $name the configure to delete
     *
     * @return void
     */
    public static function delete ($name)
    {
        if (false === self::check($name)) {
            throw new Ncw_Exception('$name is not set');
        }
        unset(self::$_configures[$name]);
    }

    /**
     * Checks if a configure is set
     *
     * @param string $name the configure to check
     *
     * @return boolean
     */
    public static function check ($name)
    {
        if (true === isset(self::$_configures[$name])) {
            return true;
        }
        return false;
    }
}
?>
