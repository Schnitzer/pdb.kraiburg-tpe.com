<?php
/* SVN FILE: $Id$ */
/**
 * Contains the UsergroupsController class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk UG
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * UsergroupsController class.
 *
 * @package netzcraftwerk
 */
class Core_UsergroupController extends Core_ModuleController
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
	public $page_title = "User Management";

	/**
	 * showUsergroups action.
	 *
	 */
	public function allAction ()
	{
		$this->layout = 'default';
        $this->registerJs(
            array(
                'ncw.core.user',
                'ncw.core.usergroup',
            )
        );	
	}
	
	/**
	 * 
	 */
	public function treeAction () 
	{		
		$usergroups = $this->Usergroup->fetch(
            "all",
            array(
                'conditions' => array('Usergroup.id !=' => 1),
                'fields' => array(
                    'Usergroup.id',
                    'Usergroup.name',
                    'Usergroup.parent_id',
                    'Usergroup.level' => 'count(p.id)-1'
                )
            )
        );
		
		$arr_usergroups = array();
		foreach ($usergroups as $usergroup) {
			$arr_usergroups[$usergroup->getParentId()][] = array(
				'id' => $usergroup->getId(),
				'name' => $usergroup->getNameEncoded(),
			);
		}
		
		$this->view->tree = $this->_treeDepth($arr_usergroups);		
	}
	
    /**
     * Gets a tree depths
     *
     * @param array $all_sites
     * @param int $parent_id
     *
     * @return string
     */
    protected function _treeDepth ($arr_usergroups, $parent_id = 1)
    {
        $tree = '';
        if (true === isset($arr_usergroups[$parent_id])) {
            $tree .= '<ul>';
            foreach ($arr_usergroups[$parent_id] as $usergroup) {

                $tree .= '<li id="ncw-group-' . $usergroup['id'] . '">';
                $tree .= '<a href="#" style="text-decoration: none !important"><ins>&nbsp;</ins>'
                    .  $usergroup['name'] . '</a>';

                $tree .= $this->_treeDepth($arr_usergroups, $usergroup['id']);

                $tree .= '</li>';

            }
            $tree .= '</ul>';
        }
        return $tree;
    }	

	/**
	 * new Usergroup action.
	 * 
	 * @param int $usergroup_id the parent usergoup
	 *
	 */
	public function newAction ($usergroup_id = 1)
	{
		$this->view->arr_options = $this->Usergroup->fetch(
            "all",
            array(
                'fields' => array(
                    'Usergroup.id',
                    'Usergroup.name',
                    'Usergroup.level' => 'count(p.id)-1'
                )
            )
        );
		$this->view->usergroup_id = $usergroup_id;
	}
	
	/**
	 * save new usergroup
	 *
	 * @return void
	 */
	public function saveAction ()
	{
		$this->view = false;

		$group_id = 0;
	    $return = 'false';
		if (true === isset($this->data['Usergroup'])) {
			$this->Usergroup->data($this->data['Usergroup']);
			if (true === $this->Usergroup->save()) {
				$group_id = $this->Usergroup->getId();
				$return = 'true';
			}
		}	
		print '{"return_value" : ' . $return . ', "group_id" : ' . $group_id . ', "invalid_fields" : ' . json_encode($this->Usergroup->invalidFields()) . '}';	
	}	

	/**
	 * edit Usergroup
	 *
	 */
	public function editAction ($id)
	{
		$this->Usergroup->setId($id);
		$this->Usergroup->read();

		$this->data['Usergroup'] = $this->Usergroup->data();
		
		// Permissions
		$this->view->acos_tree =$this->_editPermissions($id);
		
		
		$this->view->usergroup_id = $id;
		$this->view->usergroup_name = $this->Usergroup->getNameEncoded();
		$this->view->parent_id = $this->Usergroup->getParentId();

        // usergroup parent select
        $this->Usergroup->unbindModel('all');
        $this->view->arr_options = $this->Usergroup->fetch(
            'all',
             array(
                 'conditions' => array(
                     '(Usergroup.lft <' . $this->Usergroup->getLft() . '|| Usergroup.lft >'
                     . $this->Usergroup->getRgt(). ')'
                 ),
                 'fields' => array(
                     'Usergroup.id',
                     'Usergroup.level' => 'COUNT(`p`.`id`)-1',
                     'Usergroup.name'
                 )
             )
        );
	}
	
	/**
	 * 
	 */
	public function _editPermissions ($id)
	{
		$permissions = $this->acl->readACOSByARO($id);
		
		$arr_acos = array();
		$acos = $this->acl->readAllACOS();
		$prev_alias = '';
		$prev_same = 1;
		$acos_html = '<div class="ncw-permissions-tree ncw-tree"><ul>';
		$first = true;
		foreach ($acos as $aco) {
            $access = 1;
            $checked_add = '';
			$checked_allow = '';
			$checked_deny = '';
			$display = ' style="display: none; border: none; padding: 0;"';
            if (true === isset($permissions[$aco['alias']])) {
            	$display = ' style="border: none; padding: 0;"';
                $checked_add = ' checked="checked"';
				$access = $permissions[$aco['alias']]['access'];
                if (true === (boolean) $access) {
                	$checked_allow = ' checked="checked"';
				} else {
					$checked_deny = ' checked="checked"';
				}
            }			
			
			$alias = explode('/', $aco['alias']);
			
			$num_alias = count($alias);
			$same = 0;
			for ($count = 0;$count < $num_alias; ++$count) {
				if ($alias[0] != $prev_alias[0]) {
					break;
				}
				++$same;
			}
			
			if ($same > $prev_same) {
				$acos_html .= '<ul>';
			} else if ($same < $prev_same) {
				$acso_html .= '</li>';
				$acos_html .= str_repeat('</ul>', $prev_same - $same);
			} else {
				if (false === $first) {
					$acos_html .= '</li>';
				} else {
					$first = false;
				}				
			}
			
			if ($aco['alias'] == '') {
				$aco['alias'] = '/';
			}		
			
			if (false === empty($aco['name'])) {
				$name = $aco['name'] . ' (' . $aco['alias'] . ')';
			} else {
				$name = $aco['alias'];
			}
			
			$acos_html .= '<li id="ncw-permission-1">';
			$acos_html .= '<a href="#" class="ncw-tree-leaf"><ins>&nbsp;</ins>';
			$acos_html .= $name;
			$acos_html .= '</a>';
			
			$acos_html .= '<input name="data[Aco][' . $aco['alias'] . '][add]" type="checkbox" value="1" class="ncw-checkbox ncw-usergroup-permission-add"' . $checked_add . '>';
			$acos_html .= '<span class="ncw-usergroup-permission-radios"' . $display . '><input name="data[Aco][' . $aco['alias'] . '][access]" type="radio" value="1" class="ncw-checkbox"' . $checked_allow . '><label>allow</label>';
			$acos_html .= '<input name="data[Aco][' . $aco['alias'] . '][access]" type="radio" value="0" class="ncw-checkbox"' . $checked_deny . '><label>deny</label></span>';	
			
			$prev_alias = $alias;
			$prev_same = $same;		
		}
		$acos_html .= '</li></ul></div>';
		return $acos_html;		
	}

	/**
	 * Update usergroup
	 *
	 */
	public function updateAction ()
	{
		$this->view = false;

		$arr_state = array("return_value" => false);		

		if (true === isset($this->data['Usergroup'])) {
			$this->Usergroup->data($this->data['Usergroup']);
			$result = $this->Usergroup->saveFields(array("name", "parent_id"));
			if (true === $result) {
				$arr_state['return_value'] = true;
			} else {
				$arr_state['return_value'] = false;
				$arr_state['invalid_fields'] = $this->Usergroup->invalidFields();
			}

			$id = $this->Usergroup->getId();

			// ACOs
			if (true === isset($this->data['Aco'])) {
			    foreach ($this->data['Aco'] as $aco => $setting) {
			    	if ($aco == '/') {
			    		$aco = '';
			    	}
	                if (true === isset($setting['add'], $setting['access'])) {
	                    if (true === (boolean) $setting['access']) {
	                        $this->acl->allow($id, $aco);
	                    } else {
	                        $this->acl->deny($id, $aco);
	                    }
	                } else {
	                    $this->acl->remove($id, $aco);
	                }
	            }
			}
		}
		print json_encode($arr_state);
	}

	/**
	 * deleteUsergroup action.
	 *
	 */
	public function deleteAction ($id)
	{
	    $this->view = false;

		$this->Usergroup->setId($id);
		$this->Usergroup->delete();

		print '{"return_value" : true}';	
	}
}
?>
