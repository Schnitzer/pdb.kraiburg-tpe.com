<?php

/**
 * Netzcraftwerk core configuration.
 *
 * Netzcraftwerk configuration file
 * The file defines the following values:
 *   - Specific configurationss
 *   - Error reporting
 *
 * PHP Version 5.2
 * Copyright (c) 2018 Netzcraftwerk UG
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category  Netzcraftwerk
 * @package   Config
 * @author    Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright 2008-2022 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version   SVN: $Id$
 * @link      http://www.netzcraftwerk.com
 * @since     File available since Release 0.1
 * @modby     $LastChangedBy$
 * @lastmod   $LastChangedDate$
 */

/*
 * -------------------------------------------------------------------------
 * Project configurations
 * -------------------------------------------------------------------------
 */
/** Project Domain */
Ncw_Configure::write('Project.domain', 'ddev.site');
/** Project URL */
Ncw_Configure::write('Project.url', 'https://kraiburg-pdb.' . Ncw_Configure::read('Project.domain') . '');

/**
 * Lanuage in which the software
 * starts if the user has not chosen a language.
 */
Ncw_Configure::write('App.language', 'en_EN');
/** Theme */
Ncw_Configure::write('App.theme', 'default');

/**
 * Debug mode.
 * 0 = debug mode is off.
 * 1 = errors, script duration and memory allocation will be displayed.
 * Also Model descriptions are not cached
 * 2 = also queries will be saved and displayed.
 */
Ncw_Configure::write('debug_mode', 1);
/** Search engine friendly urls by using mod rewrite. */
Ncw_Configure::write('App.rewrite', false);
/** If ssl must be used. */
Ncw_Configure::write('App.ssl', true);
/** Charset used */
Ncw_Configure::write('App.encodingdb', 'utf-32');
Ncw_Configure::write('App.encoding', 'utf-8');
Ncw_Configure::write('App.encodinglogin', 'utf-8');

/*
 * -------------------------------------------------------------------------
 * Cache configurations
 * -------------------------------------------------------------------------
 */
/** If cache is enabled */
Ncw_Configure::write('Cache', false);
/** Where the cache files are located. */
Ncw_Configure::write('Cache.dir', './tmp/cache');
/** Expires header. */
Ncw_Configure::write('Cache.expires', 36000);
/** Cache database queries? */
Ncw_Configure::write('Cache.queries', false);

/*
 * -------------------------------------------------------------------------
 * Session handling
 * -------------------------------------------------------------------------
 */

/**
 * Session handling. (Standard is files.)
 * Use 'database' if you want to write the sessions
 * into a db.
 */
Ncw_Configure::write('Session.handling', 'files');
/** Use session cookies */
Ncw_Configure::write('Session.use_cookie', true);
/** The session cookie name */
Ncw_Configure::write('Session.cookie_name', 'NCWSESSID');
/** The session cookie life time in seconds */
Ncw_Configure::write('Session.cookie_lifetime', 36000);
/** The session cookie life time in seconds */
Ncw_Configure::write('Session.cookie_path', '/');
/** The session save path */
Ncw_Configure::write('Session.save_path', '.' . DS . TMP . DS . 'sessions');

/*
 * -------------------------------------------------------------------------
 * Logging
 * -------------------------------------------------------------------------
 */
/** Log */
Ncw_Configure::write('Log', true);
/** You can set the destination of the log */
Ncw_Configure::write('Log.destination', './tmp/logs');
/** If exceptions should be logged */
Ncw_Configure::write('Log.exceptions', false);

/*
 * -------------------------------------------------------------------------
 * Other
 * -------------------------------------------------------------------------
 */

/** Only requests with www */
Ncw_Configure::write('App.only_www', false);
/** Max execution time of a php script */
Ncw_Configure::write('App.max_execution_time', 30);
/** Use magic quotes */
Ncw_Configure::write('App.use_magic_quotes', false);
/** Logout route */
Ncw_Configure::write('Logout.url', 'https://pdb.' . Ncw_Configure::read('Project.domain')) . '';

/*
 * Internal IP's wird dazu verwendet Interne IP Adressen in Log Tool als solche zu kennzeichnen
 * Die letzte ist die Developer IP
 */
