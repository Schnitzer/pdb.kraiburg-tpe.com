<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Files_FileController class.
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
 * FileController class.
 *
 * @package netzcraftwerk
 */
class Files_FileController extends Files_ModuleController
{

    /**
     * Layout
     *
     * @var string
     */
    public $layout = 'blank';

    /**
     * ACL publics
     *
     * @var unknown_type
     */
    public $acl_publics = array('upload');

    /**
     * Image thumbnail sizes
     *
     * @var array
     */
    public $arr_thumbs_size = array(32, 96);

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
    public $arr_formats = array('avi', 'bmp', 'css', 'doc', 'docx', 'rtf', 'xls', 'html', 'js', 'mov', 'mp3', 'mpg', 'pdf', 'ppt', 'txt', 'wav', 'wma', 'csv', 'xml', 'xsl');

    /**
     * Pagnination options
     *
     * @var array
     */
    public $paginate = array(
        'order' => 'File.name',
        'limit' => 30
    );

    /**
     * Show files action.
     *
     * @param $folder_id int
     *
     * @return void
     */
    public function allAction ($folder_id = 0)
    {
        if ($this->request_handler->responseType() == 'javascript') {
           $this->layout = 'default';
        }

        $conditions = array();
		$folder_id = (int) $folder_id;
		
        if ($folder_id > 0) {
            $conditions = array(
                "File.folder_id" => $folder_id
            );
            $this->view->search = false;
            $this->view->folder_id = $folder_id;
            $this->view->search_value = '';
        } else {
            $str_search = '';
            if (true === isset($this->params['url']['s'])) {
                $str_search = trim(Ncw_Library_Sanitizer::escape($this->params['url']['s']));
            }
            $conditions = array(
                '(MATCH File.tags AGAINST (\'' . $str_search . '\') || File.name LIKE \'%' . $str_search . '%\''
                . ' || concat_ws(\'.\', File.name, File.type) LIKE \'%' . $str_search . '%\')',
            );

            $this->view->search = true;
            if (true === isset($this->params['url']['s'])) {
                $this->view->search_value = $this->params['url']['s'];
            } else {
                $this->view->search_value = '';
            }
            $this->view->folder_id = 0;
        }
		
        if (true === isset($this->params['url']['o'])) {
            $only_types = Ncw_Library_Sanitizer::escape($this->params['url']['o']);
            $only_types = explode(',', $only_types);
            $first = true;
			$only_types_str = '';
            foreach ($only_types as $type) {
                if (false === $first) {
                    $only_types_str .= ' || ';
                } else {
                    $only_types_str .= '';
                    $first = false;
                }
                $only_types_str .= 'File.type = \'' . $type . '\'';
            }
            $conditions[] = '(' . $only_types_str . ')';
            unset($only_types, $only_types_str);
        }		
		
        $this->view->files = $this->paginate($conditions);

        $this->view->arr_thumbs_formats = $this->arr_thumbs_formats;
        $this->view->arr_html_formats = $this->arr_html_formats;
        $this->view->arr_formats = $this->arr_formats;

		$this->loadModel('Folder');
		$this->Folder->setId($folder_id);
		$this->view->folder_name = $this->Folder->readField('name');

        if ($this->referer() == $this->base . '/' . $this->prefix . '/files/folder/all') {
            $this->view->window_mode = false;
        } else if (true === $this->request_handler->isAjax()) {
            $this->view->window_mode = true;
        }

		// permission
        $this->view->permissions = array(
            '/files/file/new' => $this->acl->check('/files/file/new'),
            '/files/file/edit' => $this->acl->check('/files/file/edit'),
            '/files/folder/edit' => $this->acl->check('/files/folder/edit'),
        );
    }

