<?php
/**
 * Contains the Folder component
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
 * Folder component.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Components_Folder extends Ncw_Component
{

    /**
     * Startup
     *
     * @param Ncw_Controller &$controller the controller
     *
     * @return void
     */
    public function startup (Ncw_Controller &$controller)
    {
        $controller->folder = $this;
    }

    /**
     * Creates a new folder with the given name.
     *
     * @param string $name  the folder name
     * @param string $chmod (optional)
     *
     * @return boolean
     */
    public function create ($name, $chmod = "0711")
    {
        if (false === empty($name)) {
            // If the folder thoes not exist.
            if (false === is_dir($name)) {
                // Make the folder.
                mkdir($name, $chmod);
            }
            return true;
        }
        return false;
    }

    /**
     * Deletes the given folder.
     *
     * @param string $name the folder name
     *
     * @return boolean
     */
    public function delete ($name)
    {
        if (false === empty($name)) {
            // Delete the folder.
            rmdir($name);
            return true;
        }
        return false;
    }
}
?>
