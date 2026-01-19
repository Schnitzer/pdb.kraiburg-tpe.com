<?php
/**
 * Contains the Form helper class
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
 * Form helper class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Helpers_Form extends Ncw_Helper
{

    /**
     * The model name.
     *
     * @var string
     */
    protected $_model = "";

    /**
     * The model fields data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * The model invalid fields
     *
     * @var array
     */
    protected $_invalid_fields = array();

    /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    public function startup (Ncw_View &$view)
    {
        $this->_model = $view->controller->name;
        $this->_data = $view->controller->data;
        $this->_invalid_fields = Ncw_Model::$invalid_fields;

        $view->form = $this;
    }

    /**
     * Creates the form
     *
     * @param string $name    form name (optional)
     * @param array  $options (optional)
     *
     * @return string the form start tag
     */
    public function create ($name = "", $options = array())
    {
        $id = "";
        $add_form_sent = false;
        $options = array_merge(array('method' => 'post', 'class' => 'form'), $options);
        // If the form name is not set, but a model name is then use it instead.
        if (true === empty($name) && false === empty($this->_model)) {
            $name = strtolower($this->_model);
            $id = $name . "_form";
            $name = $name;
            $add_form_sent = true;
        } else if (false === empty($name)) {
            $id = $name . "_form";
        }
        $output = "<form id=\"" . $id . "\" name=\"" . $name . "\"";
        $output .= $this->_parseAttributes($options, array('id', 'name'));
        $output .= '>';
        if (true === $add_form_sent) {
            $output .= $this->input('form_sent', array('type' => 'hidden', 'value' => $name));
        }
        return $output;
    }

    /**
     * Creates a input field.
     * You can choose between text, checkbox, select and radio.
     * Example: input("name", array("type" => "text"));
     * Text is the default type.
     *
     * @param string $name    the input field name (optional)
     * @param array  $options (optional)
     *
     * @return string the input field
     */
    public function input ($name = "input", $options = array())
    {
        $field_id = '';
        $field_name = 'data';
        $options = array_merge(
            array(
                'type' => 'text',
                'label' => "",
                'options' => array(),
                'message' => "",
                'div' => 'input',
                'value' => ''
            ),
            $options
        );
        // Prepare the name and the field value.
        // If the name has a dot in itself, then for example model.field
        // must be converted to model[field] and the field value must be read step by step.
        if (strstr($name, ".")) {
            $arr_name = explode(".", $name);
            $num = count($arr_name);
            $first_part = true;
            $array_value = $this->_data;
            foreach ($arr_name as $part) {
                if (true === $first_part) {
                    $first_part = false;
                    $field_id = strtolower($part);
                } else {
                    $field_id .= "_" . strtolower($part);
                }
                $field_name .= "[" . $part . "]";
                // If a value in data for this part exists.
                if (isset($array_value[$part])) {
                    $array_value = $array_value[$part];
                } else {
                    // No value exists, the array is no longer needed.
                    $array_value = null;
                }
            }
            $model = $arr_name[0];
            $name = $arr_name[$num - 1];
            $value = $array_value;
            unset($arr_name, $part, $array_value);
        } else {
            $value = '';
            $model = $this->_model;
            // If a model name is given
            if (false === empty($model)) {
                $field_name .= "[" . $model . "]" . "[" . $name . "]";
                $field_id = strtolower($model) . "_" . $name;
                // If a value in data for this field exists.
                if (true === isset($this->_data[$model][$name])) {
                    $value = $this->_data[$model][$name];
                }
            } else {
                // Do not connect the field to a module.
                $field_name .= "[" . $name . "]";
                $field_id = $name;
            }
        }
        $label = $name;
        // If values must be displayed
        if (false !== $options['value']) {
            // Set the field value to the given value if it is set.
            if ($options['value'] !== '') {
                $value = $options['value'];
            }
        } else {
            // No values must be displayed.
            $value = '';
        }
        // Create the input field.
        $output = '';
        if ($options['type'] != "hidden") {
            if (true === is_array($options['div'])) {
                $output .= '<div';
                foreach ($options['div'] as $div_field => $div_value) {
                    $output .= ' ' . $div_field . '="' . $div_value . '"';
                }
                $output .= '>';
            } else if (false !== $options['div']) {
                $output .= '<div class="' . $options['div'] . '">';
            }
        }
        // If the label must not be displayed.
        if (false !== $options['label'] && $options['type'] != "radio" && $options['type'] != "hidden") {
            $output .= '<label for="' . $field_id . '"';
            if (true === is_array($options['label'])) {
                if (true === isset($options['label']['text'])
                    && false === empty($options['label']['text'])
                ) {
                    $label = $options['label']['text'];
                    unset($options['label']['text']);
                }
                foreach ($options['label'] as $label_field => $label_value) {
                    $output .= ' ' . $label_field . '="' . $label_value . '"';
                }
            } else {
                if (false === empty($options['label'])) {
                    $label = $options['label'];
                }
            }
            $output .= '>' . $label . '</label>';
        }
        // remove the no attributes options
        $attributes = $options;
        unset(
            $attributes['type'],
            $attributes['label'],
            $attributes['options'],
            $attributes['message'],
            $attributes['div'],
            $attributes['value']
        );
        // get the field
        $output .= $this->{'_' . $options['type']}($field_id, $field_name, $value, $options, $attributes);
        // Add the error message if it is wanted
        if (false !== $options['message']
            && isset($this->_invalid_fields[$model][$name])
            && $options['type'] != "hidden"
        ) {
            $message = "";
            // If the field is not valid
            if (false === empty($options['message'])) {
                $message = $options['message'];
            } else {
                $message = $this->_invalid_fields[$model][$name];
            }
            $output .= "<span class=\"error\">" . $message . "</span>";
        }
        if ($options['type'] != "hidden" && false !== $options['div']) {
            $output .= '</div>';
        }
        return $output;
    }

    /**
     * Creates a text field
     *
     * @param string $field_id   the field id
     * @param string $field_name the field name
     * @param string $value      the value
     * @param array  $options    the options array
     * @param array  $attributes the options array
     *
     * @return string
     */
    protected function _text ($field_id, $field_name, $value, $options, $attributes)
    {
        $output = "<input id=\"" . $field_id . "\" name=\"" . $field_name . "\" type=\"text\" value=\"" . $value . "\"";
        $output .= $this->_parseAttributes($attributes, array('id', 'name', 'type', 'value'));
        $output .= " />";
        return $output;
    }

    /**
     * Creates a file field
     *
     * @param string $field_id   the field id
     * @param string $field_name the field name
     * @param string $value      the value
     * @param array  $options    the options array
     * @param array  $attributes the options array
     *
     * @return string
     */
    protected function _file ($field_id, $field_name, $value, $options, $attributes)
    {
        $output = "<input id=\"" . $field_id . "\" name=\"" . $field_name . "\" type=\"file\" value=\"" . $value . "\"";
        $output .= $this->_parseAttributes($attributes, array('id', 'name', 'type', 'value'));
        $output .= " />";
        return $output;
    }

    /**
     * Creates a textareas
     *
     * @param string $field_id   the field id
     * @param string $field_name the field name
     * @param string $value      the value
     * @param array  $options    the options array
     * @param array  $attributes the options array
     *
     * @return string
     */
    protected function _textarea ($field_id, $field_name, $value, $options, $attributes)
    {
        $output = "<textarea id=\"" . $field_id . "\" name=\"" . $field_name . "\"";
        $output .= $this->_parseAttributes($attributes, array('id', 'name'));
        $output .= ">";
        $output .= $value;
        $output .= "</textarea>";
        return $output;
    }

    /**
     * Creates a password field
     *
     * @param string $field_id   the field id
     * @param string $field_name the field name
     * @param string $value      the value
     * @param array  $options    the options array
     * @param array  $attributes the options array
     *
     * @return string
     */
    protected function _password ($field_id, $field_name, $value, $options, $attributes)
    {
        $output = "<input id=\"" . $field_id . "\" name=\"" . $field_name . "\" type=\"password\" value=\"" . $value . "\"";
        $output .= $this->_parseAttributes($attributes, array('id', 'name', 'type', 'value'));
        $output .= " />";
        return $output;
    }

    /**
     * Creates a hidden field
     *
     * @param string $field_id   the field id
     * @param string $field_name the field name
     * @param string $value      the value
     * @param array  $options    the options array
     * @param array  $attributes the options array
     *
     * @return string
     */
    protected function _hidden ($field_id, $field_name, $value, $options, $attributes)
    {
        $output = "<input id=\"" . $field_id . "\" name=\"" . $field_name . "\" type=\"hidden\" value=\"" . $value . "\"";
        $output .= $this->_parseAttributes($attributes, array('id', 'name', 'type', 'value'));
        $output .= " />";
        return $output;
    }

    /**
     * Creates a checkbox field
     *
     * @param string $field_id   the field id
     * @param string $field_name the field name
     * @param string $value      the value
     * @param array  $options    the options array
     * @param array  $attributes the options array
     *
     * @return string
     */
    protected function _checkbox ($field_id, $field_name, $value, $options, $attributes)
    {
        $value = (boolean) $value;
        $checked = "";
        if (true === $value) {
            $checked = " checked=\"checked\"";
        }
        $output = "<input id=\"" . $field_id . "\" name=\"" . $field_name . "\" type=\"checkbox\" value=\"1\"" . $checked . "";
        $output .= $this->_parseAttributes($attributes, array('id', 'name', 'type', 'value', 'checked'));
        $output .= ' />';
        return $output;
    }

    /**
     * Creates a Select field
     *
     * @param string $field_id   the field id
     * @param string $field_name the field name
     * @param string $value      the value
     * @param array  $options    the options array
     * @param array  $attributes the options array
     *
     * @return string
     */
    protected function _select ($field_id, $field_name, $value, $options, $attributes)
    {
        $output = "<select id=\"" . $field_id . "\" name=\"" . $field_name . "\"";
        $output .= $this->_parseAttributes($attributes, array('id', 'name'));
        $output .= '>';
        foreach ($options['options'] as $option_label => $option_value) {
            if ((int) $option_label === $option_label) {
                $option_label = $option_value;
            }
            // Check if this option is selected.
            $selected = "";
            if ($option_value == $value) {
                $selected = " selected=\"selected\"";
            }
            $output .= "<option value=\"" . $option_value . "\"" . $selected . ">" . $option_label . "</option>";
        }
        $output .= "</select>";
        return $output;
    }

    /**
     * Creates a radio select
     *
     * @param string $field_id   the field id
     * @param string $field_name the field name
     * @param string $value      the value
     * @param array  $options    the options array
     * @param array  $attributes the options array
     *
     * @return string
     */
    protected function _radio ($field_id, $field_name, $value, $options, $attributes)
    {
        $output = "";
        $count = 1;
        foreach ($options['options'] as $option_label => $option_value) {
            if ((int) $option_label === $option_label) {
                $option_label = $option_value;
            }
            // Check if this option is selected.
            $checked = "";
            if ($option_value == $value) {
                $checked = " checked=\"checked\"";
            }
            $output .= "<input id=\"" . $field_id . $count . "\" name=\"" . $field_name . "\" type=\"radio\" value=\"" . $option_value . "\"" . $checked . "";
            $output .= $this->_parseAttributes($attributes, array('id', 'name', 'type', 'checked'));
            $output .= ' />';
            if (false !== $options['label']) {
                $output .= "<label for=\"" . $field_id . $count . "\">" . $option_label . "</label>";
            }
            ++$count;
        }
        return $output;
    }

    /**
     * Creates a input field
     *
     * @param string $label      the submit button label (optional)
     * @param array  $attributes (optional)
     *
     * @return string the submit field
     */
    public function submit ($label = "submit", $attributes = array())
    {
        $output = '<input type="submit" id="form_submit" value="' . $label . '"';
        $output .= $this->_parseAttributes($attributes, array('id', 'type', 'value'));
        $output .= ' />';
        return $output;
    }

    /**
     * Creates a button
     *
     * @param string $name       the button label (optional)
     * @param array  $attributes (optional)
     *
     * @return string
     */
    public function button ($name = "button", $attributes = array())
    {
        $output = "<input id=\"" . $name . "_button\" name=\"" . $name . "\" value=\"" . $name . "\" type=\"button\" ";
        $output .= $this->_parseAttributes($attributes, array('id', 'name', 'type', 'value'));
        $output .= "/>";
        return $output;
    }

    /**
     * Creates the form end tag
     *
     * @return string the form end tag
     */
    public function end ()
    {
        return "</form>";
    }
}
?>
