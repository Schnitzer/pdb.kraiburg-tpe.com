<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ModuleController class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk GmbH
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
class Core_ModuleController extends AppController
{

    /**
     * General translations. only for translation tool
     *
     * @return void
     */
    private function __generalTranslations ()
    {
        T_('Applications');
        T_('Logout');
        T_('Profile');
		T_('Other');
    }

    /**
     * Before filter
     *
     */
    public function beforeFilter ()
    {
        parent::beforeFilter();
        if (Ncw_Configure::read('App.language') != 'en_EN') {
            $this->registerJs('locale/' . Ncw_Configure::read('App.language') . '/LC_MESSAGES/default');
        }
        $this->registerJs('ncw.core');
    }

	/**
	 * Init. Before Render
	 *
	 */
	public function beforeRender ()
	{
        if ($this->layout === 'default') {
            parent::beforeRender();

            $html = new Ncw_Helpers_Html();
            $html->startup($this->view);

            $this->view->menu = array(
                $html->link(T_('System Management'), array('controller' => 'usergroup', 'action' => 'all')),
                $html->link(T_('User Management'), array('controller' => 'usergroup', 'action' => 'all')),
                $html->link(T_('Module Management'), array('controller' => 'modules')),
            );

            $this->view->extras_menu = array(
                $html->link(T_('Feed Management'), array('controller' => 'feed', 'action' => 'all')),
                $html->link(T_('Language Management'), array('controller' => 'language', 'action' => 'all')),
            );
        }
	}
}
?>
