<?php
/**
 * contains the log composite class
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
 * @subpackage Library.Log.Logger
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
 * log composite class
 *    
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library.Log.Logger
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Library_Log_Logger_Composite implements Ncw_Library_Log_Log
{

    /**
     * The loggers
     * 
     * var Array
     */
    public $logger = array();

    /**
     * Adds a logger to the composite
     *
     * @param Ncw_Library_Log_Log $log the log
     * 
     * @return void
     */
    public function addLogger (Ncw_Library_Log_Log $log)
    {
        $this->logger[] = $log;
    }

    /**
     * Call the log method of each logger
     *
     * @param string $message  the message to log
     * @param string $priority the message priority
     * 
     * @return void
     */
    public function log ($message, $priority = null)
    {
        foreach ($this->logger as $log) {
            $log->log($message, $priority);
        }
    }
}

?>