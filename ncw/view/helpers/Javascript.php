<?php
/**
 * contains the Javascript helper class
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
 * Javascript helper class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Helpers_Javascript extends Ncw_Helper
{

    /**
     * If true, automatically writes events to the end of a script or to an external JavaScript file
     * at the end of page execution
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * Indicates whether <script /> blocks should be written 'safely,' i.e. wrapped in CDATA blocks
     *
     * @var boolean
     */
    public $safe = false;

    /**
     * HTML tags used by this helper.
     *
     * @var array
     */
    public $tags = array(
        'javascriptstart' => '<script type="text/javascript">',
        'javascriptend' => '</script>',
        'javascriptblock' => '<script type="text/javascript">%s</script>',
        'javascriptlink' => '<script type="text/javascript" src="%s"></script>'
    );

    /**
     * Holds options passed to codeBlock(), saved for when block is dumped to output
     *
     * @var array
     */
    protected $_block_options = array();

    /**
     * The view object
     *
     * @var Ncw_View
     */
    protected $_view = null;

    /**
     * Cache events
     *
     * @var boolean
     */
    protected $_cache_events = false;

    /**
     * Cache all
     *
     * @var boolean
     */
    protected $_cache_all = false;

    /**
     * Cached events
     *
     * @var array
     */
    protected $_cached_events = array();

    /**
     *
     *
     * @var string
     */
    private $__script_buffer = null;

    /**
     * Constructor. Checks for presence of native PHP JSON extension to use for object encoding
     *
     * @access public
     */
    public function __construct ($options = array())
    {
        if (false === empty($options)) {
            foreach ($options as $key => $val) {
                if (is_numeric($key)) {
                    $key = $val;
                    $val = true;
                }
                switch ($key) {
                    case 'cache':

                    break;
                    case 'safe':
                        $this->safe = $val;
                    break;
                }
            }
        }
    }

    /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    public function startup (Ncw_View &$view)
    {
        $this->_view =& $view;
        $view->javascript = $this;
    }

    /**
     * Returns a JavaScript script tag.
     *
     * Options:
     *
     *  - allowCache: boolean, designates whether this block is cacheable using the
     * current cache settings.
     *  - safe: boolean, whether this block should be wrapped in CDATA tags.  Defaults
     * to helper's object configuration.
     *  - inline: whether the block should be printed inline, or written
     * to cached for later output (i.e. $scripts_for_layout).
     *
     * @param string $script The JavaScript to be wrapped in SCRIPT tags.
     * @param array $options Set of options:
     *
     * @return string The full SCRIPT element, with the JavaScript inside it, or null,
     *   if 'inline' is set to false.
     */
    public function codeBlock ($script = null, $options = array())
    {
        if (!empty($options) && !is_array($options)) {
            $options = array('allowCache' => $options);
        } elseif (empty($options)) {
            $options = array();
        }
        $defaultOptions = array('allowCache' => true, 'safe' => true, 'inline' => true);
        $options = array_merge($defaultOptions, $options);

        if (empty($script)) {
            $this->__script_buffer = @ob_get_contents();
            $this->_block_options = $options;
            $this->inBlock = true;
            @ob_end_clean();
            ob_start();
            return null;
        }
        if (true === $this->_cache_events
            && true === $this->_cache_all
            && true === $options['allowCache']
        ) {
            $this->_cached_events[] = $script;
            return null;
        }
        if ($options['safe'] || $this->safe) {
            $script  = "\n" . '//<![CDATA[' . "\n" . $script . "\n" . '//]]>' . "\n";
        }
        if ($options['inline']) {
            return sprintf($this->tags['javascriptblock'], $script);
        } else {
            $this->_view->js[] = array(
                'tag' => sprintf($this->tags['javascriptblock'], $script),
            );
        }
    }

    /**
     * Ends a block of cached JavaScript code
     *
     * @return mixed
     */
    public function blockEnd ()
    {
        if (!isset($this->inBlock) || !$this->inBlock) {
            return;
        }
        $script = @ob_get_contents();
        @ob_end_clean();
        ob_start();
        echo $this->__script_buffer;
        $this->__script_buffer = null;
        $options = $this->_block_options;
        $this->_block_options = array();
        $this->inBlock = false;

        if (empty($script)) {
            return null;
        }

        return $this->codeBlock($script, $options);
    }

    /**
     * Returns a JavaScript include tag (SCRIPT element).  If the filename is prefixed with "/",
     * the path will be relative to the base path of your application.  Otherwise, the path will
     * be relative to your JavaScript path, usually webroot/js.
     *
     * @param mixed $url String URL to JavaScript file, or an array of URLs.
     * @param boolean $inline If true, the <script /> tag will be printed inline,
     *   otherwise it will be printed in the <head />, using $scripts_for_layout
     * @see JS_URL
     *
     * @return string
     */
    public function link ($url, $inline = true)
    {
        if (is_array($url)) {
            $out = '';
            foreach ($url as $i) {
                $out .= "\n\t" . $this->link($i, $inline);
            }
            if (true === $inline)  {
                return $out . "\n";
            }
            return;
        }

        if (strpos($url, '://') === false) {
            if ($url[0] !== '/') {
                $url = $this->_view->base . '/' . $this->_view->theme_path . '/web/javascript/' . $url;
            }
            if (strpos($url, '?') === false) {
                if (substr($url, -3) !== '.js') {
                    $url .= '.js';
                }
            }
        }
        $out = sprintf($this->tags['javascriptlink'], $url);

        if (true === $inline) {
            return $out;
        } else {
            $url = explode('/', $url);
            $file = array_pop($url);
            $url = str_replace($this->_view->base . DS, '', implode('/', $url)) . '/';
            $this->_view->js[] = array(
                'path' => $url,
                'file' => $file,
                'tag' => $out,
            );
        }
    }

    /**
     * Escape carriage returns and single and double quotes for JavaScript segments.
     *
     * @param string $script string that might have javascript elements
     *
     * @return string escaped string
     */
    public function escapeScript ($script)
    {
        $script = str_replace(array("\r\n", "\n", "\r"), '\n', $script);
        $script = str_replace(array('"', "'"), array('\"', "\\'"), $script);
        return $script;
    }

    /**
     * Escape a string to be JavaScript friendly.
     *
     * List of escaped ellements:
     *  + "\r\n" => '\n'
     *  + "\r" => '\n'
     *  + "\n" => '\n'
     *  + '"' => '\"'
     *  + "'" => "\\'"
     *
     * @param  string $script String that needs to get escaped.
     *
     * @return string Escaped string.
     */
    public function escapeString ($string)
    {
        $escape = array("\r\n" => "\n", "\r" => "\n");
        $string = str_replace(array_keys($escape), array_values($escape), $string);
        return $this->_utf8ToHex($string);
    }

    /**
     * Encode a string into JSON. Converts and escapes necessary characters.
     *
     * @return void
     **/
    protected function _utf8ToHex ($string)
    {
        $length = strlen($string);
        $return = '';
        for ($i = 0; $i < $length; ++$i) {
            $ord = ord($string{$i});
            switch (true) {
                case $ord == 0x08:
                    $return .= '\b';
                    break;
                case $ord == 0x09:
                    $return .= '\t';
                    break;
                case $ord == 0x0A:
                    $return .= '\n';
                    break;
                case $ord == 0x0C:
                    $return .= '\f';
                    break;
                case $ord == 0x0D:
                    $return .= '\r';
                    break;
                case $ord == 0x22:
                case $ord == 0x2F:
                case $ord == 0x5C:
                case $ord == 0x27:
                    $return .= '\\' . $string{$i};
                    break;
                case (($ord >= 0x20) && ($ord <= 0x7F)):
                    $return .= $string{$i};
                    break;
                case (($ord & 0xE0) == 0xC0):
                    if ($i + 1 >= $length) {
                        $i += 1;
                        $return .= '?';
                        break;
                    }
                    $charbits = $string{$i} . $string{$i + 1};
                    $char = uft8_encode($charbits);
                    $return .= sprintf('\u%04s', dechex($char[0]));
                    $i += 1;
                    break;
                case (($ord & 0xF0) == 0xE0):
                    if ($i + 2 >= $length) {
                        $i += 2;
                        $return .= '?';
                        break;
                    }
                    $charbits = $string{$i} . $string{$i + 1} . $string{$i + 2};
                    $char = uft8_encode($charbits);
                    $return .= sprintf('\u%04s', dechex($char[0]));
                    $i += 2;
                    break;
                case (($ord & 0xF8) == 0xF0):
                    if ($i + 3 >= $length) {
                       $i += 3;
                       $return .= '?';
                       break;
                    }
                    $charbits = $string{$i} . $string{$i + 1} . $string{$i + 2} . $string{$i + 3};
                    $char = uft8_encode($charbits);
                    $return .= sprintf('\u%04s', dechex($char[0]));
                    $i += 3;
                    break;
                case (($ord & 0xFC) == 0xF8):
                    if ($i + 4 >= $length) {
                       $i += 4;
                       $return .= '?';
                       break;
                    }
                    $charbits = $string{$i} . $string{$i + 1} . $string{$i + 2} . $string{$i + 3} . $string{$i + 4};
                    $char = uft8_encode($charbits);
                    $return .= sprintf('\u%04s', dechex($char[0]));
                    $i += 4;
                    break;
                case (($ord & 0xFE) == 0xFC):
                    if ($i + 5 >= $length) {
                       $i += 5;
                       $return .= '?';
                       break;
                    }
                    $charbits = $string{$i} . $string{$i + 1} . $string{$i + 2} . $string{$i + 3} . $string{$i + 4} . $string{$i + 5};
                    $char = uft8_encode($charbits);
                    $return .= sprintf('\u%04s', dechex($char[0]));
                    $i += 5;
                    break;
            }
        }
        return $return;
    }

    /**
     * Attach an event to an element. Used with the Jquery library.
     *
     * @param string $object Object to be observed
     * @param string $event event to observe
     * @param string $observer function to call
     * @param array $options Set options: allowCache, safe
     *
     * @return boolean true on success
     */
    public function event ($object, $event, $observer = null, $options = array())
    {
        if (strpos($object, '$(') !== false) {
            $b = "{$object}.bind('{$event}', function(event) { {$observer} });";
        } else {
            $b = "\$('{$object}').bind('{$event}', function(event) { ";
            $b .= "{$observer} });";
        }

        if (true === isset($b) && false === empty($b)) {
            if ($this->_cache_events === true) {
                $this->_cached_events[] = $b;
                return;
            } else {
                return $this->codeBlock($b, $options);
            }
        }
        return;
    }

    /**
     * Gets (and clears) the current JavaScript event cache
     *
     * @param boolean $clear
     *
     * @return string
     */
    public function getCache ($clear = true)
    {
        $data = implode("\n", $this->_cachedEvents);

        if (true === $clear) {
            $this->_cacheEvents = false;
            $this->_cachedEvents = array();
        }
        return $data;
    }

    /**
     * Write cached JavaScript events
     *
     * @param boolean $inline If true, returns JavaScript event code.  Otherwise it is added to the
     *                        output of $this->js() in the layout.
     * @param array $options Set options for codeBlock
     *
     * @return string
     */
    public function writeEvents ($inline = true, $options = array())
    {
        $out = '';

        if (false === $this->_cache_events) {
            return;
        }
        $data = $this->getCache();

        if (true === empty($data)) {
            return;
        }

        $out = $this->codeBlock("\n" . $data . "\n", $options);

        if (true === $inline) {
            return $out;
        } else {
            $this->_view->js[] = array(
                'tag' => $out,
            );
        }
    }
}
?>
