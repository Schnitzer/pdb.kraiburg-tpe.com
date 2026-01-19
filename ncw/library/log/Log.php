<?php
/**
 * contains the Log interface
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
 * @subpackage Library.Log
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
 * Log interface.
 *    
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library.Log
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
interface Ncw_Library_Log_Log
{

    /**
     * The log interface
     *
     * @param string $message  the message to log
     * @param string $priority the message priority
     * 
     * @return void
     */
    public function log ($message, $priority = null);
}

?>