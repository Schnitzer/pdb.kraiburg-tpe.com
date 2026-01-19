<?php
/**
 * contains the Between validation class
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
 * Between validation class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Validations
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Validations_Between extends Ncw_Validation
{

    /**
     * The error message
     *
     * @var string
     */
    public $error_message = 'The number is not within the permitted range';

    /**
     * Checks if the given value is
     * between two given values
     *
     * @param mixed $value   the value to check
     * @param Array $between value must be
     *        between the first and second array position
     *
     * @return boolean true or false
     */
    public function check ($value, Array $between)
    {
        if (true == preg_match("/^[0-9.]+$/", $value) && true == preg_match("/^[0-9.]+$/", $between[0]) && true == preg_match("/^[0-9.]+$/", $between[1])) {
            if ((float) $value > (float) $between[0] && (float) $value < (float) $between[1]) {
                return true;
            }
        }
        $this->error_message .= ' of ' . $between[0] . ' to ' . $between[1] . '!';
        return false;
    }
}
?>
