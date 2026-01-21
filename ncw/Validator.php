<?php

/**
 * contains the Validator class
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
 * Validator class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Validator
{
    /**
     * The validator options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * The fields.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Set the fields attribute
     *
     * @param array $fields  the fields to set
     * @param array $options the validator options (optional)
     */
    public function __construct($fields, Array $options = array())
    {
        $this->_fields = $fields;
        $this->_options = array_merge(array('db_table'), $options);
    }

    /**
     * Validates all fields.
     *
     * @param Array $values the values to validate (optional)
     *
     * @return boolean
     */
    public function validate($values = array())
    {
        $invalidFields = array();
        $return = true;
        foreach ($this->_fields as $field => $options) {
            if (false === array_key_exists($field, $values)) {
                $values[$field] = '';
            }
            list($valid, $message) = $this->_validate($field, $values[$field], $options);
            if (false === (bool) $valid) {
                $return = false;
                $invalidFields[$field] = $message;
            }
        }
        return array($return, $invalidFields);
    }

    /**
     * Validates the field with the given
     * name.
     *
     * @param string $field the field to validate
     * @param mixed  $value the value of the field
     *
     * @return boolean
     */
    public function validateField($field, $value)
    {
        if (false === empty($field)) {
            if (false === array_key_exists($field, $this->_fields)) {
                return array(true, '');
            }
            list($valid, $message) = $this->_validate($field, $value, $this->_fields[$field]);
            return array($valid, $message);
        }
        return array(false, '');
    }

    /**
     * Validates a field.
     *
     * @param string $field   the field to validate
     * @param mixed  $value   the field value
     * @param Array  $options the field options
     *
     * @return boolean
     */
    protected function _validate($field, $value, $options)
    {
        $options = array_merge(
            array(
                'rules' => array(),
                'required' => false,
                'invalidate' => false
            ),
            $options
        );
        if (true === $options['invalidate']) {
            $message = $this->_getErrorMessage($field, 'invalidated');
            return array(false, $message);
        }
        $value = $value !== null ? trim($value) : '';
        // check if the field is optional
        if (false === $options['required']) {
            // If the field is empty it can be skipped
            if (true === empty($value)) {
                return array(true, '');
            }
        }
        foreach ($options['rules'] as $key => $rule) {
            $params = null;
            // if the key is set, then the rule is the key
            // because options are given.
            if (false === empty($key) && '' . intval($key) . '' != $key) {
                $params = $rule;
                $rule = $key;
            }
            $rule_name = $rule;
            $rule_class = 'Ncw_Validations_' . $rule;
            // Initialize the rule object
            $rule = new $rule_class($field, $this->_options);
            // check
            if (false === $rule->check($value, $params)) {
                return array(false, $this->_getErrorMessage($field, $rule_name, $rule->error_message));
            }
        }
        return array(true, '');
    }

    /**
     * Check if the field has got a special error message for the given field.
     *
     * @param string $field                      the error message for this field
     * @param string $rule_name                  the rule name
     * @param string $rule_defined_error_message the rule defined error message
     *
     * @return string the message
     */
    protected function _getErrorMessage($field, $rule_name, $rule_defined_error_message = null)
    {
        $message = $rule_defined_error_message;
        // check if for this $rule_defined_error_message a special message is set.
        if (true === array_key_exists('message', $this->_fields[$field])) {
            if (true === is_array($this->_fields[$field]['message'])) {
                if (true === array_key_exists($rule_name, $this->_fields[$field]['message'])) {
                    $message = $this->_fields[$field]['message'][$rule_name];
                } else if (true === array_key_exists('default', $this->_fields[$field]['message'])) {
                    $message = $this->_fields[$field]['message']['default'];
                }
            } else if (false !== $this->_fields[$field]['message']) {
                $message = $this->_fields[$field]['message'];
            } else {
                $message = '';
            }
        }
        return $message;
    }

    /**
     * Invalidates a field.
     *
     * @param string $field the field to invalidate
     *
     * @return void
     */
    public function invalidateField($field)
    {
        if (false === empty($field)) {
            $this->_fields[$field]['invalidate'] = true;
            return $this->_getErrorMessage($field, 'invalidated', 'The field was invalidated');
        }
        return false;
    }
}
?>
