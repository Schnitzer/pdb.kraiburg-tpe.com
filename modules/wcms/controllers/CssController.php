<?php
/* SVN FILE: $Id$ */
/**
 * Contains the CssController class.
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
 * CssController class.
 *
 * @package netzcraftwerk
 */
class Wcms_CssController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Css :: Website";

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
		$all_css = $this->Css->fetch("all");
		$this->view->all_css = $all_css;
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
		if (true === isset($this->data['Css'])) {

		    $text = new Ncw_Helpers_Text();

			$this->Css->data($this->data['Css']);
			// set new filename
			$this->Css->setFilename($text->cleanForUrl($this->Css->getName()));
			$code = $this->data['Css']['code'];
			$this->data['Css']['code'] = "";
			if (true === $this->Css->save()) {
				// Create the css file
				$template_file_name = ASSETS . DS . "wcms" . DS . "css" . DS . $this->Css->getFilename() . ".css";
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
	public function editAction ($id)
	{
        $this->registerJs(
            array(
                'lib/codemirror/js/codemirror',
                'ncw.wcms',
                'ncw.wcms.extras',
            )
        );
        $this->registerCss('wcms');

		$this->Css->setId($id);
		$this->Css->read();
		if (true === isset($this->data['Css'])) {

		    $text = new Ncw_Helpers_Text();

			// get old filename form database
			$template_file_old_name = ASSETS . DS . "wcms" . DS . "css" . DS . $this->Css->getFilename() . ".css";
			// set data from the form
			$this->Css->data($this->data['Css']);
			// set new filename
			$this->Css->setFilename($text->cleanForUrl($this->Css->getName()));
			$template_file_name = ASSETS . DS . "wcms" . DS . "css" . DS . $this->Css->getFilename() . ".css";
			$code = $this->data['Css']['code'];
			$this->data['Css']['code'] = "";
			if (true === $this->Css->save()) {
				// edit the css file
				$this->file->write($template_file_old_name, "w", $code);
				// rename the files
				rename($template_file_old_name, $template_file_name);
				$this->Css->read();
			}
		} else {
			$template_file_name = ASSETS . DS . "wcms" . DS . "css" . DS . $this->Css->getFilename() . ".css";
			$code = $this->file->read($template_file_name);
			$this->data['Css'] = $this->Css->data();
		}
		$this->view->code = $code;
		$this->view->css_id = $id;
	}

	/**
	 * delete site action.
	 *
	 */
	public function deleteAction ($id)
	{
		$this->view = false;

		$this->Css->setId($id);
		if (true === $this->Css->delete()) {
			$template_file_name = ASSETS . DS . "wcms" . DS . "css" . DS . $this->Css->getFilename() . ".css";
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
