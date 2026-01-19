<?php
/**
 * contains the abtract Validation class
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
 * Validation class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
abstract class Ncw_Validation extends Ncw_Object
{

    /**
     * The options
     *
     * @var array
     */
    protected $options = array();

    /**
     * The field name
     *
     * @var string
     */
    protected $field = '';

    /**
     * The erros message
     *
     * @var string
     */
    public $error_message = 'An error occured!';

    /**
     * Sets the field and the option
     *
     * @param string $field   the field to validate (optional)
     * @param array  $options the options (optional)
     */
    public function __construct ($field = '', Array $options = array())
    {
        $this->field = $field;
        $this->options = $options;
    }

    /**
     * Every field validation class muss implement
     * this method.
     *
     * @return void
     */
    public function check ()
    {

    }
}
?>