$internal_ips = array(
  '212.68.121.65',
  '212.68.121.67',
  '69.15.210.208',
  '58.26.183.192',
  '218.188.198.242',
  '110.92.79.234',
  '212.68.120.129',
  '212.68.120.129',
  '87.148.10.174',
  '176.74.57.181',
  '62.154.179.198',
  '79.208.207.11',
  '80.151.191.169',
  '87.148.5.188',
  '87.148.5.156',
  '212.68.120.129',
  '118.143.45.16',
  '118.143.45.17',
  '118.143.45.18',
  '118.143.45.19',
  '118.143.45.20',
  '118.143.45.21',
  '118.143.45.22',
  '118.143.45.23',
  '118.143.45.24',
  '118.143.45.25',
  '118.143.45.26',
  '118.143.45.27',
  '118.143.45.28',
  '118.143.45.29',
  '118.143.45.30',
  '118.143.45.31',
  '118.140.87.96',
  '118.140.87.97',
  '118.140.87.98',
  '118.140.87.99',
  '118.140.87.100',
  '118.140.87.101',
  '118.140.87.102',
  '118.140.87.103',
  '118.140.87.104',
  '118.140.87.105',
  '118.140.87.106',
  '118.140.87.107',
  '118.140.87.108',
  '118.140.87.109',
  '118.140.87.110',
  '118.140.87.111',
  '121.121.43.8',
  '121.121.43.9',
  '121.121.43.10',
  '121.121.43.11',
  '121.121.43.12',
  '121.121.43.13',
  '121.121.43.14',
  '121.121.43.15',
  '121.121.43.16',
  '121.121.43.17',
  '121.121.43.18',
  '121.121.43.19',
  '121.121.43.20',
  '121.121.43.21',
  '121.121.43.22',
  '121.121.43.23',
  '121.121.43.24',
  '121.121.43.25',
  '121.121.43.26',
  '121.121.43.27',
  '121.121.43.28',
  '121.121.43.29',
  '121.121.43.30',
  '121.121.43.31',
  '121.121.43.32',
  '121.121.43.33',
  '121.121.43.34',
  '121.121.43.35',
  '121.121.43.36',
  '121.121.43.37',
  '121.121.43.38',
  '121.121.43.39',
  '103.3.73.32',
  '103.3.73.33',
  '103.3.73.34',
  '103.3.73.35',
  '103.3.73.36',
  '103.3.73.37',
  '103.3.73.38',
  '103.3.73.39',
  '180.166.10.72',
  '180.166.10.73',
  '180.166.10.74',
  '180.166.10.75',
  '180.166.10.76',
  '180.166.10.77',
  '180.166.10.78',
  '180.166.10.79',
  '180.166.10.80',
  '180.166.10.81',
  '180.166.10.82',
  '180.166.10.83',
  '180.166.10.84',
  '180.166.10.85',
  '180.166.10.86',
  '180.166.10.87',
  '180.166.10.88',
  '180.166.10.89',
  '180.166.10.90',
  '180.166.10.91',
  '180.166.10.92',
  '180.166.10.93',
  '180.166.10.94',
  '180.166.10.95',
  '180.166.10.96',
  '180.166.10.97',
  '180.166.10.98',
  '180.166.10.99',
  '180.166.10.100',
  '180.166.10.101',
  '180.166.10.102',
  '180.166.10.103',
  '213.13.113.13',
  '210.99.119.20',
  '183.82.20.30',
  '125.99.241.71',
  '12.44.84.144',
  '12.44.84.145',
  '12.44.84.146',
  '12.44.84.147',
  '12.44.84.148',
  '12.44.84.149',
  '12.44.84.150',
  '12.44.84.151',
  '12.44.84.152',
  '12.44.84.153',
  '12.44.84.154',
  '12.44.84.155',
  '12.44.84.156',
  '12.44.84.157',
  '12.44.84.158',
  '12.44.84.159',
  '118.140.164.238',
  '187.167.207.154',
  '',
  '79.208.202.220', '87.148.9.75'
);

Ncw_Configure::write('internal_ips', $internal_ips);

Ncw_Configure::write('developer_internal_ip', $internal_ips[count($internal_ips) - 1]);

?>
