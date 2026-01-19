<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ComponenttemplateController class.
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
 * ComponenttemplateController class.
 *
 * @package netzcraftwerk
 */
class Wcms_ComponenttemplateController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Component templates :: Website";

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
		$all_componenttemplates = $this->Componenttemplate->fetch("all", array('order' => 'name'));
		$this->view->all_componenttemplates = $all_componenttemplates;
	}

	/**
	 * New site action.
	 *
	 */
	public function newAction ()
	{
        $text = new Ncw_Helpers_Text();

		$code = "";
		if (true === isset($this->data['Componenttemplate'])) {
			$code = $this->data['Componenttemplate']['code'];
			list($replaced_code, $this->data['Componenttemplate']['text'], $this->data['Componenttemplate']['shorttext'], $this->data['Componenttemplate']['file']) = self::replaceTags($code);
			$this->Componenttemplate->data($this->data['Componenttemplate']);
			$filename = str_replace(array('/', '|'), '-', $this->Componenttemplate->getName());
			$this->Componenttemplate->setFilename($text->cleanForUrl($filename));

			$this->data['Componenttemplate']['code'] = '';
			if (true === $this->Componenttemplate->save()) {
				$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . "_tmp.phtml";
				$template_file_name = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . ".phtml";
				// Create the temporary template file
				$this->file->write($template_file_name_tmp, "w", $code);
				// Create the real (with replaced tags) template file
				$this->file->write($template_file_name, "w", $replaced_code);

				$this->redirect(array("action" => "all"));
			}
		}
		$this->view->code = htmlspecialchars($code);

        $this->registerJs(
            array(
                'lib/codemirror/js/codemirror',
                'ncw.wcms',
                'ncw.wcms.extras',
            )
        );
        $this->registerCss('wcms');
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

        $text = new Ncw_Helpers_Text();

		$this->Componenttemplate->setId($id);
		$this->Componenttemplate->read();
		if (true === isset($this->data['Componenttemplate'])) {
		    if (false === isset($this->data['Componenttemplate']['in_list'])) {
                $this->data['Componenttemplate']['in_list'] = 0;
            }

			// get old filename
			$template_file_old_name_tmp = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . "_tmp.phtml";
			$template_file_old_name = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . ".phtml";
			$code = $this->data['Componenttemplate']['code'];
			list($replaced_code, $this->data['Componenttemplate']['text'], $this->data['Componenttemplate']['shorttext'], $this->data['Componenttemplate']['file']) = self::replaceTags($code);

			$this->Componenttemplate->data($this->data['Componenttemplate']);
			$filename = str_replace(array('/', '|'), '-', $this->Componenttemplate->getName());
			$this->Componenttemplate->setFilename($text->cleanForUrl($filename));
			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . ".phtml";

			$this->data['Componenttemplate']['code'] = '';

			if (true === $this->Componenttemplate->save()) {
				// Create the temporary template file
				$this->file->write($template_file_old_name_tmp, "w", $code);
				// Create the real (with replaced tags) template file
				$this->file->write($template_file_old_name, "w", $replaced_code);

				// rename the files
				rename($template_file_old_name_tmp, $template_file_name_tmp);
				rename($template_file_old_name, $template_file_name);
				$this->Componenttemplate->read();
			}
		} else {
			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . ".phtml";
			$code = $this->file->read($template_file_name_tmp);
			$this->data['Componenttemplate'] = $this->Componenttemplate->data();
		}
		$code = htmlspecialchars($code);

		//$code = str_replace('textarea' , 'ncwbigtext', $code);
		$this->view->code = $code;
		$this->view->componenttemplate_id = $id;
	}

	/**
	 * delete site action.
	 *
	 */
	public function deleteAction ($id)
	{
		$this->view=false;

		$this->Componenttemplate->setId($id);
		$this->Componenttemplate->read();
		$filename = $this->Componenttemplate->getFilename();
		if (true === $this->Componenttemplate->delete()) {
			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $filename . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $filename . ".phtml";
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
	 * Template generator
	 *
	 * @return void
	 */
	public function generatorAction ()
	{
	    $text = new Ncw_Helpers_Text();

        if (true === isset($this->data['Componenttemplate'])) {
            $code = $this->data['Componenttemplate']['code'];
            list($replaced_code, $this->data['Componenttemplate']['text'], $this->data['Componenttemplate']['shorttext'], $this->data['Componenttemplate']['file']) = self::replaceTags($code);
            $this->Componenttemplate->data($this->data['Componenttemplate']);
            $this->Componenttemplate->setFilename($text->cleanForUrl($this->Componenttemplate->getName()));

            $this->data['Componenttemplate']['code'] = '';
            if (true === $this->Componenttemplate->save()) {
                $template_file_name_tmp = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . "_tmp.phtml";
                $template_file_name = ASSETS . DS . "wcms" . DS . "component_templates" . DS . $this->Componenttemplate->getFilename() . ".phtml";
                // Create the temporary template file
                $this->file->write($template_file_name_tmp, "w", $code);
                // Create the real (with replaced tags) template file
                $this->file->write($template_file_name, "w", $replaced_code);
                $this->redirect(array("action" => "all"));
            }
        }

        $this->registerJs(
            array(
                'ncw.wcms',
                'ncw.wcms.tinymce.gzip',
                'ncw.wcms.tinymce',
                'ncw.wcms.extras',
            )
        );
		
		$this->view->jsload_tiny_mce = true;
	}

	/**
	 * Replace the tags in the code.
	 *
	 * @static
	 * @param string $code
	 *
	 * @return string the code with replaced tags.
	 */
	public static function replaceTags ($code)
	{
		$matches = array();
		preg_match_all('/{text\.([0-9]+)}/', $code, $matches);
		$num_text = count(array_unique($matches[0]));
		preg_match_all('/{shorttext\.([0-9]+)}/', $code, $matches);
		$num_shorttext = count(array_unique($matches[0]));
		preg_match_all('/{file\.([0-9]+)}/', $code, $matches);
		$num_file = count(array_unique($matches[0]));

		$tags = array('/{subarea\.([0-9]+)}/',
					  '/{subarea\.([0-9]+)\.id}/',
					  '/{subarea\.([0-9]+)\.noadmin}/',
					  '/{subarea\.admin\.class.sub}/',
					  '/{([a-z]+)\.([0-9]+)}/',
					  '/{file\.([0-9]+)\.alt}/',
					  '/{file\.([0-9]+)\.title}/',
					  '/{file\.([0-9]+)\.link}/',
					  '/{file\.([0-9]+)\.target}/',
					  '/{([a-z]+)\.([0-9]+)\.id}/',
					  '/{([a-z]+)\.([0-9]+)\.label=(.+)}/',
					  '/{([a-z]+)\.([0-9]+)\.description=(.+)}/',
					  '/{([a-z]+)\.([0-9]+)\.width=([0-9]*)}/',
					  '/{([a-z]+)\.([0-9]+)\.height=([0-9]*)}/',
					  '/{([a-z]+)\.([0-9]+)\.background=([[:print:]]*)}/',
					  '/{([a-z]+)\.([0-9]+)\.mode=([[:print:]]*)}/',
					  '/{([a-z]+)\.([0-9]+)\.button-top=([-0-9]*)}/',
					  '/{([a-z]+)\.([0-9]+)\.button-left=([-0-9]*)}/',
					  '/{([a-z]+)\.([0-9]+)\.text-top=([-0-9]*)}/',
					  '/{([a-z]+)\.([0-9]+)\.text-left=([-0-9]*)}/',
		              '/{([a-z]+)\.([0-9]+)\.remove-tags=([[:print:]]*)}/',
					  '/{component\.id}/',
					  '/{component\.move-top=([-0-9]*)}/',
					  '/{component\.move-left=([-0-9]*)}/',
					  '/{admin\.class}/',
					  '/{admin\.class\.sub}/',
					  '/{admin\.content\.class}/',
					  '/{admin\.image\.class}/',
					  '/{admin\.file\.class}/',
					  '/{admin\.folder\.class}/',
					  );
		$replaced_tags_with = array(
    		'<?php if (true === $admin) { ?><div id="ncw-area-$1-<?php print $component[\'id\'] ?>" class="ncw-sortable-sub"><?php } if (true === isset($areas[$1 . "-" . $component[\'id\']])) { $component_save = $component; $area_save = $area; $area = $1; foreach ($areas[$1 . "-" . $component[\'id\']] as $position => $component) { include (ASSETS . DS . \'wcms\' . DS . \'component_templates\' . DS . $component[\'template_id\'] . \'.phtml\'); } $area = $area_save; $component = $component_save; } if (true === $admin) { ?><div class="ncw-area-clear"></div></div><?php } ?>',
    		'ncw-area-$1-<?php print $component[\'id\'] ?>', // {subarea\.([0-9]+)\.id}
    		'<?php if (true === isset($areas[$1 . "-" . $component[\'id\']])) { $component_save = $component; $area_save = $area; $area = $1; foreach ($areas[$1 . "-" . $component[\'id\']] as $position => $component) { include (ASSETS . DS . \'wcms\' . DS . \'component_templates\' . DS . $component[\'template_id\'] . \'.phtml\'); } $area = $area_save; $component = $component_save; } ?>',
    		'<?php if (true === $admin) { ?>ncw-sortable-sub<?php } ?>',
    		'<?php print $component[\'$1\'][$2][\'content\']; ?>',
    		'<?php print $component[\'file\'][$1][\'alt\']; ?>',
    		'<?php print $component[\'file\'][$1][\'title\']; ?>',
    		'<?php print $component[\'file\'][$1][\'link\']; ?>',
    		'<?php print $component[\'file\'][$1][\'target\']; ?>',
    		'ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2',
            '',
            '',
    		'<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-width" value="$3" /><?php } ?>',
    		'<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-height" value="$3" /><?php } ?>',
    		'<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-background" value="$3" /><?php } ?>',
    		'<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-mode" value="$3" /><?php } ?>',
    		'<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-button-top" value="$3" /><?php } ?>',
    		'<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-button-left" value="$3" /><?php } ?>',
    		'<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-text-top" value="$3" /><?php } ?>',
    		'<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-text-left" value="$3" /><?php } ?>',
		    '<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-<?php print $component[\'componentlanguage_id\']; ?>-<?php print $component[\'$1\'][$2][\'componentcontent_id\']; ?>-$1-$2-remove-tags" value="$3" /><?php } ?>',
    		'ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>',
		    '<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-move-top" value="$1" /><?php } ?>',
		    '<?php if (true === $admin) { ?><input type="hidden" id="ncw-<?php print $area; ?>-component-<?php print $component[\'id\']; ?>-move-left" value="$1" /><?php } ?>',
    		'<?php if (true === $admin) { ?>ncw-sortable-item<?php } ?>',
    		'<?php if (true === $admin) { ?>ncw-sortable-sub-item<?php } ?>',
    		'<?php if (true === $admin) { ?>ncw-wysiwyg-website-admin<?php } ?>',
    		'<?php if (true === $admin) { ?>ncw-image-droppable<?php } ?>',
    		'<?php if (true === $admin) { ?>ncw-image-droppable<?php } ?>',
    		'<?php if (true === $admin) { ?>ncw-wcms-folder-choose<?php } ?>',
		);
		$code = preg_replace($tags, $replaced_tags_with, $code);
		$code = Wcms_SitetemplateController::replaceTags($code, false);
		$code = Wcms_NavtemplateController::replaceTags($code, 0, false);
		return array($code, $num_text, $num_shorttext, $num_file);
	}
}
?>
