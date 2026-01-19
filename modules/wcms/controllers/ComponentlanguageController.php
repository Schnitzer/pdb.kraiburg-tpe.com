<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ComponentlanguageController class.
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
 * ComponentlanguageController class.
 *
 * @package netzcraftwerk
 */
class Wcms_ComponentlanguageController extends Wcms_ModuleController
{

    /**
     * Components...
     *
     * @var array
     */
    public $components = array('Acl', 'Session');

	/**
	 * Layout
	 *
	 * @var string
	 */
	public $layout = 'blank';

	/**
	 * New sitelanguage action.
	 *
	 * @param int   $component_id  the component it
	 * @param mixed $language_code the language code
	 *
	 * @return void
	 */
	public function newAction ($component_id, $language_code = false)
	{
		$this->view->component_id = $component_id;

		$this->Componentlanguage->unbindModel('all');
		$this->Componentlanguage->bindModel(
            array(
                'belongs_to' => array(
                    'Language'
                )
            )
		);
		$languages = $this->Componentlanguage->fetch(
            'list',
            array(
                'fields' => array(
                    'Language.id'
                ),
                'conditions' => array(
                    'Componentlanguage.component_id' => $component_id
                )
            )
		);
		$language_condition = array();
		foreach ($languages as $language_id) {
		    $language_condition[] = 'Language.id !=' . $language_id;
		}
		$language_condition = implode(' && ', $language_condition);

        $language = new Wcms_Language();
        $language->unbindModel('all');
        $languages = $language->fetch(
            "all",
            array(
                "fields" => array(
                    "Language.name",
                    "Language.id",
                    'Language.shortcut'
                ),
                'conditions' => array(
                    $language_condition
                )
            )
        );
        $list = array();
        foreach ($languages as $language) {
            if (true === $this->checkLanguageAccess($language->getId(), false)) {
                $list[$language->getName()] = $language->getId();
                if ($language_code === $language->getShortcut()) {
                    $this->data['Componentlanguage']['language_id'] = $language->getId();
                }
            }
        }
        $this->view->arr_languages = $list;
	}

	/**
	 * save new componentlanguage action
	 *
	 * @return void
	 */
	public function saveAction ()
	{
	    $this->view = false;

	    if (true === isset($this->data['Componentlanguage'])) {
            $this->Componentlanguage->data($this->data['Componentlanguage']);
            $access = $this->checkLanguageAccess(
                $this->Componentlanguage->getLanguageId(),
                false
            );
            if (true === $access && true === $this->Componentlanguage->save()) {
                if ($this->Componentlanguage->getId() > 0) {
                    $this->Componentlanguage->Component = new Wcms_Component();
                    $this->Componentlanguage->Component->setId($this->Componentlanguage->getComponentId());
                    $this->Componentlanguage->Component->read();
                    // Add the text, shortext and file contents
                    $template = new Wcms_Componenttemplate();
                    $template->setId($this->Componentlanguage->Component->getComponenttemplateId());
                    $template->read();
                    // The text fields
                    $num = $template->getText();
                    for ($count = 1; $count <= $num; ++$count) {
                        $text = new Wcms_Componenttext();
                        $text->data(array("componentlanguage_id" => $this->Componentlanguage->getId(), "position" => $count));
                        $text->save();
                    }
                    // The shorttext fields
                    $num = $template->getShorttext();
                    for ($count = 1; $count <= $num; ++$count) {
                        $shorttext = new Wcms_Componentshorttext();
                        $shorttext->data(array("componentlanguage_id" => $this->Componentlanguage->getId(), "position" => $count));
                        $shorttext->save();
                    }
                    // The file fields
                    $num = $template->getFile();
                    for ($count = 1; $count <= $num; ++$count) {
                        $file = new Wcms_Componentfile();
                        $file->data(array("componentlanguage_id" => $this->Componentlanguage->getId(), "position" => $count));
                        $file->save();
                    }
                }
                print '{"return_value" : true}';
            } else {
                print '{"return_value" : false}';
            }
        } else {
            print '{"return_value" : false}';
        }
	}

