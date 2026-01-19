<?php
/* SVN FILE: $Id$ */
/**
 * Contains the SitetemplateController class.
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
 * SitetemplateController class.
 *
 * @package netzcraftwerk
 */
class Wcms_SitetemplateController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Site templates :: Website";

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
		$this->Sitetemplate->unbindModel('all');
		$this->view->all_sitetemplates = $this->Sitetemplate->fetch("all");
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

        $text = new Ncw_Helpers_Text();

		$code = "";

		if (true === isset($this->data['Sitetemplate'])) {

			$this->Sitetemplate->data($this->data['Sitetemplate']);
			$code = $this->data['Sitetemplate']['code'];
			// set new filename
			$this->Sitetemplate->setFilename($text->cleanForUrl($this->Sitetemplate->getName()));
			$this->data['Sitetemplate']['code'] = "";
			if (true === $this->Sitetemplate->save()) {

				$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . "_tmp.phtml";
				$template_file_name = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . ".phtml";
				// Create the temporary template file
				$this->file->write($template_file_name_tmp, "w", $code);

                // write the siteareas
                $this->_writeSiteareas($this->Sitetemplate->getId(), $code);

                // tags replace
				$code_without_tags = $code;


                $this->_replacteSitetypeTags(
                    $this->Sitetemplate->getSitetypeId(),
                    $code_without_tags
                );

				// Create the real (with replaced tags) template file
				$this->file->write($template_file_name, "w", self::replaceTags($code_without_tags));

				$this->redirect(array("action" => "all"));
			}
		}
		$this->view->code = htmlspecialchars($code);

        // site type select
        $sitetype = new Wcms_Sitetype();
        $this->view->sitetypes = $sitetype->fetch(
            "list",
            array(
                "fields" => array(
                    "Sitetype.name", "Sitetype.id"
                )
            )
        );
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

		$this->Sitetemplate->setId($id);
		$this->Sitetemplate->unbindModel('all');
		$this->Sitetemplate->read();
		if (true === isset($this->data['Sitetemplate'])) {
			// get old filename form database
			$template_file_old_name_tmp = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . "_tmp.phtml";
			$template_file_old_name = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . ".phtml";
			// set data from the form
			$this->Sitetemplate->data($this->data['Sitetemplate']);
			// set new filename
			$this->Sitetemplate->setFilename(
                $text->cleanForUrl(
                    $this->Sitetemplate->getName()
                )
            );
			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . ".phtml";
			$code = $this->data['Sitetemplate']['code'];
			$this->data['Sitetemplate']['code'] = "";
			if (true === $this->Sitetemplate->save()) {
				// Create the temporary template file
				$this->file->write($template_file_old_name_tmp, "w", $code);

				// write the siteareas
                $this->_writeSiteareas($this->Sitetemplate->getId(), $code);

                // tags replace
                $code_without_tags = $code;

                $this->_replacteSitetypeTags(
                    $this->Sitetemplate->getSitetypeId(),
                    $code_without_tags
                );

				// Create the real (with replaced tags) template file
				$this->file->write(
                    $template_file_old_name,
                    "w",
                    self::replaceTags($code_without_tags)
                );

				// rename the old files
				rename($template_file_old_name_tmp, $template_file_name_tmp);
				rename($template_file_old_name, $template_file_name);
				$this->Sitetemplate->read();
			}
		} else {
			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . ".phtml";
			$code = $this->file->read($template_file_name_tmp);
			$this->data['Sitetemplate'] = $this->Sitetemplate->data();
		}
		$this->view->code = htmlspecialchars($code);
		$this->view->sitetemplate_id = $id;

        // site type select
        $sitetype = new Wcms_Sitetype();
        $this->view->sitetypes = $sitetype->fetch(
            "list",
            array(
                "fields" => array(
                    "Sitetype.name", "Sitetype.id"
                )
            )
        );
	}

	/**
	 * Write the siteareas
	 *
	 * @param int    $sitetemplate_id the template id
	 * @param string $code            the template code
	 *
	 * @return void
	 */
	protected function _writeSiteareas ($sitetemplate_id, $code)
	{
        $sitearea = new Wcms_Sitearea();
        $sitearea->deleteAll(
            array(
                'Sitearea.sitetemplate_id' => $sitetemplate_id
            )
        );
        $sitearea->setSitetemplateId($sitetemplate_id);

        $matches = array();
        preg_match_all(
            '/{(area|area\.begin|subarea\.define)\.([0-9]+)\.([A-Za-z-_0-9. ]+)}/',
            $code,
            $matches
        );
        $areas = array();
        $num = count($matches[3]);
        for ($count = 0; $count < $num; ++$count) {
            if (false === in_array($matches[3][$count], $areas)) {
                $sitearea->create();
                $sitearea->setArea($matches[2][$count]);
                $sitearea->setName($matches[3][$count]);
                if (true === $sitearea->save()) {
                    $areas[] = $matches[3];
                }
            }
        }
	}

	/**
	 * Replaces the site type tags
	 *
	 * @param int    $sitetype_id
	 * @param string $code_without_tags
	 *
	 * @return void
	 */
	protected function _replacteSitetypeTags ($sitetype_id, &$code_without_tags)
	{
        // replace sitetype tags
        $sitetype = new Wcms_Sitetype();
        $sitetype->setId($sitetype_id);
        $sitetype->read();
        $sitetype_site_class = ucfirst($sitetype->getSiteClass());
        if (false == empty($sitetype_site_class)) {
            $sitetype_site_class_full = 'Wcms_' . $sitetype_site_class . 'Controller';
            $sitetype_site_object = new $sitetype_site_class_full();
            $code_without_tags = $sitetype_site_object->replaceSiteTags($code_without_tags);
            unset($sitetype_site_object, $sitetype_site_object);
        }
        $sitetype_sitelanguage_class = ucfirst($sitetype->getSitelanguageClass());
        if (false == empty($sitetype_sitelanguage_class)) {
            $sitetype_sitelanguage_class_full = 'Wcms_' . $sitetype_sitelanguage_class . 'Controller';
            $sitetype_sitelanguage_object = new $sitetype_sitelanguage_class_full();
            $code_without_tags = $sitetype_sitelanguage_object->replaceSiteTags($code_without_tags);

            unset($sitetype_sitelanguage_class_full, $sitetype_sitelanguage_object);
        }
    }

	/**
	 * delete site action.
	 *
	 */
	public function deleteAction ($id)
	{
		$this->view = false;

		$this->Sitetemplate->bindModel(
		  array(
		      'has_many' => array(
		          'Sitearea'
		      )
		  )
		);
		$this->Sitetemplate->setId($id);
		if (true === $this->Sitetemplate->delete()) {
			$template_file_name_tmp = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . "_tmp.phtml";
			$template_file_name = ASSETS . DS . "wcms" . DS . "site_templates" . DS . $this->Sitetemplate->getFilename() . ".phtml";
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
	 * reads the sitetemplates which belongs to the
	 * given sitetype id
	 *
	 * @param int $sitetype_id the sitetype id
	 *
	 * @return void
	 */
	public function readAction ($sitetype_id)
	{
	    $this->view = false;

        $this->Sitetemplate->unbindModel('all');
        $sitetemplates = $this->Sitetemplate->fetch(
            'list',
            array(
                'fields' => array(
                    'Sitetemplate.id',
                    'Sitetemplate.name',
                ),
                'conditions' => array(
                    'Sitetemplate.sitetype_id' => $sitetype_id
                )
            )
        );
        print json_encode($sitetemplates);
	}

	/**
	 * Replace the tags in the code.
	 *
	 * @static
	 * @param string $code
	 * @param boolean $width_nav_template
	 *
	 * @return string the code with replaced tags.
	 */
	public static function replaceTags ($code, $width_nav_template = true)
	{
	    $base = Ncw_Configure::read('Project.url');
	    $theme = Ncw_Configure::read('App.theme');

		$tags = array(
    		'/{site\.id}/',
    		'/{sitelanguage\.id}/',
    		'/{site\.title}/',
            '/{site\.keywords}/',
            '/{site\.description}/',
            '/{site\.language.code}/',
            '/{site\.language.id}/',
            '/{site\.author}/',
            '/{site\.modified}/',
            '/{site\.created}/',
            '/{site\.copies}/',
            '/{if\.site\.copies}/',
            '/{admin\.css}/',
            '/{admin\.area}/',
            '/{css\.([A-Za-z-_0-9.]+)}/',
            '/{jquery}/',
            '/{javascript\.([A-Za-z-_0-9.]+)}/',
            '/{nav\.([A-Za-z-_0-9]+)}/',
            '/{area\.begin\.([0-9]+)\.([A-Za-z-_0-9. ]+)}/',
            '/{area\.contents\.([0-9]+)}/',
            '/{area\.content}/',
            '={/area\.contents}=',
            '={/area\.begin}=',
            '/{area\.([0-9]+)\.([A-Za-z-_0-9. ]+)}/',
            '/{subarea\.define\.([0-9]+)\.([A-Za-z-_0-9. ]+)}/',
            '/{area\.([0-9]+)\.noadmin}/',
            '/{area\.([0-9]+)\.id}/',
            '/{area\.admin\.class}/',
            '/{area\.admin\.nocontent\.clear}/',
            '/{project\.url}/',
            '/{site\.url}/',
            '/{if\.admin}/',
            '/{if\.!admin}/',
            '={/if}=',
            '/{news}/',
            '/{news\.head}/',
            '/{news\.body}/',
            '={/news}=',
            '/{if\.news}/',
            '/{contentbox\.([A-Za-z-_0-9.]+)}/',
            '/{languages}/',
            '/{language\.id}/',
            '/{language\.name}/',
            '/{language\.shortcut}/',
            '={/languages}=',
            '/{path\.files}/',
            '/{path\.images}/',
            '/{path\.css}/',
            '/{path\.javascript}/',
            '/{path\.base}/',
            '/{path\.site_url}/',
            '/{path\.site_file}/',
		    '/{if\.no\.admin}/',
		    '/{if\.area\.([0-9]+)}/',
		    '/{else}/',
		    '/{search}/',
		    '/{search.results}/',
		    '={/search.results}=',
		    '/{search.term}/',
		    '/{search.result.name}/',
		    '/{search.result.content}/',
		    '/{search.result.url}/',
		    '/{if.search}/',
		    '/{if.search.has_result}/',
		    '/{if.search.!has_result}/',
		    '/{login\.([[:print:]]+)}/',
		    '/{if.logged_in}/',
		    '/{if.!logged_in}/',
		    '/{if.login_error}/',
		    '/{logout}/',
		    '/{if.logout}/',
		    '/{user.id}/',
		    '/{user.name}/',
        );
		$replaced_tags_with = array(
            '<?php print $site[\'id\']; ?>',
            '<?php print $site[\'sitelanguage_id\']; ?>',
            '<?php print $site[\'title\']; ?>',
            '<?php print $site[\'keywords\']; ?>',
            '<?php print $site[\'description\']; ?>',
            '<?php print $site[\'language_code\']; ?>',
            '<?php print $site[\'language_id\']; ?>',
            '<?php print $site[\'author\']; ?>',
            '<?php print str_replace(\' \', \'T\', $site[\'modified\']); ?>',
            '<?php print str_replace(\' \', \'T\', $site[\'created\']); ?>',
            '<?php print $site[\'copies\']; ?>',
            '<?php if (true === $site_copies) { ?>',
            '<?php if (false !== $admin_code) { print $admin_css; } ?>',
            '<?php if (false !== $admin_code) { print $admin_code; } ?>',
            $base . '/' . ASSETS . '/wcms/css/$1.css',
            $base . '/' . THEMES . '/' . $theme . '/web/javascript/lib/jquery-1.4.2.min.js',
            $base . '/' . ASSETS . '/wcms/javascript/$1.js',
            '<?php include (ASSETS . DS . \'wcms\' . DS . \'nav_templates\' . DS . \'$1.phtml\'); ?>',
            '<?php if (true === $admin) { ?><div id="ncw-area-$1" class="ncw-sortable"><?php } ?>',
            '<?php if (true === isset($areas[$1])) { foreach ($areas[$1] as $area => $content) { ?>',
            '<?php print $content; ?>',
            '<?php } if (true === $admin) { ?><div class="ncw-area-clear"></div><?php } } ?>',
            '<?php if (true === $admin) { ?></div><?php } ?>',
            '<?php if (true === $admin) { ?><div id="ncw-area-$1" class="ncw-sortable"><?php } if (true === isset($areas[$1])) { foreach ($areas[$1] as $area =>  $content) { print $content; } } if (true === $admin) { ?><div class="ncw-area-clear"></div></div><?php } ?>',
            '',
            '<?php if (true === isset($areas[$1])) { foreach ($areas[$1] as $area =>  $content) { print $content; } } ?>',
            '<?php if (true === $admin) { ?>ncw-area-$1<?php } ?>',
            '<?php if (true === $admin) { ?>ncw-sortable<?php } ?>',
            '<?php if (true === $admin) { ?>ncw-area-nocontent-clear<?php } ?>',
            $base . '/',
            '<?php print $site_url; ?>',
            '<?php if (true === $admin) { ?>',
            '<?php if (true !== $admin) { ?>',
            '<?php } ?>',
            '<?php foreach ($news as $news_head => $news_body) { ?>',
            '<?php print $news_head; ?>',
            '<?php print $news_body; ?>',
            '<?php } ?>',
            '<?php if (count($news) > 0) { ?>',
            '<?php print Wcms_ContentboxController::getContenbox(\'$1\', $language_id); ?>',
            '<?php foreach ($languages as $language) { ?>',
            '<?php print $language[\'id\']; ?>',
            '<?php print $language[\'name\']; ?>',
            '<?php print $language[\'shortcut\']; ?>',
            '<?php } ?>',
            $base . '/' . ASSETS . '/wcms/files/',
            $base . '/' . ASSETS . '/wcms/images/',
            $base . '/' . ASSETS . '/wcms/css/',
            $base . '/' . ASSETS . '/wcms/javascript/',
            $base . '/',
            '<?php print $site_url; ?>',
            '<?php print $site_file; ?>',
            '<?php if (false === $admin) { ?>',
            '<?php if ($area === $1) { ?>',
            '<?php } else { ?>',
            '<?php $this->_search(); ?>',
            '<?php foreach ($this->_getSearchResult() as $search_result) { ?>',
            '<?php } ?>',
            '<?php print $this->_getSearchTerm(); ?>',
            '<?php print $search_result["name"]; ?>',
            '<?php print $search_result["content"]; ?>',
            '<?php print $search_result["url"]; ?>',
            '<?php if (true === $this->_isSearched()) { ?>',
            '<?php if (true === $this->_searchHasResult()) { ?>',
            '<?php if (false === $this->_searchHasResult()) { ?>',
            '<?php
            $login_error = false;
            if (true === isset($_POST["Login"]["user"], $_POST["Login"]["password"])) {
                try {
                    Ncw_Components_Session::regenerate();

                    $fields = array(
                        "name" => array("rules" => array("Username"), "required" => true),
                        "password" => array("rules" => array("Password"), "required" => true)
                    );

                    $validator = new Ncw_Validator($fields);
                    $data = array();
                    $data["name"] = $_POST["Login"]["user"];
                    $data["password"] = $_POST["Login"]["password"];
                    list($success, $invalid_fields) = $validator->validate($data);
                    if (true === $success) {
                        $obj_user = Core_UserController::validateLogin($data["name"], $data["password"]);
                        if (true === $obj_user instanceof Core_User) {
                            Core_UserController::login($obj_user);
                            if (true === Ncw_Configure::read("App.rewrite")) {
                                $url = $this->base . "/" . $language_code . "$1";
                            } else {
                                $url = $this->base . "/index.php?url=/" . $language_code . "$1";
                            }
                            header("Location: " . $url);
                            exit();
                        }
                    } else {
                        $login_error = true;
                    }
                } catch (Exception $e) {
                    print $e->getMessage();
                }
            }
            ?>',
            '<?php if (1 === Core_UserController::checkUserLogin()) { ?>',
            '<?php if (1 !== Core_UserController::checkUserLogin()) { ?>',
            '<?php if (true === $login_error) { ?>',
            '<?php Core_UserController::logout(); ?>',
            '<?php if (true === isset($_GET["logout"]) && $_GET["logout"] == 1) { ?>',
            '<?php print $this->_current_user["id"]; ?>',
            '<?php print $this->_current_user["name"]; ?>',
		);
		$code = preg_replace($tags, $replaced_tags_with, $code);
		if (true === $width_nav_template) {
            $code = Wcms_NavtemplateController::replaceTags($code, 0, false);
		}
		return $code;
	}
}
?>
