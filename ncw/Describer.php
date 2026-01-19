<?php
/**
 * Contains the Describer class.
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
 * The Describer class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Describer
{

    /**
     * The model fields
     *
     * @var Array
     */
    private static $_fields = array();

    /**
     * The model getters and setters
     *
     * @var Array
     */
    public static $getters_and_setters = array();

    /**
     * Returns the model fields.
     *
     * @param string $name       the model name
     * @param string $table_name the table name of the given model
     *
     * @return Array the model fields
     */
    public static function getModelFields ($name, $table_name)
    {
        if (false === isset(self::$_fields[$name])) {
            self::__readModelFields($name, $table_name);
        }
        return self::$_fields[$name];
    }

    /**
     * Reads the model attributes and defines the model setters and getters
     * If a tmp file of the model fields exists then read this, elsewise
     * get the fields from the database.
     *
     * @param string $name       the model name
     * @param string $table_name the table name
     *
     * @return void
     */
    private static function __readModelFields ($name, $table_name)
    {
        $path_to_description_file = Ncw_Configure::read('Cache.dir') . DS . 'models' . DS . $name;
        if (Ncw_Configure::read('debug_mode') === 0 && true === is_file($path_to_description_file)) {
            $description = unserialize(file_get_contents($path_to_description_file));
            self::$_fields[$name] = $description['fields'];
            self::$getters_and_setters[$name] = $description['getters_setters'];
        } else {
            // Get the model attributes
            $conn = Ncw_Database::getInstance();
            $stmt = $conn->prepare("DESCRIBE " . $table_name);
            try {
                if (false === $stmt->execute()) {
                    throw new Ncw_Exception('Could not access table ' . $table_name);
                }
            } catch (Ncw_Exception $e) {
                $e->exitWithMessage();
            }
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                self::$_fields[$name][] = $row['Field'];
                $field_method_name = str_replace('_', '', $row['Field']);
                self::$getters_and_setters[$name]['setters']['set' . $field_method_name] = self::$getters_and_setters[$name]['getters']['get' . $field_method_name . 'encoded']
                    = self::$getters_and_setters[$name]['getters']['get' . $field_method_name] = $row['Field'];
            }
            if (Ncw_Configure::read('debug_mode') === 0) {
                $file_code = serialize(
                    array(
                        'fields' => self::$_fields[$name],
                        'getters_setters' => self::$getters_and_setters[$name])
                );
                $file = fopen($path_to_description_file, 'w');
                fputs($file, $file_code);
                fclose($file);
            }
        }
    }
}
?>
