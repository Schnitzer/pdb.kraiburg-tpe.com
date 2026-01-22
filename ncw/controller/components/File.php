<?php
/**
 * Contains the File component class
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
 * File component class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Components_File extends Ncw_Component
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
		$controller->file = $this;
	}

    /**
     * Reads a file and returns the content.
     * Read mode can be set.
     *
     * @param string $filename the file name
     * @param string $method   (optional)
     *
     * @return void
     */
    public function read ($filename, $method = "r")
    {
        $fp = fopen($filename, $method);
        $filecode = fread($fp, filesize($filename));
        fclose($fp);
        return $filecode;
    }

    /**
     * Writes into a file.
     * Write mode can be set.
     *
     * @param string $filename the file name
     * @param string $method   (optional)
     * @param string $value    (optional)
     *
     * @return void
     */
    public function write ($filename, $method = "w+", $value = "")
    {
        $fp = fopen($filename, $method);
        fwrite($fp, $value);
        fclose($fp);
    }

    /**
     * Creates a File with the given name
     *
     * @param string $filename the file name
     * @param string $value    the value
     *
     * @return void
     */
    public function create ($filename, $value)
    {
        // Ensure directory exists
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $fp = @fopen($filename, "w+");
        if ($fp === false) {
            throw new Exception("Cannot create file: $filename - check directory permissions");
        }
        fwrite($fp, $value);
        fclose($fp);
    }

    /**
     * Renames a file.
     *
     * @param string $oldfilename the old file name
     * @param string $newfilename the new file name
     *
     * @return boolean
     */
    public function rename ($oldfilename, $newfilename)
    {
        if (true === is_file($oldfilename)) {
            rename($oldfilename, $newfilename);
            return true;
        }
        return false;
    }

    /**
     * Deletes a file
     *
     * @param string $filename the file name
     *
     * @return boolean
     */
    public function delete ($filename)
    {
        if (true === is_file($filename)) {
            return unlink($filename);
        }
        return false;
    }

    /**
     * get filesize
     *
     * @param string $filename the file name
     *
     * @return mixed
     */
    public function size ($filename)
    {
        if (true === is_file($filename)) {
            return filesize($filename);
        }
        return false;
    }
}
?>
