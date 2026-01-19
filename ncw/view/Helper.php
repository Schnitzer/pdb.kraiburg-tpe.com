<?php
/**
 * Contains the Helper class.
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
 * Helper class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
abstract class Ncw_Helper extends Ncw_Object
{

    /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    abstract public function startup (Ncw_View &$view);

    /**
     * Finds URL for specified action.
     *
     * Returns an URL pointing to a combination of controller and action. Param
     * $url can be:
     *  + Empty - the method will find adress to actuall controller/action.
     *  + '/' - the method will find base URL of application.
     *  + A combination of controller/action - the method will find url for it.
     *
     * @param  mixed  $url    Ncw-relative URL, like "/products/edit/92" or "/presidents/elect/4"
     *                        or an array specifying any of the following: 'controller', 'action',
     *                        and/or 'plugin', in addition to named arguments (keyed array elements),
     *                        and standard URL arguments (indexed array elements)
     * @param boolean $full   If true, the full base URL will be prepended to the result
     *
     * @return string Full translated URL with base path.
     */
    public function url ($url = null, $full = true)
    {
        return h(Ncw_Router::url($url, $full));
    }

    /**
     * Returns a space-delimited string with items of the $options array. If a
     * key of $options array happens to be one of:
     *  + 'compact'
     *  + 'checked'
     *  + 'declare'
     *  + 'readonly'
     *  + 'disabled'
     *  + 'selected'
     *  + 'defer'
     *  + 'ismap'
     *  + 'nohref'
     *  + 'noshade'
     *  + 'nowrap'
     *  + 'multiple'
     *  + 'noresize'
     *
     * And its value is one of:
     *  + 1
     *  + true
     *  + 'true'
     *
     * Then the value will be reset to be identical with key's name.
     * If the value is not one of these 3, the parameter is not output.
     *
     * @param  array  $options       Array of options.
     * @param  array  $exclude       Array of options to be excluded.
     * @param  string $insert_before String to be inserted before options.
     * @param  string $insert_after  String to be inserted ater options.
     *
     * @return string
     */
    protected final function _parseAttributes ($options, $exclude = null, $insert_before = ' ', $insert_after = null)
    {
        if (is_array($options)) {
            $options = array_merge(array('escape' => true), $options);

            if (!is_array($exclude)) {
                $exclude = array();
            }
            $keys = array_diff(
                array_keys($options),
                array_merge((array) $exclude, array('escape'))
            );
            $values = array_intersect_key(array_values($options), $keys);
            $escape = $options['escape'];
            $attributes = array();

            foreach ($keys as $index => $key) {
                $attributes[] = $this->_formatAttribute(
                    $key, $values[$index], $escape
                );
            }
            $out = implode(' ', $attributes);
        } else {
            $out = $options;
        }
        return $out ? $insert_before . $out . $insert_after : '';
    }

    /**
     * @param  string $key
     * @param  string $value
     *
     * @return string
     */
    protected final function _formatAttribute ($key, $value, $escape = true)
    {
        $attribute = '';
        $attributeFormat = '%s="%s"';
        $minimizedAttributes = array(
            'compact',
            'checked',
            'declare',
            'readonly',
            'disabled',
            'selected',
            'defer',
            'ismap',
            'nohref',
            'noshade',
            'nowrap',
            'multiple',
            'noresize'
        );
        if (true === is_array($value)) {
            $value = '';
        }

        if (true === in_array($key, $minimizedAttributes)) {
            if ($value === 1 || $value === true || $value === 'true'
                || $value == $key
            ) {
                $attribute = sprintf($attributeFormat, $key, $key);
            }
        } else {
            $attribute = sprintf(
                $attributeFormat,
                $key,
                ($escape ? h($value) : $value)
            );
        }
        return $attribute;
    }

    /**
     * Called before the View::render()
     *
     * @param Ncw_View $view the view
     *
     * @return void
     */
    public function beforeRender (Ncw_View &$view)
    {

    }

    /**
     * Called after the View::render()
     *
     * @param Ncw_View $view the view
     *
     * @return void
     */
    public function afterRender (Ncw_View &$view)
    {

    }

    /**
     * Called before the layout is rendered
     *
     * @param Ncw_View $view the view
     *
     * @return void
     */
    public function beforeLayout (Ncw_View &$view)
    {

    }

    /**
     * Called after the layout is rendered
     *
     * @param Ncw_View $view the view
     *
     * @return void
     */
    public function afterLayout (Ncw_View &$view)
    {

    }
}
?>
