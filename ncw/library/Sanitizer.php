<?php
/**
 * includes the Sanitizer class
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
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library
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
 * Data Sanitization.
 *
 * Functions to remove non alphanumeric characters, to get sql safe strings
 * and HTML friendly strings.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Library_Sanitizer
{

    /**
     * Removes none alphanumeric characters.
     * You can define special characters which
     * will be not removed.
     *
     * @param string $string  the string
     * @param Array  $allowed (optional)
     *
     * @return string
     */
    public static function removeNonAlphanumeric ($string, Array $allowed = array())
    {
        $allow = null;
        if (false === empty($allowed)) {
            foreach ($allowed as $value) {
                $allow .= "\\$value";
            }
        }

        if (true === is_array($string)) {
            $cleaned = array();
            foreach ($string as $key => $clean) {
                $cleaned[$key] = preg_replace("/[^{$allow}a-zA-Z0-9]/", '', $clean);
            }
        } else {
            $cleaned = preg_replace("/[^{$allow}a-zA-Z0-9]/", '', $string);
        }
        return $cleaned;
    }

    /**
     * Escapes the string
     *
     * @param string $string the string
     *
     * @return string
     */
    public static function escape ($string)
    {
        return addslashes($string);
    }

    /**
     * Makes the string html friendly.
     *
     * @param string  $string the string
     * @param boolean $remove set to true if you want to remove html tags
     *
     * @return string
     */
    public static function html ($string, $remove = false)
    {
        if (true === $remove) {
            $string = strip_tags($string);
        } else {
            $patterns = array("/\&/", "/%/", "/</", "/>/", '/"/', "/'/", "/\(/", "/\)/", "/\+/", "/-/");
            $replacements = array("&amp;", "&#37;", "&lt;", "&gt;", "&quot;", "&#39;", "&#40;", "&#41;", "&#43;", "&#45;");
            $string = preg_replace($patterns, $replacements, $string);
        }
        return $string;
    }

    /**
     * Removes the whitespaces in the string.
     *
     * @param string $string the string
     *
     * @return string
     */
    public static function removeWhitespace ($string)
    {
        $string = preg_replace("/[\n\r\t]+/", "", $string);
        return preg_replace("/\s{2,}/", " ", $string);
    }

    /**
     * Removes the html image tags in the string.
     *
     * @param string $string the string
     *
     * @return string
     */
    public static function removeImages ($string)
    {
        $string = preg_replace("/(<a[^>]*>)(<img[^>]+alt=\")([^\"]*)(\"[^>]*>)(<\/a>)/i", "$1$3$5<br />", $string);
        $string = preg_replace("/(<img[^>]+alt=\")([^\"]*)(\"[^>]*>)/i", "$2<br />", $string);
        return preg_replace("/<img[^>]*>/i", "", $string);
    }

    /**
     * Removes the html script tags in the string.
     *
     * @param string $string the string
     *
     * @return string
     */
    public static function removeScripts ($string)
    {
        return preg_replace("/(<link[^>]+rel=\"[^\"]*stylesheet\"[^>]*>|<img[^>]*>|style=\"[^\"]*\")|<script[^>]*>.*?<\/script>|<style[^>]*>.*?<\/style>|<!--.*?-->/i", "", $string);
    }

    /**
     * Removes the defined html tags in the string.
     *
     * param string $string the string
     * param string $tag    the first tag
     * param string $tag    the second tag and so on
     *
     * @return string
     */
    public static function removeTags ()
    {
        $params = func_get_args();
        $string = $params[0];
        for ($i = 1; $i < count($params); $i++) {
            $string = preg_replace("/<" . $params[$i] . "[^>]*>/i", "", $string);
            $string = preg_replace("/<\/" . $params[$i] . "[^>]*>/i", "", $string);
        }
        return $string;
    }

    /**
     * Removes the whitespaces, images and scripts.
     *
     * @param string $string the string
     *
     * @return string
     */
    public static function removeAll ($string)
    {
        $string = self::removeWhitespace($string);
        $string = self::removeImages($string);
        return self::removeScripts($string);
    }

    /**
     * Removes the html and escapes the string.
     * This function is good to use when
     * you have user input that needs to
     * be cleaned.
     *
     * @param mixed $data    the string
     * @param Array $options (optional)
     *
     * @return mixed
     */
    public static function clean ($data, $options = array())
    {
        if (true === empty($data)) {
            return $data;
        }

        $options = array_merge(array('odd_spaces' => true, 'encode' => true, 'dollar' => true, 'carriage' => true, 'unicode' => true, 'escape' => true, 'backslash' => true), $options);

        if (true === is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = self::clean($val, $options);
            }
            return $data;
        } else {
            if (true === $options['odd_spaces']) {
                $data = str_replace(chr(0xCA), '', str_replace(' ', ' ', $data));
            }
            if (true === $options['encode']) {
                $data = self::html($data);
            }
            if (true === $options['dollar']) {
                $data = str_replace("\\\$", "$", $data);
            }
            if (true === $options['carriage']) {
                $data = str_replace("\r", "", $data);
            }

            $data = str_replace("'", "'", str_replace("!", "!", $data));

            if (true === $options['unicode']) {
                $data = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $data);
            }
            if (true === $options['escape']) {
                $data = self::escape($data);
            }
            if (true === $options['backslash']) {
                $data = preg_replace("/\\\(?!&amp;#|\?#)/", "\\", $data);
            }
            return $data;
        }
    }
}
?>