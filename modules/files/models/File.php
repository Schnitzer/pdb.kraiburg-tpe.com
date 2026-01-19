<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Wcms_File class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk GmbH
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * File class.
 *
 * @package netzcraftwerk
 */
class Files_File extends Ncw_Model
{

	/**
	 * validation array
	 *
	 * @var array
	 */
	public $validation = array(
	   'name' => array('rules' => array('NotEmpty','MaxLength' => 100), "required" => true),
	   'tags' => array('rules' => array('MaxLength' => 255), "required" => false)
	);

	// array of maximum square sizes of thumbnails
	public static $arr_thumbs_size = array(32, 96);

	// array of filetypes with thumbnails
	public static $arr_thumbs_formats = array('jpg', 'jpeg', 'gif', 'png');

	/**
	 * Deletes the model entry
	 *
	 * @param int $id
	 * @return boolean
	 */
	protected function _doDelete ($id)
	{
	    $text = new Ncw_Helpers_Text();

		$this->read();
		$str_targetFile = ASSETS . DS . 'files' . DS . 'uploads' . DS . $text->cleanForUrl($this->getName()) . '_' . $id . '.' . $this->getType();

		$file_helper = new Ncw_Components_File();
		$file_helper->delete($str_targetFile);

		if(in_array($this->getType(), self::$arr_thumbs_formats)) {
			foreach (self::$arr_thumbs_size as $size) {
				$str_targetFileThumb = ASSETS . DS . 'files' . DS . 'thumbnails' . DS . $text->cleanForUrl($this->getName()) . '_' . $id . '_' . $size . '.' . $this->getType();
				$file_helper->delete($str_targetFileThumb);
			}
		}
		$sql = "DELETE FROM `" . $this->db_table_name . "`
				WHERE `id`=:id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":id", $id, PDO::PARAM_INT);
		try {
			if (false === $stmt->execute()) {
				$error_info = $stmt->errorInfo();
				throw new Ncw_Exception('Delete failed (' . $error_info[2] . ')', 1);
			}
		} catch (Ncw_Exception $e) {
			if (DEBUG_MODE > 0) {
				$e->exitWithMessage();
			}
			return false;
		}
		return true;
	}

	/**
	 * Returns the image url
	 *
	 * @param int $file_id
	 * @param string $file_name
	 * @param string $file_type
	 *
	 * @return string
	 */
	public static function makeImageUrl ($file_id, $file_name, $file_type, $size = 0)
	{
	    if ($size > 0) {
	       $size = '_' . $size;
	    } else {
	        $size = '';
	    }

	    $text = new Ncw_Helpers_Text();

        return Ncw_Configure::read('Project.url') . '/' . ASSETS . '/files/uploads/' . $text->cleanForUrl($file_name) . '_' . $file_id . $size . '.' . $file_type;
	}

	/**
	 * Returns a image url
	 *
	 * @param int $file_id
	 *
	 * @return void
	 */
    public static function imageUrl ($file_id)
    {
        $file = new Files_File();
        $file->setId($file_id);
        $file->unbindModel('all');
        $file->read(
            array(
                'fields' => array(
                    'File.name',
                    'File.type',
                )
            )
        );

        $text = new Ncw_Helpers_Text();

        return Ncw_Configure::read('Project.url') . '/' . ASSETS . '/files/uploads/' . $text->cleanForUrl($file->getName()) . '_' . $file_id . '.' . $file->getType();
    }

    /**
     * Returns a image url
     *
     * @param int $file_id
     * @param int $site     either 32 or 96 px
     *
     * @return void
     */
    public static function previewImageUrl ($file_id, $size = 32)
    {
        $file = new Files_File();
        $file->setId($file_id);
        $file->read(
            array(
                'fields' => array(
                    'File.name',
                    'File.type',
                )
            )
        );

        $text = new Ncw_Helpers_Text();

        return Ncw_Configure::read('Project.url') . '/' . ASSETS . '/files/thumbnails/' . $text->cleanForUrl($file->getName()) . '_' . $file_id . '_' . $size . '.' . $file->getType();
    }
}
?>
