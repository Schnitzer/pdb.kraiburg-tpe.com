<?php
/* SVN FILE: $Id$ */
/**
 * Contains the SettingController class.
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
 * SettingController class.
 *
 * @package netzcraftwerk
 */
class Wcms_SettingController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Settings :: Website";

    /**
     * Components...
     *
     * @var array
     */
    public $components = array('Acl');

	/**
	 * Index action.
	 *
	 */
	public function indexAction ()
	{
        $this->registerJs(
            array(
                'ncw.wcms',
                'ncw.wcms.extras',
            )
        );

		$this->Setting->setId(1);
		if (true === isset($this->data['Setting'])) {
			$this->Setting->data($this->data['Setting']);
			$this->Setting->save();
		} else {
			$this->Setting->read();
			$this->data['Setting'] = $this->Setting->data();
		}

		$language = new Wcms_Language();
		$this->view->languages = $language->fetch(
            'list',
            array(
                'fields' => array(
                    'Language.name',
                    'Language.id'
                )
            )
		);
	}
}
?>
