<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Wcms_Contentboxlanguage class.
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
 * Wcms_Contentboxlanguage class.
 *
 * @package netzcraftwerk
 */
class Wcms_ContentboxlanguageController extends Wcms_ModuleController
{

    /**
     * Layout
     *
     * @var string
     */
    public $layout = 'blank';

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Contentboxes :: Website";

	/**
	 * new Contentboxlanguage
	 *
	 * @param int $contentbox_id
	 */
	public function newAction ($contentbox_id)
	{
	    $contentbox_id = (int) $contentbox_id;
        
		$contentbox_languages = $this->Contentboxlanguage->fetch(
            'id',
            array(
                'conditions' => array('Contentboxlanguage.contentbox_id' => $contentbox_id)
            )
        );
        
        $conditions = array();
        if (count($contentbox_languages) > 0) {
            
            foreach ($contentbox_languages as $language) {
                $conditions[] = 'Language.id != \'' . $language->getLanguageId() . '\'';
            }
            $conditions = array(@implode(' && ', $conditions));
        }
        
        $language = new Wcms_Language();
        $this->view->arr_languages = $language->fetch(
            'list',
            array(
                'fields' => array('Language.name', 'Language.id'),
                'conditions' => $conditions
            )
        );
        
        $this->view->contentbox_id = $contentbox_id;
	}

    /**
     * save action
     *
     * @return void
     */
    public function saveAction ()
    {
        $this->view = false;

       if (true === isset($this->data['Contentboxlanguage'])) {
            $this->Contentboxlanguage->data($this->data['Contentboxlanguage']);
            $this->checkLanguageAccess(
                $this->Contentboxlanguage->getLanguageId()
            );
            if (true === $this->Contentboxlanguage->save()) {
                $this->flushWebsiteCache();
                print '{"return_value" : true, "contentbox_id" : ' . $this->Contentboxlanguage->getId() . '}';
            } else {
                print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->Contentboxlanguage->invalidFields()) . '}';
            }
        }
    }

	/**
	 * Edit action.
	 *
	 * @param int $contentbox_id
	 * @param int $contentboxlanguage_id
	 */
	public function editAction ($contentbox_id, $contentboxlanguage_id)
	{
	    $contentbox_id = (int) $contentbox_id;
        $contentboxlanguage_id = (int) $contentboxlanguage_id;
        
		$this->Contentboxlanguage->setId($contentboxlanguage_id);
        
        $this->checkLanguageAccess(
            $this->Contentboxlanguage->readField('language_id')
        );
        
		$this->Contentboxlanguage->read();
		$this->data['Contentboxlanguage'] = $this->Contentboxlanguage->data();

		$this->view->contentbox_id = $contentbox_id;
		$this->view->contentboxlanguage_id = $this->Contentboxlanguage->getId();
		$this->view->status = $this->Contentboxlanguage->getStatus();

		$language = new Wcms_Language();
		$language->setId($this->Contentboxlanguage->getLanguageId());
		$language_code = $language->readField('shortcut');
		$this->view->language_code = $language_code;
		$this->session->write('language_code', $language_code);
	}
    

    /**
     * update action
     *
     * @return void
     */
    public function updateAction ()
    {
        $this->view = false;

        if (true === isset($this->data['Contentboxlanguage'])) {
            $this->Contentboxlanguage->data($this->data['Contentboxlanguage']);
            $this->checkLanguageAccess(
                $this->Contentboxlanguage->readField('language_id')
            );
            if (true === $this->Contentboxlanguage->saveFields(array('body'))) {
                $this->flushWebsiteCache();
                print '{"return_value" : true}';
            } else {
                print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->Contentbox->invalidFields()) . '}';
            }
        }
    }

 	/**
	 * Delete Contentboxlanguage
	 *
     * @param int $id
     * @param int $contentbox_id
	 *
	 * @return void
	 */
	public function deleteAction ($id)
	{
		$this->view = false;

        $id = (int) $id;
        
	    $this->Contentboxlanguage->setId($id);
        $this->checkLanguageAccess(
            $this->Contentboxlanguage->readField('language_id')
        );
        $this->Contentboxlanguage->delete();
        $this->flushWebsiteCache();
        
        print '{"return_value" : true}';
	}
}
?>
