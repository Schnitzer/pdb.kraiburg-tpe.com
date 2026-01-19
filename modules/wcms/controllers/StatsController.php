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
class Wcms_StatsController extends Wcms_ModuleController
{

    public $acl_publics = array(
        "exportSuchbegriffeCsv",
		"updateSuchbegriffe"
    );

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
		//$this->view = false;
		//$all_css = $this->Stats->fetch("all");
		//$this->view->all_css = $all_css;
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


	public function updateSuchbegriffeAction()
	{

		exit;
		$this->view = false;
		$count = 0;
		$jahr = date('Y', time()) - 1;
		if (date('m') == 12) {
			$monat = '01';
		} else {
			$monat = date('m') - 1;
		}


		$str = 
		"
		SELECT se.id, se.term, se.checkbox, se.modified FROM `ncw_wcms_search` AS se 
		INNER JOIN ncw_tpepdb2_compound as com
		ON se.term = com.name
		
		WHERE se.modified > '2024-12-01 00:00:00' AND se.modified < '2024-12-18 00:00:01' ORDER by se.modified DESC";

		//echo $str;
		
	    $db = Ncw_Database::getInstance();

		$sth = $db->prepare($str);
		$sth->execute();
		$results = $sth->fetchAll();

		foreach ($results As $row) {
			$count++;
			echo '<br>' . $count . ' ' . $row['id'] . ' ' . $row['term'];
			$strUpd = "UPDATE ncw_wcms_search SET compound='1' WHERE id=".$row['id']."";
			echo $strUpd;
			$sthUpd = $db->prepare($strUpd);
			$sthUpd->execute();
		}

	}


	/**
	 * Suchbegriffe aus dem Texteingabefeld in csv Datei exportieren
	 */
	public function exportSuchbegriffeCsvAction ()
	{
		$this->view = false;
		
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="suchbegriffe.csv"');
		
		$jahr = date('Y', time()) - 1;
		if (date('m') == 12) {
			$monat = '01';
		} else {
			$monat = date('m') - 1;
		}
	    $db = Ncw_Database::getInstance();
		$sth = $db->prepare(
				"SELECT id, term, checkbox, compound,  modified FROM `ncw_wcms_search` WHERE modified > '" . $jahr . "-" . $monat . "-01 00:00:00' ORDER by modified DESC"
		);
		$sth->execute();
		$results = $sth->fetchAll();

		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');

		// output the column headings
		fputcsv($output, array('Term', 'Checkbox', 'Compound', 'Date', 'Time'), ';');

		// loop over the rows, outputting them
		foreach ($results As $row) {
			$row = str_replace("'", '', $row);
			$row = str_replace('"', '', $row);

			$arr_date = explode(' ',$row['modified']);
			$arr_row = array($row['term'], $row['checkbox'], $row['compound'], $arr_date[0], $arr_date[1].$jahr);
			if ( strlen( trim($row['term']) ) > 0 || strlen( trim($row['checkbox']) ) > 1) {
				fputcsv($output, $arr_row , ';');
			}
		}
	}
	
	public function exportStatsCsvAction ()
	{
		
		$this->view = false;
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="stats.csv"');
	
        $db = Ncw_Database::getInstance();



        $sth = $db->prepare(
            "SELECT * FROM `ncw_wcms_stats` WHERE file LIKE '%//pdb.%' "
        );
		

        $sth->execute();

        $results = $sth->fetchAll();


		
		
		//header('Content-Type: text/csv; charset=utf-8');
		//header('Content-Disposition: attachment; filename=data.csv');

		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');

		// output the column headings
		fputcsv($output, array('File', 'Name', 'Type', 'Mode', 'Lang', 'Internal', 'Date'), ';');


		// loop over the rows, outputting them
		foreach ($results As $row) {
			
			$str_file = $row['file'];
			$arr_file = explode('?', $str_file);
			$arr_was = explode('=', $arr_file[1]);
			$str_was = $arr_was[0];
			$such_id = $arr_was[1];
			$str_print == '';
			if ($str_was == 'cid') {
				$obj_print = new Tpepdb2_Compound();
				$obj_print->unbindModel('all');
				$obj_print->setId($such_id);
				$obj_print->read();
				$str_print = $obj_print->getName();
			} else if ($str_was == 'sid') {
				$obj_print = new Tpepdb2_Serie();
				$obj_print->unbindModel('all');
				$obj_print->setId($such_id);
				$obj_print->read();
				$str_print = $obj_print->getName();
			}
			
			if (strlen($str_print) > 1) {
				$arr_row = array($row['file'], $str_print, $row['type'], $row['mode'] , $row['lang'] , $row['internal'], $row['date']);
				fputcsv($output, $arr_row , ';');
			}

			
		}
		//while ($row = mysql_fetch_assoc($results)) fputcsv($output, $row);
		
		
	}
	
}
?>
