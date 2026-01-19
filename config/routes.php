<?php
/**
 * Netzcraftwerk routes configuration.
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
 * @author    W.Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright 1997-2019 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version   SVN: $Id$
 * @link      http://www.netzcraftwerk.com
 * @since     File available since Release 0.1
 * @modby     $LastChangedBy$
 * @lastmod   $LastChangedDate$
 */
/*
 * -------------------------------------------------------------------------
 * Configure your routes here:
 * -------------------------------------------------------------------------
 */
/**
 * If you want to use a url prefix for all of your links
 * then define it below.
 */
Ncw_Configure::write('Router.prefix', 'admin');
// Routes
/* TPEPDB 2 */
Ncw_Router::connect(
    'tpepdb/pdf',
    array(
        'module' => 'tpepdb2',
        'controller' => 'pdf',
        'action' => 'index',
    )
);
Ncw_Router::connect(
    'tpepdb/markets',
    array(
        'module' => 'tpepdb2',
        'controller' => 'ajax',
        'action' => 'markets',
    )
);
Ncw_Router::connect(
    'tpepdb/applications',
    array(
        'module' => 'tpepdb2',
        'controller' => 'ajax',
        'action' => 'applications',
    )
);
Ncw_Router::connect(
    'tpepdb/advantages',
    array(
        'module' => 'tpepdb2',
        'controller' => 'ajax',
        'action' => 'advantages',
    )
);
Ncw_Router::connect(
    'tpepdb/safetydata',
    array(
        'module' => 'tpepdb2',
        'controller' => 'pdf',
        'action' => 'safetydata',
    )
);
Ncw_Router::connect(
    'tpepdb/allseries',
    array(
        'module' => 'tpepdb2',
        'controller' => 'pdf',
        'action' => 'allseries',
    )
);
Ncw_Router::connect(
    'tpepdb/allseriesjoin',
    array(
        'module' => 'tpepdb2',
        'controller' => 'pdf',
        'action' => 'allseriesjoin',
    )
);
/* Newsletter */
Ncw_Router::connect(
    'newsletter/subscribe',
    array(
        'module' => 'newsletter',
        'controller' => 'recipient',
        'action' => 'subscribe'
    )
);

Ncw_Router::connect(
    'newsletter/unsubscribe',
    array(
        'module' => 'newsletter',
        'controller' => 'recipient',
        'action' => 'unsubscribe'
    )
);
Ncw_Router::connect(
    'newsletter/confirmSubscription',
    array(
        'module' => 'newsletter',
        'controller' => 'recipient',
        'action' => 'confirmSubscription'
    )
);
Ncw_Router::connect(
    'newsletter/onlineVersion',
    array(
        'module' => 'newsletter',
        'controller' => 'letter',
        'action' => 'showBody'
    )
);
/* CORE */
Ncw_Router::connect(
    'admin',
    array(
        'prefix' => 'admin',
        'module' => 'core',
        'controller' => 'user',
        'action' => 'login'
    )
);
Ncw_Router::connect(
    'admin/profile',
    array(
        'prefix' => 'admin',
        'module' => 'core',
        'controller' => 'user',
        'action' => 'profile'
    )
);
Ncw_Router::connect(
    'admin/logout',
    array(
        'prefix' => 'admin',
        'module' => 'core',
        'controller' => 'user',
        'action' => 'logout'
    )
);
Ncw_Router::connect(
    'admin/*',
    array(
        'prefix' => 'admin',
        'module' => ':module',
        'controller' => ':controller',
        'action' => ':action',
        'params' => ':params'
    )
);
/* WCMS */
Ncw_Router::connect(
    'sitemap.xml',
    array(
        'module' => 'wcms',
        'controller' => 'xml',
        'action' => 'sitemap.xml',
    )
);
Ncw_Router::connect(
    'robots.txt',
    array(
        'module' => 'wcms',
        'controller' => 'website',
        'action' => 'robots'
    )
);
Ncw_Router::connect(
    '*',
    array(
        'module' => 'wcms',
        'controller' => 'website',
        'params' => array(
            'named' => array('url' => ':url')
        )
    )
);
?>
