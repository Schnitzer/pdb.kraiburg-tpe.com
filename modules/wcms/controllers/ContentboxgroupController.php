<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Wcms_ContentboxgroupController class.
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
 * Wcms_ContentboxgroupController class.
 *
 * @package netzcraftwerk
 */
class Wcms_ContentboxgroupController extends Wcms_ModuleController
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
	public $page_title = "Contentbox :: Website";

    /**
     * Show files action.
     *   *
     * @return void
     */
    public function allAction ()
    {
        if (false === $this->request_handler->isAjax()) {
           $this->layout = 'default';
        }

        $this->registerJs(
            array(
                'ncw.wcms.tinymce.gzip',
                'ncw.wcms.tinymce',
                'ncw.wcms.extras',
                'ncw.wcms.contentbox',
                'ncw.wcms.contentboxgroup',
            )
        );
        $this->view->jsload_tiny_mce = true;
    }
    
    /**
     * New contentboxgroup action.
     *
     * @param int $parent_id
     *
     * @return void
     */
    public function newAction ($parent_id = 1)
    {
        $this->Contentboxgroup->unbindModel('all');
        $this->view->groups_options = $this->groupsSelectOptions($parent_id);
    }

    /**
     * save action
     *
     * @return void
     */
    public function saveAction ()
    {
        $this->view = false;

       if (true === isset($this->data['Contentboxgroup'])) {
            $this->Contentboxgroup->data($this->data['Contentboxgroup']);
            if (true === $this->Contentboxgroup->save()) {
                print '{"return_value" : true, "contentboxgroup_id" : ' . $this->Contentboxgroup->getId() . '}';
            } else {
                print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->Contentboxgroup->invalidFields()) . '}';
            }
        }

        
    }

    /**
     * Edit Contentboxgroup action.
     *
     * @param int $id the contentboxgroup id
     *
     * @return void
     */
    public function editAction ($contentboxgroup_id)
    {
        $contentboxgroup_id = (int) $contentboxgroup_id;
        
        $this->Contentboxgroup->setId($contentboxgroup_id);
        $this->Contentboxgroup->unbindModel('all');
        $this->Contentboxgroup->read();

        $this->view->contentboxgroup_id = $contentboxgroup_id;
        $this->view->contentboxgroup_name = $this->Contentboxgroup->getNameEncoded();
        $this->view->contentboxgroup_parent_id = $parent_id = $this->Contentboxgroup->getParentId();
        $this->data['Contentboxgroup'] = $this->Contentboxgroup->data();
        
        $this->view->groups_options = $this->groupsSelectOptions($parent_id, $contentboxgroup_id, 0);
    }

    /**
     * update action
     *
     * @return void
     */
    public function updateAction ()
    {
        $this->view = false;

        if (true === isset($this->data['Contentboxgroup'])) {
            $this->Contentboxgroup->data($this->data['Contentboxgroup']);
            if (true === $this->Contentboxgroup->saveFields(array('name', 'parent_id'))) {
                print '{"return_value" : true}';
            } else {
                print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->Contentboxgroup->invalidFields()) . '}';
            }
        }
    }

    /**
     * delete Contentboxgroup action.
     *
     * @param int $id the contentboxgroup id
     *
     * @return void
     */
    public function deleteAction ($id)
    {
        $this->view = false;

        $this->Contentboxgroup->setId($id);
        $this->Contentboxgroup->delete();

        print '{"return_value" : true}';
    }

    /**
     * Builds a tree
     *
     * @return string the tree
     */
    public function treeAction ()
    {
        // read the sites
        $this->Contentboxgroup->unbindModel('all');
        $contentboxgroups = $this->Contentboxgroup->fetch(
            'all',
            array(
              'conditions' => array('Contentboxgroup.id !=' => 1),
              'fields' => array(
                  'Contentboxgroup.id',
                  'Contentboxgroup.name',
                  'Contentboxgroup.parent_id',
              ),
              'order' => array('Contentboxgroup.parent_id', 'Contentboxgroup.name')
            )
        );
        $all_contentboxgroups = array();
        foreach ($contentboxgroups as $contentboxgroup) {
            $all_contentboxgroups[$contentboxgroup->getParentId()][] = array(
                'id' => $contentboxgroup->getId(),
                'name' => $contentboxgroup->getName(),
                'parent_id' => $contentboxgroup->getParentId(),
            );
        }

        $this->view->tree = $this->_treeDepth($all_contentboxgroups, 1);
    }

    /**
     * Gets a tree depths
     *
     * @param array $all_sites
     * @param int $parent_id
     *
     * @return string
     */
    protected function _treeDepth ($all_contentboxgroups, $parent_id = 1)
    {
        $tree = '';
        if (true === isset($all_contentboxgroups[$parent_id])) {
            $tree .= '<ul>';
            foreach ($all_contentboxgroups[$parent_id] as $contentboxgroup) {

                $tree .= '<li id="ncw-contentboxgroup-' . $contentboxgroup['id'] . '">';
                $tree .= '<a href="#" style="text-decoration: none !important"><ins>&nbsp;</ins>'
                    .  $contentboxgroup['name'] . '</a>';

                $tree .= $this->_treeDepth($all_contentboxgroups, $contentboxgroup['id']);

                $tree .= '</li>';

            }
            $tree .= '</ul>';
        }
        return $tree;
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
}
?>
