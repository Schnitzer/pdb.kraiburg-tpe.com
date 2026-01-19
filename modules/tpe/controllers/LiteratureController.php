<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Literature class.
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
 * LiteratureController class.
 *
 * @package netzcraftwerk
 */
class Tpe_LiteratureController extends Tpe_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "TPE :: Literature";

	/**
	 * ACL publics
	 *
	 * @var array
	 */
	public $acl_publics = array('frontend', 'getEntries');

    /**
     * Paginate optionen
     */
    public $paginate = array(
        'limit' => '30',
        'order' => array('Literature.id' => 'asc')
    );

    /**
     *
     *
     */
    public $layout = 'default';

	/**
	 * Index
	 *
	 */
	public function indexAction ()
	{
		$this->registerJS(array('ncw.tpe.literature'));
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
            $search_conditions[0] = 'Literature.name LIKE \'%' . $search . '%\'';
        }

		$this->view->all_items = $literature = $this->paginate($search_conditions);	
		$this->view->search_str = $search;
	}

	/**
	 * New
	 *
	 */
	public function newAction ()
	{
		// wcms languages
		$this->loadModel("Wcms_Language");
		$this->view->wcms_languages = $this->Wcms_Language->fetch('all');
	}

    /**
     * Save
     */
    public function saveAction ()
    {
        $this->view = false;

        $return = array('return_value' => false);

        if (true === isset($this->data['Literature'])) {
            $this->Literature->data($this->data['Literature']);
            if (true === $this->Literature->save()) {
            	$return['literature_id'] = $this->Literature->getId();

				$this->loadModel("LiteratureWcmslanguage");
            	foreach ($this->data['LiteratureWcmslanguage'] as $language) {
            		$this->LiteratureWcmslanguage->create();
            		$this->LiteratureWcmslanguage->setChecked(0);
            		$this->LiteratureWcmslanguage->data($language);
            		$this->LiteratureWcmslanguage->setLiteratureId($this->Literature->getId());
            		$this->LiteratureWcmslanguage->save();
            	}

                $return['return_value'] = true;
            } else {
                $return['invalid_fields'] = array_merge($this->Literature->invalidFields());
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
		$this->Literature->setId($id);
		$this->Literature->read();
		$this->data['Literature'] = $this->Literature->data();

		$this->view->id = $id;

		$literature_language = null;
		$added_languages = array();
		if (true === is_object($this->Literature->LiteratureLanguage)) {
			$literature_language = $this->Literature->LiteratureLanguage;
			foreach ($literature_language as $language) {
				$added_languages[] = $language->getLanguageId();
			}
		}
		$this->view->literature_language = $literature_language;
		$this->view->added_languages = $added_languages;

		$added_wcmslanguages = array();
		if (true === is_object($this->Literature->LiteratureWcmslanguage)) {
			$literature_wcmslanguage = $this->Literature->LiteratureWcmslanguage;
			foreach ($literature_wcmslanguage as $language) {
				$added_wcmslanguages[$language->getLanguageId()] = array(
					'id' => $language->getId(),
					'checked' => $language->getChecked()
				);
			}			
		}
		$this->view->added_wcmslanguages = $added_wcmslanguages;

		// languages
		$this->loadModel("Language");
		$languages = $this->Language->fetch('all');
	    $list = array();
        foreach ($languages as $language) {
        	if (false == in_array($language->getId(), $added_languages)) {
            	$list[$language->getName()] = $language->getId();
            }
        }
        $this->view->languages = $list;	

		// wcms languages
		$this->loadModel("Wcms_Language");
		$this->view->wcms_languages = $this->Wcms_Language->fetch('all');
	}

	/**
	 * Update
	 *
	 */
	public function updateAction () {
        $this->view = false;
        if (true === isset($this->data['Literature'])) {
            $this->Literature->data($this->data['Literature']);
            if (true === $this->Literature->save()) {
            	
            	$this->loadModel("LiteratureLanguage");
            	foreach ($this->data['LiteratureLanguage'] as $language) {
            		$this->LiteratureLanguage->data($language);
            		$this->LiteratureLanguage->saveFields(array('file_id', 'pic_id', 'title', 'description','position'));
            	}

				$this->loadModel("LiteratureWcmslanguage");
            	foreach ($this->data['LiteratureWcmslanguage'] as $language) {
            		$this->LiteratureWcmslanguage->create();
            		$this->LiteratureWcmslanguage->setLiteratureId($this->Literature->getId());
            		$this->LiteratureWcmslanguage->setChecked(0);
            		$this->LiteratureWcmslanguage->data($language);
            		$this->LiteratureWcmslanguage->save();
            	}

                print '{ "return_value" : true }';
            } else {
                print '{ "return_value" : false , "invalid_fields" : ' . json_encode($this->Literature->invalidFields()) . ' }';
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
            $this->Literature->setId((int) $id);
            $this->Literature->delete();
        }

        print '{ "return_value" : true }';
    }	

	/**
	 * Add Language
	 *
	 */
	public function addLanguageAction ($id, $language_id) {
        $this->view = false;

		$this->loadModel("LiteratureLanguage");
		$this->LiteratureLanguage->setLiteratureId((int) $id);
		$this->LiteratureLanguage->setLanguageId((int) $language_id);
		$this->LiteratureLanguage->save();

		print '{ "return_value" : true }';
	}

	/**
	 * Remove Language
	 *
	 */
	public function removeLanguageAction ($literatur_language_id) {
        $this->view = false;

		$this->loadModel("LiteratureLanguage");
		$this->LiteratureLanguage->setId((int) $literatur_language_id);
		$this->LiteratureLanguage->delete();

		print '{ "return_value" : true }';
	}

	/**
	 * Frontend
	 *
	 */
	public function frontendAction () {
		$this->layout = 'blank';

		// languages
		$this->loadModel("Language");
		$languages = $this->Language->fetch('all');
	    $list = array();
        foreach ($languages as $language) {
        	$list[$language->getName()] = $language->getId();
        }
        $this->view->languages = $list;	
        $this->view->selected_language = (int) $this->params['language_id'];

        $this->view->wcms_language_id = (int) $this->params['wcms_language_id'];
	}

	/**
	 *
	 *
	 */
	public function getEntriesAction ($language_id, $wcms_language_id) 
	{
		$this->layout = 'blank';

		$language_id = (int) $language_id;
		$wcms_language_id = (int) $wcms_language_id;
		$this->view->wcms_language_id = $wcms_language_id;

        // literature
        $this->Literature->unbindModel('all');
        $this->Literature->bindModel(
        	array(
        		'has_one' => array(
        			'LiteratureLanguage',
        			'LiteratureWcmslanguage'
        		)
        	)
        );
        $this->view->entries = $this->Literature->fetch(
        	'all',
        	array(
        		'fields' => array(
        			'LiteratureLanguage.title',
        			'LiteratureLanguage.description',
        			'LiteratureLanguage.file_id',
        			'LiteratureLanguage.pic_id',
        			'LiteratureLanguage.position'
        		),
        		'conditions' => array(
        			'LiteratureWcmslanguage.language_id' => $wcms_language_id,
        			'LiteratureWcmslanguage.checked' => 1,
        			'LiteratureLanguage.language_id' => $language_id
        		),
        		'order' => array('LiteratureLanguage.position','Literature.name')
        	)
        );
	}
}
?>
