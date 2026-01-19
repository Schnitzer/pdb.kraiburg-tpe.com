<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Wcms_ContentboxController class.
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
 * Wcms_ContentboxController class.
 *
 * @package netzcraftwerk
 */
class Wcms_ContentboxController extends Wcms_ModuleController
{

    /**
     * Layout
     *
     * @var string
     */
    public $layout = 'blank';

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Contentboxes :: Website";

    /**
     * Components...
     *
     * @var array
     */
    public $components = array('Acl');

	/**
	 * The contentboxes which have been already read.
	 *
	 * @var array
	 */
	private static $_contentboxes = array();

	/**
	 * shows all existing Contentboxes
	 *
	 */
	public function allAction ($group_id)
    {
        $group_id = (int) $group_id;
        
        $conditions = array();
        if ($group_id > 1) {
            $conditions = array(
                'Contentbox.contentboxgroup_id' => $group_id,
								'Contentbox.used' => 1
            );
        } else {
            $conditions = array(
								'Contentbox.used' => 1
            );
				}
        
        $this->Contentbox->unbindModel('all');
        $arr_boxes = $this->Contentbox->fetch(
            "all",
            array(
                'conditions' => $conditions
            )
        );
        $this->view->contentboxes = $arr_boxes;
        
        // read the languages
        $contentboxlanguage = new Wcms_Contentboxlanguage();
        $contentboxlanguage = $contentboxlanguage->fetch(
            'all',
            array(
                'fields' => array(
                    'Contentboxlanguage.contentbox_id',
                    'Language.shortcut',
                    'Language.name'
                ),
                'order' => array('Language.id')
            )
        );
        $arr_languages = array();
        foreach ($contentboxlanguage as $language) {
            $arr_languages[$language->getContentboxId()][] = array(
                'shortcut' => $language->Language->getShortcut(),
                'name' => $language->Language->getName()
            );
        }
        
        $this->view->arr_languages = $arr_languages;
    }

	/**
	 * new Contentbox
	 *
	 */
	public function newAction ($group_id)
	{
        $this->view->group_id = (int) $group_id;
	}

