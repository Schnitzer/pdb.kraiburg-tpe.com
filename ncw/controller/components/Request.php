<?php
/**
 * Contains the Request component
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
 * Include the HTTP_Request2 class.
 */
require_once 'ncw/vendor/pear/HTTP/Request2.php';
/**
 * Request component. (Extends from the PEAR HTTP Request 2 class.)
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Components_Request extends Ncw_Component
{

    /**
     * the header object
     *
     * @var HTTP_Request2
     */
    public $object = null;

    /**
     * construct
     *
     */
    public function __construct ()
    {
        $this->object = new HTTP_Request2();
    }

    /**
     * Startup
     *
     * @param Ncw_Controller &$controller the controller
     *
     * @return void
     */
    public function startup (Ncw_Controller &$controller)
    {
        $controller->header = $this->object;
    }
}
?>
