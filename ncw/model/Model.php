<?php
/**
 * Contains the model class.
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
 * The model class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
abstract class Ncw_Model extends Ncw_DataModel
{

    /**
     * The database connection object
     *
     * @var Ncw_Database
     */
    public $db = null;

    /**
     * The id of the last created row of this object.
     *
     * @var int
     */
    public $insert_id = 0;

    /**
     * The num of the rows found by the fetch method.
     *
     * @var int
     */
    public $num_rows = 0;

    /**
     * The database table name of this object.
     *
     * @var string
     */
    public $db_table_name = '';

    /**
     * The validation array.
     *
     * @var array
     */
    public $validation = array();

    /**
     * The one to one associations.
     *
     * @var array
     */
    public $has_one = array();

    /**
     * The belongs to associations.
     *
     * @var array
     */
    public $belongs_to = array();

    /**
     * The one to many associations.
     *
     * @var array
     */
    public $has_many = array();

    /**
     * The backup of the associations.
     *
     * @var array
     */
    public $associations_backup = array();

    /**
     * The Validator object.
     *
     * @var Ncw_Validator
     */
    public $validator = null;

    /**
     * Array with all model names.
     *
     * @var Array
     */
    public static $names = array();

    /**
     * Constructor
     *
     */
    public final function __construct ()
    {
        // set the names
        $model_class = get_class($this);
        if (true === isset(self::$names[$model_class])) {
            $this->module_name = self::$names[$model_class]['module_name'];
            $this->name = self::$names[$model_class]['name'];
            $this->db_table_name = self::$names[$model_class]['db_table_name'];
        } else {
            // name and module name
            $arr_class_name = explode('_', $model_class);
            $this->module_name = $arr_class_name[0];
            $this->name = $arr_class_name[1];
            // db table name
            $arr_class_name[1][0] = strtolower($arr_class_name[1][0]);
            $arr_class_name[1] = preg_replace(
                "([A-Z])", "_\\0",
                $arr_class_name[1]
            );
            $this->db_table_name = strtolower(
                Ncw_Database::getConfig('prefix') . $this->module_name . "_" . $arr_class_name[1]
            );
            unset($arr_class_name);
            self::$names[$model_class] = array(
                'name' => $this->name,
                'module_name' => $this->module_name,
                'db_table_name' => $this->db_table_name
            );
        }
        // Get the model fields
        $fields = Ncw_Describer::getModelFields(
            $model_class,
            $this->db_table_name
        );
        foreach ($fields as $field) {
            ;
            $this->data[$field] = "";
        }
        // Backup the associations
        $this->associations_backup = array(
            'has_one' => $this->has_one,
            'belongs_to' => $this->belongs_to,
            'has_many' => $this->has_many
        );
        // Set the db attribute
        $this->db = Ncw_Database::getInstance();
    }

    /**
     * Initializes the validator if it has not happened yet.
     *
     * @return void
     */
    protected final function _initValidator ()
    {
        if (false === $this->validator instanceof Ncw_Validator) {
            // Init the validator
            $this->validator = new Ncw_Validator(
                $this->validation,
                array('db_table' => $this->db_table_name)
            );
        }
    }

    /**
     * Fetches entries.
     *
     * @param string $type   (first, all, list) (optional)
     * @param array  $params (conditions, fields, order, group, limit) (optional)
     *
     * @return mixed
     */
    public final function fetch ($type = 'first', Array $params = array())
    {
        if (false === is_string($type)) {
            throw new Ncw_Exception('$type must be of type string');
        }
        if (false === $this->beforeFetch()) {
            return false;
        }
        $params = array_merge(
            array(
                'conditions' => array(),
                'fields' => array(),
                'order' => array(),
                'group' => array(),
                'limit' => array(),
            ),
            $params
        );
        $params['type'] = array();
        $params['has_one_tables'] = array();
        $params['has_one_fields'] = array();
        $params['belongs_to_tables'] = array();
        $params['belongs_to_fields'] = array();
        $params['has_one_join_conditions'] = array();
        $params['has_one_foreign_keys'] = array();
        $params['belongs_to_join_conditions'] = array();
        $params['belongs_to_foreign_keys'] = array();
        if ($type === "first") {
            $params['limit'] = 1;
        }
        $params['type'] = $type;
        // prepare the model associations.
        $params = $this->_prepareFetchAssociations($params);
        // get the sql
        list($sql, $conditions) = $this->_prepareFetch($params);
        // do the query.
        $stmt = $this->db->prepare($sql);
        if (false === $stmt) {
            $error_info = $this->db->errorInfo();
            throw new Ncw_Exception('Prepare of fetch failed (' . $error_info[2] . ')', 1);
        }
        if (false === $stmt->execute($conditions)) {
            $error_info = $stmt->errorInfo();
            throw new Ncw_Exception('Fetch failed (' . $error_info[2] . ')', 1);
        }
        switch ($type) {
        case "list" :
            return $this->_fetchList($stmt, $params);
            break;

        case "array" :
            return $this->_fetchArray($stmt);
            break;

        case 'count' :
            return $this->_fetchCount($stmt);
            break;

        default :
            return $this->_fetchObjects($stmt, $params);
        }
    }

    /**
     * Builds the SQL for the fetch functions
     *
     * @param array $params the fetch parameters
     *
     * @return string
     */
    protected function _prepareFetch (Array $params)
    {
        // build the sql.
        $sql = "SELECT ";
        if ($params['type'] !== 'count') {
            $sql .= $this->_prepareFetchFields($params);
        } else {
        	$sql .= 'count(1) AS \'count\' ';
        }
        $sql .= " FROM `" . $this->db_table_name . "` AS `" . $this->name . "`";
        $sql .= $this->_prepareFetchJoins($params);
        // where conditions
        $conditions = array();
        if (false === empty($params['conditions'])) {
            $sql .= " WHERE ";
            list($sql_conditions, $conditions) = $this->_prepareFetchConditions($params);
            $sql .= $sql_conditions;
            unset($sql_conditions);
        }
        // group
        if (false === empty($params['group'])) {
            $sql .= " GROUP BY ";
            $first = true;
            foreach ($params['group'] as $group) {
                if (false === $first) {
                    $sql .= ",";
                }
                if (false === strpos($group, '.')) {
                    $group = $this->name . '.' . $group;
                }
                $sql .= $group;
                $first = false;
            }
        }
        // order
        if (false === empty($params['order'])) {
            $sql .= " ORDER BY ";
            $first = true;
            if (true === is_string($params['order'])) {
                $params['order'] = array($params['order']);
            }
            foreach ($params['order'] as $key => $value) {
                $direction = '';
                if (true === is_string($key)) {
                    $direction = $value;
                    $value = $key;
                }
                if (false === $first) {
                    $sql .= ",";
                }
                if (false === strpos($value, '.')) {
                    $value = $this->name . '.' . $value;
                }
                if (false === empty($direction)) {
                    $value .= ' ' . $direction;
                }
                $sql .= $value;
                $first = false;
            }
        }
        // limitations
        if (false === empty($params['limit'])) {
            if (is_array($params['limit'])
                && isset($params['limit'][1])
                && is_int($params['limit'][1])
            ) {
                $sql .= " LIMIT " . $params['limit'][0] . ","
                    . $params['limit'][1];
            } else {
                $sql .= " LIMIT " . $params['limit'];
            }
        }
        return array($sql, $conditions);
    }

    /**
     * Get the fields for the current fetch.
     *
     * @param array $params the fetch parameters
     *
     * @return string the fields
     */
    protected final function _prepareFetchFields (Array $params)
    {
        $sql = '';
        // fields
        if (false === empty($params['fields'])) {
            $first = true;
            foreach ($params['fields'] as $key => $field) {
                if (false === $first) {
                    $sql .= ",";
                }
                $first = false;
                if (true === is_string($key)) {
                    $sql .= $field . ' AS `' . $key . '`';
                } else {
                    if (false === strpos($field, '.')) {
                        $sql .= $field;
                    } else {
                        $sql .= $field . ' AS `' . $field . '`';
                    }
                }
            }
        } else {
            $first = true;
            foreach (array_keys($this->data) as $field) {
                if (false === $first) {
                    $sql .= ",";
                }
                $first = false;
                $sql .= "`" . $this->name . "`.`" . $field . "` AS `" . $this->name . "." . $field . "`";
            }
            // add the has one association fields
            if (false === empty($params['has_one_fields'])) {
                $sql .= ",";
                $first = true;
                foreach ($params['has_one_fields'] as $field) {
                    if (false === $first) {
                        $sql .= ",";
                    }
                    $first = false;
                    $sql .= $field . " AS " . "`" . $field . "`";
                }
            }
            // add the belongs to association fields
            if (false === empty($params['belongs_to_fields'])) {
                $sql .= ",";
                $first = true;
                foreach ($params['belongs_to_fields'] as $field) {
                    if (false === $first) {
                        $sql .= ",";
                    }
                    $first = false;
                    $sql .= $field . " AS " . "`" . $field . "`";
                }
            }
        }
        return $sql;
    }

    /**
     * Get the joins for the current fetch.
     *
     * @param array $params the fetch parameters
     *
     * @return string the joins
     */
    protected final function _prepareFetchJoins (Array $params)
    {
        $sql = '';
        // Add the belongs to associations join
        foreach ($params['belongs_to_tables'] as $association => $table) {
            $sql .= " LEFT JOIN `" . $table . "` AS `" . $association . "`";
            if (false === isset($params['belongs_to_join_conditions'][$association])) {
                $fk = '`' . $this->name . '`.'
                    . $params['belongs_to_foreign_keys'][$association];
                $sql .= ' ON ' . $fk . '=`' . $association . '`.`id`';
            } else {
                $sql .= ' ON ' . $params['belongs_to_join_conditions'][$association];
            }
        }
        // Add the has one associations join
        foreach ($params['has_one_tables'] as $association => $table) {
            $sql .= " LEFT JOIN `" . $table . "` AS `" . $association . "`";
            if (false === isset($params['has_one_join_conditions'][$association])) {
                $fk = '`' . $association . '`.'
                    . $params['has_one_foreign_keys'][$association];
                $sql .= ' ON `' . $this->name . '`.`id`=' . $fk;
            } else {
                $sql .= ' ON ' . $params['has_one_join_conditions'][$association];
            }
        }
        return $sql;
    }

    /**
     * Get the conditions for the current fetch.
     *
     * @param array   $params     the fetch parameters
     * @param boolean $nested_set set to true if the model is a nested set (optional)
     * @param boolean $nice_keys  (optional)
     *
     * @return array the sql and the conditions
     */
    protected final function _prepareFetchConditions (Array $params, $nested_set = false, $nice_keys = true)
    {
        $sql = '';
        $conditions = array();
        $count = 1;
        foreach ($params['conditions'] as $key => $condition) {
            $operation = "=";
            $sql_connector = '&&';
            if (true === is_integer($key)) {
                if (true === $nested_set || $count > 1) {
                    $sql .= ' ' . $sql_connector . ' ';
                }
                $sql .= $condition;
            } else {
                if (false !== strpos($key, ' ')) {
                    $key = explode(' ', $key);
                    if (true === in_array($key[0], array('&&', 'and', '||', 'or'))) {
                        $sql_connector = $key[0];
                        if (true === isset($key[2])) {
                            $operation = $key[2];
                        }
                        $key = $key[1];
                    } else {
                        if (true === isset($key[1])) {
                            $operation = $key[1];
                        }
                        $key = $key[0];
                    }
                }
                if (true === $nested_set || $count > 1) {
                    $sql .= ' ' . $sql_connector . ' ';
                }
                if (true === $nice_keys && false === strpos($key, '.')) {
                    $key = $this->name . '.' . $key;
                }
                $sql .= $key . ' ' . $operation . ' :' . $count . "";
                $conditions[":" . $count] = $condition;
            }
            ++$count;
        }
        return array($sql, $conditions);
    }

    /**
     * Prepares the has one and belongs to associations for the fetch.
     *
     * @param array $params the fetch parameters
     *
     * @return Array
     */
    protected final function _prepareFetchAssociations (Array $params)
    {
        if (false === empty($this->has_one)) {
            foreach ($this->has_one as $key => $association) {
                $field_params = false;
                if (true === is_array($association)) {
                    $name = $key;
                    if (false === empty($association['foreign_key'])) {
                        $params['has_one_foreign_keys'][$name] = $association['foreign_key'];
                        unset($association['foreign_key']);
                    }
                    if (false === empty($association['join_condition'])) {
                        $params['has_one_join_conditions'][$name] = $association['join_condition'];
                        unset($association['join_condition']);
                    }

                    if (true === isset($association['fields'])) {
                        $field_params = true;
                        $params['has_one_fields'] = array_merge(
                            $association['fields'],
                            $params['has_one_fields']
                        );
                        unset($association['fields']);
                    }

                    $params = array_merge_recursive($association, $params);
                } else {
                    $name = $association;
                }
                $class_name = $name;
                if (false === isset($params['has_one_foreign_keys'][$name])) {
                    if (false === strpos($class_name, "_")) {
                        $params['has_one_foreign_keys'][$name] = strtolower($this->name) . '_id';
                    } else {
                        $params['has_one_foreign_keys'][$name] = array_pop(
                            explode('_', strtolower($this->name))
                        ) . '_id';
                    }
                }
                if (false === strpos($class_name, "_")) {
                    $class_name = $this->module_name . "_" . $class_name;
                }
                $obj_association = new $class_name();
                $params['has_one_tables'][$name] = self::$names[$class_name]['db_table_name'];

                if (false === $field_params) {
                    $fields = array_keys($obj_association->data());
                    foreach ($fields as $field) {
                         $params['has_one_fields'][] = $name . '.' . $field;
                    }
                }
            }
        }
        if (false === empty($this->belongs_to)) {
            foreach ($this->belongs_to as $key => $association) {
                $field_params = false;
                if (true === is_array($association)) {
                    $name = $key;
                    if (false === empty($association['foreign_key'])) {
                        $params['belongs_to_foreign_keys'][$name] = $association['foreign_key'];
                        unset($association['foreign_key']);
                    }
                    if (false === empty($association['join_condition'])) {
                        $params['belongs_to_join_conditions'][$name] = $association['join_condition'];
                        unset($association['join_condition']);
                    }

                    if (true === isset($association['fields'])) {
                        $field_params = true;
                        $params['belongs_to_fields'] = array_merge(
                            $association['fields'],
                            $params['belongs_to_fields']
                        );
                        unset($association['fields']);
                    }

                    $params = array_merge_recursive($association, $params);
                } else {
                    $name = $association;
                }
                $class_name = $name;
                if (false === isset($params['has_one_foreign_keys'][$name])) {
                    if (false === strpos($class_name, "_")) {
                        $params['belongs_to_foreign_keys'][$name] = strtolower($name) . '_id';
                    } else {
                        $params['belongs_to_foreign_keys'][$name] = array_pop(
                            explode('_', strtolower($name))
                        ) . '_id';
                    }
                }
                if (false === strpos($class_name, "_")) {
                    $class_name = $this->module_name . "_" . $class_name;
                }
                $obj_association = new $class_name();
                $params['belongs_to_tables'][$name] = self::$names[$class_name]['db_table_name'];

                if (false === $field_params) {
                    $fields = array_keys($obj_association->data());
                    foreach ($fields as $field) {
                         $params['belongs_to_fields'][] = $name . '.' . $field;
                    }
                }
            }
        }
        return $params;
    }

    /**
     * Return the fetch as a list.
     *
     * @param PDOStatement $stmt   the pdo statement
     * @param Array        $params the fetch parameters
     *
     * @return Array
     */
    protected final function _fetchList (PDOStatement $stmt, Array $params)
    {
        // Get the num of rows.
        $this->num_rows = count($all_rows = $stmt->fetchAll(PDO::FETCH_NUM));
        $list = array();
        $num = count($params['fields']);
        foreach ($all_rows as $row) {
            if ($num > 1) {
                $list[$row[0]] = $row[1];
            } else {
                $list[$row[0]] = $row[0];
            }
        }
        $this->_resetAssociations();
        return $list;
    }

    /**
     * Return the fetch as an array
     *
     * @param PDOStatement $stmt the pdo statement
     *
     * @return Array
     */
    protected final function _fetchArray (PDOStatement $stmt)
    {
        $this->num_rows = count($all_rows = $stmt->fetchAll(PDO::FETCH_ASSOC));
        $result = array();
        $row_count = 0;
        foreach ($all_rows as $row) {
            foreach ($row as $field => $value) {
                $field = explode(".", $field);
                if (true === isset($field[1])) {
                    $result[$row_count][$field[0]][$field[1]] = $value;
                }
            }
            ++$row_count;
        }
        $this->_resetAssociations();
        return $result;
    }

    /**
     * Count fetch method
     *
     * @param PDOStatement $stmt the pdo statement
     *
     * @return int
     */
    protected final function _fetchCount (PDOStatement $stmt)
    {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows[0]['count']) {
            return $rows[0]['count'];
        }
        return 0;
    }

    /**
     * Return the fetch as a object list
     *
     * @param PDOStatement $stmt   the fetch parameters
     * @param Array        $params the pdo statement
     *
     * @return Ncw_ModelList
     */
    protected final function _fetchObjects (PDOStatement $stmt, Array $params)
    {
        // Get the num of rows.
        $this->num_rows = count($all_rows = $stmt->fetchAll(PDO::FETCH_ASSOC));
        $result = new Ncw_ModelList();
        foreach ($all_rows as $row) {
            $model = new Ncw_DataModel($this->name, $this->module_name);
            $associations = array_merge($this->has_one, $this->belongs_to);
            if (false === empty($associations)) {
                // initialize the has one and belongs to association objects
                foreach ($associations as $key => $association) {
                    if (true === is_array($association)) {
                        $association_name = $model_name = $key;
                    } else {
                        $association_name = $model_name = $association;
                    }
                    if (false === strpos($model_name, '_')) {
                        $module_name = $this->module_name;
                    } else {
                        $arr_name = explode('_', $model_name);
                        $module_name = $arr_name[0];
                        $model_name = $arr_name[1];
                    }
                    $associated_model = new Ncw_DataModel($model_name, $module_name);
                    // Include the model class of the association
                    $model->addAssociatedModel($associated_model, $association_name);
                }
            }
            // set the fields.
            foreach ($row as $field => $value) {
                $field = explode(".", $field);
                if (true === isset($field[1])) {
                    if ($field[0] === $this->name) {
                        $model->data[$field[1]] = $value;
                    } else {
                        $model->associated_models[$field[0]]->data[$field[1]] = $value;
                    }
                }
            }
            // if the model has one to many associations then read the associated objects.
            if (false === empty($this->has_many)) {
                foreach ($this->has_many as $key => $association) {
                    $options = array();
                    if (true === is_array($association)) {
                        $name = $key;
                        $options = $association;
                    } else {
                        $name = $association;
                    }
                    $class_name = $name;
                    if (false === strpos($class_name, "_")) {
                        $class_name = $this->module_name . "_" . $class_name;
                    }
                    // init the model object and read all
                    // which are associated to the current model
                    $associated_model = new $class_name();
                    $unbind = array("belongs_to" => array($this->name));
                    if (true === isset($options['unbind'])
                        && true === is_array($options['unbind'])
                    ) {
                        $unbind = array_merge($unbind, $options['unbind']);
                    }
                    $associated_model->unbindModel($unbind);
                    if (false === isset($options['foreign_key'])) {
                        $fk = strtolower($this->name) . '_id';
                    } else {
                        $fk = $options['foreign_key'];
                    }
                    $model->addAssociatedModelList(
                        $associated_model->findAllBy(
                            $fk,
                            $model->getId(),
                            $options
                        ),
                        $name
                    );
                }
            }
            unset($class_name, $associated_model);
            $result->addModel($model);
        }
        $this->_resetAssociations();
        if ($params['type'] === "first") {
            if (true === isset($result[0])) {
                return $result[0];
            }
            return false;
        }
        return $result;
    }

    /**
     * Is called before any fetch, read
     *
     * @return void
     */
    public function beforeFetch ()
    {

    }

    /**
     * Resets the associations
     *
     * @return void
     */
    protected final function _resetAssociations ()
    {
        $this->has_one = $this->associations_backup['has_one'];
        $this->belongs_to = $this->associations_backup['belongs_to'];
        $this->has_many = $this->associations_backup['has_many'];
    }

    /**
     * FindBy field method. Finds the first occurance.
     *
     * @param string $field  the field to find by
     * @param mixed  $value  the value to search
     * @param Array  $params the fetch parameters (optional)
     *
     * @return Ncw_DataModel the found model
     */
    public final function findBy ($field, $value, Array $params = array())
    {
        if (false === is_string($field)) {
            throw new Ncw_Exception('$field must be of type string');
        }
        $params = array_merge(array("conditions" => array()), $params);
        $params['conditions'][$this->name . "." . $field] = $value;
        return $this->fetch('first', $params);
    }

    /**
     * FindAllBy field method.
     *
     * @param string $field  the field to find by
     * @param mixed  $value  the value to search
     * @param Array  $params the fetch parameters (optional)
     *
     * @return Ncw_ModelList the found models
     */
    public final function findAllBy ($field, $value, Array $params = array())
    {
        if (false === is_string($field)) {
            throw new Ncw_Exception('$field must be of type string');
        }
        $params = array_merge(array("conditions" => array()), $params);
        $params['conditions'][$this->name . "." . $field] = $value;
        return $this->fetch('all', $params);
    }

    /**
     * Reads the model data from the database.
     *
     * @param Array $params the fetch parameters (optional)
     *
     * @return boolean
     */
    public final function read (Array $params = array())
    {
        if ($this->getId() > 0) {
            $params = array_merge(array("conditions" => array()), $params);
            $params['conditions']["`" . $this->name . "`.`id`"] = $this->getId();
            $model = $this->fetch('first', $params);
            if (true === $model instanceof Ncw_DataModel) {
                $this->copyFrom($model);
                return true;
            }
        }
        return false;
    }

    /**
     * Reads a single field of the model and returns the value
     *
     * @param string $field   the field to read
     * @param Array  $options the fetch options (optional)
     *
     * @return mixed the field value or false at failure
     */
    public final function readField ($field, $options = array())
    {
        if (false === is_string($field)) {
            throw new Ncw_Exception('$field must be of type string');
        }
        if ($this->getId() > 0) {
            $options = array_merge(array('conditions' => array(), 'fields' => array()), $options);
            $options['conditions'][$this->name . '.id'] = $this->getId();
            $options['fields'][] = $this->name . '.' . $field;
            $list = $this->fetch('list', $options);
            return key($list);
        }
        return false;
    }

    /**
     * Creates or updates the model.
     *
     * @param boolean $validate     set to false
     *        if you don't want to validate (optional)
     * @param boolean $dependencies set to false
     *        if you don't want to associated models (optional)
     *
     * @return boolean
     */
    public final function save ($validate = true, $dependencies = true)
    {
        if (false === is_bool($validate)) {
            throw new Ncw_Exception('$validate must be of type boolean');
        }
        if (false === is_bool($dependencies)) {
            throw new Ncw_Exception('$dependencies must be of type boolean');
        }
        if (false === $this->_doBeforeSave()) {
            return false;
        }
        $return = false;
        // validate the model data
        if (false === $validate || true === $this->validate($dependencies)) {
            // If a id is already set do a update.
            if ($this->getId() > 0) {
                $modified_field = false;
                $data = array();
                foreach ($this->data as $field => $value) {
                    if ($field != "id" && $field != "created" && $field != "modified") {
                        $data[$field] = $value;
                    } else if ($field === "created") {
                        $modified_field = true;
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
                if (true === $modified_field) {
                    $fields .= ',`modified`=NOW()';
                }
                $return = $this->_doUpdate($fields, $values);
            } else {
                $created_field = false;
                // If no id is set do an insert.
                $data = array();
                foreach ($this->data as $field => $value) {
                    if (false === empty($value) && ($field != "created" || $field != "modified")) {
                        $data[$field] = $value;
                    } else if ($field === "created") {
                        $created_field = true;
                    }
                }
                $fields = "";
                $holders = "";
                $values = array();
                $count = 1;
                $count_data = count($data);
                foreach ($data as $field => $value) {
                    $fields .= "`" . $field . "`";
                    $holders .= ":" . $field . "";
                    $values[":" . $field] = $value;
                    if ($count < $count_data) {
                        $fields .= ",";
                        $holders .= ",";
                    }
                    ++$count;
                }
                if (true === $created_field) {
                    $fields .= ',`created`';
                    $holders .= ',NOW()';
                }
                $return = $this->_doInsert($fields, $holders, $values);
            }
            if (true === $dependencies) {
                foreach ($this->associated_models as $key => $association) {
                    if (true === $association instanceof Ncw_ModelList) {
                        foreach ($association as $obj_association) {
                            if (false === $obj_association instanceof Ncw_Model) {
                                continue;
                            }
                            if ($obj_association->{"get" . $this->name . "Id"}() == 0) {
                                $obj_association->{"set" . $this->name . "Id"}($this->getId());
                            }
                            $obj_association->save($validate);
                        }
                    } else if (true === in_array($key, $this->has_one) || true === isset($this->has_one[$key])) {
                        if (false === $association instanceof Ncw_Model) {
                            continue;
                        }
                        if ($association->{"get" . $this->name . "Id"}() == 0) {
                            $association->{"set" . $this->name . "Id"}($this->getId());
                        }
                        $association->save($validate);
                    }
                }
            }
        }
        $this->flushCache();
        if (false === $this->_doAfterSave()) {
            $return = false;
        }
        return $return;
    }

    /**
     * Makes the model ready to write it into the database.
     * (What it does: If you save more then one model you need to
     * reset the id, and thats done here.)
     *
     * @return void
     */
    public final function create ()
    {
        $this->setId(0);
    }

    /**
     * Insert a new model entry
     *
     * @param string $fields  the fields
     * @param string $holders the holders
     * @param array  $values  the values
     *
     * @return boolean
     */
    protected function _doInsert ($fields, $holders, Array $values)
    {
        try {
            // Build the sql query.
            $sql = "INSERT INTO `" . $this->db_table_name . "`
                    (";
            $sql .= $fields;
            $sql .= ")
                    VALUES
                    (";
            $sql .= $holders;
            $sql .= ")";
            // Do the query.
            $stmt = $this->db->prepare($sql);
            if (false === $stmt) {
                $error_info = $this->db->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            if (false === $stmt->execute($values)) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
        } catch (Ncw_Exception $e) {
            if (Ncw_Configure::read('debug_mode') > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        $this->insert_id = $this->db->lastInsertId();
        $this->setId($this->insert_id);
        return true;
    }

    /**
     * Updates the model entry
     *
     * @param string $fields the fields
     * @param array  $values the values
     *
     * @return boolean
     */
    protected function _doUpdate ($fields, Array $values)
    {
        if (false === $this->beforeSave()) {
            return false;
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
            if (Ncw_Configure::read('debug_mode') > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        return true;
    }

    /**
     * Saves a field of the model into the database.
     *
     * @param string  $field    the field to save
     * @param boolean $validate set to false if
     *        you don't want to validate the data (optional)
     *
     * @return boolean
     */
    public final function saveField ($field, $validate = true)
    {
        if (false === is_string($field)) {
            throw new Ncw_Exception('$validate must be of type string');
        }
        if (false === is_bool($validate)) {
            throw new Ncw_Exception('$validate must be of type boolean');
        }
        return $this->saveFields(array($field), $validate);
    }

    /**
     * Saves fields of the model into the database.
     *
     * @param string  $fields   the fields to save
     * @param boolean $validate set to false if
     *        you don't want to validate the data (optional)
     *
     * @return boolean
     */
    public final function saveFields (Array $fields, $validate = true)
    {
        if (false === is_bool($validate)) {
            throw new Ncw_Exception('$validate must be of type boolean');
        }
        if (false === $this->_doBeforeSave()) {
            return false;
        }
        if ($this->getId() > 0 && is_array($fields)) {
            $values = array(":id" => $this->getId());
            $count = 1;
            $count_data = count($fields);
            $fields_sql = "";
            $valid = true;
            foreach ($fields as $field) {
                // if the field validation is false then abort the saving.
                if (true === $validate) {
                    $return = $this->validateField($field, $this->data[$field]);
                    if (false === $return) {
                        $valid = false;
                    }
                }
                if (true === $valid) {
                    $fields_sql .= "`" . $field . "`=:" . $field;
                    $values[":" . $field] = $this->data[$field];
                    if ($count < $count_data) {
                        $fields_sql .= ",";
                    }
                    ++$count;
                }
            }
            if (false === $valid) {
                $return = false;
            } else {
                if (true === array_key_exists('modified', $this->data)) {
                    $fields_sql .= ',`modified`=NOW()';
                }
                $return = $this->_doSaveFields($fields_sql, $values);
            }
        }
        $this->flushCache();
        if (false === $this->_doAfterSave()) {
            $return = false;
        }
        return $return;
    }

    /**
     * Saves fields of the model entry
     *
     * @param string $fields_sql the fields
     * @param array  $values     the values
     *
     * @return boolean
     */
    protected function _doSaveFields ($fields_sql, Array $values)
    {
        try {
            // Build the sql query.
            $sql = "UPDATE " . $this->db_table_name . "
                    SET ";
            $sql .= $fields_sql;
            $sql .= " WHERE id=:id";
            // Do the query.
            $stmt = $this->db->prepare($sql);
            if (false === $stmt) {
                $error_info = $this->db->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            if (false === $stmt->execute($values)) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Save fields failed (' . $error_info[2] . ')', 1);
            }
        } catch (Ncw_Exception $e) {
            if (Ncw_Configure::read('debug_mode') > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        return true;
    }

    /**
     * This method is called before the model is saved.
     *
     * @return mixed
     */
    protected function _doBeforeSave ()
    {
        return $this->beforeSave();
    }

    /**
     * This method is called after the model is saved.
     *
     * @return mixed
     */
    protected function _doAfterSave ()
    {
        return $this->afterSave();
    }

    /**
     * This method is called before the model is saved.
     *
     * @return void
     */
    public function beforeSave ()
    {

    }

    /**
     * This method is called after the model is saved.
     *
     * @return void
     */
    public function afterSave ()
    {

    }

    /**
     * Deletes a instance of this model with the set id.
     *
     * @param boolean $dependencies set to false if dependencies must not be deleted. (optional)
     *
     * @return boolean
     */
    public final function delete ($dependencies = true)
    {
        if (false === is_bool($dependencies)) {
            throw new Ncw_Exception('$dependencies must be of type boolean');
        }
        if ($this->getId() > 0) {
            if (false === $this->_doBeforeDelete()) {
                return false;
            }
            if (true === $dependencies) {
                if (false === $this->deleteDependencies()) {
                    return false;
                }
            }
            $this->flushCache();
            // Delete the model object.
            $return = $this->_doDelete($this->getId());
            if (false === $this->_doAfterDelete($return)) {
                $return = false;
            }
            return $return;
        }
        return false;
    }

    /**
     * Deletes the dependencies
     *
     * @return boolean
     */
    protected function deleteDependencies ()
    {
        $this->read();
        // Delete the associated model objects first.
        foreach ($this->associated_models as $model_name => $model_association) {
            if (true === $model_association instanceof Ncw_ModelList) {
                // It is an one to many association. The whole entry can be deleted.
                foreach ($model_association as $association) {
                    if (true === empty($association->module_name)) {
                        throw new Ncw_Exception('module name is not set.');
                    }
                    if (true === empty($association->name)) {
                        throw new Ncw_Exception('name is not set.');
                    }
                    $class_name = $association->module_name . '_' . $association->name;
                    $model = new $class_name();
                    $model->copyFrom($association);
                    if (false === $model->delete()) {
                        return false;
                    }
                }
            } else if (true === in_array($model_name, $this->has_one) || true === isset($this->has_one[$model_name])) {
                if (true === empty($model_association->module_name)) {
                    throw new Ncw_Exception('module name is not set.');
                }
                if (true === empty($model_association->name)) {
                    throw new Ncw_Exception('name is not set.');
                }
                $class_name = $model_association->module_name . '_' . $model_association->name;
                $model = new $class_name();
                $model->copyFrom($model_association);
                if (false === $model->delete()) {
                    return false;
                }
            }
        }
        unset($class_name);
        return true;
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
        try {
            $sql = "DELETE FROM `" . $this->db_table_name . "`
                    WHERE `id`=:id";
            $stmt = $this->db->prepare($sql);
            if (false === $stmt) {
                $error_info = $this->db->errorInfo();
                throw new Ncw_Exception('Insert failed (' . $error_info[2] . ')', 1);
            }
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            if (false === $stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Ncw_Exception('Delete failed (' . $error_info[2] . ')', 1);
            }
        } catch (Ncw_Exception $e) {
            if (Ncw_Configure::read('debug_mode') > 0) {
                $e->exitWithMessage();
            }
            return false;
        }
        return true;
    }

    /**
     * This method is called before the model is saved.
     *
     * @return mixed
     */
    protected function _doBeforeDelete ()
    {
        return $this->beforeDelete();
    }

    /**
     * This method is called after the model is saved.
     *
     * @param boolean $return the do delete method return value
     *
     * @return mixed
     */
    protected function _doAfterDelete ($return)
    {
        return $this->afterDelete($return);
    }

    /**
     * This method is called before the model is deleted.
     *
     * @return void
     */
    public function beforeDelete ()
    {

    }

    /**
     * This method is called after the model is deleted.
     *
     * @param boolean $deleted true if the object was deleted
     *
     * @return void
     */
    public function afterDelete ($deleted)
    {

    }

    /**
     * Deletes more instances of this model.
     *
     * @param array   $conditions   the options array
     * @param boolean $dependencies set to false if dependencies must not be deleted. (optional)
     *
     * @return boolean
     */
    public function deleteAll ($conditions = array(), $dependencies = true)
    {
        if (false === is_bool($dependencies)) {
            throw new Ncw_Exception('$dependencies must be of type boolean');
        }
        if (false === is_array($conditions)) {
            throw new Ncw_Exception('$options must be of type array');
        }
        $ids = $this->fetch(
            'list',
            array(
                'fields' => array($this->name . '.id'),
                'conditions' => $conditions
            )
        );
        $curr_id = $this->getId();
        foreach ($ids as $id) {
            $this->setId($id);
            $this->delete($dependencies);
        }
        $this->setId($curr_id);
    }

    /**
     * Flush the module cache
     *
     * @return void
     */
    protected final function flushCache ()
    {
        if (true === is_dir(TMP . DS . 'cache' . DS . '/' . $this->module_name)) {
            try {
                $cache = new Ncw_Helper_Cache();
                $res = $cache->flush($this->module_name);
                if (true === Cache::isError($res)) {
                    throw new Ncw_Exception($res);
                }
            } catch (Ncw_Exception $e) {
                if (Ncw_Configure::read('debug_mode') > 0) {
                    $e->exitWithMessage();
                }
            }
        }
    }

    /**
     * Unbinds a model for the next fetch
     *
     * @param mixed $associations the associations to unbind
     *
     * @return void
     */
    public final function unbindModel ($associations)
    {
        if (false === is_string($associations) && false === is_array($associations)) {
            throw new Ncw_Exception('$association must be either of type string or array');
        }
        if ($associations === "all") {
            $this->has_one = array();
            $this->belongs_to = array();
            $this->has_many = array();
        } else {
            foreach ($associations as $type => $association) {
                switch ($type) {
                case "has_many" :
                    foreach ($this->has_many as $key => $value) {
                        if (true === in_array($value, $association) || (true === is_array($value) && true === in_array($key, $association))) {
                            unset($this->has_many[$key]);
                        }
                    }
                    break;

                default :
                    foreach ($this->{$type} as $key => $value) {
                        if (true === in_array($value, $association) || (true === is_array($value) && true === in_array($key, $association))) {
                            unset($this->{$type}[$key]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Binds a model for the next fetch
     *
     * @param array|string $associations the associations to bind
     *
     * @return void
     */
    public final function bindModel (Array $associations)
    {
        foreach ($associations as $type => $association) {
            switch ($type) {
            case "has_one" :
                $this->has_one = array_merge_recursive($this->has_one, $association);
                break;

            case "belongs_to" :
                $this->belongs_to = array_merge_recursive($this->belongs_to, $association);
                break;

            case "has_many" :
                $this->has_many = array_merge_recursive($this->has_many, $association);
            }
        }
    }

    /**
     * Sets a model association if conditions are right.
     *
     * @param string $name  the association name
     * @param mixed  $value the assocation object(s)
     *
     * @return void
     */
    public final function __set ($name, $value)
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string');
        }
        if (true === empty($name)) {
            throw new Ncw_Exception('$name can not be empty');
        }
        if (false === $value instanceof Ncw_DataModel && false === $value instanceof Ncw_ModelList) {
            throw new Ncw_Exception('$value must be either a instance of Ncw_DataModel or Ncw_ModelList');
        }
        if (true === is_null($value)) {
            $this->associated_models[$name] = null;
        } else {
            if (false === $value instanceof Ncw_Model && false === $value instanceof Ncw_ModelList) {
                throw new Ncw_Exception('$value must be of Type Ncw_Model or Ncw_ModelList');
            }
            if (true === $value instanceof Ncw_ModelList) {
                $this->addAssociatedModelList($value, $name);
            } else {
                $this->addAssociatedModel($value);
            }
        }
    }

    /**
     * Validates to model data.
     *
     * @param boolean $dependencies set to false if
     *        you don't want to validate (optional)
     *
     * @return boolean
     */
    public final function validate ($dependencies = true)
    {
        if (false === is_bool($dependencies)) {
            throw new Ncw_Exception('$dependencies must be of type boolean');
        }
        if (false === $this->beforeValidate()) {
            return false;
        }
        $this->_initValidator();
        list($return, self::$invalid_fields[$this->name]) = $this->validator->validate($this->data());
        if (true === $dependencies) {
            // Validate the associations
            foreach ($this->associated_models as $model_name => $model_association) {
                if (true === $model_association instanceof Ncw_ModelList) {
                    // It is an one to many association. The whole entry can be deleted.
                    foreach ($model_association as $association) {
                        if (false === $association instanceof Ncw_Model) {
                            continue;
                        }
                        if (false === $association->validate()) {
                            $return = false;
                        }
                    }
                } else if (true === in_array($model_name, $this->has_one) || true === isset($this->has_one[$model_name])) {
                    if (false === $model_association instanceof Ncw_Model) {
                        continue;
                    }
                    $model_association->validate();
                    if (false === $model_association->validate()) {
                        $return = false;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Validates to model data.
     *
     * @param string $field the field to validate
     * @param mixed  $value the value to validate (optional)
     *
     * @return boolean
     */
    public final function validateField ($field, $value = '')
    {
        if (false === is_string($field)) {
            throw new Ncw_Exception('$validate must be of type string');
        }
        if (false === $this->beforeValidate()) {
            return false;
        }
        if (true === empty($value)) {
            $value = $this->data[$field];
        }
        $this->_initValidator();
        if (true === empty($value) && true === array_key_exists($field, $this->data)) {
            $value = $this->data[$field];
        }
        list($return, $message) = $this->validator->validateField($field, $value);
        if (false === empty($message)) {
            self::$invalid_fields[$this->name][$field] = $message;
        }
        return $return;
    }

    /**
     * Invalidates a model field.
     *
     * @param string $field   the field to invalidate
     * @param string $message the error message
     *
     * @return boolean
     */
    public final function invalidateField ($field, $message = false)
    {
        if (false === is_string($field)) {
            throw new Ncw_Exception('$validate must be of type string');
        }
        if (false === empty($field)) {
            $this->_initValidator();
            if (false === $message) {
                $message = $this->validator->invalidateField($field);
            }
            if (false !== $message) {
                self::$invalid_fields[$this->name][$field] = $message;
                return true;
            }
        }
        return false;
    }

    /**
     * Is called before any validation
     *
     * @return void
     */
    public function beforeValidate ()
    {

    }

    /**
     * Sets a attribute
     *
     * @param string $field the field to return
     * @param Array  $args  set to false if
     *        you don't want to validate
     *
     * @return string
     */
    protected final function _attributeSet ($field, Array $args)
    {
        if (false === is_string($field)) {
            throw new Ncw_Exception('$validate must be of type string');
        }
        // If the field must not be validated
        if (true === array_key_exists(1, $args) && false === $args[1]) {
            $this->data[$field] = $args[0];
            return true;
        } else {
            // Validate
            $valid = $this->validateField($field, $args[0]);
            if (true === $valid) {
                $this->data[$field] = $args[0];
                return true;
            }
        }
        return false;
    }

    /**
     * Copies the data and associations form the given data model
     *
     * @param Ncw_DataModel $model the model to copy from
     *
     * @return void
     */
    public final function copyFrom (Ncw_DataModel $model)
    {
        $this->data($model->data());
        $this->associatedModels($model->associatedModels());
    }
}
?>