    /**
     * upload action
     *
     * @param int $folder_id the folder id
     *
     * @return void
     */
    public function uploadAction ($folder_id = 1)
    {
        $this->view = false;

        $upload_dir = ASSETS . DS . 'files' . DS . 'uploads' . DS;

        include_once MODULES . DS . 'files' . DS . 'vendor' . DS . 'FilesUploadHandler.php';
        $upload_handler = new NcwUploadHandler(
            array('upload_dir' => $upload_dir), 
            $this->File,
            (int) $folder_id,
            new Ncw_Helpers_Text()
        );
        
        /*$filename = $this->params['form']['Filedata']['name'];
        $temp_file = $this->params['form']['Filedata']['tmp_name'];
        $arr_filename = @explode('.', $filename);
        $filetype = array_pop($arr_filename);
        $filename = implode('.', $arr_filename);
        $filetype = strtolower($filetype);


        if (strlen($filetype) > 1 && strlen($filetype) < 10) {

            $this->File->setName($filename, false);
            $this->File->setType(strtolower($filetype), false);
            $this->File->setTags($this->params['form']['tags'], false);
            $filesize = $this->params['form']['Filedata']['size'];
            $this->File->setSize($filesize, false);
            $this->File->setFolderId($folder_id, false);
            $this->File->create();
            $this->File->save();
            $file_id = $this->File->getId();

            $text = new Ncw_Helpers_Text();
            $target_file = ASSETS . DS . 'files' . DS . 'uploads' . DS . $text->cleanForUrl($filename) . '_' . $file_id . '.' . $filetype;

            if (move_uploaded_file($temp_file, $target_file)) {
                chmod($target_file, 0644);
                if (true === in_array($filetype, $this->arr_thumbs_formats)) {
                    include_once MODULES . DS . 'files' . DS . 'vendor' . DS . 'wideimage' . DS . 'WideImage.php';
                    foreach ($this->arr_thumbs_size as $size) {
                        $target_file_thumb = ASSETS . DS . 'files' . DS . 'thumbnails' . DS . $text->cleanForUrl($filename) . '_' . $file_id . '_' . $size . '.' . $filetype;
                        if ($size == 16) {
                            if ($filetype == 'png') {
                                WideImage::load($target_file)->resize($size, $size/4*3)->saveToFile($target_file_thumb, NULL, 6);
                            } else if ($filetype == 'gif') {
                                // do's not support compression level
                                WideImage::load($target_file)->resize($size, $size/4*3)->saveToFile($target_file_thumb, NULL);
                            } else {
                                WideImage::load($target_file)->resize($size, $size/4*3)->saveToFile($target_file_thumb, NULL, 35);
                            }
                        } else {
                            if ($filetype == 'png') {
                                WideImage::load($target_file)->resize($size, $size/4*3)->saveToFile($target_file_thumb, NULL, 6);
                            } else if ($filetype == 'gif') {
                                // do's not support compression level
                                WideImage::load($target_file)->resize($size, $size/4*3)->saveToFile($target_file_thumb, NULL);
                            } else {
                                WideImage::load($target_file)->resize($size, $size/4*3)->saveToFile($target_file_thumb, NULL, 47);
                            }
                        }
                    }
                }
                print '1';
                $this->_stop();
            }
        }
        print '0';*/
    }

    /**
     * Edit file action.
     *
     * @return void
     */
    public function editAction ($id)
    {
        $text = new Ncw_Helpers_Text();

        $this->File->setId($id);
        $this->File->read();

        $this->view->filetype_name = $this->File->getType();
        $this->view->file_id = $id;
        $this->view->file_name = $text->cleanForUrl($this->File->getName()) . '_' . $id . '.' . $this->File->getType();
        $this->view->file_path = $this->base . '/' . ASSETS . '/' . 'files/uploads' . '/' . $this->view->file_name;
        if (true === in_array($this->File->getType(), $this->arr_thumbs_formats)) {
            $this->view->is_image = true;
        } else {
            $this->view->is_image = false;
        }

        $this->data['File'] = $this->File->data();

        $this->view->folders_options = $this->folderSelectOptions(
            $this->File->getFolderId()
        );

        $this->view->arr_html_formats = $this->arr_html_formats;
        $this->view->arr_formats = $this->arr_formats;

		// permissions
        $this->view->permissions = array(
            '/files/file/delete' => $this->acl->check('/files/file/delete'),
        );
    }