    /**
     * save action
     *
     * @return void
     */
    public function saveAction ()
    {
        $this->view = false;

       if (true === isset($this->data['Contentbox'])) {
            $text = new Ncw_Helpers_Text();
            $this->Contentbox->data($this->data['Contentbox']);
            $this->Contentbox->setFilename(
                $text->cleanForUrl($this->data['Contentbox']['name'])
            );
            if (true === $this->Contentbox->save()) {
                print '{"return_value" : true, "contentbox_id" : ' . $this->Contentbox->getId() . '}';
            } else {
                print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->Contentbox->invalidFields()) . '}';
            }
        }
    }

	/**
	 * to edit one Contentbox
	 *
	 * @param int $id (id of the contentbox)
	 */
	public function editAction ($id)
    {
        $id = (int) $id;
        
        $this->Contentbox->setId($id);
        $this->Contentbox->read();
        $this->view->contentbox_id = $id;
        
        $this->data['Contentbox'] = $this->Contentbox->data();
        
        $this->view->arr_all_languages = $this->Contentbox->Contentboxlanguage;
        $this->view->groups = $this->groupsSelectOptions($this->Contentbox->getContentboxgroupId(), false, 1);
    }

    /**
     * Group select opionts
     *
     * @param mixed $not_this_group
     * @param int $parent_id
     * @param int $depth
     *
     * @return string
     */
    public function groupsSelectOptions ($parent_id = 0, $not_this_group = false, $start_parent_id = 0, $depth = 0)
    {
        $this->loadModel("Contentboxgroup");
        $this->Contentboxgroup->unbindModel('all');
        $parent_options = $this->Contentboxgroup->fetch(
            'all',
             array(
                 'fields' => array(
                     'Contentboxgroup.id',
                     'Contentboxgroup.name'
                 ),
                 'conditions' => array('Contentboxgroup.parent_id' => $start_parent_id),
                 'order' => array('Contentboxgroup.parent_id', 'Contentboxgroup.name')
             )
        );
        $options = '';
        foreach ($parent_options as $option) {
            if ($not_this_group == $option->getId()) {
                continue;
            }
            $selected = '';
            if ($parent_id == $option->getId()) {
                $selected = ' selected="selected"';
            }
            $name = $option->getName();
            if ($option->getId() == 1) {
                $name = T_('First level');
            }
            $options .= '<option value="' . $option->getId() . '"' . $selected. '>' . str_repeat('--', $depth) . ' ' . $name . '</option>';
            $options .= $this->groupsSelectOptions($parent_id, $not_this_group, $option->getId(), $depth + 1);
        }
        return $options;
    }

    /**
     * update action
     *
     * @return void
     */
    public function updateAction ()
    {
        $this->view = false;

        if (true === isset($this->data['Contentbox'])) {
            $text = new Ncw_Helpers_Text();
            $this->Contentbox->data($this->data['Contentbox']);
            $this->Contentbox->setFilename(
                $text->cleanForUrl($this->data['Contentbox']['name'])
            );
            if (true === $this->Contentbox->saveFields(array('name', 'filename', 'contentboxgroup_id'))) {
                print '{"return_value" : true}';
            } else {
                print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->Contentbox->invalidFields()) . '}';
            }
        }
    }

	/**
	 * Delete Contentbox
	 *
	 * @param int $id (id of the contentbox)
	 */
	public function deleteAction ($id)
	{
        $this->view = false;
        
        $this->Contentbox->setId($id);
        $this->Contentbox->delete();
        $this->flushWebsiteCache();
        
        print '{"return_value" : true}';
	}

	/**
	 * Gets a contentbox
	 *
	 * @param string $name        the contenbox file name
	 * @param int    $language_id the language id
	 *
	 * @return mixed
	 */
	public static function getContenbox ($name, $language_id)
	{
	    $return = self::_getContenbox($name, $language_id);
        if (false !== $return) {
            return $return;
        } else {
            $setting = new Wcms_Setting();
            $setting->setId(1);
            $language_id = $setting->readField('language_id');
            $return = self::_getContenbox($name, $language_id);
            if (false !== $return) {
                return $return;
            }
        }
        return false;
	}

	/**
     * Reads the contentbox data
     *
     * @param string $name        the contenbox file name
     * @param int    $language_id the language id
     *
     * @return mixed
	 */
	private static function _getContenbox ($name, $language_id)
	{
	    if (true === isset(self::$_contentboxes[$name][$language_id])) {
	        return self::$_contentboxes[$name][$language_id];
	    }
        $contentbox = new Wcms_Contentbox();
        $contentbox->unbindModel('all');
        $contentbox->bindModel(array('has_one' => array('Contentboxlanguage')));
        $obj_contentbox = $contentbox->findBy(
            'filename',
            $name,
            array(
                'fields' => array('Contentboxlanguage.body'),
                'conditions' => array(
                    'Contentboxlanguage.language_id' => $language_id
                )
            )
        );

				//$obj_contentbox->setUsed(1);
				//$obj_contentbox->saveField('used');
				//return $name;
				//$obj_contentbox->saveFields(array('used'));
		
				$obj_contentbox_update = new Wcms_Contentbox();
				$obj_contentbox_update->unbindModel('all');
				$arr_contentboxes_update = $obj_contentbox_update->fetch('all', array('conditions' => array(
                    'Contentbox.filename' => $name
                )));
		if ($_SERVER['REMOTE_ADDR'] == '79.208.205.247') {
				if (count($arr_contentboxes_update) > 0) {
					$obj_contentbox_update_save = new Wcms_Contentbox();
					$obj_contentbox_update_save->unbindModel('all');
					//var_dump($arr_contentboxes_update[0]->getId());
					$obj_contentbox_update_save->setId($arr_contentboxes_update[0]->getId());
					$obj_contentbox_update_save->setUsed(1);
					$obj_contentbox_update_save->saveFields(array('used'));
				}
		}

        if (false !== $obj_contentbox) {
            $content = $obj_contentbox->Contentboxlanguage->getBody();
            if (true === Ncw_Configure::read('App.rewrite')) {
                $content = str_replace(
                    'index.php?url=',
                    '',
                    $content
                );
            }
            return self::$_contentboxes[$name][$language_id] =
                $content;
        }

        return false;
	}
}
?>
