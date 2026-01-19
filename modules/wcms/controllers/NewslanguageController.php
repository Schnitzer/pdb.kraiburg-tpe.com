<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Wcms_Newslanguage class.
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
 * Wcms_Newslanguage class.
 *
 * @package netzcraftwerk
 */
class Wcms_NewslanguageController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "News :: Website";

    /**
     * Components...
     *
     * @var array
     */
    public $components = array('Acl');

	/**
	 * new Langauge
	 *
	 * @param int $id (id of the news)
	 */
	public function newAction ($id)
	{
		if (true === isset($this->data['Newslanguage'])) {
			$this->Newslanguage->data($this->data['Newslanguage']);
            $this->checkLanguageAccess(
                $this->Newslanguage->getLanguageId()
            );
			$this->Newslanguage->setNewsId($id);
			if (true === $this->Newslanguage->save()) {
				$this->redirect(
				    array(
				        'controller' => 'news',
				        'action' => 'edit',
				        'id' => $id
				    )
				);
			}
		}

        $this->registerJs(
            array(
                'lib/tiny_mce/tiny_mce_gzip',
                'ncw.wcms.tinymce.gzip',
                'ncw.wcms.tinymce',
                'ncw.wcms.news',
            )
        );

		$arr_news_languages = $this->Newslanguage->fetch(
            'id',
            array(
                'conditions' => array('Newslanguage.news_id' => $id)
            )
        );
		$arr_conditions = array();
		if (count($arr_news_languages) > 0) {
			foreach ($arr_news_languages as $language) {
				$arr_conditions[] = 'Language.id != \'' . $language->getLanguageId() . '\'';
			}
		}
		$str_conditions = @implode(' && ', $arr_conditions);
		// get the list of all Wcms_languages
		$language = new Wcms_Language();
		if (count($arr_conditions) > 0) {
			$this->view->arr_languages = $language->fetch(
                'list',
                array(
                    'fields' => array(
                        'Language.name',
                        'Language.id'
                    ),
                    'conditions' => array($str_conditions)
                )
            );
		} else {
			$this->view->arr_languages = $language->fetch(
                'list',
                array(
                    'fields' => array('Language.name', 'Language.id')
                )
            );
		}
		$this->view->news_id = $id;
	}

	/**
	 * Edit action.
	 *
	 * @param int $id
	 * @param int $newslanguage_id
	 */
	public function editAction ($id, $newslanguage_id)
	{
        $this->registerJs(
            array(
                'lib/tiny_mce/tiny_mce_gzip',
                'ncw.wcms.tinymce.gzip',
                'ncw.wcms.tinymce',
                'ncw.wcms.news',
            )
        );

		$this->Newslanguage->setId($newslanguage_id);
        $this->checkLanguageAccess(
            $this->Newslanguage->readField('language_id')
        );
		if (isset($this->data['Newslanguage'])) {
			$this->Newslanguage->data($this->data['Newslanguage']);
			$this->Newslanguage->setNewsId($id);
			$this->Newslanguage->save();
		}
		$this->Newslanguage->read();
		$this->data['Newslanguage'] = $this->Newslanguage->data();
		$this->view->news_id = $id;
		$this->view->newslanguage_id = $this->Newslanguage->getId();
		$this->view->newslanguage_status = $this->Newslanguage->getStatus();

		$language = new Wcms_Language();
		$language->setId($this->Newslanguage->getLanguageId());
		$this->view->language_code = $language->readField('shortcut');

        $this->view->permissions = array(
            '/wcms/newslanguage/new' => $this->acl->check('/wcms/newslanguage/new'),
            '/wcms/newslanguage/edit' => $this->acl->check('/wcms/newslanguage/edit'),
            '/wcms/newslanguage/delete' => $this->acl->check('/wcms/newslanguage/delete'),
            '/wcms/newslanguage/publish' => $this->acl->check('/wcms/newslanguage/publish'),
            '/wcms/newslanguage/unpublish' => $this->acl->check('/wcms/newslanguage/unpublish'),
        );
	}

 	/**
	 * Delete newslanguage-site
	 *
	 * @param int $id id of the newslanguage
	 * @param int $news_id id of the news
	 *
	 * @return void
	 */
	public function deleteAction ($id, $news_id)
	{
		$this->view = false;

		$this->Newslanguage->setId($id);
        $this->checkLanguageAccess(
            $this->Newslanguage->readField('language_id')
        );
		$this->Newslanguage->delete();

		$this->redirect(
            array(
                'controller' => 'news',
                'action' => 'edit',
                'id' => $news_id
            )
        );
	}

	/**
	 * publish newslanguages
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function publishAction ($id)
	{
        $this->view = false;

        // publish the sitelanguage
        $this->Newslanguage->setId($id);

        $this->checkLanguageAccess(
           $this->Newslanguage->readField('language_id')
        );

        $this->Newslanguage->setStatus('published');
        $this->Newslanguage->saveField('status');

        $this->Newslanguage->unbindModel('all');
        $this->Newslanguage->read();

        $this->publishObject('Newslanguage', $this->Newslanguage);

        $this->flushWebsiteCache();

        print '{"return_value" : true}';
	}

	/**
	 * unpublish newslanguage
	 *
     * @param int $id
     *
     * @return void
	 */
	public function unpublishAction ($id)
	{
        $this->view = false;

        $this->Newslanguage->setId($id);

        $this->checkLanguageAccess(
           $this->Newslanguage->readField('language_id')
        );

        $this->Newslanguage->setStatus('unpublished');
        $this->Newslanguage->saveField('status');

        $this->Newslanguage->unbindModel('all');
        $this->Newslanguage->read();

        $published_newslanguage = new Wcms_PublishedNewslanguage();
        $published_newslanguage->setId($this->Newslanguage->getId());
        $published_newslanguage->delete();

        $this->flushWebsiteCache();

        print '{"return_value" : true}';
	}
}
?>
