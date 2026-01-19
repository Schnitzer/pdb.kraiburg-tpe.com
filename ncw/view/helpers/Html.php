<?php
/**
 * contains the Html helper class
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
 * Html helper methods.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Helpers_Html extends Ncw_Helper
{

    /**
     * The model name.
     *
     * @var string
     */
    protected $_module = '';

    /**
     * The controller name.
     *
     * @var string
     */
    protected $_controller = '';

    /**
     * The base path
     *
     * @var string
     */
    protected $_base = '';

    /**
     * The theme path
     *
     * @var string
     */
    protected $_theme_path = '';

    /**
     * The view object
     *
     * @var Ncw_View
     */
    protected $_view = null;

    /**
     * The existing tags
     *
     * @var array
     */
    protected $_tags = array(
        'meta' => '<meta%s/>',
        'metalink' => '<link href="%s"%s/>',
        'link' => '<a href="%s"%s>%s</a>',
        'mailto' => '<a href="mailto:%s" %s>%s</a>',
        'form' => '<form %s>',
        'formend' => '</form>',
        'input' => '<input name="%s" %s/>',
        'textarea' => '<textarea name="%s" %s>%s</textarea>',
        'hidden' => '<input type="hidden" name="%s" %s/>',
        'checkbox' => '<input type="checkbox" name="%s" %s/>',
        'checkboxmultiple' => '<input type="checkbox" name="%s[]"%s />',
        'radio' => '<input type="radio" name="%s" id="%s" %s />%s',
        'selectstart' => '<select name="%s"%s>',
        'selectmultiplestart' => '<select name="%s[]"%s>',
        'selectempty' => '<option value=""%s>&nbsp;</option>',
        'selectoption' => '<option value="%s"%s>%s</option>',
        'selectend' => '</select>',
        'optiongroup' => '<optgroup label="%s"%s>',
        'optiongroupend' => '</optgroup>',
        'checkboxmultiplestart' => '',
        'checkboxmultipleend' => '',
        'password' => '<input type="password" name="%s" %s/>',
        'file' => '<input type="file" name="%s" %s/>',
        'file_no_model' => '<input type="file" name="%s" %s/>',
        'submit' => '<input type="submit" %s/>',
        'submitimage' => '<input type="image" src="%s" %s/>',
        'button' => '<input type="%s" %s/>',
        'image' => '<img src="%s" %s/>',
        'tableheader' => '<th%s>%s</th>',
        'tableheaderrow' => '<tr%s>%s</tr>',
        'tablecell' => '<td%s>%s</td>',
        'tablerow' => '<tr%s>%s</tr>',
        'block' => '<div%s>%s</div>',
        'blockstart' => '<div%s>',
        'blockend' => '</div>',
        'tag' => '<%s%s>%s</%s>',
        'tagstart' => '<%s%s>',
        'tagend' => '</%s>',
        'para' => '<p%s>%s</p>',
        'parastart' => '<p%s>',
        'label' => '<label for="%s"%s>%s</label>',
        'fieldset' => '<fieldset%s>%s</fieldset>',
        'fieldsetstart' => '<fieldset><legend>%s</legend>',
        'fieldsetend' => '</fieldset>',
        'legend' => '<legend>%s</legend>',
        'css' => '<link rel="%s" type="text/css" href="%s" %s/>',
        'style' => '<style type="text/css"%s>%s</style>',
        'charset' => '<meta http-equiv="Content-Type" content="text/html; charset=%s" />',
        'ul' => '<ul%s>%s</ul>',
        'ol' => '<ol%s>%s</ol>',
        'li' => '<li%s>%s</li>',
        'error' => '<div%s>%s</div>'
    );

    /**
     * The existing doc types
     *
     * @var array
     */
    protected $_doc_types = array(
        'html4-strict'  => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
        'html4-trans'  => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
        'html4-frame'  => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
        'xhtml-strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        'xhtml-trans' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        'xhtml-frame' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
        'xhtml11' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'
    );

    /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    public function startup (Ncw_View &$view)
    {
        $this->_module = $view->controller->module_name;
        $this->_controller = strtolower($view->controller->name);
        $this->_base = $view->base;
        $this->_theme_path = $view->theme_path;
        $this->_view =& $view;
        $view->html = $this;
    }

    /**
     * Returns a charset META-tag.
     *
     * @param  string  $charset The character set to be used in the meta tag. Example: "utf-8".
     *
     * @return string A meta tag containing the specified character set.
     */
    public function charset ($charset = null)
    {
        return sprintf($this->_tags['charset'], (!empty($charset) ? $charset : 'utf-8'));
    }

    /**
     * Returns a doctype string.
     *
     * Possible doctypes:
     *   + html4-strict:  HTML4 Strict.
     *   + html4-trans:  HTML4 Transitional.
     *   + html4-frame:  HTML4 Frameset.
     *   + xhtml-strict: XHTML1 Strict.
     *   + xhtml-trans: XHTML1 Transitional.
     *   + xhtml-frame: XHTML1 Frameset.
     *   + xhtml11: XHTML1.1.
     *
     * @param  string $type Doctype to use.
     *
     * @return string Doctype.
     */
    public function docType ($type = 'xhtml-strict')
    {
        if (true === isset($this->_doc_types[$type])) {
            return $this->_doc_types[$type];
        }
        return null;
    }

    /**
     * Creates a link to an external resource and handles basic meta tags
     *
     * @param  string  $type       The title of the external resource
     * @param  mixed   $url        The address of the external resource or string for content attribute
     * @param  array   $attributes Other attributes for the generated tag. If the type attribute is html, rss, atom, or icon, the mime-type is returned.
     *
     * @return string
     */
    public function meta ($type, $url = null, $attributes = array())
    {
        if (false === is_array($type)) {
            $types = array(
                'rss'   => array('type' => 'application/rss+xml', 'rel' => 'alternate', 'title' => $type, 'link' => $url),
                'atom'  => array('type' => 'application/atom+xml', 'title' => $type, 'link' => $url),
                'icon'  => array('type' => 'image/x-icon', 'rel' => 'icon', 'link' => $url),
                'keywords' => array('name' => 'keywords', 'content' => $url),
                'description' => array('name' => 'description', 'content' => $url),
            );

            if (true === isset($types[$type])) {
                $type = $types[$type];
            } else if (false === isset($attributes['type']) && $url !== null) {
                if (true === is_array($url) && true === isset($url['ext'])) {
                    $type = $types[$url['ext']];
                } else {
                    $type = $types['rss'];
                }
            } else if (true === isset($attributes['type'])
                && true === isset($types[$attributes['type']])
            ) {
                $type = $types[$attributes['type']];
                unset($attributes['type']);
            } else {
                $type = array();
            }
        }
        $attributes = array_merge($type, $attributes);
        $out = '';

        if (true === isset($attributes['link'])) {
            if (true === isset($attributes['rel'])
                && $attributes['rel'] === 'icon'
            ) {
                if (false === strpos($attributes['link'], '://') ) {
                    $attributes['link'] = $this->_base . '/' . $this->_theme_path . '/web/images/' . $attributes['link'];
                }
                $out = sprintf(
                    $this->_tags['metalink'],
                    $attributes['link'],
                    $this->_parseAttributes($attributes, array('link'))
                );
                $attributes['rel'] = 'shortcut icon';
            } else {
                $attributes['link'] = $this->url($attributes['link'], true);
            }
            $out .= sprintf(
                $this->_tags['metalink'],
                $attributes['link'],
                $this->_parseAttributes($attributes, array('link'))
            );
        } else {
            $out = sprintf(
                $this->_tags['meta'],
                $this->_parseAttributes($attributes, array('type'))
            );
        }

        return $out;
    }

    /**
     * Builds CSS style data from an array of CSS properties
     *
     * @param array $data Style data array
     * @param boolean $inline Whether or not the style block should be displayed inline
     *
     * @return string CSS styling data
     */
    public function style ($data, $inline = true)
    {
        if (false === is_array($data)) {
            return $data;
        }
        $out = array();
        foreach ($data as $key=> $value) {
            $out[] = $key.':'.$value.';';
        }
        if (true === $inline) {
            return implode(' ', $out);
        }
        return implode("\n", $out);
    }

    /**
     * Creates a link element for CSS stylesheets.
     *
     * @param mixed $path The name of a CSS style sheet or an array containing names of
     *   CSS stylesheets. If `$path` is prefixed with '/', the path will be relative to the webroot
     *   of your application. Otherwise, the path will be relative to your CSS path, usually webroot/css.
     * @param string $rel Rel attribute. Defaults to "stylesheet". If equal to 'import' the stylesheet will be imported.
     * @param array $htmlAttributes Array of HTML attributes.
     * @param boolean $inline If set to false, the generated tag appears in the head tag of the layout.
     *
     * @return string CSS <link /> or <style /> tag, depending on the type of link.
     */
    public function css ($path, $rel = null, $html_attributes = array(), $inline = true)
    {
        if (true === is_array($path)) {
            $out = '';
            foreach ($path as $i) {
                $out .= "\n\t" . $this->css($i, $rel, $html_attributes, $inline);
            }
            if (true === $inline)  {
                return $out . "\n";
            }
            return;
        }

        if (strpos($path, '://') !== false) {
            $url = $path;
        } else {
            if ($path[0] !== '/') {
                $path = $this->_base . '/' . $this->_theme_path . '/web/css/' . $path;
            }

            if (strpos($path, '?') === false) {
                if (substr($path, -4) !== '.css') {
                    $path .= '.css';
                }
            }
            $url = $path;
        }

        if ($rel == null) {
            $rel = 'stylesheet';
        }

        $out = sprintf($this->_tags['css'], $rel, $url, $this->_parseAttributes($html_attributes, null, '', ' '));

        if (true === $inline) {
            return $out;
        } else {
            $url = explode('/', $url);
            $file = array_pop($url);
            $url = str_replace($this->_base . DS, '', implode('/', $url)) . '/';
            $this->_view->css[] = array(
                'path' => $url,
                'file' => $file,
                'tag' => $out
            );
        }
    }

    /**
     * HTML image helper methos.
     * Give the image name and the module in which the image
     * is located. If you want the current module then leave
     * it emtpy.
     * If the image is located in the image folder of the theme
     * then set the second parameter to false => image("foo.bar", false);.
     * In the attributes array you can define
     * the attributes of the image tags.
     * Example: array("alt" => "foo", "width" => "bar").
     *
     * @param string $image      the image file name
     * @param mixed  $module     (optional)
     * @param array  $attributes (optional)
     *
     * @return string
     */
    public function image ($image, $module = null, $attributes = array())
    {
        $url = Ncw_Configure::read('Project.url') . '/';
        if (false !== $module) {
            if (true === is_null($module)) {
                $module = $this->_module;
            }
            $url .= MODULES . '/' . $module . '/' . 'web' . '/' . 'images' . '/' . $image;
        } else {
            $url .= THEMES . '/' . Ncw_Configure::read('App.theme') . '/' . 'web' . '/' . 'images' . '/' . $image;
        }
        $img = '<img src=\'' . $url . '\' ';
        $img .= $this->_parseAttributes($attributes);
        $img .= '/>';
        return $img;
    }

    /**
     * Returns a row of formatted and named TABLE headers.
     *
     * @param array $names       Array of tablenames.
     * @param array $tr_options  HTML options for TR elements.
     * @param array $th_options  HTML options for TH elements.
     *
     * @return string
     */
    public function tableHeaders ($names, $tr_options = null, $th_options = null)
    {
        $out = array();
        foreach ($names as $arg) {
            $out[] = sprintf(
                $this->_tags['tableheader'],
                $this->_parseAttributes($th_options),
                $arg
            );
        }
        $data = sprintf(
            $this->_tags['tablerow'],
            $this->_parseAttributes($tr_options),
            implode(' ', $out)
        );
        return $data;
    }

    /**
     * Returns a formatted string of table rows (TR's with TD's in them).
     *
     * @param array $data              Array of table data
     * @param array $odd_tr_options    HTML options for odd TR elements if true useCount is used
     * @param array $even_tr_options   HTML options for even TR elements
     * @param bool  $use_count         adds class "column-$i"
     * @param bool  $continue_odd_even If false, will use a non-static $count variable, so that the odd/even count is reset to zero just for that call
     *
     * @return string   Formatted HTML
     */
    public function tableCells ($data, $odd_tr_options = null, $even_tr_options = null, $use_count = false, $continue_odd_even = true)
    {
        if (true === empty($data[0]) || false === is_array($data[0])) {
            $data = array($data);
        }

        if ($odd_tr_options === true) {
            $use_count = true;
            $odd_tr_options = null;
        }

        if ($even_tr_options === false) {
            $continue_odd_even = false;
            $even_tr_options = null;
        }

        if ($continue_odd_even) {
            static $count = 0;
        } else {
            $count = 0;
        }

        foreach ($data as $line) {
            $count++;
            $cells_out = array();
            $i = 0;
            foreach ($line as $cell) {
                $cell_options = array();

                if (is_array($cell)) {
                    $cell_options = $cell[1];
                    $cell = $cell[0];
                } elseif ($use_count) {
                    $cell_options['class'] = 'column-' . ++$i;
                }
                $cells_out[] = sprintf(
                    $this->_tags['tablecell'],
                    $this->_parseAttributes($cell_options),
                    $cell
                );
            }
            $options = $this->_parseAttributes($count % 2 ? $odd_tr_options : $even_tr_options);
            $out[] = sprintf($this->_tags['tablerow'], $options, implode(' ', $cells_out));
        }
        return implode("\n", $out);
    }

    /**
     * Generate a link to the given path.
     * The path can be set in the second parameter like this:
     * array("module" => "somemodule", "controller" => "somecontroller", "action" => "index")
     * If you leave module and controller away then the current module and controller are set.
     * With third parameter you can give url parameters.
     * With the fourth parameter you can give html-tag attributes.
     *
     * @param string  $title           the link title tag value
     * @param array   $url             (optional)
     * @param array   $html_attributes (optional)
     * @param boolean $confirm_message (optional)
     * @param boolean $escape_title    (optional)
     *
     * @see url
     *
     * @return string
     */
    public function link ($title, $url = null, $html_attributes = array(), $confirm_message = false, $escape_title = true)
    {
        if ($url !== null) {
            $url = $this->url($url, true);
        } else {
            $url = $this->url($title, true);
            $title = $url;
            $escape_title = false;
        }

        if (true === isset($html_attributes['escape'])
            && true === $escape_title
        ) {
            $escape_title = $html_attributes['escape'];
        }

        if ($escape_title === true) {
            $title = h($title);
        } else if (true === is_string($escape_title)) {
            $title = h($title, $escape_title);
        }

        if (false === empty($html_attributes['confirm'])) {
            $confirm_message = $html_attributes['confirm'];
            unset($html_attributes['confirm']);
        }
        if (false !== $confirm_message) {
            $confirm_message = str_replace("'", "\'", $confirm_message);
            $confirm_message = str_replace('"', '\"', $confirm_message);
            $html_attributes['onclick'] = "return confirm('{$confirm_message}');";
        } else if (true === isset($html_attributes['default'])
            && true === $html_attributes['default']
        ) {
            if (true === isset($html_attributes['onclick'])) {
                $html_attributes['onclick'] .= ' event.returnValue = false; return false;';
            } else {
                $html_attributes['onclick'] = 'event.returnValue = false; return false;';
            }
            unset($html_attributes['default']);
        }
        return sprintf(
            $this->_tags['link'],
            $url,
            $this->_parseAttributes($html_attributes),
            $title
        );
    }

    /**
     * Returns a formatted block tag, i.e DIV, SPAN, P.
     *
     * @param string $name Tag name.
     * @param string $text String content that will appear inside the div element.
     *   If null, only a start tag will be printed
     * @param array $attributes Additional HTML attributes of the DIV tag
     * @param boolean $escape If true, $text will be HTML-escaped
     *
     * @return string The formatted tag element
     */
    public function tag ($name, $text = null, $attributes = array(), $escape = false)
    {
        if ($escape) {
            $text = h($text);
        }
        if (!is_array($attributes)) {
            $attributes = array('class' => $attributes);
        }
        if ($text === null) {
            $tag = 'tagstart';
        } else {
            $tag = 'tag';
        }
        return sprintf(
            $this->_tags[$tag],
            $name,
            $this->_parseAttributes($attributes),
            $text,
            $name
        );
    }

    /**
     * Returns a formatted DIV tag for HTML FORMs.
     *
     * @param string $class CSS class name of the div element.
     * @param string $text String content that will appear inside the div element.
     *   If null, only a start tag will be printed
     * @param array $attributes Additional HTML attributes of the DIV tag
     * @param boolean $escape If true, $text will be HTML-escaped
     *
     * @return string The formatted DIV element
     */
    public function div ($class = null, $text = null, $attributes = array(), $escape = false)
    {
        if ($class != null && false === empty($class)) {
            $attributes['class'] = $class;
        }
        return $this->tag('div', $text, $attributes, $escape);
    }

    /**
     * Returns a formatted P tag.
     *
     * @param string $class CSS class name of the p element.
     * @param string $text String content that will appear inside the p element.
     * @param array $attributes Additional HTML attributes of the P tag
     * @param boolean $escape If true, $text will be HTML-escaped
     *
     * @return string The formatted P element
     */
    public function para ($class, $text, $attributes = array(), $escape = false)
    {
        if ($escape) {
            $text = h($text);
        }
        if ($class != null && false === empty($class)) {
            $attributes['class'] = $class;
        }
        if ($text === null) {
            $tag = 'parastart';
        } else {
            $tag = 'para';
        }
        return sprintf(
            $this->_tags[$tag],
            $this->_parseAttributes($attributes),
            $text
        );
    }

    /**
     * Build a nested list (UL/OL) out of an associative array.
     *
     * @param array $list Set of elements to list
     * @param array $attributes Additional HTML attributes of the list (ol/ul) tag or if ul/ol use that as tag
     * @param array $item_attributes Additional HTML attributes of the list item (LI) tag
     * @param string $tag Type of list tag to use (ol/ul)
     *
     * @return string The nested list
     */
    public function nestedList ($list, $attributes = array(), $item_attributes = array(), $tag = 'ul')
    {
        if (true === is_string($attributes)) {
            $tag = $attributes;
            $attributes = array();
        }
        $items = $this->__nestedListItem($list, $attributes, $item_attributes, $tag);
        return sprintf(
            $this->_tags[$tag],
            $this->_parseAttributes($attributes),
            $items
        );
    }

    /**
     * Internal function to build a nested list (UL/OL) out of an associative array.
     *
     * @see nestedList()
     * @param array $list Set of elements to list
     * @param array $attributes Additional HTML attributes of the list (ol/ul) tag
     * @param array $item_attributes Additional HTML attributes of the list item (LI) tag
     * @param string $tag Type of list tag to use (ol/ul)
     *
     * @return string The nested list element
     */
    public function __nestedListItem ($items, $attributes, $item_attributes, $tag)
    {
        $out = '';

        $index = 1;
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $item = $key . $this->nestedList($item, $attributes, $item_attributes, $tag);
            }
            if (isset($item_attributes['even']) && $index % 2 == 0) {
                $item_attributes['class'] = $item_attributes['even'];
            } else if (isset($item_attributes['odd']) && $index % 2 != 0) {
                $item_attributes['class'] = $item_attributes['odd'];
            }
            $out .= sprintf(
                $this->_tags['li'],
                $this->_parseAttributes(
                    array_diff_key($item_attributes, array_flip(array('even', 'odd')))
                ),
                $item
            );
            $index++;
        }
        return $out;
    }
}
?>
