<?php
/**
 * Contains the Captcha component.
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
 * Include the secureimage classes
 */
require_once 'ncw/vendor/securimage/securimage.php';
/**
 * Captcha component.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Components_Captcha extends Ncw_Component
{

    /**
     * the full path to wav files used
     *
     * @var string
     */
    protected $_audio_path = 'audio/';

    /**
     * the gd font to use
     *
     * @var string
     */
    protected $_gd_font_file = 'gdfonts/bubblebath.gdf';

    /**
     * the path to the ttf font file to load
     *
     * @var string
     */
    protected $_ttf_file = 'AHGBold.ttf';

    /**
     * the wordlist to use
     *
     * @var string
     */
    protected $_wordlist_file = 'words/words.txt';

    /**
     * Startup
     *
     * @param Ncw_Controller &$controller the controller
     *
     * @return void
     */
    public function startup (Ncw_Controller &$controller)
    {
    	$captcha_dir = VENDOR . DS . 'securimage';

        $controller->captcha = new securimage();

        $controller->captcha->audio_path = $captcha_dir . DS . $this->_audio_path;
        $controller->captcha->gd_font_file = $captcha_dir . DS . $this->_gd_font_file;
        $controller->captcha->ttf_file = $captcha_dir . DS . $this->_ttf_file;
        $controller->captcha->wordlist_file = $captcha_dir . DS . $this->_wordlist_file;

    	$controller->view->captcha = $controller->captcha;
    }
}
?>