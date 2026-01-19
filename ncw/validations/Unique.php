<?php
/**
 * contains the Unique validation class
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
 * Unique validation class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Validations
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Validations_Unique extends Ncw_Validation
{

    /**
     * The error message
     *
     * @var string
     */
    public $error_message = 'This value already exists!';

    /**
     * Checks if the value is unique in its table.
     *
     * @param mixed $value the value to check
     *
     * @return boolean true or false
     */
    public function check ($value)
    {
        $sql = "SELECT count(1) AS `count`
                FROM " . $this->options['db_table'] . "
                WHERE `" . $this->field . "`=:value";
        $db = Ncw_Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(":value", $value, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($row[0]['count'] == 0) {
            return true;
        }
        return false;
    }
}
?>
