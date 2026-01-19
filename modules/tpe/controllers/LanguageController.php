<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Language class.
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
 * LanguageController class.
 *
 * @package netzcraftwerk
 */
class Tpe_LanguageController extends Tpe_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "TPE :: Language";
	
    /**
     * Paginate optionen
     */
    public $paginate = array(
        'limit' => '30',
        'order' => array('Language.id' => 'asc')
    );

	/**
	 * Index
	 *
	 */
	public function indexAction ()
	{
		$this->registerJS(array('ncw.tpe.language'));
		// permissions
		/*$this->view->permissions = array(
			'/newsletter/container/new' => $this->acl->check('/newsletter/container/new'),
        );*/			
	}

	/**
	 * Show all items
	 *
	 */
	public function allAction ()
	{
		$search = '';
        if (true === isset($this->params['url']['s'])) {
            $search = $this->params['url']['s'];
            $search_conditions[0] = 'Language.name LIKE \'%' . $search . '%\'';
        }

		$this->view->all_items = $language = $this->paginate($search_conditions);	
		$this->view->search_str = $search;
	}

	/**
	 * New
	 *
	 */
	public function newAction ()
	{

	}

    /**
     * Save
     */
    public function saveAction ()
    {
        $this->view = false;

        $return = array('return_value' => false);

        if (true === isset($this->data['Language'])) {
            $this->Language->data($this->data['Language']);
            if (true === $this->Language->save()) {
            	$return['language_id'] = $this->Language->getId();
                $return['return_value'] = true;
            } else {
                $return['invalid_fields'] = array_merge($this->Language->invalidFields());
            }
        }

        print json_encode($return);
    }


	/**
	 * Edit
	 *
	 */
	public function editAction ($id)
	{
		$this->Language->setId($id);
		if (true === isset($this->data['Language'])) {
			$this->Language->data($this->data['Language']);
			$this->Language->save();
		} else {
			$this->Language->read();
			$this->data['Language'] = $this->Language->data();
		}

		$this->view->id = $id;
	}

	/**
	 * Update
	 *
	 */
	public function updateAction () {
        $this->view = false;
        if (true === isset($this->data['Language'])) {
            $this->Language->data($this->data['Language']);
            if (true === $this->Language->save()) {
                print '{ "return_value" : true }';
            } else {
                print '{ "return_value" : false , "invalid_fields" : ' . json_encode($this->Language->invalidFields()) . ' }';
            }
        }
	}

	/**
	 * Delete
	 *
	 */
    public function deleteAction ($id)
    {
        $this->view = false;

        if ($id > 0) {
            $this->Language->setId((int) $id);
            $this->Language->delete();
        }

        print '{ "return_value" : true }';
    }	
}
?>
