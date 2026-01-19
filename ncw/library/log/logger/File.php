<?php
/**
 * contains the log file class
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
 * log file class
 *    
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library.Log.Logger
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Library_Log_Logger_File implements Ncw_Library_Log_Log
{

    /**
     * The log file
     *
     * @var string
     */
    protected $file = '';

    /**
     * Set the log file
     *
     * @param string $file the log file
     */
    public function __construct ($file)
    {
        try {
            if (false === is_string($file)) {
                throw new InvalidArgumentException('$file must be of type string!');
            }
            $this->file = $file;
        } catch (InvalidArgumentException $e) {
            print $e->getMessage();
        }
    }

    /**
     * Log to file
     *
     * @param string $message  the message to log
     * @param string $priority the message priority
     * 
     * @return void
     */
    public function log ($message, $priority = null)
    {
        try {
            /*if (false === is_string($message)) {
                throw new InvalidArgumentException('$message must be of type string!');
            }
            if (false === is_string($priority)) {
                throw new InvalidArgumentException('$prioritye must be of type string!');
            }*/
            $msg = '------------------------------------------------' . "\n";
            $msg .= date(DATE_RFC822) . ":\n";
            $msg .= $priority . ': ' . $message . '' . "\n";
            error_log($msg, 3, LOG_DESTINATION . DS . $this->file);
        } catch (InvalidArgumentException $e) {
            print $e->getMessage();
        }
    }
}
?>
