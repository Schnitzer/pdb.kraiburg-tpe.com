<?php

/**
 * Netzcraftwerk database configuration
 *
 * Netzcraftwerk configuration file
 * The file defines the following values:
 *   - Database configuration
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category  Netzcraftwerk
 * @package   Config
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 1997-2008 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version   SVN: $Id$
 * @link      http://www.netzcraftwerk.com
 * @since     File available since Release 0.1
 * @modby     $LastChangedBy$
 * @lastmod   $LastChangedDate$
 */
// Set the connection config
Ncw_Database::set(
    array(
        'host' => 'db',
        'user' => 'db',
        'password' => 'db',
        'database' => 'db',
        'engine' => 'mysql',
        'prefix' => 'ncw_'
    )
);
?>
