<?php
/**
 * contains the InList validation class
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
 * @subpackage Validations
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
 * InList validation class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Validations
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Validations_InList extends Ncw_Validation
{

    /**
     * The error message
     *
     * @var string
     */
    public $error_message = 'This value does not exist in the predefined list!';

    /**
     * Checks if the given value in a list of values
     *
     * @param mixed $value    the value to check
     * @param Array $arr_list the value must be in this array
     *
     * @return boolean true or false
     */
    public function check ($value, Array $arr_list)
    {
        if (in_array($value, $arr_list)) {
            return true;
        }
        return false;
    }
}
?>
