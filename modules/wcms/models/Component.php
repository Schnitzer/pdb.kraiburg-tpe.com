<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Component class.
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
 * Component class.
 *
 * @package netzcraftwerk
 */
class Wcms_Component extends Ncw_Model
{

	/**
	 * Belongs to associations
	 *
	 * @var array
	 */
	public $belongs_to = array('Site');

	/**
	 * Has many associations
	 *
	 * @var array
	 */
	public $has_many = array('Componentlanguage');

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
        "site_id" => array("rules" => array("Integer"), "required" => true),
        "componenttemplate_id" => array("rules" => array("Integer"), "required" => true),
        "area" => array("rules" => array('Integer'), "required" => true),
        "name" => array("rules" => array("NotEmpty", "MaxLength" => 100), "required" => true),
        "schedule" => array("rules" => array("Integer")),
        "publish" => array("rules" => array("DateTime")),
        "expire" => array("rules" => array("DateTime")),
	    'status' => array('rules' => array('InList' => array('new', 'published', 'unpublished'))),
	);

    /**
     * sort the components method
     *
     * @param string $second_sort (optional)
     */
    public function sort ($second_sort = 'modified')
    {
        try {
            $this->unbindModel('all');
            $components = $this->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Component.site_id' => $this->getSiteId(),
                        'Component.parent_id' => $this->getParentId(),
                        'Component.area' => $this->getArea(),
                    ),
                    'fields' => array('Component.id'),
                    'order' => array(
                        'Component.position',
                        'Component.' . $second_sort . ' DESC'
                    )
                )
            );
            $position = 0;
            foreach ($components as $obj_component) {
                $stmt = $this->db->prepare(
                    'UPDATE ' . $this->db_table_name
                    . ' SET position=:position WHERE id=:id'
                );
                $stmt->bindValue(':position', ++$position);
                $stmt->bindValue(':id', $obj_component->getId());
                if (false === $stmt->execute()) {
                    $error_info = $stmt->errorInfo();
                    throw new Ncw_Exception($error_info[2], 1);
                }
            }
        } catch (Ncw_Exception $e) {
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
        }
    }

    /**
     * After save set the sitelanguage status to modified
     *
     */
    public function afterSave ()
    {
        $this->_setSitelanguageStatus($this->getId());
    }

    /**
     * Before delete set the sitelanguage status to modified
     *
     */
    public function beforeDelete ()
    {
        $status = $this->readField('status');
        if ($status === 'unpublished'
            || $status === 'new'
        ) {
            $this->_setSitelanguageStatus($this->getId());
            return true;
        }
        return false;
    }

    /**
     * Sets the status of the sitelanguage
     *
     * @param int $id the component id
     *
     * @return viod
     */
    private function _setSitelanguageStatus ($id)
    {
        $stmt = $this->db->prepare(
           'SELECT Sitelanguage.id, Sitelanguage.status '
           . 'FROM ' . Ncw_Database::getConfig('prefix') . 'wcms_component AS Component '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_componentlanguage AS Componentlanguage '
           . 'ON Componentlanguage.component_id=Component.id '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_sitelanguage AS Sitelanguage '
           . 'ON Component.site_id=Sitelanguage.site_id '
           . 'WHERE Component.id=:id '
           . '&& Componentlanguage.language_id=Sitelanguage.language_id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if ($row['status'] !== 'new'
                && $row['status'] !== 'unpublished'
            ) {
                $stmt = $this->db->prepare(
                   'UPDATE ' . Ncw_Database::getConfig('prefix') . 'wcms_sitelanguage AS Sitelanguage '
                   . 'SET Sitelanguage.status=:status '
                   . 'WHERE Sitelanguage.id=:id'
                );
                $stmt->bindValue(':id', $row['id'], PDO::PARAM_INT);
                $stmt->bindValue(':status', 'modified', PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
}
?>