     /**
     * Nach der neuanlage einer Sprache müssen die Seiteninhalte kopiert werden
     *
     */
    public function copyAllAction ()
    {
        $this->view = false;
        $master_language = 1;
        $new_language_id = 10;
        echo 'Components kopieren';
        $obj_component = new Wcms_Component();
        
        $obj_component->unbindModel('all');
        $arr_components = $obj_component->fetch('all');
        //var_dump($arr_components);
        foreach ($arr_components As $component) {
            echo '<br />' . $component->getId();
            
            // original Componentlangugage lesen
            $obj_componentangugage = new Wcms_Componentlanguage();
            
            $arr_componentlanguage = $obj_componentangugage->fetch('all', array('conditions' => array('component_id' => $component->getId(), 'language_id' => $master_language)));
            
            if (count($arr_componentlanguage) > 0) {
                $old_componentlangugage_id = $arr_componentlanguage[0]->getId();
                // Prüfen ob die Übersetzung schon vorhanden ist
                $obj_componentlangugage_controle = new Wcms_Componentlanguage();
                $arr_componentlanguage_controle = $obj_componentlangugage_controle->fetch('all', array('conditions' => array('component_id' => $component->getId(), 'language_id' => $new_language_id)));
 
                if (count($arr_componentlanguage_controle) < 1) {
                    // Duplikat anlegen 
                    $obj_componentlangugage_new = new Wcms_Componentlanguage();
                    $obj_componentlangugage_new->setLanguageId($new_language_id);
                    $obj_componentlangugage_new->setComponentId($component->getId());
                    $obj_componentlangugage_new->setCreated(date('Y-m-d H:i:s'));
                    $obj_componentlangugage_new->create();
                    $obj_componentlangugage_new->save();
                    $new_componentlangugae_id = $obj_componentlangugage_new->getId();
                    echo ' - angelegt';
                } else {
                    $new_componentlangugae_id = $arr_componentlanguage_controle[0]->getId();
                    echo ' - schon vorhanden';
                }
                echo ' OCLID=' . $old_componentlangugage_id;
                echo ' NCLID=' . $new_componentlangugae_id;
                // Anlegen der Textfelder Shorttexts
                /*
                $obj_old_shorttext = new Wcms_Componentshorttext();
                $obj_old_shorttext->unbindModel('all');
                // Prüfen ob der Baustein einen Shorttext hat
                $arr_old_shorttext = $obj_old_shorttext->fetch('all', array('conditions' => array('componentlanguage_id' => $old_componentlangugage_id)));
                if (count($arr_old_shorttext) > 0) {
                    foreach ($arr_old_shorttext As $old_shorttext) {
                        echo ' shorttext = true';
                        $obj_new_shorttextcontrole = new Wcms_Componentshorttext();
                        $obj_new_shorttextcontrole->unbindModel('all');
                        // Prüfen ob der Baustein disen einen Shorttext hat
                        $arr_new_shorttext = $obj_new_shorttextcontrole->fetch('all', array('conditions' => array('componentlanguage_id' => $new_componentlangugae_id, 'position' => $old_shorttext->getPosition())));
                        // Prüfen ob der Shorttext schon angelegt wurde, wenn nicht soll das gemacht werden
                        var_dump($arr_new_shorttext);
                        if (count($arr_new_shorttext) < 1) {
                            $obj_new_shorttext = new Wcms_Componentshorttext();
                            //$obj_new_shorttext->unbindModel('all');
                            $obj_new_shorttext->setComponentlanguageId($new_componentlangugae_id);
                            $obj_new_shorttext->setPosition($old_shorttext->getPosition());
                            $obj_new_shorttext->create();
                            $obj_new_shorttext->save();
                            echo ' - angelegt';
                        } else {
                            echo ' - schon vorhanden';
                        }
                    }
                }
                */
                // Anlegen der Textfelder Text
                /*
                $obj_old_text = new Wcms_Componenttext();
                $obj_old_text->unbindModel('all');
                // Prüfen ob der Baustein einen Shorttext hat
                $arr_old_text = $obj_old_text->fetch('all', array('conditions' => array('componentlanguage_id' => $old_componentlangugage_id)));
                if (count($arr_old_text) > 0) {
                    foreach ($arr_old_text As $old_text) {
                        echo ' text = true';
                        $obj_new_textcontrole = new Wcms_Componenttext();
                        $obj_new_textcontrole->unbindModel('all');
                        // Prüfen ob der Baustein disen einen Shorttext hat
                        $arr_new_text = $obj_new_textcontrole->fetch('all', array('conditions' => array('componentlanguage_id' => $new_componentlangugae_id, 'position' => $old_text->getPosition())));
                        // Prüfen ob der Shorttext schon angelegt wurde, wenn nicht soll das gemacht werden
                        var_dump($arr_new_text);
                        if (count($arr_new_text) < 1) {
                            $obj_new_text = new Wcms_Componenttext();
                            //$obj_new_shorttext->unbindModel('all');
                            $obj_new_text->setComponentlanguageId($new_componentlangugae_id);
                            $obj_new_text->setPosition($old_text->getPosition());
                            $obj_new_text->create();
                            $obj_new_text->save();
                            echo ' - angelegt';
                        } else {
                            echo ' - schon vorhanden';
                        }
                    }
                }*/

                // Anlegen der Bilder
                $obj_old_file = new Wcms_Componentfile();
                $obj_old_file->unbindModel('all');
                // Prüfen ob der Baustein einen Shorttext hat
                $arr_old_file = $obj_old_file->fetch('all', array('conditions' => array('componentlanguage_id' => $old_componentlangugage_id)));
                if (count($arr_old_file) > 0) {
                    foreach ($arr_old_file As $old_file) {
                        echo ' text = true';
                        $obj_new_filecontrole = new Wcms_Componentfile();
                        $obj_new_filecontrole->unbindModel('all');
                        // Prüfen ob der Baustein disen einen Shorttext hat
                        $arr_new_file = $obj_new_filecontrole->fetch('all', array('conditions' => array('componentlanguage_id' => $new_componentlangugae_id, 'position' => $old_file->getPosition())));
                        // Prüfen ob der Shorttext schon angelegt wurde, wenn nicht soll das gemacht werden
                        var_dump($arr_new_file);
                        if (count($arr_new_file) < 1) {
                            $obj_new_file = new Wcms_Componentfile();
                            //$obj_new_shorttext->unbindModel('all');
                            $obj_new_file->setComponentlanguageId($new_componentlangugae_id);
                            $obj_new_file->setPosition($old_file->getPosition());
                            $obj_new_file->create();
                            $obj_new_file->save();
                            echo ' - angelegt';
                        } else {
                            echo ' - schon vorhanden';
                        }
                    }
                }
            }

        }
    }



