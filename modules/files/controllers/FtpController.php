<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Files_FtpController class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author          Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright       Copyright 2007-2008, Netzcraftwerk UG (haftungsbeschränkt)
 * @link            http://www.netzcraftwerk.com
 * @package         netzcraftwerk
 * @since           Netzcraftwerk v 3.0.0.1
 * @version         Revision: $LastChangedRevision$
 * @modifiedby      $LastChangedBy$
 * @lastmodified    $LastChangedDate$
 * @license         http://www.netzcraftwerk.com/licenses/
 */
/**
 * FtpController class.
 *
 * @package netzcraftwerk
 */
class Files_FtpController extends Files_ModuleController
{

    /**
     * Layout
     *
     * @var string
     */
    public $layout = 'blank';

    /**
     * No Model
     *
     * @var boolean
     */
    public $has_model = false;

    /**
     * Pagnination options
     *
     * @var array
     */
    public $paginate = array(
        'order' => 'File.name',
    );

    /**
     * Image thumbnails formats
     *
     * @var array
     */
    public $arr_thumbs_formats = array('jpg', 'jpeg', 'gif', 'png');

    /**
     * HTML formats
     *
     * @var array
     */
    public $arr_html_formats = array('htm', 'html');

    /**
     * Formats with icons
     *
     * @var array
     */
    public $arr_formats = array('avi', 'bmp', 'css', 'doc', 'xls', 'html', 'js', 'mov', 'mp3', 'mpg', 'pdf', 'ppt', 'txt', 'wav', 'wma', 'wmv', 'xml', 'xsl');


    /**
     * The ftp configurations
     *
     * @var Array
     */
    public $configs = array('ftp.config');

    /**
     * Show ftp files action.
     *
     * @return void
     */
    public function allAction ()
    {
        $ftp_folder = Ncw_Configure::read('FTP_FOLDER');
        if (true === is_string($ftp_folder)) {
            $ftp_folder = array($ftp_folder);
        }

        $files = array();
        $dir = str_replace(array('..', '.'), '', $this->params['url']['d']);

        $in_folder = false;
        foreach ($ftp_folder as $folder) {
            if ($folder == substr($dir, 0, strlen($folder))) {
                $in_folder = true;
                break;
            }
        }
        if (false === $in_folder) {
            exit();
        }

        $verz = opendir($dir);
        while ($file = readdir ($verz)) {
            $file_path = $dir . DS . $file;
            if ($file != "."
                && $file != ".."
                && $file[0] != "."
                && true === is_file($file_path)
            ) {
                $type = array_pop(explode('.', $file));

                $files[] = array(
                    'name' => $file,
                    'path' => $file_path,
                    'type' => $type,
                    'size' => filesize($file_path),
                    'last_modified' => date('Y-m-d H:i:s', filemtime($file_path))
                );
            }
        }
        closedir($verz);

        $this->view->dir = $dir;
        $this->view->files = $files;

        $this->view->arr_thumbs_formats = $this->arr_thumbs_formats;
        $this->view->arr_html_formats = $this->arr_html_formats;
        $this->view->arr_formats = $this->arr_formats;
    }
}
?>
