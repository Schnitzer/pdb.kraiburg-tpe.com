<?php
/**
 * Netzcraftwerk folder configuration
 *
 * Netzcraftwerk configuration file
 * The file defines the following values:
 *   - Folder configurations
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
/*
 * -------------------------------------------------------------------------
 * Folder configurations
 * -------------------------------------------------------------------------
 */
/**
 * Directory Separator
 */
define("DS", DIRECTORY_SEPARATOR);
/**
 * Config folder.
 */
define("NCW", "ncw");
/**
 * Modules folder.
 */
define("MODULES", "modules");
/**
 * Themes folder.
 */
define("THEMES", "themes");
/**
 * Library folder.
 */
define("LIBRARY", NCW . DS . "library");
/**
 * External folder.
 */
define("VENDOR", NCW . DS . "vendor");
/**
 * Assets folder.
 */
define("ASSETS", "assets");
/**
 * Config folder.
 */
define("CONFIG", "config");
/**
 * Tmp folder.
 */
define("TMP", "tmp");
// Set the include path to make it possible to include the PEAR Libraries.
set_include_path(
    get_include_path() . PATH_SEPARATOR . VENDOR . DS . 'pear'
);
?>