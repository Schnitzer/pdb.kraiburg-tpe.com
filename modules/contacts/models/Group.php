<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Contactgroup class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
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
 * Contactgroup class.
 *
 * @package netzcraftwerk
 */
class Contacts_Group extends Ncw_Model
{

	/**
	 * Has many
	 *
	 * @var array
	 */
	public $has_many = array('GroupContact');

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
		'parent_id' => array('rules' => array('Integer'), 'required' => true),
	    "name" => array("rules" => array('NotEmpty', 'MaxLength' => 100), "required" => true),
	);

	/**
	 * 
	 */
	public function fetchAsList ($id = 1, $not_this_group = null, $nots_rekursiv = false)
	{
		$arr_groups = array();
		$this->_buildGroupsList($id, $arr_groups, $not_this_group, 0, $nots_rekursiv);
		return $arr_groups;
	}
	
	/**
	 * liest die gruppen rekursiv aus.
	 * 
	 * @param int $parent_id
	 * @param array $arr_groups
	 * @param mixed $not_this_group
	 * @param int $level
	 * @param boolean $nots_rekursiv
	 */
	protected function _buildGroupsList ($parent_id, &$arr_groups, $not_this_group = null, $level = 0, $nots_rekursiv = false)
	{
		$list = $this->fetch(
            'list',
            array(
                'fields' => array(
                    'Group.name',
                    'Group.id'
                ),
                'conditions' => array(
		            'Group.parent_id' => $parent_id,
		        ),
                'order' => array('Group.name')
            )
        );
		foreach ($list as $name => $id) {
			if ((true === is_integer($not_this_group)
				&& $id != $not_this_group)
				|| (true === is_array($not_this_group)
				&& false === in_array($id, $not_this_group))
				|| true === is_null($not_this_group)
			) {
				$not = false;	
			} else {
				$not = true;	
			}
			if (true === $not && true === $nots_rekursiv) {
				continue;	
			}
			if (false === $not) {
				$arr_groups[$id] = array(
					'id' => $id,
					'name' => $name,
					'level' => $level,
				);
			}
			$this->_buildGroupsList($id, $arr_groups, $not_this_group, $level + 1, $nots_rekursiv);
		}	
	}	
}
?>
