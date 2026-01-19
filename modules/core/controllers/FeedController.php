<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Note FeedController class.
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
 * FeedController class.
 *
 * @package netzcraftwerk
 */
class Core_FeedController extends Core_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Feed Management";

	/**
	 * Paginate
	 *
	 * @var array
	 */
	public $paginate = array(
        'limit' => 25,
        'order' => 'Feed.name'
	);
	
	/**
	 * 
	 */
	public $acl_publics = array('feed');

	/**
	 * 
	 */
	public function allAction () 
	{
		$this->view->feeds = $this->paginate();
	}
	
	/**
	 * 
	 */
	public function newAction ()
	{
		
	}
	
	/**
	 * 
	 */
	public function saveAction ()
	{
		$this->view = false;
		
		if (true === isset($this->data['Feed'])) {
			$this->Feed->data($this->data['Feed']);
			if (true === $this->Feed->save()) {
				print '{ "return_value" : true }';	
			} else {
				print '{ "return_value" : false , "invalid_fields" : ' . json_encode($this->Feed->invalidFields()) . '}';
			}
			return;
		}
		
		print '{ "return_value" : false }';
	}
	
	/**
	 * 
	 */
	public function editAction ($id)
	{
		$this->Feed->setId($id);
		$this->Feed->read();
		$this->data['Feed'] = $this->Feed->data();
		
		$this->view->feed_id = (int) $id;
		
		$this->registerJs('ncw.core.feed');
	}
	
	/**
	 * 
	 */
	public function updateAction ($id)
	{
		$this->view = false;
		
		$this->Feed->setId($id);
		
		if (true === isset($this->data['Feed'])) {
			$this->Feed->data($this->data['Feed']);
			if (true === $this->Feed->save()) {
				print '{ "return_value" : true }';	
			} else {
				print '{ "return_value" : false , "invalid_fields" : ' . json_encode($this->Feed->invalidFields()) . '}';
			}
			return;
		}
		
		print '{ "return_value" : false }';
	}
	
	/**
	 * 
	 */
	public function deleteAction ($id)
	{
		$this->view = false;
		
		if ((int) $id > 0) {
			$this->Feed->setId($id);
			$this->Feed->delete();
			
			print '{ "return_value" : true }';
			return;			
		}
		
		print '{ "return_value" : false }';
	}
	
	/**
	 * 
	 */
	public function feedAction ()
	{
		if (true === isset($this->params['url']['id'])) {
			$feed_id = (int) $this->params['url']['id'];
			$this->layout_path = 'rss';
			$this->view_path = 'feed/rss';
			
			$this->request_handler->respondAs('rss');			
							
			$this->Feed->setId($feed_id);
			$this->Feed->read();
			
			$this->page_title = $this->Feed->getTitle();
			
	        $this->view->page_title = $this->Feed->getTitle();
	        $this->view->description = $this->Feed->getDescription();
	        $this->view->language = $this->Feed->getLanguage();
	        $this->view->pub_date = $this->Feed->getPubDate();
	        $this->view->generator = $this->Feed->getGenerator();
	        $this->view->managing_editor = $this->Feed->getManagingEditor();
	        $this->view->web_master = $this->Feed->getWebMaster();
			
			$params = array();
			foreach (explode(';', $this->Feed->getParams()) as $key => $value) {
				$data = explode(':', $value);
				$params[$data[0]] = $data[1];
			}

			$this->view->feed_items = $this->requestAction(
				array(
					'module' => $this->Feed->getModule(),
					'controller' => $this->Feed->getController(),
					'action' => 'dataForFeed',
					
				),
				array(
			        'named' => $params
	            )			
			);
		} else {
			$this->view = false;
		}
		
	}
}
?>
