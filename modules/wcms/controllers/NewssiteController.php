<?php
/* SVN FILE: $Id$ */
/**
 * Contains the NewssiteController class.
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
 * NewsSiteController class.
 *
 * @package netzcraftwerk
 */
class Wcms_NewssiteController extends Wcms_ModuleController
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
	 * add news to site
	 *
	 * @param int $id id of the news
	 *
	 * @return void
	 */
	public function newAction ($news_id)
	{
		if (true === isset($this->data['Newssite'])) {
			$this->Newssite->setNewsId($news_id);
			$this->Newssite->setSiteId($this->data['Newssite']['site_id']);
			$this->Newssite->save();
			$this->redirect(
                array(
                    'controller' => 'news', 'action' => 'edit', 'id' => $news_id
                )
            );
		}

        $this->registerJS(
            array(
                'ncw.wcms.news'
            )
        );

		// reads all the sites which are still connected to the news
		$arr_news_sites = $this->Newssite->fetch(
            'id',
            array(
                'conditions' => array('Newssite.news_id' => $news_id)
            )
        );
		$arr_conditions = array();
		$str_conditions = '';
		if (true === isset($arr_news_sites[0])) {
			foreach ($arr_news_sites as $site) {
				$arr_conditions[] = ' Site.id != \'' . $site->getSiteId() . '\'';
			}
			$str_conditions = ' && ' . @implode(' && ', $arr_conditions);
		}

		// new site Object
		$site = new Wcms_Site();
		$site->unbindModel('all');
		$this->view->arr_options = $site->fetch(
            'all',
            array(
                'fields' => array(
                    'Site.id',
                    'Site.level' => 'COUNT(`p`.`id`)-1',
                    'Site.name'
                ),
                'conditions' => array(
                    'Site.id != 1' . $str_conditions
                )
            )
        );

        $this->registerJS(
            array(
                'lib/date',
                'lib/jquery.datePicker',
                'lib/jquery.timePicker',
                'ncw.wcms.news'
            )
        );

		$this->view->news_id = $news_id;
	}

 	/*
	 * Delete news-site
	 *
	 * @param int $id (id of the news)
	 * @param int $id_site
	 */
	public function deleteAction ($id, $news_id)
	{
		$this->view = false;

		$news_site = new Wcms_NewsSite();
		$news_site->setId($id);
		$news_site->delete();

		$this->redirect(array('controller' => 'news', 'action' => 'edit', 'id' => $news_id));

	}

}
?>
