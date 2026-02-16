<?php
/**
 * contains the Session component.
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  1997-2008 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version    SVN: $Id$
 * @link       http://www.netzcraftwerk.com
 * @since      File available since Release 0.1
 * @modby      $LastChangedBy$
 * @lastmod    $LastChangedDate$
 */
/**
 * Handles the sessions which are used by any module.
 * The sessions are grouped by module and because of
 * that there cannot be a douple declaration.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Components_Session extends Ncw_Component
{

    /**
     * The module name which the session must be read.
     *
     * @var string
     */
    protected $_module = '';

    /**
     * Starts the session.
     *
     * @return boolean
     */
    public static function start ()
    {
        // Check if session is already active (PHP 5.4+)
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }
        
        if (function_exists('session_write_close')) {
            session_write_close();
        }
        if (headers_sent()) {
            if (false === isset($_SESSION)) {
                $_SESSION = array();
            }
            return false;
        } elseif (false === isset($_SESSION)) {
            session_cache_limiter('must-revalidate');
            session_start();
            header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
            return true;
        } else {
            session_start();
            return true;
        }
    }

    /**
     * Regenerates the session.
     *
     * @return void
     */
    public static function regenerate ()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        $old_session_id = session_id();
        if ($old_session_id) {
            $sessionpath = session_save_path();
            if (empty($sessionpath)) {
                $sessionpath = "/tmp";
            }
            if (isset($_COOKIE[session_name()])) {
                setcookie(
                    Ncw_Configure::read('Session.cookie_name'),
                    '',
                    time() - 42000,
                    Ncw_Configure::read('Session.cookie_path')
                );
            }
            session_regenerate_id();
            $new_sessid = session_id();

            if (function_exists('session_write_close')) {
                session_write_close();
            }
            session_id($old_session_id);
            session_start();
            session_destroy();
            $file = $sessionpath . DS . 'sess_' . $old_session_id;
            @unlink($file);
            session_id($new_sessid);
            session_start();
        }
    }

    /**
     * Destroys the session.
     *
     * @return void
     */
    public static function destroy ()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Checks if the session is started
     *
     * @return boolean
     */
    public static function isStarted ()
    {
        if (true === isset($_SESSION)) {
            return true;
        }
        return false;
    }

    /**
     * Writes a session.
     *
     * @param string $name  the session name
     * @param mixed  $value the session value
     *
     * @return boolean success
     */
    public static function writeInAll ($name, $value)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (true === self::_validateKeys($name)) {
            $_SESSION[$name] = $value;
            return true;
        }
        return false;
    }

    /**
     * Reads a session.
     *
     * @param string $name the session name
     *
     * @return mixed the sesssion value or false
     */
    public static function readInAll ($name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (true === self::checkInAll($name)) {
            return $_SESSION[$name];
        }
        return false;
    }

    /**
     * Deletes a session.
     *
     * @param string $name the session name
     *
     * @return boolean true or false
     */
    public static function deleteInAll ($name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (true === self::checkInAll($name)) {
            unset($_SESSION[$name]);
            return true;
        }
        return false;
    }

    /**
     * Checks if a session with the given name exists.
     *
     * @param string $name the session to check
     *
     * @return boolean true or false
     */
    public static function checkInAll ($name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (true === self::_validateKeys($name)
            && $name !== 'modules'
            && true === array_key_exists($name, $_SESSION)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Validates the name string.
     *
     * @param string $name the session name
     *
     * @return boolean true or false.
     */
    protected static function _validateKeys ($name)
    {
        if (true === is_string($name) && preg_match("/^[ 0-9a-zA-Z._-]+$/", $name)) {
            return true;
        }
        return false;
    }

    /**
     * Sets the module name.
     *
     * @param Ncw_Controller &$controller the controller
     *
     * @return void
     */
    public function startup (Ncw_Controller &$controller)
    {
    	$this->_module = $controller->module_name;
        $controller->session = $this;
    }

    /**
     * Checks if a session to the given name exists.
     *
     * @param string $name the session to check
     *
     * @return boolean true or false
     */
    public function check ($name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (false === empty($this->_module) && true === self::_validateKeys($name) && isset($_SESSION['modules'][$this->_module][$name])) {
            return true;
        }
        return false;
    }

    /**
     * Checks if a session to the given name and module exists.
     *
     * @param string $module the module name
     * @param string $name   the session to check
     *
     * @return boolean true or false
     */
    public function checkModule ($module, $name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (false === empty($module) && true === self::_validateKeys($name) && true === array_key_exists($name, $_SESSION['modules'][$module])) {
            return true;
        }
        return false;
    }

    /**
     * Writes a session which depends on
     * the module.
     *
     * @param string $name  the session name
     * @param mixed  $value to value
     *
     * @return boolean
     */
    public function write ($name, $value)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (!empty($this->_module) && true === self::_validateKeys($name)) {
            $_SESSION['modules'][$this->_module][$name] = $value;
            return true;
        }
        return false;
    }

    /**
     * Returns the value of the session with the given name.
     *
     * @param string $name the session
     *
     * @return mixed the session value
     */
    public function read ($name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (false === empty($this->_module) && true === $this->check($name)) {
            return $_SESSION['modules'][$this->_module][$name];
        }
        return false;
    }

    /**
     * Returns the value of the session with the given name and
     * the module.
     *
     * @param string $module the module name
     * @param string $name   the session name
     *
     * @return mixed the session value
     */
    public function readModule ($module, $name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (!empty($module) && true === $this->checkInModule($module, $name)) {
            return $_SESSION['modules'][$module][$name];
        }
        return false;
    }

    /**
     * Deletes the session with the given name.
     *
     * @param string $name the session to delete
     *
     * @return boolean true or false
     */
    public function delete ($name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
        if (!empty($this->_module) && true === $this->check($name)) {
            unset($_SESSION['modules'][$this->_module][$name]);
            return true;
        }
        return false;
    }
}
?>