    /**
     * update action
     *
     * @return void
     */
    public function updateAction ()
    {
        $this->view = false;

        if (true === isset($this->data['File'])) {
            $text = new Ncw_Helpers_Text();

            $this->File->setId($this->data['File']['id']);
            $this->File->read();

            $old_filename = $this->File->getName();
            $new_filename = $this->data['File']['name'];

            $this->File->data($this->data['File']);
            $result = $this->File->saveFields(array('name', 'tags', 'folder_id'));

			if (true === $result) {
				
	            $old_file = ASSETS . DS . 'files' . DS . 'uploads' . DS . $text->cleanForUrl($old_filename) . '_' . $this->File->getId() . '.' . $this->File->getType();
            	$new_file = ASSETS . DS . 'files' . DS . 'uploads' . DS . $text->cleanForUrl($new_filename) . '_' . $this->File->getId() . '.' . $this->File->getType();

	            if (true === rename($old_file, $new_file)) {
	                if (true === in_array($this->File->getType(), $this->arr_thumbs_formats)) {
	                    foreach ($this->arr_thumbs_size as $size) {
	                        $old_file_thumb = ASSETS . DS . 'files' . DS . 'thumbnails' . DS . $text->cleanForUrl($old_filename) . '_' . $this->File->getId() . '_' . $size . '.' . $this->File->getType();
	                        $new_file_thumb = ASSETS . DS . 'files' . DS . 'thumbnails' . DS . $text->cleanForUrl($new_filename) . '_' . $this->File->getId() . '_' . $size . '.' . $this->File->getType();
	                        rename($old_file_thumb, $new_file_thumb);
	                    }
	                }
	            }				
				
            	print '{"return_value" : true}';
			} else {
				print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->File->invalidFields()) . '}';	
			}
        }
    }

    /**
     * delete file action.
     *
     *
     */
    public function deleteAction ($id)
    {
        $this->view = false;

        $this->File->setId($id);
        $this->File->delete();

        print '{"return_value" : true}';
    }

    /**
     * Search dialog action
     *
     * @return void
     */
    public function searchDialogAction ()
    {
        $this->layout = 'default';
    }

    /**
     * Generates the image linklist for the tinymce
     *
     * @return void
     */
    public function linklistAction ()
    {
        $this->view = false;

        if (true === isset($this->params['named']['absolute'])
            && true === (boolean) $this->params['named']['absolute']
        ) {
            $path_begin = $this->base . '/';
        } else {
            $path_begin = Ncw_Configure::read('Project.relative_uri') . '/';
        }

        if (true === isset($this->params['named']['linklist'])
            && true === (boolean) $this->params['named']['linklist']
        ) {
            $linklist = 'var tinyMCELinkList = new Array(';
        } else {
            $linklist = 'var tinyMCEImageList = new Array(';
        }

        $this->File->bindModel(array('belongs_to' => array('Folder')));
        $files = $this->File->fetch(
            'all',
            array(
                'fields' => array(
                    'File.id',
                    'File.name',
                    'File.type',
                    'Folder.id',
                    'Folder.name'
                ),
                'order' => array(
                    'File.folder_id',
                    'File.name'
                ),
                'conditions' => array(
					'File.type = \'jpg\' || File.type = \'jpeg\' || File.type = \'gif\' || File.type = \'png\''
				)
            )
        );

        $text = new Ncw_Helpers_Text();

        $arr_files = array();
        $folder_id = 0;

        foreach ($files as $file) {
            if ($folder_id != $file->Folder->getId()) {
                $arr_files[] = '["---' .$file->Folder->getName() . '---", ""]';
            }

            $path = $path_begin . ASSETS . DS . 'files' . DS . 'uploads' . DS . $text->cleanForUrl($file->getName()) . '_' . $file->getId() . '.' . $file->getType();
            $arr_files[] = '["' . $file->getName() . '.' . $file->getType() . '", "' . $path . '"]';
            $folder_id = $file->Folder->getId();
        }
        $linklist .= implode(',', $arr_files);
        $linklist .= ');';
        header('Content-type: text/javascript');
        print $linklist;
    }
}
?>