	/**
	 * Edit sitelanguage action.
	 *
	 * @param int     $id          the componentlanguage id
	 * @param boolean $window_mode set to true if window mode
	 *
	 * @return void
	 */
	public function editAction ($id, $window_mode = false)
	{
		$this->Componentlanguage->setId($id);
		$this->checkLanguageAccess(
            $this->Componentlanguage->readField('language_id')
        );

		$this->Componentlanguage->unbindModel(
            array(
                'has_many' => array('Componentfile'),
            )
        );
		$this->Componentlanguage->read();

		// component files
		$this->loadModel('Componentfile');
		$componentfiles = $this->Componentfile->fetch(
            'all',
            array(
                'conditions' => array(
                    'Componentfile.componentlanguage_id' => $id
                )
            )
        );

        // component template
        $this->loadModel('Componenttemplate');
        $this->Componenttemplate->setId($this->Componentlanguage->Component->getComponenttemplateId());
        $filename = $this->Componenttemplate->readField('filename');
        $component_template = file_get_contents(ASSETS . DS . 'wcms' . DS . 'component_templates' . DS . $filename . '_tmp.phtml');
        $component_template = str_replace(
            '}',
            "}\n",
            Ncw_Library_Sanitizer::removeWhitespace($component_template)
        );

        $matches = array();
        preg_match_all('/{(text|shorttext|file)\.([0-9])\.(label|description)=(.*)}/', $component_template, $matches);
        $names = array(
            'text' => array('label' => array(), 'description' => array()),
            'shorttext' => array('label' => array(), 'description' => array()),
            'file' => array('label' => array(), 'description' => array()),
        );
        foreach ($matches[4] as $key => $name) {
            $type = $matches[1][$key];
            $count = $matches[2][$key];
            $value = $matches[3][$key];
            $names[$type][$value][] = $name;
        }
        $this->view->names = $names;

        // vars
        $linklist = array();
		$this->siteLinklist(
            $linklist,
            $this->Componentlanguage->Language->getShortcut(),
            false
        );
        $this->view->linklist = $linklist;

		$this->data['Componentlanguage'] = $this->Componentlanguage->data();
		$this->view->componentlanguage_id = $id;

		$this->view->component_id = $this->data['Componentlanguage']['component_id'];
		$this->view->language_code = $this->Componentlanguage->Language->getShortcut();

		$this->view->componenttexts = $this->Componentlanguage->Componenttext;
		$this->view->componentshorttexts = $this->Componentlanguage->Componentshorttext;
		$this->view->componentfiles = $componentfiles;

        $this->view->permissions = array(
            '/wcms/componentlanguage/delete' => $this->acl->check('/wcms/componentlanguage/delete'),
            '/wcms/componentlanguage/new' => $this->acl->check('/wcms/componentlanguage/new'),
        );

        $this->view->window_mode = (boolean) $window_mode;

        $this->view->targets = array(
          T_('Open in this window / frame') => '_self',
          T_('Open in new window (_blank)') => '_blank',
          T_('Open in new parent window / frame (_parent)') => '_parent',
          T_('Open in top frame (replaces all frames) (_top)') => '_top',
       );
	}

