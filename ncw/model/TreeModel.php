<?php
/**
 * Contains the NestedSetModel class
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 1997-2008 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version   SVN: $Id$
 * @link      http://www.netzcraftwerk.com
 * @since     File available since Release 0.1
 * @modby     $LastChangedBy$
 * @lastmod   $LastChangedDate$
 */
/**
 * Nested set model class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
abstract class Ncw_TreeModel extends Ncw_Model
{

    /**
     * Builds the SQL for the fetch functions
     *
     * @param array $params the fetch parameters
     *
     * @return string
     */
    protected function _prepareFetch (Array $params)
    {
        if ($params['limit'] == 1) {
            return parent::_prepareFetch($params);
        }
        // build the sql.
        $sql = "SELECT ";
        if ($params['type'] !== 'count') {
            $sql .= $this->_prepareFetchFields($params);
        } else {
            $sql .= 'count(1) AS \'count\' ';
        }
        $sql .= " FROM `" . $this->db_table_name . "` AS `p`, `" . $this->db_table_name . "` AS `" . $this->name . "`";
        $sql .= $this->_prepareFetchJoins($params);
        // where conditions
        switch ($params['type']) {
        case 'parents' :
            $sql .= " WHERE `p`.`lft` BETWEEN `" . $this->name . "`.`lft` AND `" . $this->name . "`.`rgt`";
            break;

        default :
            $sql .= " WHERE `" . $this->name . "`.`lft` BETWEEN `p`.`lft` AND `p`.`rgt`";
        }
        $conditions = array();
        list($sql_conditions, $conditions) = $this->_prepareFetchConditions($params, true);
        $sql .= $sql_conditions;
        unset($sql_conditions);
        // group
        switch ($params['type']) {
        case 'parents' :
            if (false === empty($params['group'])) {
                $sql .= " GROUP BY ";
                $first = true;
                foreach ($params['group'] as $group) {
                    if (false === $first) {
                        $sql .= ",";
                    }
                    $first = false;
                    if (false === strpos($group, '.')) {
                        $group = $this->name . '.' . $group;
                    }
                    $sql .= $group;
                }
            }
            break;

        default :
            $sql .= " GROUP BY `" . $this->name . "`.`lft`";
            if (false === empty($params['group'])) {
                foreach ($params['group'] as $group) {
                    if (false === strpos($group, '.')) {
                        $group = $this->name . '.' . $group;
                    }
                    $sql .= ", " . $group;
                }
            }
        }
        // order
        switch ($params['type']) {
        case 'parents' :
            $sql .= " ORDER BY `p`.`lft` ";
            $first = false;
            break;

        default :
            if (true != in_array($this->name . ".lft DESC", $params['order'])) {
                $sql .= " ORDER BY `" . $this->name . "`.`lft`";
                $first = false;
            } else {
                $sql .= " ORDER BY ";
                $first = true;
            }
        }
        if (false === empty($params['order'])) {
            foreach ($params['order'] as $order) {
                if (false === $first) {
                    $sql .= ",";
                }
                $first = false;
                if (false === strpos($order, '.')) {
                    $order = $this->name . '.' . $order;
                }
                $sql .= $order;
            }
        }
        // limitations
        if (false === empty($params['limit'])) {
            if (is_array($params['limit']) && isset($params['limit'][1]) && is_int($params['limit'][1])) {
                $sql .= " LIMIT " . $params['limit'][0] . "," . $params['limit'][1];
            } else {
                $sql .= " LIMIT " . $params['limit'];
            }
        }
        return array($sql, $conditions);
    }

    /**
     * Insert a new tree model entry
     *
     * @param string $fields  the fields (unused in TreeModel, kept for compatibility)
     * @param string $holders the holders (unused in TreeModel, kept for compatibility)
     * @param array  $values  the values (unused in TreeModel, kept for compatibility)
     *
     * @return boolean
     */
    protected function _doInsert ($fields = '', $holders = '', array $values = array())
    {
        try {
            // Do the query.
            $this->db->beginTransaction();
            $stmt = $this->db->prepare('SELECT `rgt` FROM ' . $this->db_table_name . ' WHERE `id`=:parent_id');
            $stmt->bindValue(":parent_id", $this->getParentId(), PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($row[0]['rgt'] <= 0) {
                throw new Ncw_Exception('Insert failed (Parents right value is null)', 1);
            }
            $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET `rgt`=`rgt`+2 WHERE `rgt`>=:right_value');
            $stmt->bindValue(":right_value", $row[0]['rgt'], PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET `lft`=`lft`+2 WHERE `lft`>:right_value');
            $stmt->bindValue(":right_value", $row[0]['rgt'], PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            $stmt = $this->db->prepare('INSERT INTO ' . $this->db_table_name . ' (`lft`, `rgt`) VALUES (:left_value, :right_value)');
            $stmt->bindValue(":left_value", $row[0]['rgt'], PDO::PARAM_INT);
            $stmt->bindValue(":right_value", $row[0]['rgt'] + 1, PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
        } catch (Ncw_Exception $e) {
            $this->db->rollBack();
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        try {
            $stmt = $this->db->prepare("SELECT last_insert_id() AS intRecordKey");
            if (false === $stmt) {
                $error_info = $this->db->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->insert_id = $row[0]['intRecordKey'];
            $this->setId($this->insert_id);
            if ($this->insert_id > 0 && false === $this->save(false, false)) {
                throw new Ncw_Exception('Insert failed because update of Nested Set Model failed.');
            }
        } catch (Ncw_Exception $e) {
            $this->db->rollBack();
            $this->setId(0);
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        $this->db->commit();
        $this->setId($this->insert_id);
        return true;
    }

    /**
     * Updates the model entry
     *
     * @param string $fields  the fields (unused in TreeModel, kept for compatibility)
     * @param array  $values  the values (unused in TreeModel, kept for compatibility)
     *
     * @return boolean
     */
    protected function _doUpdate ($fields = '', array $values = array())
    {
        $values = array(":id" => $this->getId());
        $data = array();
        $not_wanted_fields = array("id", "created", "modified", "lft", "rgt", "level");
        foreach ($this->data as $field => $value) {
            if (false === in_array($field, $not_wanted_fields)) {
                $data[$field] = $value;
            }
        }
        $fields = "";
        $values = array(":id" => $this->getId());
        $count = 1;
        $count_data = count($data);
        foreach ($data as $field => $value) {
            $fields .= "`" . $field . "`=" . ":" . $field;
            $values[":" . $field] = $value;
            if ($count < $count_data) {
                $fields .= ",";
            }
            ++$count;
        }
        try {
            // Build the sql query.
            $sql = "UPDATE `" . $this->db_table_name . "`
					SET ";
            $sql .= $fields;
            $sql .= " WHERE id=:id";
            // Do the query.
            $stmt = $this->db->prepare($sql);
            if (false === $stmt) {
                $error_info = $this->db->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            if (false === $stmt->execute($values)) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
            }
        } catch (Ncw_Exception $e) {
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        return true;
    }

    /**
     * This method is called before the model is saved.
     *
     * @return void
     */
    protected function _doBeforeSave ()
    {
        if (false === $this->beforeSave()) {
            return false;
        }
        // If the parent has changed then the tree node must be moved.
        $current_value = $this->readField('parent_id');
        $target_id = $this->getParentId();
        if ($current_value != 0
            && $target_id != ''
            && $current_value != $target_id
        ) {
            $this->db->beginTransaction();
            try {
                $this->moveAcross($target_id);
            } catch (Ncw_Exception $e) {
                $this->db->rollBack();
                if (DEBUG_MODE > 0) {
                    $e->exitWithMessage();
                }
                return false;
            }
            $this->db->commit();
        }
        return true;
    }

    /**
     * Moves the node across
     *
     * @param int $target_id the id of the target node
     *
     * @return void
     */
    public function moveAcross ($target_id)
    {
        $stmt = $this->db->prepare(
            'SELECT `lft`, `rgt` '
            . 'FROM ' . $this->db_table_name . ' WHERE id=:parent_id'
        );
        if (false === $stmt) {
            $error_info = $this->db->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $stmt->bindValue(':parent_id', $target_id, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $a_lft = $row[0]['lft'];
        $a_rgt = $row[0]['rgt'];
        $stmt = $this->db->prepare(
            'SELECT `lft`, `rgt`, '
            . 'ROUND((`rgt` - `lft` - 1) / 2) AS children '
            . 'FROM ' . $this->db_table_name . ' WHERE id=:id'
        );
        if (false === $stmt) {
            $error_info = $this->db->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $b_lft = $row[0]['lft'];
        $b_rgt = $row[0]['rgt'];
        $b_children = $row[0]['children'];
        if ($b_lft > $a_lft && $b_rgt < $a_rgt) {
            $this->_moveUp($a_lft, $a_rgt, $b_lft, $b_rgt, $b_children);
        } else if ($b_lft > $a_lft && $b_rgt > $a_rgt) {
            $this->_moveLeft($target_id, $a_lft, $a_rgt, $b_lft, $b_rgt, $b_children);
        } else {
            $this->_moveRight($target_id, $a_lft, $a_rgt, $b_lft, $b_rgt, $b_children);
        }
    }

    /**
     * Moves a nested set node up in the tree hierarchy
     *
     * @param int $a_lft      left value of the parent node
     * @param int $a_rgt      right value of the parent node
     * @param int $b_lft      left value of the affected node
     * @param int $b_rgt      right value of the affected node
     * @param int $b_children the number of children of the affected node
     *
     * @return viod
     */
    protected function _moveUp ($a_lft, $a_rgt, $b_lft, $b_rgt, $b_children)
    {
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET  lft = :a_rgt - ( (:b_children + 1) * 2 ), rgt = :a_rgt - 1, updated = 1 WHERE id = :id');
        $stmt->bindValue(":id", $this->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET  lft = lft + (:a_rgt - :b_rgt - 1), rgt = rgt + (:a_rgt - :b_rgt - 1), updated = 1 WHERE lft > :b_lft AND rgt < :b_rgt AND updated != 1');
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_lft", $b_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_rgt", $b_rgt, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET  rgt = rgt - ( (:b_children + 1) * 2 ), updated = 1 WHERE lft < :b_lft AND lft > :a_lft AND rgt > :b_lft AND updated != 1');
        $stmt->bindValue(":a_lft", $a_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_lft", $b_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET  lft = lft - ( (:b_children + 1) * 2 ), rgt = rgt - ( (:b_children + 1) * 2 ), updated = 1 WHERE lft > :b_rgt AND rgt < :a_rgt AND updated != 1');
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_rgt", $b_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET updated = 0');
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
    }

    /**
     * Moves a nested set node left in the tree hierarchy
     *
     * @param int $parent_id  id of the parent node
     * @param int $a_lft      left value of the parent node
     * @param int $a_rgt      right value of the parent node
     * @param int $b_lft      left value of the affected node
     * @param int $b_rgt      right value of the affected node
     * @param int $b_children the number of children of the affected node
     *
     * @return viod
     */
    protected function _moveLeft ($parent_id, $a_lft, $a_rgt, $b_lft, $b_rgt, $b_children)
    {
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET rgt = rgt + ( ( :b_children + 1 ) * 2 ), updated = 1 WHERE id = :parent_id');
        $stmt->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft = :a_rgt, rgt = ( :a_rgt - 1  + ( ( :b_children + 1 ) * 2 ) ), parent_id = :parent_id, updated = 1 WHERE id = :id');
        $stmt->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
        $stmt->bindValue(":id", $this->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft = lft - ( :b_lft - :a_rgt ), rgt = rgt - ( :b_lft - :a_rgt ), updated = 1 WHERE lft > :b_lft AND rgt < :b_rgt AND updated != 1');
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_lft", $b_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_rgt", $b_rgt, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft = lft + ( ( :b_children + 1 ) * 2 ), rgt = rgt + ( ( :b_children + 1 ) * 2 ), updated = 1 WHERE lft > :a_rgt AND rgt < :b_lft AND updated != 1');
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_lft", $b_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET rgt = rgt + ( ( :b_children + 1 ) * 2 ), updated = 1 WHERE lft < :a_lft AND rgt > :a_rgt AND rgt < :b_lft AND updated != 1');
        $stmt->bindValue(":a_lft", $a_lft, PDO::PARAM_INT);
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_lft", $b_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft = lft + ( ( :b_children + 1 ) * 2 ), updated = 1 WHERE lft > :a_rgt AND lft < :b_lft AND rgt > :b_rgt AND updated != 1');
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_lft", $b_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_rgt", $b_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET updated = 0');
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
    }

    /**
     * Moves a nested set node right in the tree hierarchy
     *
     * @param int $parent_id  id of the parent node
     * @param int $a_lft      left value of the parent node
     * @param int $a_rgt      right value of the parent node
     * @param int $b_lft      left value of the affected node
     * @param int $b_rgt      right value of the affected node
     * @param int $b_children the number of children of the affected node
     *
     * @return viod
     */
    private function _moveRight ($parent_id, $a_lft, $a_rgt, $b_lft, $b_rgt, $b_children)
    {
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft = lft - ( ( :b_children + 1 ) * 2 ), updated = 1 WHERE id = :parent_id');
        $stmt->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft =  :a_rgt - ( ( :b_children + 1 ) * 2 ), rgt =  :a_rgt - 1, parent_id =  :parent_id, updated = 1 WHERE id = :id');
        $stmt->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
        $stmt->bindValue(":id", $this->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft = lft + ( ( :a_rgt - 1 ) - :b_rgt ), rgt = rgt + ( ( :a_rgt - 1 ) - :b_rgt ), updated = 1 WHERE lft > :b_lft AND rgt < :b_rgt AND updated != 1');
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_lft", $b_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_rgt", $b_rgt, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET rgt = rgt - ( ( :b_children + 1) * 2 ), updated = 1 WHERE lft < :b_lft AND rgt > :b_rgt AND rgt < :a_lft AND updated != 1');
        $stmt->bindValue(":a_lft", $a_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_lft", $b_lft, PDO::PARAM_INT);
        $stmt->bindValue(":b_rgt", $b_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft = lft - ( ( :b_children + 1 ) * 2 ), rgt = rgt - ( ( :b_children + 1 ) * 2 ), updated = 1 WHERE lft > :b_rgt AND rgt < :a_rgt AND updated != 1');
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_rgt", $b_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET lft = lft - ( ( :b_children + 1 ) * 2 ), updated = 1 WHERE lft > :b_rgt AND lft < :a_rgt AND rgt > :a_rgt AND updated != 1');
        $stmt->bindValue(":a_rgt", $a_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_rgt", $b_rgt, PDO::PARAM_INT);
        $stmt->bindValue(":b_children", $b_children, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET updated = 0');
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Update failed (' . $error_info[2] . ')', 1);
        }
    }

    /**
     * Switches the position of the current model
     * with the left neighbour.
     *
     * @param Ncw_DataModel $target the target
     *
     * @return void
     */
    public function positionUp (Ncw_DataModel $target)
    {
        $this->db->beginTransaction();
        try {
            if ($this->getId() <= 0) {
                throw new Ncw_Exception('Target model id must be greater then 0!');
            }
            if ($target->getId() <= 0) {
                throw new Ncw_Exception('Target model id must be greater then 0!');
            }
            if ($target->getLft() <= 0 && $target->getRgt()) {
                throw new Ncw_Exception(
                    'Target model lft and rgt value must be greater then 0!'
                );
            }
            $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET `rgt`=`rgt`+2 WHERE `rgt`>:lft');
            $stmt->bindValue(":lft", $target->getLft(), PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Position up (' . $error_info[2] . ')', 1);
            }
            $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET `lft`=`lft`+2 WHERE `lft`>=:lft');
            $stmt->bindValue(":lft", $target->getLft(), PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Position up (' . $error_info[2] . ')', 1);
            }

            // create a new node.
            $stmt = $this->db->prepare(
                'INSERT INTO ' . $this->db_table_name . ' '
                . '(lft, rgt) VALUES '
                . '(:lft, :rgt)'
            );
            $stmt->bindValue(':lft', $target->getLft(), PDO::PARAM_INT);
            $stmt->bindValue(':rgt', $target->getLft() + 1, PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Position up failed (' . $error_info[2] . ')', 1);
            }
            $new_model_id = $this->db->lastInsertId();
            $old_id = $this->getId();
            $this->setId($new_model_id);
            $this->save(false);

            // update the children
            $children = $this->fetch(
                'list',
                array(
                    'conditions' => array('parent_id' => $old_id)
                )
            );
            $child = new Wcms_Site();
            foreach ($children as $child_id) {
                $child->setId($child_id);
                $child->moveAcross($this->getId());
            }
            // delete the old node
            $this->_delete($old_id);

            // update the id and the parent ids of the children
            $stmt = $this->db->prepare(
                'UPDATE ' . $this->db_table_name . ' SET '
                . 'id=:old_id '
                . 'WHERE id=:new_id'
            );
            $stmt->bindValue(':old_id', $old_id, PDO::PARAM_INT);
            $stmt->bindValue(':new_id', $this->getId(), PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Position up failed (' . $error_info[2] . ')', 1);
            }
            $this->setId($old_id);
            foreach ($children as $child_id) {
                $stmt = $this->db->prepare(
                    'UPDATE ' . $this->db_table_name . ' SET '
                    . 'parent_id=:parent_id '
                    . 'WHERE id=:id'
                );
                $stmt->bindValue(':id', $child_id, PDO::PARAM_INT);
                $stmt->bindValue(':parent_id', $old_id, PDO::PARAM_INT);
                if (false === $stmt->execute()) {
                    $error_info = $stmt->errorInfo();
                    throw new Ncw_Exception('Position up failed (' . $error_info[2] . ')', 1);
                }
            }
        } catch (Ncw_Exception $e) {
            $this->db->rollBack();
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
        }
        $this->db->commit();
    }

    /**
     * Switches the position of the current model
     * with the right neighbour.
     *
     * @param Ncw_DataModel $target the target
     *
     * @return void
     */
    public function positionDown (Ncw_DataModel $target)
    {
        $this->db->beginTransaction();
        try {
            if ($this->getId() <= 0) {
                throw new Ncw_Exception('Target model id must be greater then 0!');
            }
            if ($target->getId() <= 0) {
                throw new Ncw_Exception('Target model id must be greater then 0!');
            }
            if ($target->getLft() <= 0 && $target->getRgt()) {
                throw new Ncw_Exception(
                    'Target model lft and rgt value must be greater then 0!'
                );
            }
            $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET `rgt`=`rgt`+2 WHERE `rgt`>:rgt');
            $stmt->bindValue(":rgt", $target->getRgt(), PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Position up (' . $error_info[2] . ')', 1);
            }
            $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET `lft`=`lft`+2 WHERE `lft`>:rgt');
            $stmt->bindValue(":rgt", $target->getRgt(), PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Position up (' . $error_info[2] . ')', 1);
            }

            // create a new node.
            $stmt = $this->db->prepare(
                'INSERT INTO ' . $this->db_table_name . ' '
                . '(lft, rgt) VALUES '
                . '(:lft, :rgt)'
            );
            $stmt->bindValue(':lft', $target->getRgt() + 1, PDO::PARAM_INT);
            $stmt->bindValue(':rgt', $target->getRgt() + 2, PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Position up failed (' . $error_info[2] . ')', 1);
            }
            $new_model_id = $this->db->lastInsertId();
            $old_id = $this->getId();
            $this->setId($new_model_id);
            $this->save(false);

            // update the children
            $children = $this->fetch(
                'list',
                array(
                    'conditions' => array('parent_id' => $old_id)
                )
            );
            $child = new Wcms_Site();
            foreach ($children as $child_id) {
                $child->setId($child_id);
                $child->moveAcross($this->getId());
            }
            // delete the old node
            $this->_delete($old_id);

            // update the id and the parent ids of the children
            $stmt = $this->db->prepare(
                'UPDATE ' . $this->db_table_name . ' SET '
                . 'id=:old_id '
                . 'WHERE id=:new_id'
            );
            $stmt->bindValue(':old_id', $old_id, PDO::PARAM_INT);
            $stmt->bindValue(':new_id', $this->getId(), PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Position up failed (' . $error_info[2] . ')', 1);
            }
            $this->setId($old_id);
            foreach ($children as $child_id) {
                $stmt = $this->db->prepare(
                    'UPDATE ' . $this->db_table_name . ' SET '
                    . 'parent_id=:parent_id '
                    . 'WHERE id=:id'
                );
                $stmt->bindValue(':id', $child_id, PDO::PARAM_INT);
                $stmt->bindValue(':parent_id', $old_id, PDO::PARAM_INT);
                if (false === $stmt->execute()) {
                    $error_info = $stmt->errorInfo();
                    throw new Ncw_Exception('Position up failed (' . $error_info[2] . ')', 1);
                }
            }
        } catch (Ncw_Exception $e) {
            $this->db->rollBack();
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
        }
        $this->db->commit();
    }

    /**
     * Deletes the model entry
     *
     * @param int $id the model id
     *
     * @return boolean
     */
    protected function _doDelete ($id)
    {
        $this->db->beginTransaction();
        try {
            $this->_delete($id);
        } catch (Ncw_Exception $e) {
            $this->db->rollBack();
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        $this->db->commit();
        return true;
    }

    /**
     * Deletes the node entry
     *
     * @param int $id the node id
     *
     * @return boolean
     */
    protected function _delete ($id)
    {
        $stmt = $this->db->prepare('SELECT `rgt`, `lft` FROM ' . $this->db_table_name . ' WHERE `id`=:node_id');
        $stmt->bindValue(":node_id", $id, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($row[0]['rgt'] <= 0 || $row[0]['lft'] <= 0) {
            throw new Ncw_Exception('Insert failed (Nodes right or left value is null)', 1);
        }
        $stmt = $this->db->prepare('DELETE FROM ' . $this->db_table_name . ' WHERE `lft` BETWEEN :left_value AND :right_value');
        $stmt->bindValue(":left_value", $row[0]['lft'], PDO::PARAM_INT);
        $stmt->bindValue(":right_value", $row[0]['rgt'], PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET `lft`=`lft`-ROUND((:right_value-:left_value+1)) WHERE `lft`>:right_value');
        $stmt->bindValue(":left_value", $row[0]['lft'], PDO::PARAM_INT);
        $stmt->bindValue(":right_value", $row[0]['rgt'], PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $this->db->prepare('UPDATE ' . $this->db_table_name . ' SET `rgt`=`rgt`-ROUND((:right_value-:left_value+1)) WHERE `rgt`>:right_value');
        $stmt->bindValue(":left_value", $row[0]['lft'], PDO::PARAM_INT);
        $stmt->bindValue(":right_value", $row[0]['rgt'], PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
    }
}
?>