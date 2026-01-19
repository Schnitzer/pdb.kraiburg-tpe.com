<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Group class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
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
 * GroupController class.
 *
 * @package netzcraftwerk
 */
class Contacts_GroupController extends Contacts_ModuleController
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
	public $page_title = "Contacts";

	/**
	 * show all Groups
	 *
	 */
	public function allAction ()
	{		
		$this->layout = 'default';
		
		$contact_id = 0;
		if (true === isset($this->params['url']['c_id'])) {
            $contact_id = (int) $this->params['url']['c_id'];
        }	
		$this->view->contact_id = $contact_id;	
		
        $this->registerJs(
            array(
                'ncw.contacts.contact',
                'ncw.contacts.group',
            )
        );

        $this->registerCss(
            array(
                'contact.less',
            )
        );
		
		// permissions
		$this->view->permissions = array(
			'/contacts/contact/new' => $this->acl->check('/contacts/contact/new'),
			'/contacts/group/new' => $this->acl->check('/contacts/group/new'),
			'/contacts/group/edit' => $this->acl->check('/contacts/group/edit'),
            '/contacts/group/delete' => $this->acl->check('/contacts/group/delete'),
        );		
	}

    /**
     * Builds a tree
     *
     * @return string the tree
     */
    public function treeAction ()
    {
        // read the groups
        $this->Group->unbindModel('all');
        $groups = $this->Group->fetch(
            'all',
            array(
              'conditions' => array('Group.id !=' => 1),
              'fields' => array(
                  'Group.id',
                  'Group.name',
                  'Group.parent_id',
              ),
              'order' => array('Group.parent_id', 'Group.name')
            )
        );
        $all_groups = array();
        foreach ($groups as $group) {
            $all_groups[$group->getParentId()][] = array(
                'id' => $group->getId(),
                'name' => $group->getName(),
                'parent_id' => $group->getParentId(),
            );
        }

        $this->view->tree = $this->_treeDepth($all_groups, 1);
	}

    /**
     * Gets a tree depths
     *
     * @param array $all_groups
     * @param int $parent_id
     *
     * @return string
     */
    protected function _treeDepth ($all_groups, $parent_id = 1)
    {
        $tree = '';
        if (true === isset($all_groups[$parent_id])) {
            $tree .= '<ul>';
            foreach ($all_groups[$parent_id] as $group) {

                $tree .= '<li id="ncw-group-' . $group['id'] . '">';
                $tree .= '<a href="#" style="text-decoration: none !important"><ins>&nbsp;</ins>'
                    .  $group['name'] . '</a>';

                $tree .= $this->_treeDepth($all_groups, $group['id']);

                $tree .= '</li>';

            }
            $tree .= '</ul>';
        }
        return $tree;
    }

	/**
	 * new contactgroup
	 *
	 * @param int $group_id
	 */
	public function newAction ($group_id)
	{
		$this->view->groups_options = $this->_groupSelectOptions($group_id);
	}
	
	/**
	 * save new contactgroup
	 *
	 * @return void
	 */
	public function saveAction ()
	{
		$this->view = false;

		if (true === isset($this->data['Group'])) {
			$this->Group->data($this->data['Group']);
			if (true === $this->Group->save()) {
				print '{"return_value" : true, "group_id" : ' .  $this->Group->getId(). '}';
			} else {
				print '{"return_value" : false , "invalid_fields" : ' . json_encode($this->Group->invalidFields()) . '}';
			}
		}	
	}	
	

	/**
	 * edit a group
	 *
	 */
	public function editAction ($id)
	{
		$this->Group->unbindModel(
            array(
                'belongs_to' => array('Groupcategory'),
                'has_many' => array('GroupContact')
            )
        );
		$this->Group->setId($id);
		$this->Group->read();
	    $this->data['Group'] = $this->Group->data();
		$this->view->group_id = $id;
		$this->view->group_name = $this->Group->getNameEncoded();
		$this->view->groups_options = $this->_groupSelectOptions($this->Group->getParentId(), $id);
		
		// permissions
		$this->view->permissions = array(
			'/contacts/group/delete' => $this->acl->check('/contacts/group/delete'),
        );			
	}
	
	/**
	 * Update group
	 *
	 */
	public function updateAction ()
	{
		$this->view = false;
		$arr_state = array("return_value" => false);		
		if (true === isset($this->data['Group'])) {
			$this->Group->data($this->data['Group']);
			if (true === $this->Group->saveFields(array("name", "parent_id"))) {
				$arr_state['return_value'] = true;
			} else {
				$arr_state['invalid_fields'] = $this->Group->invalidFields();
			}
		}
		print json_encode($arr_state);
	}	

    /**
     * Group select options
     *
     * @param int $parent_id
	 * @param mixed $not_this_group
     *
     * @return string
     */
    protected function _groupSelectOptions ($parent_id = 0, $not_this_group = 0)
    {	
    	$parent_options = $this->_allGroups(0, (int) $not_this_group, true);
        $options = '';
        foreach ($parent_options as $option) {
            $selected = '';
            if ($parent_id == $option['id']) {
                $selected = ' selected="selected"';
            }
			if ($option['id'] == 1) {
				$option['name'] = T_('First level');
			}
            $options .= '<option value="' .$option['id'] . '"' . $selected. '>' . str_repeat('--', $option['level']) . ' ' . $option['name'] . '</option>';
        }
        return $options;
    }

	/**
	 * Delete group
	 *
	 */
	public function deleteAction ($id)
	{
	    $this->view = false;

		$this->Group->setId($id);
		$this->Group->delete();

		print '{"return_value" : true}';
	}
}
?>
