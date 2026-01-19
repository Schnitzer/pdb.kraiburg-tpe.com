<?php
/* SVN FILE: $Id$ */
/**
 * Contains the LanguagesController class.
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
class Core_LanguageController extends Core_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Language Management";

	/**
	 * shows all existing languages
	 *
	 */
	public function allAction ()
	{
		$this->view->arr_all_languages = $this->Language->fetch(
            'all',
            array(
                'fields' => array(
                    'Language.id',
                    'Language.name',
                    'Language.shortcut'
                )
            )
        );
	}

	/**
	 * new language package
	 *
	 */
	public function newAction ()
	{
        $this->registerJs(
            array(
                'ncw.core.languages',
            )
        );
	}

	/**
	 * save new language
	 *
	 */
	public function saveAction ()
	{
	    $this->view = false;

	    $return = array("return_value" => false);
	    if (true === isset($this->data['Language'])) {
            $this->Language->data($this->data['Language']);
	        if (false === $this->Language->save()) {
                $return['invalid_fields'] = $this->Language->invalidFields();
            } else {
                $return['return_value'] = true;
            }
        }

        print json_encode($return);
	}

	/**
	 * to edit one language
	 *
	 */
	public function editAction ($id)
	{
        $this->registerJs(
            array(
                'ncw.core.languages',
            )
        );

		$this->Language->setId($id);
		$this->Language->read();

		$this->data['Language'] = $this->Language->data();
		$this->view->language_id = $id;
	}

	/**
	 * to edit one language
	 *
	 */
	public function updateAction ()
	{
		$this->view = false;

		$return = array("return_value" => false);
		if (true === isset($this->data['Language'])) {
			$this->Language->data($this->data['Language']);
			if (false === $this->Language->save()) {
				$return['invalid_fields'] = $this->Language->invalidFields();
			} else {
				$return['return_value'] = true;
			}
		}

		print json_encode($return);
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

		print '{"return_value" : true}';
	}
}
?>
