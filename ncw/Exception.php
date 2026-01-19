<?php

/**
 * Contains the NcwException class.
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
 * NcwException class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Exception extends Exception
{
    /**
     * If a error occured then the value is true
     *
     * @var boolean
     */
    public static $error = false;

    /**
     * Sets the static error var to true, calls the parents construct and
     * appends the error message to the error log.
     *
     * @param string|Throwable|PEAR_Error $message the exception message, Throwable or PEAR_Error object
     * @param string|int $code    the exception status code (optional)
     */
    public function __construct($message, $code = 0)
    {
        self::$error = true;

        // Handle PEAR_Error objects
        if (is_object($message) && method_exists($message, 'getMessage')) {
            // PEAR_Error compatibility
            $pear_code = $message->getCode();
            $pear_message = $message->getMessage();
            // Ensure code is an integer
            $code = is_int($pear_code) ? $pear_code : 0;
            $message = $pear_message;
            parent::__construct($message, $code);
        } elseif ($message instanceof Throwable) {
            // Handle Throwable objects (Exception or Error)
            $code = $message->getCode();
            $previous = $message;
            $message = $message->getMessage();
            parent::__construct($message, $code, $previous);
        } else {
            // Ensure code is an integer for string messages
            $code = is_int($code) ? $code : 0;
            parent::__construct($message, $code);
        }

        if (true === Ncw_Configure::read('Log') && true === Ncw_Configure::read('Log.exceptions')) {
            // log what we know
            $msg = '';
            $msg .= __CLASS__ . ': [' . $this->code . ']: {'
                . $this->message . '}' . "\n";
            $msg .= $this->getTraceAsString() . "\n";
            $log = Ncw_Library_LogFactory::factory('Composite');
            $log->addLogger(new Ncw_Library_Log_Logger_File('exceptions.log'));
            $log->log($msg, 'critical');
        }
    }

    /**
     * exit the programm with error message
     *
     * @return void
     */
    public function exitWithMessage()
    {
        switch ($this->code) {
            case 1:
                // DB error, show the last query
                $queries = Ncw_Database::getLoggedQueries();
                $this->message .= '<br /><br />'
                    . $queries[count($queries) - 1]['query'];
        }
        include_once THEMES . DS . Ncw_Configure::read('App.theme') . DS . 'errors' . DS . 'error.phtml';
        exit();
    }

    /**
     * static exception_handler for default exception handling
     *
     * @param Throwable $exception the exception to handle
     *
     * @return void
     */
    public static function exceptionHandler(Throwable $exception)
    {
        // Avoid recursive exception handling for Ncw_Exception
        if ($exception instanceof Ncw_Exception) {
            // Already an Ncw_Exception, just handle it
            $exception->exitWithMessage();
            return;
        }
        
        // Wrap other exceptions
        $wrapped = new Ncw_Exception($exception);
        $wrapped->exitWithMessage();
    }
}
?>
