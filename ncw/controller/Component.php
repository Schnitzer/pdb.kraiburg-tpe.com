<?php
/**
 * Contains the Component class.
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
 * Component class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
abstract class Ncw_Component extends Ncw_Object
{

    /**
     * Callbacks are enabled
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * Startup
     *
     * @param Ncw_Controller &$controller the controller
     *
     * @return void
     */
	abstract public function startup (Ncw_Controller &$controller);

    /**
     * Called before the Controller::beforeFilter().
     *
     * @param Ncw_Controller $controller Controller with components to initialize
     *
     * @return void
     */
    public function initialize (Ncw_Controller &$controller)
    {

    }

    /**
     * Called after the Controller::beforeRender(), after the view class is loaded, and before the
     * Controller::render()
     *
     * @param Ncw_Controller $controller Controller with components to beforeRender
     *
     * @return void
     */
    public function beforeRender (Ncw_Controller &$controller)
    {

    }

    /**
     * Called before Controller::redirect().
     *
     * @param Ncw_Controller $controller Controller with components to beforeRedirect
     * @param string         $url        the redirect url
     * @param int            $status     the status code
     * @param boolean        $exit       if exit after redirect
     *
     * @return void
     */
    public function beforeRedirect (Ncw_Controller &$controller, $url, $status = null, $exit = true)
    {

    }

    /**
     * Called after Controller::render() and before the output is printed to the browser.
     *
     * @param Ncw_Controller $controller Controller with components to shutdown
     *
     * @return void
     */
    public function shutdown (Ncw_Controller &$controller)
    {

    }
}
?>
