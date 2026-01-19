<?php
/**
 * contains the Decimal validation class
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
 * Decimal validation class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Validations
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Validations_Decimal extends Ncw_Validation
{

    /**
     * The error message
     *
     * @var string
     */
    public $error_message = 'This is not a valid decimal number';

    /**
     * Checks if the given value is of
     * decimal type.
     *
     * @param mixed $value the value to check
     *
     * @return boolean true or false
     */
    public function check ($value)
    {
        if (true == is_string($value)) {
            $new_value = (float) $value;
            if (sprintf("%.2f", $new_value) === $value || sprintf("%.1f", $new_value) === $value) {
                $value = $new_value;
            }
        }
        if (true === is_double($value)) {
            return true;
        }
        return false;
    }
}
?>
