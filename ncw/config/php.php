<?php
/**
 * Netzcraftwerk core configuration.
 *
 * Netzcraftwerk configuration file
 * The file defines the following values:
 *   - PHP-ini configurations
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
 * Ini configurations
 * -------------------------------------------------------------------------
 */
// The Session-Id (if use_cookie is null) is added to the values
// of the specified HTML-attributes
ini_set("url_rewriter.tags", "");
// Session save handler
ini_set("session.save_handler", Ncw_Configure::read('Session.handling'));
// Session name.
ini_set("session.name", Ncw_Configure::read('Session.cookie_name'));
if (true === Ncw_Configure::read('Session.use_cookie')) {
    // Use cookies for session-id managment.
    ini_set("session.use_cookies", 1);
    // Use only cookies specifies if only
    // cookies must be used. Set to 0
    // if you dont want to use cookies.
    ini_set("session.use_only_cookies", 1);
    $use_trans_id = 0;
} else {
    $use_trans_id = 1;
}
// Do not use transparent SID assistance.
ini_set("session.use_trans_id", $use_trans_id);
// Cooke lifetime.
// 0 = cookie will be destroyed after browser is closed.
ini_set("session.cookie_lifetime", Ncw_Configure::read('Session.cookie_lifetime'));
// Assign cookie to a specific domain.
if (Ncw_Configure::read('Project.domain') !== 'localhost') {
    ini_set("session.cookie_domain", '.' . Ncw_Configure::read('Project.domain'));
}
// Specify in which path the cookie must be placed.
ini_set("session.cookie_path", Ncw_Configure::read('Session.cookie_path'));
// auto start sessions
ini_set('session.auto_start', 0);
// session save path
session_save_path(Ncw_Configure::read('Session.save_path'));
// Use html encoded &-separator.
ini_set("arg_separator.output", "&amp;");
// Maximal execution time of the script.
// Value in seconds. (Standard is 30)
ini_set("max_execution_time", Ncw_Configure::read('App.max_execution_time'));
// Turn off magic quotes
if (true === Ncw_Configure::read('App.use_magic_quotes')) {
    $magic_quotes = 1;
} else {
    $magic_quotes = 0;
}
ini_set('magic_quotes_runtime', $magic_quotes);
// file endings
ini_set("auto_detect_line_endings", true);
/*
 * -------------------------------------------------------------------------
 * Magic Quotes
 * -------------------------------------------------------------------------
 */
// set_magic_quotes_runtime(false);
?>
