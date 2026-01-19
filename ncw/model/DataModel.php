<?php
/**
 * Contains the DataModel class.
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
 * The DataModel class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_DataModel extends Ncw_Object implements IteratorAggregate
{

    /**
     * The model name.
     *
     * @var string
     */
    public $name = "";

    /**
     * The module name.
     *
     * @var string
     */
    public $module_name = "";

    /**
     * The module data.
     *
     * @var array
     */
    public $data = array();

    /**
     * The associated models.
     *
     * @var array
     */
    public $associated_models = array();

    /**
     * The invalid fields of all models
     *
     * @var array
     */
    public static $invalid_fields = array();

    /**
     * Read the fields, getters and setters.
     *
     * @param string $name        the model name
     * @param string $module_name the module name
     */
    public function __construct ($name, $module_name)
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string');
        }
        if (false === is_string($module_name)) {
            throw new Ncw_Exception('$module_name must be of type string');
        }
        $this->name = $name;
        $this->module_name = $module_name;
    }

    /**
     * Sets the model data and|or returns the data
     *
     * @param Array $data the model data to set (optional)
     *
     * @return Array the model data
     */
    public final function data (Array $data = array())
    {
        if (false === empty($data)) {
            foreach ($data as $key => $value) {
                if (true === array_key_exists($key, $this->data)) {
                    $this->data[$key] = $value;
                }
            }
        }
        return $this->data;
    }

    /**
     * Return the associated models
     *
     * @param Array $associated_models to this model associated models
     *
     * @return Array
     */
    public function associatedModels (Array $associated_models = array())
    {
        if (false === empty($associated_models)) {
            $this->associated_models = $associated_models;
        }
        return $this->associated_models;
    }

    /**
     * Return the model name
     *
     * @return string
     */
    public function name ()
    {
        return $this->name;
    }

    /**
     * Add a model to the associated models array
     *
     * @param Ncw_DataModel $model the model to add
     * @param string        $name  the model name
     *
     * @return void
     */
    public final function addAssociatedModel (Ncw_DataModel $model, $name = '')
    {
        if (true === empty($name)) {
            $name = $model->name;
        }
        $this->associated_models[$name] = $model;
    }

    /**
     * Add a model list to the associated models array
     *
     * @param Ncw_ModelList $model_list the model list to add
     * @param string        $name       the model name (optional)
     *
     * @return void
     */
    public final function addAssociatedModelList (Ncw_ModelList $model_list, $name = '')
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string');
        }
        if (true === empty($name)) {
            $name = $model_list[0]->name();
            if (true === empty($name)) {
                throw new Ncw_Exception('$name can not be empty');
            }
        }
        $this->associated_models[$name] = $model_list;
    }

    /**
     * Returns the invalid fields (validation errors)
     *
     * @return Array with the invalid fields and the erros.
     */
    public function invalidFields ()
    {
        return array($this->name => self::$invalid_fields[$this->name]);
    }

    /**
     * get or set method
     *
     * @param string $name the field name
     * @param Array  $args the if value must be encoded or not (optional)
     *
     * @return mixed
     */
    public final function __call ($name, Array $args = array())
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string');
        }
        $name = strtolower($name);
        // If the called method is a setter method then parameters are given
        if (true === isset(Ncw_Describer::$getters_and_setters[$this->module_name . '_' . $this->name]['getters'][$name])) {
            $encoded = false;
            if (false !== strstr($name, 'encoded')) {
                $encoded = true;
            }
            return $this->_attributeGet(
                Ncw_Describer::$getters_and_setters[$this->module_name . '_' . $this->name]['getters'][$name],
                $encoded
            );
        } else if (true === isset(Ncw_Describer::$getters_and_setters[$this->module_name . '_' . $this->name]['setters'][$name])) {
            return $this->_attributeSet(
                Ncw_Describer::$getters_and_setters[$this->module_name . '_' . $this->name]['setters'][$name],
                $args
            );
        } else {
            if (strpos($name, 'get') !== 0) {
                throw new Ncw_Exception('Method ' . $name . ' does not exist!');
            }
            $encoded = false;
            if (false !== strstr($name, 'encoded')) {
                $encoded = true;
            }
            $field = str_replace(array('get', 'encoded'), '', $name);
            return $this->_attributeGet($field, $encoded);
        }
    }

    /**
     * Sets a attribute is from Ncw_DataModel not allowed.
     *
     * @param string $field the field to return
     * @param Array  $args  set to false if
     *        you don't want to validate
     *
     * @return string
     */
    protected function _attributeSet ($field, Array $args)
    {
        throw new Ncw_Exception('Can not set ' . $field . '[' . implode(',', $args) . '] attributes from here.');
    }

    /**
     * Gets a attribute
     *
     * @param string  $field   the field to return
     * @param boolean $encoded set to true if the value must be html encoded (optional)
     *
     * @return string
     */
    protected final function _attributeGet ($field, $encoded = false)
    {
        if (false === is_string($field)) {
            throw new Ncw_Exception('$field must be of type string');
        }
        if (false === is_bool($encoded)) {
            throw new Ncw_Exception('$encoded must be of type boolean');
        }
        if (false === array_key_exists($field, $this->data)) {
            return false;
        }
        switch ($encoded) {
        case true :
            return Ncw_Library_Sanitizer::html($this->data[$field]);
            break;

        default :
            return $this->data[$field];
        }
    }

    /**
     * Gets a model association.
     *
     * @param string $name the variable name
     *
     * @return Ncw_DataModel
     */
    public final function __get ($name)
    {
        if (false === is_string($name)) {
            throw new Ncw_Exception('$name must be of type string');
        }
        if (true === empty($name)) {
            throw new Ncw_Exception('$name can not be empty');
        }
        if (false === array_key_exists($name, $this->associated_models)) {
            throw new Ncw_Exception('Association ' . $name . ' does not exist.');
        }
        return $this->associated_models[$name];
    }

    /**
     * Return the Iterater object
     *
     * @return ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * To string method.
     *
     * @return string
     */
    public function __toString ()
    {
        $string = "";
        foreach ($this->data as $key => $value) {
            $string .= $key . "=" . $value . "\n";
        }
        return $string;
    }
}

?>