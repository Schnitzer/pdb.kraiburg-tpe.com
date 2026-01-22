<?php
/**
 * Contains a assets helpers.
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
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
 * assets helpers.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Helpers_Asset extends Ncw_Helper {


    /**
     * If time should be checked
     *
     * @var boolean
     */
    public $check_ts = true;

    /**
     * md5 file names
     *
     * @var boolean
     */
    public $md5_file_name = true;

    /**
     * The css cache path
     *
     * @var string
     */
    public $css_cache_path = 'css/';

    /**
     * The js cache path
     *
     * @var string
     */
    public $js_cache_path = 'javascript/';

    /**
     * set the css compression level
     * options: default, low_compression, high_compression, highest_compression
     * default is no compression
     * I like high_compression because it still leaves the file readable.
     * NOTE: Disabled for PHP 8.3 compatibility - csstidy is not compatible
     */
    public $css_compression = 'default';

    /**
     * Debug mode
     *
     * @var boolean
     */
    public $debug = false;

    /**
     * The vie object
     *
     * @var Ncw_View
     */
    protected $_view = null;

     /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    public function startup (Ncw_View &$view)
    {
        $view->asset = $this;
        $this->_view =& $view;
    }

    /**
     * Js
     *
     * @return string
     */
    public function js ()
    {
        if (Ncw_Configure::read('debug_mode') > 0 && true === $this->debug) {
            return join("\n\t", $this->_view->js);
        }

        $arr_js = array();
        foreach ($this->_view->js as $key => $js) {
            if (true == preg_match('/(.*).js/', $js['file'], $match)) {
                if (false === empty($js['path'])) {
                    $temp = array();
                    $temp['path'] = $js['path'];
                    $temp['script'] = $match[1];
                    $arr_js[] = $temp;

                    unset($this->_view->js[$key]);
                }
            }
        }

        $out = '';
        if (false === empty($arr_js)) {
            $url = $this->_view->base . '/' . ASSETS . '/'
                . $this->js_cache_path . $this->process('js', $arr_js);
            $out .= $this->_view->javascript->link($url);
        }

        $out .= join("\n\t", $this->_view->js);

        return $out;
    }

    /**
     * Css
     *
     * @return string
     */
    public function css ()
    {
        if (Ncw_Configure::read('debug_mode') > 0 && true === $this->debug) {
            return join("\n\t", $this->_view->css);
        }

        $arr_css = array();
        foreach ($this->_view->css as $key => $css) {
            if (true == preg_match('/(.*).css/', $css['file'], $match)) {
                if (false === empty($css['path'])) {
                    $temp = array();
                    $temp['path'] = $css['path'];
                    $temp['script'] = $match[1];
                    $arr_css[] = $temp;

                    unset($this->_view->css[$key]);
                }
            }
        }

        $out = '';
        if (false === empty($arr_css)) {
            $url = $this->_view->base . '/' . ASSETS . '/'
                . $this->css_cache_path . $this->process('css', $arr_css);
            $out .= $this->_view->html->css($url);
            $out .= "\n\t";
        }

        $out .= join("\n\t", $this->_view->css);

        return $out;
    }

    /**
     * Process
     *
     * @param string $type
     * @param array $data
     *
     * @return string
     */
    public function process ($type, $data)
    {

        if ($type === 'css') {
            $cache_path = ASSETS . DS . $this->css_cache_path;
        } else {
            $cache_path = ASSETS . DS . $this->js_cache_path;
        }

        $folder = new Ncw_Components_Folder();

        $names = array();
        foreach ($data as $date) {
            $names[] = $date['script'];
        }

        //make sure the cache folder exists
        $folder->create($cache_path, '777');

        //check if the cached file exists
        $file_name = $this->__generateFileName($names) . '.' . $type;
        if (false === file_exists($cache_path . $file_name)) {
            $file_name = null;
        }

        //make sure all the pieces that went into the packed script
        //are OLDER then the packed version
        if ($this->check_ts && false === is_null($cache_path . $file_name)) {
            $packed_ts = filemtime($cache_path . $file_name);

            $latest_ts = 0;
            foreach ($data as $date) {
                $latest_ts = max($latest_ts, filemtime($date['path'] . $date['script'] . '.' . $type));
            }

            //an original file is newer. need to rebuild
            if ($latest_ts > $packed_ts) {
                unlink($cache_path . $file_name);
                $file_name = null;
            }
        }

        // file doesn't exist.  create it.
        if (true === is_null($file_name)) {
            include VENDOR . DS . 'jsmin' . DS . 'jsmin.php';
            include VENDOR . DS . 'csstidy' . DS . 'class.csstidy.php';
            include VENDOR . DS . 'lessphp' . DS . 'lessc.inc.php';

            //merge the script
            $script_buffer = '';
            foreach ($data as $date) {
                $buffer = file_get_contents($date['path'] . $date['script'] . '.' . $type);

                switch ($type) {
                    case 'js' :
                        $buffer = trim(JSMin::minify($buffer));
                        break;

                    case 'css' :
                        if (false !== strpos($date['script'], '.less')) {
                            $less = new lessc();
                            $buffer = $less->parse($buffer);
                        }
                        // csstidy disabled - incompatible with PHP 8.3
                        // Just combine CSS files without compression
                        $buffer = trim($buffer);
                        break;
                }

                $script_buffer .= "\n/*" . $date['script'] . "." . $type . "*/\n" . $buffer;
            }

            //write the file
            $file_name = $this->__generateFileName($names) . '.' . $type;
            $file = new Ncw_Components_File();
            $file->create($cache_path . $file_name, $script_buffer);
        }

        return $file_name;
    }

    /**
     * Generates the file name
     *
     * @param array $names the file names
     *
     * @return string
     */
    private function __generateFileName ($names)
    {
        $file_name = str_replace('.', '-', implode('_', $names));

        if (true === $this->md5_file_name) {
            $file_name = md5($file_name);
        }

        return $file_name;
    }
}
?>
