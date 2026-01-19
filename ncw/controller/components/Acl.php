<?php

/**
 * contains the Access Control List component.
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  1997-2008 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version    SVN: $Id$
 * @link       http://www.netzcraftwerk.com
 * @since      File available since Release 0.1
 * @modby      $LastChangedBy$
 * @lastmod    $LastChangedDate$
 */

/**
 * Access Control List component.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Components_Acl extends Ncw_Component
{
    /**
     * The ACO table
     *
     * @var string
     */
    protected $_table = '';

    /**
     * The ACO ARO join table
     *
     * @var string
     */
    protected $_join_table = '';

    /**
     * The usergroup_users table
     *
     * @var string
     */
    protected $_usergroups_users_table = '';

    /**
     * The users table
     *
     * @var string
     */
    protected $_users_table = '';

    /**
     * Is ACOS read
     *
     * @var boolean
     */
    protected $_is_acos_read = false;

    /**
     * An array with all acos (Numeric)
     *
     * @var array
     */
    protected $_acos = array();

    /**
     * Sets the database table strings
     */
    public function __construct()
    {
        $this->_table = '`' . Ncw_Database::getConfig('prefix') . 'acos`';
        $this->_join_table = '`' . Ncw_Database::getConfig('prefix') . 'aros_acos`';
        $this->_usergroups_users_table = '`' . Ncw_Database::getConfig('prefix') . 'core_usergroup_user`';
        $this->_users_table = '`' . Ncw_Database::getConfig('prefix') . 'core_user`';
    }

    /**
     * Startup
     *
     * @param Ncw_Controller &$controller the controller
     *
     * @return voind
     */
    public function startup(Ncw_Controller &$controller)
    {
        $controller->acl = $this;
    }

    /**
     * Reads alls ACOS for the given group id
     *
     * @param int    $user_id the user id
     * @param string $start   the ACO from which the read begins.
     *
     * @return void
     */
    public function read($user_id, $start)
    {
        $sql = "SELECT `aco3`.`id`,
\t\t\t\t\t   `aco3`.`alias`,
\t\t\t\t\t   `aco3`.`lft`,
\t\t\t\t\t   `aco3`.`rgt`,
\t\t\t\t\t   `join`.`access`
\t\t\t\tFROM " . $this->_table . " AS `aco`,
\t\t\t\t\t " . $this->_table . " AS `aco2`,
\t\t\t\t\t " . $this->_table . " AS `aco3`
\t\t\t\tLEFT JOIN " . $this->_join_table . " AS `join`
\t\t\t\tON `aco3`.`id`=`join`.`aco_id`
\t\t\t\tLEFT JOIN " . $this->_usergroups_users_table . " AS `usergroups_users`
\t\t\t\tON `join`.`aro_id`=`usergroups_users`.`usergroup_id`
\t\t\t\tLEFT JOIN " . $this->_users_table . " AS `users`
\t\t\t\tON `usergroups_users`.`user_id`=`users`.`id`
\t\t\t\tWHERE `aco3`.`lft` BETWEEN `aco2`.`lft` AND `aco2`.`rgt`
\t\t\t\t\t  AND `aco3`.`lft` BETWEEN `aco`.`lft` AND `aco`.`rgt`
\t\t\t\t\t  AND `aco`.`alias`=:alias
\t\t\t\t\t  AND `users`.`id`=:id
\t\t\t\tGROUP BY `aco3`.`lft`
\t\t\t\tORDER BY `aco3`.`lft`";
        $conn = Ncw_Database::getInstance();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':alias', $start, PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $this->_acos[$row['alias']] = $row;
        }
        $this->_is_acos_read = true;
    }

    /**
     * reads all acos
     *
     * @return array the acos
     */
    public function readAllACOS()
    {
        $sql = 'SELECT `aco3`.`id`,
                       `aco3`.`alias`,
                       `aco3`.`lft`,
                       `aco3`.`rgt`
                FROM ' . $this->_table . ' AS `aco`,
                     ' . $this->_table . ' AS `aco2`,
                     ' . $this->_table . ' AS `aco3`
                WHERE `aco3`.`lft` BETWEEN `aco2`.`lft` AND `aco2`.`rgt`
                      AND `aco3`.`lft` BETWEEN `aco`.`lft` AND `aco`.`rgt`
                GROUP BY `aco3`.`lft`
                ORDER BY `aco3`.`lft`';
        $conn = Ncw_Database::getInstance();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $acos = array();
        foreach ($rows as $row) {
            $acos[$row['alias']] = $row;
        }
        return $acos;
    }

    /**
     * reads all acos by an aro id
     *
     * @param int $aro_id the aro id
     *
     * @return array the acos
     */
    public function readACOSByARO($aro_id)
    {
        $sql = 'SELECT `aco3`.`id`,
                       `aco3`.`alias`,
                       `aco3`.`lft`,
                       `aco3`.`rgt`,
                       `join`.`access`
                FROM ' . $this->_table . ' AS `aco`,
                     ' . $this->_table . ' AS `aco2`,
                     ' . $this->_table . ' AS `aco3`
                LEFT JOIN ' . $this->_join_table . ' AS `join`
                ON `aco3`.`id`=`join`.`aco_id`
                WHERE `aco3`.`lft` BETWEEN `aco2`.`lft` AND `aco2`.`rgt`
                      AND `aco3`.`lft` BETWEEN `aco`.`lft` AND `aco`.`rgt`
                      AND `join`.`aro_id`=:aro_id
                GROUP BY `aco3`.`lft`
                ORDER BY `aco3`.`lft`';
        $conn = Ncw_Database::getInstance();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':aro_id', $aro_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $acos = array();
        foreach ($rows as $row) {
            $acos[$row['alias']] = $row;
        }
        return $acos;
    }

    /**
     * Returns an array of the read ACOS.
     *
     * @return void
     */
    public function getACOS()
    {
        return $this->_acos;
    }

    /**
     * Checks if the acos are already read
     *
     * @return boolean
     */
    public function getIsAcosRead()
    {
        return $this->_is_acos_read;
    }

    /**
     * Checks if the set group has got the permission for the given aco.
     *
     * @param string $aco the aco to check
     *
     * @return boolean
     */
    public function check($aco)
    {
        $arr_aco = explode('/', $aco);
        $return = false;
        $aco = '';
        if (true === isset($this->_acos[$aco])) {
            $return = $this->_acos[$aco]['access'];
        }
        $num = count($arr_aco);
        for ($count = 1; $count < $num; ++$count) {
            if (true === isset($arr_aco[$count]) && false === empty($arr_aco[$count])) {
                $aco .= '/' . $arr_aco[$count];
                if (true === isset($this->_acos[$aco])) {
                    $return = $this->_acos[$aco]['access'];
                }
            } else {
                break;
            }
        }
        return (bool) $return;
    }

    /**
     * Adds an new ACO if it is not already.
     *
     * @param string $aco the control object to add
     *
     * @return boolean
     */
    public function addACO($aco)
    {
        $arr_aco = explode('/', $aco);
        $acos = array();
        $alias = '';
        $parent = '';
        $num = count($arr_aco);
        for ($count = 1; $count < $num; ++$count) {
            if (true === isset($arr_aco[$count]) && false === empty($arr_aco[$count])) {
                $parent = $alias;
                $alias = $alias . '/' . $arr_aco[$count];
                $acos[] = array('alias' => $alias, 'parent' => $parent);
            } else {
                break;
            }
        }
        unset($parent, $alias);
        $conn = Ncw_Database::getInstance();
        $conn->beginTransaction();
        try {
            foreach ($acos as $aco) {
                $stmt = $conn->prepare('SELECT count(1) AS count FROM  ' . $this->_table . ' WHERE `alias`=:alias');
                $stmt->bindValue(':alias', $aco['alias'], PDO::PARAM_STR);
                if (false === $stmt->execute()) {
                    $error_info = $stmt->errorInfo();
                    throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
                }
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($row[0]['count'] < 1) {
                    $stmt = $conn->prepare('SELECT `rgt` FROM ' . $this->_table . ' WHERE `alias`=:parent');
                    $stmt->bindValue(':parent', $aco['parent'], PDO::PARAM_STR);
                    if (false === $stmt->execute()) {
                        $error_info = $stmt->errorInfo();
                        throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
                    }
                    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($row[0]['rgt'] < 0) {
                        throw new Ncw_Exception('Insert failed (Parents right value is null)', 1);
                    }
                    $stmt = $conn->prepare('UPDATE ' . $this->_table . ' SET `rgt`=`rgt`+2 WHERE `rgt`>=:right_value');
                    $stmt->bindValue(':right_value', $row[0]['rgt'], PDO::PARAM_STR);
                    if (false === $stmt->execute()) {
                        $error_info = $stmt->errorInfo();
                        throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
                    }
                    $stmt = $conn->prepare('UPDATE ' . $this->_table . ' SET `lft`=`lft`+2 WHERE `lft`>:right_value');
                    $stmt->bindValue(':right_value', $row[0]['rgt'], PDO::PARAM_STR);
                    if (false === $stmt->execute()) {
                        $error_info = $stmt->errorInfo();
                        throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
                    }
                    $stmt = $conn->prepare('INSERT INTO ' . $this->_table . ' (`alias`, `lft`, `rgt`) VALUES (:alias, :left_value, :right_value)');
                    $stmt->bindValue(':left_value', $row[0]['rgt'], PDO::PARAM_STR);
                    $stmt->bindValue(':right_value', $row[0]['rgt'] + 1, PDO::PARAM_STR);
                    $stmt->bindValue(':alias', $aco['alias'], PDO::PARAM_STR);
                    if (false === $stmt->execute()) {
                        $error_info = $stmt->errorInfo();
                        throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
                    }
                }
            }
        } catch (Ncw_Exception $e) {
            $conn->rollBack();
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        $conn->commit();
        return true;
    }

    /**
     * Removes an ACO.
     *
     * @param string $aco the control object to remove
     *
     * @return boolean
     */
    public function removeACO($aco)
    {
        // Delete the ACO
        $conn = Ncw_Database::getInstance();
        $conn->beginTransaction();
        $stmt = $conn->prepare('SELECT `rgt`, `lft` FROM ' . $this->_table . ' WHERE `alias`=:alias');
        $stmt->bindValue(':alias', $aco, PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($row[0]['rgt'] <= 0 || $row[0]['lft'] <= 0) {
            throw new Ncw_Exception('Insert failed (Nodes right or left value is null)', 1);
        }
        $stmt = $conn->prepare('DELETE FROM ' . $this->_table . ' WHERE `lft` BETWEEN :left_value AND :right_value');
        $stmt->bindValue(':left_value', $row[0]['lft'], PDO::PARAM_INT);
        $stmt->bindValue(':right_value', $row[0]['rgt'], PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $conn->prepare('UPDATE ' . $this->_table . ' SET `lft`=`lft`-ROUND((:right_value-:left_value+1)) WHERE `lft`>:right_value');
        $stmt->bindValue(':left_value', $row[0]['lft'], PDO::PARAM_INT);
        $stmt->bindValue(':right_value', $row[0]['rgt'], PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $stmt = $conn->prepare('UPDATE ' . $this->_table . ' SET `rgt`=`rgt`-ROUND((:right_value-:left_value+1)) WHERE `rgt`>:right_value');
        $stmt->bindValue(':left_value', $row[0]['lft'], PDO::PARAM_INT);
        $stmt->bindValue(':right_value', $row[0]['rgt'], PDO::PARAM_INT);
        if (false === $stmt->execute()) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
        }
        $conn->commit();
        return true;
    }

    /**
     * Allows the acces to the aco for the given group
     *
     * @param int    $group_id the group id
     * @param string $aco      the aco
     *
     * @return boolean
     */
    public function allow($group_id, $aco)
    {
        if ($group_id > 0) {
            // Check If this node already is in the database.
            $conn = Ncw_Database::getInstance();
            $stmt = $conn->prepare(
                'SELECT `id` '
                . 'FROM ' . $this->_table
                . 'WHERE `alias`=:alias'
            );
            $stmt->bindValue(':alias', $aco, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (true === isset($row[0]['id']) && $row[0]['id'] > 0) {
                // Set the access.
                $stmt = $conn->prepare(
                    'REPLACE INTO ' . $this->_join_table
                    . '(`aro_id`, `aco_id`, `access`)'
                    . 'VALUES (:aro_id, :aco_id, 1)'
                );
                $stmt->bindValue(':aro_id', $group_id, PDO::PARAM_INT);
                $stmt->bindValue(':aco_id', $row[0]['id'], PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
        return false;
    }

    /**
     * Denies the acces to the aco for the given group
     *
     * @param int    $group_id the group id
     * @param string $aco      the aco
     *
     * @return boolean
     */
    public function deny($group_id, $aco)
    {
        if ($group_id > 0) {
            // Check If this node allready is in the database.
            $conn = Ncw_Database::getInstance();
            $stmt = $conn->prepare(
                'SELECT `id`'
                . 'FROM ' . $this->_table
                . 'WHERE `alias`=:alias'
            );
            $stmt->bindValue(':alias', $aco, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (true === isset($row[0]['id']) && $row[0]['id'] > 0) {
                // Set the access.
                $stmt = $conn->prepare(
                    'REPLACE INTO ' . $this->_join_table
                    . '(`aro_id`, `aco_id`, `access`)'
                    . 'VALUES (:aro_id, :aco_id, 0)'
                );
                $stmt->bindValue(':aro_id', $group_id, PDO::PARAM_INT);
                $stmt->bindValue(':aco_id', $row[0]['id'], PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
        return false;
    }

    /**
     * Removes an access
     *
     * @param int    $group_id the group id
     * @param string $aco      the aco
     *
     * @return boolean
     */
    public function remove($group_id, $aco)
    {
        if ($group_id > 0) {
            // Check If this node allready is in the database.
            $conn = Ncw_Database::getInstance();
            $stmt = $conn->prepare(
                'SELECT `id`'
                . 'FROM ' . $this->_table
                . 'WHERE `alias`=:alias'
            );
            $stmt->bindValue(':alias', $aco, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (true === isset($row[0]['id']) && $row[0]['id'] > 0) {
                // Set the access.
                $stmt = $conn->prepare(
                    'DELETE FROM ' . $this->_join_table
                    . ' WHERE `aro_id`=:aro_id && `aco_id`=:aco_id'
                );
                if (false !== $stmt) {
                    $stmt->bindValue(':aro_id', $group_id, PDO::PARAM_INT);
                    $stmt->bindValue(':aco_id', $row[0]['id'], PDO::PARAM_INT);
                    return $stmt->execute();
                }
            }
        }
        return false;
    }
}
?>
