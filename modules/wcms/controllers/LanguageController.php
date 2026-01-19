<?php
/* SVN FILE: $Id$ */
/**
 * Contains the WcmsLanguageController class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschraenkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcrafrtwerk.com>
 * @copyright		Copyright 2007-2009, Netzcraftwerk UG
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * LanguagesController class.
 *
 * @package netzcraftwerk
 */
class Wcms_LanguageController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Languages :: Website";

    /**
     * Components...
     *
     * @var array
     */
    public $components = array('Acl');

	/**
	 * shows all existing languages
	 *
	 */
	public function allAction ()
	{
		$this->Language->unbindModel('all');
		$this->view->arr_all_languages = $this->Language->fetch('all');
	}

	/**
	 * new language
	 *
	 */
	public function newAction ()
	{
        $this->registerJs(
            array(
                'ncw.wcms',
                'ncw.wcms.extras',
            )
        );

		if (isset($this->data['Language'])) {
			$this->Language->data($this->data['Language']);
			if (true === $this->Language->save()) {
                $this->acl->addACO(
                    '/wcms/permissions/language/' . $this->Language->getId()
                );
                $this->redirect(array("action" => "all"));
			}
		}
	}

	/**
	 * edit language
	 *
	 */
	public function editAction ($id)
	{
        $this->registerJs(
            array(
                'ncw.wcms',
                'ncw.wcms.extras',
            )
        );

	    $this->Language->setId($id);
	    if (true === isset($this->data['Language']) && false === empty($id)) {
            $this->Language->data($this->data['Language']);
            $this->Language->save();
        }
		$this->Language->read();
		$this->data['Language'] = $this->Language->data();
		$this->view->language_id = $id;
	}

	/**
	 * Delete language
	 *
	 */
	public function deleteAction ($id)
	{
	    $this->view = false;

		$this->Language->setId($id);
		$this->Language->delete();
        $this->acl->removeACO(
            '/wcms/permissions/language/' . $id
        );

        $this->redirect(
            array(
                'action' => 'all'
            )
        );
	}
}
?>
