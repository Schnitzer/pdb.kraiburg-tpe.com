<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ModuleController class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk UG (haftungsbeschränkt)
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * ModuleController class.
 *
 * @package netzcraftwerk
 */
class Tpe_ModuleController extends AppController
{

    /**
     * Before filter
     *
     */
    public function beforeFilter ()
    {
        parent::beforeFilter();
        $this->registerJs('ncw.tpe');
    }

    /**
     * before Render
     *
     */
    public function beforeRender ()
    {
        if ($this->layout === 'default') {
            parent::beforeRender();

            $html = new Ncw_Helpers_Html();
            $html->startup($this->view);

            $this->view->menu = array(
                $html->link(T_('TPE - Sales Offices / Partners'), array('controller' => 'region', 'action' => 'all')),
                $html->link(T_('TPE - Literature'), array('controller' => 'literature', 'action' => 'index')),
                $html->link(T_('TPE - Language'), array('controller' => 'language', 'action' => 'index')),
                $html->link(T_('Regions'), array('controller' => 'region', 'action' => 'all')),
            );
        }
    }
}
?>
