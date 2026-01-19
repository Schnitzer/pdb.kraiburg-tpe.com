<?php
/* SVN FILE: $Id$ */
/**
 * Contains the NavtemplateControlle class.
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
 * NavtemplateControlle class.
 *
 * @package netzcraftwerk
 */
class Wcms_NavtemplateController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Navigation templates :: Website";

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
	    $this->Navtemplate->unbindModel('all');
		$this->view->all_navtemplates = $this->Navtemplate->fetch("all");
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
		if (true === isset($this->data['Navtemplate'])) {

		    $text = new Ncw_Helpers_Text();

			$this->Navtemplate->data($this->data['Navtemplate']);
			$code = $this->data['Navtemplate']['code'];
			$this->data['Navtemplate']['code'] = "";
			// set new filename
			$this->Navtemplate->setFilename($text->cleanForUrl($this->Navtemplate->getName()));
			if (true === $this->Navtemplate->save()) {
				// Create the template file
				$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $this->Navtemplate->getFilename() . "_tmp.phtml";
				$template_file_name = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $this->Navtemplate->getFilename() . ".phtml";
				// Create the temporary template file
				$this->file->write($template_file_name_tmp, "w", $code);
				// Create the real (with replaced tags) template file
				$this->file->write($template_file_name, "w", self::replaceTags($code, $this->Navtemplate->getId()));
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

		$this->Navtemplate->setId($id);
		$this->Navtemplate->unbindModel('all');
		$this->Navtemplate->read();

		$text = new Ncw_Helpers_Text();

		if (true === isset($this->data['Navtemplate'])) {
		    if (false === isset($this->data['Navtemplate']['in_list'])) {
		        $this->data['Navtemplate']['in_list'] = 0;
		    }

			// get old filename
			$template_file_old_name_tmp = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $this->Navtemplate->getFilename() . "_tmp.phtml";
			$template_file_old_name = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $this->Navtemplate->getFilename() . ".phtml";
			// set data from the form
			$this->Navtemplate->data($this->data['Navtemplate']);
			// set new filename
			$this->Navtemplate->setFilename($text->cleanForUrl($this->Navtemplate->getName()));
			$code = $this->data['Navtemplate']['code'];
			$this->data['Navtemplate']['code'] = "";

			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $this->Navtemplate->getFilename() . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $this->Navtemplate->getFilename() . ".phtml";

			if (true === $this->Navtemplate->save()) {
				// Create the temporary template file
				$this->file->write($template_file_old_name_tmp, "w", $code);
				// Create the real (with replaced tags) template file
				$this->file->write($template_file_old_name, "w", self::replaceTags($code, $this->Navtemplate->getId()));
				// rename the files
				rename($template_file_old_name_tmp, $template_file_name_tmp);
				rename($template_file_old_name, $template_file_name);
				$this->Navtemplate->read();
			}
		} else {
			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $this->Navtemplate->getFilename() . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $this->Navtemplate->getFilename() . ".phtml";
			$code = $this->file->read($template_file_name_tmp);
			$this->data['Navtemplate'] = $this->Navtemplate->data();
		}
		$this->view->code = $code;
		$this->view->navtemplate_id = $id;
	}

	/**
	 * delete site action.
	 *
	 */
	public function deleteAction ($id)
	{
		$this->view=false;

		$this->Navtemplate->setId($id);
		$this->Navtemplate->unbindModel('all');
		$this->Navtemplate->read();
		$filename = $this->Navtemplate->getFilename();
		if (true === $this->Navtemplate->delete()) {
			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $filename . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "nav_templates" . DS . $filename . ".phtml";
			$this->file->delete($template_file_name_tmp);
			$this->file->delete($template_file_name);
		}

        $this->redirect(
            array(
                'action' => 'all'
            )
        );
	}

	/**
	 * Replace the tags in the code.
	 *
	 * @static
	 * @param string $code
	 * @param int $id
	 * @param boolean $width_site_template
	 *
	 * @return string the code with replaced tags.
	 */
	public static function replaceTags ($code, $id = 0, $width_site_template = true)
	{
		$tags = array(
            '/{site\.title}/',
            '/{site\.name}/',
            '/{site\.id}/',
            '/{site\.url}/',
            '/{site\.highlight}/',
            '/{site\.sitetype.site.([0-9a-zA-z]+)}/',
            '/{site\.sitetype.sitelanguage.([0-9a-zA-z]+)}/',
            '/{if\.site\.highlight}/',
            '={/if}=',
            '/{else}/',
            '/{sites\.1}/',
            '/{sites\.([0-9]+)}/',
            '={/sites}=',
            '/{if\.sites\.1}/',
            '/{if\.sites\.([0-9]+)}/',
            '/{tree\.1}/',
            '/{tree\.([0-9]+)}/',
            '/{tree\.all\.1}/',
            '/{tree\.all\.([0-9]+)}/',
		    '={/tree}=',
            '/{if\.tree\.1}/',
            '/{if\.tree\.([0-9]+)}/',
            '/{if\.tree\.all\.1}/',
            '/{if\.tree\.all\.([0-9]+)}/',
			'/{breadcrumb}/',
			'={/breadcrumb}=',
		);
		$replaced_tags_with = array(
            '<?php print $node[\'title\']; ?>',
            '<?php print $node[\'name\']; ?>',
            '<?php print $node[\'id\']; ?>',
            '<?php print $node[\'url\']; ?>',
            '<?php print $node[\'highlight\']; ?>',
            '<?php print $node[\'sitetype\'][\'site\'][$1]; ?>',
            '<?php if (true === isset($node[\'sitetype\'][\'sitelanguage\'][\'$1\'])) { print $node[\'sitetype\'][\'sitelanguage\'][\'$1\']; } ?>',
            '<?php if (true === $node[\'highlight\']) { ?>',
            '<?php } ?>',
            '<?php } else { ?>',
            '<?php '
            . 'if (true === isset($sites[1], $sites[1][1], $sites[1][1][' . $id . '])) { '
            . '$node_save = false; '
            . 'if (true === isset($node)) { '
            . '$node_save = $node; '
            . '} '
            . 'foreach ($sites[1][1][' . $id . '] as $node) { '
            . '?> ',
            '<?php '
            . 'if (true === isset($sites[$1], $breadcrumb[$1-2][\'id\'], $sites[$1][$breadcrumb[$1-2][\'id\']], $sites[$1][$breadcrumb[$1-2][\'id\']][' . $id . '])) { '
            . '$node_save = false; '
            . 'if (true === isset($node)) { '
            . '$node_save = $node; '
            . '} '
            . 'foreach ($sites[$1][$breadcrumb[$1-2][\'id\']][' . $id . '] as $node) { '
            . '?>',
            '<?php '
            . '} '
            . 'if (true === isset($node_save) && false !== $node_save) {'
            . ' $node = $node_save;'
            . '} '
            . '} ?>',
            '<?php if (true === isset($sites[1], $sites[1][1], $sites[1][1][' . $id . '])) { ?>',
            '<?php if (true === isset($sites[$1], $breadcrumb[$1-2][\'id\'], $sites[$1][$breadcrumb[$1-2][\'id\']], $sites[$1][$breadcrumb[$1-2][\'id\']][' . $id . '])) { ?>',
            '<?php '
            . 'if (true === isset($sites[1], $sites[1][1], $sites[1][1][' . $id . '])) { '
            . '$node_save = false; '
            . 'if (true === isset($node)) { '
            . '$node_save = $node; '
            . '} '
            . 'foreach ($sites[1][1][' . $id . '] as $node) { '
            . '?> ',
            '<?php '
            . 'if (true === isset($node[\'id\'], $sites[$1][$node[\'id\']][' . $id . '])) { '
            . '$node_save = false; '
            . 'if (true === isset($node)) { '
            . '$node_save = $node; '
            . '} '
            . 'foreach ($sites[$1][$node[\'id\']][' . $id . '] as $node) { '
            . '?> ',
            '<?php '
            . 'if (true === isset($sites[1], $sites[1][1], $sites[1][1][0])) { '
            . '$node_save = false; '
            . 'if (true === isset($node)) { '
            . '$node_save = $node; '
            . '} '
            . 'foreach ($sites[1][1][0] as $node) { '
            . '?> ',
            '<?php '
            . 'if (true === isset($sites[$1], $sites[$1][$node[\'id\']], $sites[$1][$node[\'id\']][0])) { '
            . '$node_save = false; '
            . 'if (true === isset($node)) { '
            . '$node_save = $node; '
            . '} '
            . 'foreach ($sites[$1][$node[\'id\']][0] as $node) { '
            . '?> ',
            '<?php '
            . '} '
            . 'if (true === isset($node_save) && false !== $node_save) {'
            . ' $node = $node_save;'
            . '} '
            . '} ?>',
            '<?php if (true === isset($sites[1], $sites[1][1], $sites[1][1][' . $id . '])) { ?>',
            '<?php if (true === isset($sites[$1], $node[\'id\'], $sites[$1][$node[\'id\']], $sites[$1][$node[\'id\']][' . $id . '])) { ?>',
            '<?php if (true === isset($sites[1], $sites[1][1], $sites[1][1][0])) { ?>',
            '<?php if (true === isset($sites[$1], $sites[$1][$node[\'id\']], $sites[$1][$node[\'id\']][0])) { ?>',
            '<?php foreach ($breadcrumb as $node) { ?>',
            '<?php } ?>',
        );
		$code = preg_replace($tags, $replaced_tags_with, $code);
		if (true === $width_site_template) {
		  $code = Wcms_SitetemplateController::replaceTags($code, false);
		}
		return $code;
	}
}
?>
