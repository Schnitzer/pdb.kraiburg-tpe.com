<?php
/**
 * contains the Database class.
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
 * Database connection.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Database extends PDO
{

    /**
     * Instance of this class
     *
     * @var Ncw_Database
     */
    protected static $_instance = null;

    /**
     * Each sent query.
     *
     * @var Array
     */
    protected static $_logged_queries = array();

    /**
     * The database config
     *
     * @var Array
     */
    protected static $_config = array();

    /**
     * Cache of prepared statement
     *
     * @var Array
     */
    protected $_prepared_statements = array();

    /**
     * Whether to cache prepared statements.
     *
     * @var boolean
     */
    protected $_cache_prepared_statements = false;

    /**
     * Get the instance of this class.
     *
     * @return Ncw_Database
     */
    public static function getInstance ()
    {
        if (null === self::$_instance) {
            $dns = self::$_config['engine'] . ':dbname='
                . self::$_config['database'] . ';charset=utf8;host=' . self::$_config['host'];
            self::$_instance = new self(
                $dns, self::$_config['user'],
                self::$_config['password']
            );
            $encoding = Ncw_Configure::read('App.encodingdb');
            if ($encoding === 'utf-8'
                || $encoding === 'utf8'
                || $encoding === 'UTF-8'
                || $encoding === 'UTF8'
            ) {
                // Set db default charset to utf8.
                self::$_instance->exec('SET NAMES \'utf8\';');
                self::$_instance->exec('SET CHARACTER SET \'utf8\';');
                            //  self::$_instance->exec('SET encoding SET \'utf8\';');
            }
            if ($encoding === 'utf-32'
                || $encoding === 'utf32'
                || $encoding === 'UTF-32'
                || $encoding === 'UTF32'
            ) {
                // Set db default charset to utf8 (utf32 is not supported by MySQL for client connection).
                self::$_instance->exec('SET NAMES \'utf8mb4\';');
                self::$_instance->exec('SET CHARACTER SET \'utf8mb4\';');
            }
            if (true === Ncw_Configure::check('Cache.queries')) {
                self::$_instance->_cache_prepared_statements = Ncw_Configure::read('Cache.queries');
            }
        }
        return self::$_instance;
    }

    /**
     * Disconnect
     *
     * @return void
     */
    public static function disconnect ()
    {
        self::$_instance = null;
    }

    /**
     * Sets the configs
     *
     * @param array $config the database configuration
     *
     * @return void
     */
    public static function set ($config = array())
    {
        self::$_config = $config;
    }

    /**
     * Returns the config or a specific config value
     *
     * @param mixed $name a specific config
     *
     * @return mixed
     */
    public static function getConfig ($name = null)
    {
        if (true === is_string($name)
            && true === isset(self::$_config[$name])
        ) {
           return self::$_config[$name];
        } else {
            return self::$_config;
        }
    }

    /**
     * Connect to the database.
     *
     * @param string $dns      the dns
     * @param string $user     the user name
     * @param string $password the password
     *
     * @return void
     */
    public function __construct ($dns, $user, $password = "")
    {
        try {
            try {
                parent::__construct($dns, $user, $password);
            } catch (PDOException $e) {
                throw new Ncw_Exception(
                    'Connection to database failed: ' . $e->getMessage()
                );
            }
        } catch (Ncw_Exception $e) {
            if (Ncw_Configure::read('debug_mode') > 0) {
                $e->exitWithMessage();
            }
        }
    }

    /**
     * Prepares the query.
     *
     * @param string $sql            the sql string
     * @param array  $driver_options see PDO manual
     *
     * @return PDOStatement
     */
    #[\ReturnTypeWillChange]
    public function prepare ($sql, $driver_options = array())
    {
        if (Ncw_Configure::read('debug_mode') > 1) {
            self::addSentQuery($sql);
        }
        if (true === $this->_cache_prepared_statements) {
            $key = $sql;
            if (false === isset($this->_prepared_statements[$key])) {
                $stmt = parent::prepare($sql, $driver_options);
                $this->_prepared_statements[$key] = $stmt;
                return $stmt;
            } else {
                return $this->_prepared_statements[$key];
            }
        } else {
            return parent::prepare($sql, $driver_options);
        }
    }

    /**
     * Executes a query.
     *
     * @param string $sql the sql string
     *
     * @return PDOStatement
     */
    #[\ReturnTypeWillChange]
    public function exec ($sql)
    {
        if (Ncw_Configure::read('debug_mode') > 1) {
            self::addSentQuery($sql);
        }
        return parent::exec($sql);
    }

    /**
     * Adds the sent query to the queries array.
     *
     * @param string $sql            the sql string
     * @param int    $execution_time (optional)
     *
     * @return void
     */
    public static function addSentQuery ($sql, $execution_time = 0)
    {
        // Add the query to the queries array
        self::$_logged_queries[] = array(
            'query' => $sql, 'execution_time' => $execution_time
        );
    }

    /**
     * Returns the queries array.
     *
     * @return Array
     */
    public static function getLoggedQueries ()
    {
        return self::$_logged_queries;
    }
}
?>
