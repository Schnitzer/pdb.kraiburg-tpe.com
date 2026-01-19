<?php
/* SVN FILE: $Id$ */
/**
 * Contains the JavascriptController class.
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
 * JavascriptController class.
 *
 * @package netzcraftwerk
 */
class Wcms_JavascriptController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Javascript :: Website";

    /**
     * Components...
     *
     * @var array
     */
    public $components = array('Acl', 'File');

    /**
	 * Show sites action.
	 *
	 */
	public function allAction ()
	{
		$all_javascripts = $this->Javascript->fetch("all");
		$this->view->all_javascripts = $all_javascripts;
	}

	/**
	 * New site action.
	 *
	 */
	public function newAction ()
	{
        $this->registerJs(
            array(
                'lib/codemirror/js/codemirror',
                'ncw.wcms',
                'ncw.wcms.extras',
            )
        );
        $this->registerCss('wcms');

		$code = "";
		if (true === isset($this->data['Javascript'])) {

		    $text = new Ncw_Helpers_Text();

			$this->Javascript->data($this->data['Javascript']);
			// set new filename
			$this->Javascript->setFilename($text->cleanForUrl($this->Javascript->getName()));
			$code = $this->data['Javascript']['code'];
			$this->data['Javascript']['code'] = "";
			if (true === $this->Javascript->save()) {
				// Create the css file
				$template_file_name = ASSETS . DS . "wcms" . DS . "javascript" . DS . $this->Javascript->getFilename() . ".js";
				$this->file->write($template_file_name, "w", $code);
				$this->redirect(array("action" => "all"));
			}
		}
		$this->view->code = $code;
	}

	/**
	 * Edit site action.
	 *
	 */
	public function editAction ($id = 0)
	{
        $this->registerJs(
            array(
                'lib/codemirror/js/codemirror',
                'ncw.wcms',
                'ncw.wcms.extras',
            )
        );
        $this->registerCss('wcms');

		$this->Javascript->setId($id);
		$this->Javascript->read();
		if (true === isset($this->data['Javascript'])) {

		    $text = new Ncw_Helpers_Text();

			// get old filename form database
			$template_file_old_name = ASSETS . DS . "wcms" . DS . "javascript" . DS . $this->Javascript->getFilename() . ".js";
			// set data from the form
			$this->Javascript->data($this->data['Javascript']);
			// set new filename
			$this->Javascript->setFilename($text->cleanForUrl($this->Javascript->getName()));
			$template_file_name = ASSETS . DS . "wcms" . DS . "javascript" . DS . $this->Javascript->getFilename() . ".js";
			$code = $this->data['Javascript']['code'];
			$this->data['Javascript']['code'] = "";
			// rename the files
			if (true === $this->Javascript->save()) {
				// edit the css file
				$this->file->write($template_file_old_name, "w", $code);
				rename($template_file_old_name, $template_file_name);
				$this->Javascript->read();
			}
		} else {
			$template_file_name = ASSETS . DS . "wcms" . DS . "javascript" . DS . $this->Javascript->getFilename() . ".js";
			$code = $this->file->read($template_file_name);
			$this->data['Javascript'] = $this->Javascript->data();
		}
		$this->view->code = $code;
		$this->view->javascript_id = $id;
	}

	/**
	 * delete site action.
	 *
	 */
	public function deleteAction ($id)
	{
		$this->view = false;

		$this->Javascript->setId($id);
		if (true === $this->Javascript->delete()) {
			$template_file_name = ASSETS . DS . "wcms" . DS . "javascript" . DS . $this->Javascript->getFilename() . ".js";
			// Delete the css file
			$this->file->delete($template_file_name);
		}

        $this->redirect(
            array(
                'action' => 'all'
            )
        );
	}
}
?>
