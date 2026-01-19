<?php
/**
 * contains the DateTime validation class
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
 * DateTime validation class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Validations
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Validations_DateTime extends Ncw_Validation
{

    /**
     * The error message
     *
     * @var string
     */
    public $error_message = 'Not a valid date and time combination!';

    /**
     * Checks if the given value is a valid date time.
     *
     * @param mixed $value the value to check
     *
     * @return boolean true or false
     */
    public function check ($value)
    {
        $matches = array();
        if (true == preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value, $matches)) {
            if (true === checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }
        return false;
    }
}
?>
