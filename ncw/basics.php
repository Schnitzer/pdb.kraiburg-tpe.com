<?php
/**
 * Contains the basic functions
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
 * Convenience method for htmlspecialchars.
 *
 * @param string $text Text to wrap through htmlspecialchars
 * @param string $charset Character set to use when escaping.  Defaults to config value in 'App.encoding' or 'UTF-8'
 *
 * @return string Wrapped text
 */
function h ($text, $charset = null)
{
    if (is_array($text)) {
        return array_map('h', $text);
    }
    if (empty($charset)) {
        $charset = Ncw_Configure::read('App.encoding');
    }
    if (empty($charset)) {
        $charset = 'UTF-8';
    }
    return htmlspecialchars($text, ENT_QUOTES, $charset);
}

/**
 * Gets an environment variable from available sources, and provides emulation
 * for unsupported or inconsistent environment variables (i.e. DOCUMENT_ROOT on
 * IIS, or SCRIPT_NAME in CGI mode).  Also exposes some additional custom
 * environment information.
 *
 * @param  string $key Environment variable name.
 *
 * @return string Environment variable setting.
 */
function env ($key)
{
    if ($key == 'HTTPS') {
        if (isset($_SERVER['HTTPS'])) {
            return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        }
        return (strpos(env('SCRIPT_URI'), 'https://') === 0);
    }

    if ($key == 'SCRIPT_NAME') {
        if (env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
            $key = 'SCRIPT_URL';
        }
    }

    $val = null;
    if (isset($_SERVER[$key])) {
        $val = $_SERVER[$key];
    } elseif (isset($_ENV[$key])) {
        $val = $_ENV[$key];
    } elseif (getenv($key) !== false) {
        $val = getenv($key);
    }

    if ($key === 'REMOTE_ADDR' && $val === env('SERVER_ADDR')) {
        $addr = env('HTTP_PC_REMOTE_ADDR');
        if ($addr !== null) {
            $val = $addr;
        }
    }

    if ($val !== null) {
        return $val;
    }

    switch ($key) {
        case 'SCRIPT_FILENAME':
            if (defined('SERVER_IIS') && SERVER_IIS === true) {
                return str_replace('\\\\', '\\', env('PATH_TRANSLATED'));
            }
        break;
        case 'DOCUMENT_ROOT':
            $name = env('SCRIPT_NAME');
            $filename = env('SCRIPT_FILENAME');
            $offset = 0;
            if (!strpos($name, '.php')) {
                $offset = 4;
            }
            return substr($filename, 0, strlen($filename) - (strlen($name) + $offset));
        break;
        case 'PHP_SELF':
            return str_replace(env('DOCUMENT_ROOT'), '', env('SCRIPT_FILENAME'));
        break;
        case 'CGI_MODE':
            return (PHP_SAPI === 'cgi');
        break;
        case 'HTTP_BASE':
            $host = env('HTTP_HOST');
            if (substr_count($host, '.') !== 1) {
                return preg_replace('/^([^.])*/i', null, env('HTTP_HOST'));
            }
        return '.' . $host;
        break;
    }
    return null;
}

/**
 * Recursively strips slashes from all values in an array
 *
 * @param array $values Array of values to strip slashes
 *
 * @return mixed What is returned from calling stripslashes
 */
function stripslashes_deep($values) {
    if (is_array($values)) {
        foreach ($values as $key => $value) {
            $values[$key] = stripslashes_deep($value);
        }
    } else {
        $values = stripslashes($values);
    }
    return $values;
}
?>