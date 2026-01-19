<?php
/**
 * contains the Letter validation class
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
 * Letter validation class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Validations
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Validations_Letter extends Ncw_Validation
{

    /**
     * The error message
     *
     * @var string
     */
    public $error_message = 'Only letters are allowed!';

    /**
     * Checks if the given value is only letters
     * only  type.
     *
     * @param mixed $value          the value to check
     * @param mixed $case_sensitive (optional)
     *
     * @return boolean true or false
     */
    public function check ($value, $case_sensitive = false)
    {
        if (false === (boolean) $case_sensitive) {
            if (preg_match('/^[A-Za-züöäÜÖÄßŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ]*$/', $value)) {
                return true;
            }
        } else if ($case_sensitive == "upper") {
            if (preg_match('/^[A-ZÜÖÄŸ¥ŠŒŽÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ]*$/', $value)) {
                return true;
            }
        } else if ($case_sensitive == "lower") {
            if (preg_match('/^[a-züöäßšœžµßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ]*$/', $value)) {
                return true;
            }
        }
        return false;
    }

}
?>