	/**
	 * update action
	 *
	 */
	public function updateAction ()
	{
        $this->view = false;

        if (true === isset($this->data['Componentlanguage'])) {
            if (true === isset($this->data['Componenttext'])) {
                $componenttext = new Wcms_Componenttext();
                foreach ($this->data['Componenttext'] as $text) {
                    $componenttext->create();
                    $componenttext->data($text);
                    $componenttext->saveFields(array('content'));
                }
            }
            if (true === isset($this->data['Componentshorttext'])) {
                $componentshorttext = new Wcms_Componentshorttext();
                foreach ($this->data['Componentshorttext'] as $shorttext) {
                    $componentshorttext->create();
                    $componentshorttext->data($shorttext);
                    $componentshorttext->saveFields(array('content'));
                }
            }
            if (true === isset($this->data['Componentfile'])) {
                $componentfile = new Wcms_Componentfile();
                foreach ($this->data['Componentfile'] as $file) {
                    $componentfile->create();
                    $componentfile->data($file);
                    $componentfile->saveFields(
                        array(
                            'file_id',
                            'alt',
                            'title',
                            'link',
                            'target'
                        )
                    );
                }
            }

            print '{"return_value" : true}';
        } else {
            print '{"return_value" : false}';
        }
	}

	/**
	 * delete site action.
	 *
	 * @param int $id the componentlanguage id
	 *
	 * @return void
	 */
	public function deleteAction ($id)
	{
		$this->view = false;

		$this->Componentlanguage->setId($id);
        $this->checkLanguageAccess(
            $this->Componentlanguage->readField('language_id')
        );
		$this->Componentlanguage->delete();

		print '{"return_value" : true}';
	}

	/**
	 * Saves a specific content.
	 *
	 * @param int    $id   the componentype id
	 * @param string $type the component type
	 *
	 * @return void
	 */
	public function saveContentAction ($id, $type)
	{
		$this->view = false;

		if (true === isset($this->data['Componentcontent']['content'])
            && true === in_array($type, array('shorttext', 'text', 'file'))
        ) {
			$obj_content = null;
			switch ($type) {
				case 'file':
					$obj_content = new Wcms_Componentfile();
					$obj_content->setFileId($this->data['Componentcontent']['content']);
					$obj_content->setId($id);
                    $obj_content->read(array('fields' => array('Componentlanguage.language_id')));
                    $this->checkLanguageAccess(
                        $obj_content->Componentlanguage->getLanguageId()
                    );
					$obj_content->saveField('file_id', false);
                    $this->session->delete('choose_file');
                    $this->session->delete('choose_file_componenttype_id');
					break;
				default:
					switch ($type) {
						case 'shorttext':
							$obj_content = new Wcms_Componentshorttext();
							break;
						case 'text':
							$obj_content = new Wcms_Componenttext();
							break;
					}
					$obj_content->setContent($this->data['Componentcontent']['content']);
					$obj_content->setId($id);
					$obj_content->read(array('fields' => array('Componentlanguage.language_id')));
                    $this->checkLanguageAccess(
                        $obj_content->Componentlanguage->getLanguageId()
                    );
					$obj_content->saveField('content', false);
			}

			print '{"success": true}';
		} else {
			print '{"success": false}';
		}
	}
}
?>
