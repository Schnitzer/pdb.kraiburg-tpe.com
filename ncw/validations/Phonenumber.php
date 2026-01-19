<?php
/**
 * contains the Phonenumber validation class
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
 * Phonenumber validation class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Validations
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Validations_Phonenumber extends Ncw_Validation
{

    /**
     * The error message
     *
     * @var string
     */
    public $error_message = 'This is not a valid phonenumber!';

    /**
     * Checks if the given value is of
     * Phonenumber type.
     * +49 08631 985 or
     * 049 8631 985
     *
     * @param mixed $value the value to check
     *
     * @return boolean true or false
     */
    public function check ($value)
    {
        if (true == preg_match("/^((((\+|00)[1-9]{1})-[1-9]{1}[0-9]{2,3}-[1-9]{1}[0-9]{3,9})|(((\+|00)[1-9]{1}[0-9]{1})-[1-9]{1}[0-9]{2,3}-[1-9]{1}[0-9]{3,8})|(((\+|00)[1-9]{1}[0-9]{2})-[0-9]{2,4}-[1-9]{1}[0-9]{3,7}))$/", $value)) {
            return true;
        }
        return false;
    }
}
?>
