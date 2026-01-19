<?php
/* SVN FILE: $Id$ */
/**
 * Contains the FolderController class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright		Copyright 2007-2009, Netzcraftwerk UG (haftungsbeschränkt)
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * FolderController class.
 *
 * @package netzcraftwerk
 */
class Files_FolderController extends Files_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "Files";

    /**
     * Layout
     *
     * @var string
     */
    public $layout = 'blank';

    /**
     * The ftp configurations
     *
     * @var Array
     */
    public $configs = array('ftp.config');
	
    /**
     * Pagnination options
     *
     * @var array
     */
    public $paginate = array(
        'order' => 'Folder.name',
    );	

	/**
	 * Show files action.
	 *	 *
	 * @return void
	 */
	public function allAction ()
	{
	    if (false === $this->request_handler->isAjax()) {
           $this->layout = 'default';
        }

        $this->registerJs(array('ncw.files.folder', 'ncw.files.file'));
				
        $this->registerCss(array('files'));

		// permissions
        $this->view->permissions = array(
        	'/files/folder/new' => $this->acl->check('/files/folder/new'),
        	'/files/folder/edit' => $this->acl->check('/files/folder/edit'),
        );
	}
	
	
	public function dialogAction ()
	{
      $this->view->only_types = '';
      if (true === isset($this->params['url']['ot'])) {
        $ot = explode(',', $this->params['url']['ot']);
        foreach ($ot as &$type) {
          if (true == in_array($type, array('jpg', 'jpeg', 'png', 'gif'))) {
            $type = 'image/' . $type;
          }
        }
        $this->view->only_types = implode(',', $ot);
      }

      $this->view->permissions = array(
          '/files/file/all' => $this->acl->check('/files/file/all'),
          '/files/file/new' => $this->acl->check('/files/file/new'),
      );
	}

	/**
	 * New folder action.
	 *
	 * @param int $parent_id
	 *
	 * @return void
	 */
	public function newAction ($parent_id = 1)
	{
		$this->Folder->unbindModel('all');
		$this->view->folders_options = $this->folderSelectOptions($parent_id);
	}

	/**
	 * save action
	 *
	 * @return void
	 */
	public function saveAction ()
	{
	    $this->view = false;

	   if (true === isset($this->data['Folder'])) {
            $this->Folder->data($this->data['Folder']);
            if (true === $this->Folder->save()) {
                print '{"return_value" : true, "folder_id" : ' . $this->Folder->getId() . '}';
            } else {
            	print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->Folder->invalidFields()) . '}';
            }
        }

        
	}

	/**
	 * Edit folder action.
	 *
	 * @param int $id the folder id
	 *
	 * @return void
	 */
	public function editAction ($folder_id)
	{
    $folder_id = (int) $folder_id;

    $this->Folder->setId($folder_id);
    $this->Folder->unbindModel('all');
    $this->Folder->read();

    $this->view->folders_options = $this->folderSelectOptions(
    $this->Folder->getParentId(), $folder_id, 0
    );

    $this->view->folder_id = $folder_id;
    $this->view->folder_name = $this->Folder->getNameEncoded();
    $this->view->folder_parent_id = $this->Folder->getParentId();
    $this->data['Folder'] = $this->Folder->data();

    // permissions
    $this->view->permissions = array(
      '/files/folder/delete' => $this->acl->check('/files/folder/delete'),
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

	    if (true === isset($this->data['Folder'])) {
            $this->Folder->data($this->data['Folder']);
	        if (true === $this->Folder->saveFields(array('name', 'parent_id'))) {
                print '{"return_value" : true}';
            } else {
            	print '{"return_value" : false, "invalid_fields" : ' . json_encode($this->Folder->invalidFields()) . '}';
            }
        }
	}

	/**
	 * delete folder action.
	 *
	 * @param int $id the folder id
	 *
	 * @return void
	 */
	public function deleteAction ($id)
	{
		$this->view = false;

		$this->Folder->setId($id);
		$this->Folder->delete();

		print '{"return_value" : true}';
	}

    /**
     * Builds a tree
     *
     * @return string the tree
     */
    public function treeAction ()
    {
        // read the sites
        $this->Folder->unbindModel('all');
        $folders = $this->Folder->fetch(
            'all',
            array(
              'conditions' => array('Folder.id !=' => 1),
              'fields' => array(
                  'Folder.id',
                  'Folder.name',
                  'Folder.parent_id',
              ),
              'order' => array('Folder.parent_id', 'Folder.name')
            )
        );
        $all_folders = array();
        foreach ($folders as $folder) {
            $all_folders[$folder->getParentId()][] = array(
                'id' => $folder->getId(),
                'name' => $folder->getName(),
                'parent_id' => $folder->getParentId(),
            );
        }

        $this->view->tree = $this->_treeDepth($all_folders, 1);

        // ftp tree
        $ftp_folder = Ncw_Configure::read('FTP_FOLDER');
        if ($ftp_folder != ''
            || (true === is_array($ftp_folder)
            && $ftp_folder[0] != '')
        ) {
            if (true === is_string($ftp_folder)) {
                $ftp_folder = array($ftp_folder);
            }
            $tree = array();
            foreach ($ftp_folder as $folder) {
                $tree[] = $this->_ftpTree($folder);
            }
            $this->view->ftp_tree = $tree;
            $this->view->ftp_folder = $ftp_folder;
        } else {
            $this->view->ftp_tree = '';
            $this->view->ftp_folder = '';
        }
    }

    /**
     * Builds the ftp tree
     *
     * @param string $dir
     *
     * @return string
     */
    protected function _ftpTree ($dir = '')
    {
        $tree = '';

        $first_dir = true;
        $verz = opendir($dir);
        while ($file = readdir($verz)) {
            $file_path = $dir . DS . $file;
            if ($file != "."
                && $file != ".."
                && $file[0] != "."
                && true === is_dir($file_path)
            ) {
                if (true === $first_dir) {
                    $tree .= '<ul>';
                    $first_dir = false;
                }


                $tree .= '<li rel="ftp" name="' . $file_path . '" class="ncw-tree-ftp-folder">';
                $tree .= '<a href="#" style="text-decoration: none !important"><ins>&nbsp;</ins>'
                    .  $file . '</a>';

                $tree .= $this->_ftpTree($file_path);

                $tree .= '</li>';
            }
        }
        closedir($verz);

        if (false === $first_dir) {
            $tree .= '</ul>';
        }

        return $tree;
    }

    /**
     * Gets a tree depths
     *
     * @param array $all_sites
     * @param int $parent_id
     *
     * @return string
     */
    protected function _treeDepth ($all_folders, $parent_id = 1)
    {
        $tree = '';
        if (true === isset($all_folders[$parent_id])) {
            $tree .= '<ul>';
            foreach ($all_folders[$parent_id] as $folder) {

                $tree .= '<li id="ncw-folder-' . $folder['id'] . '">';
                $tree .= '<a href="#" style="text-decoration: none !important"><ins>&nbsp;</ins>'
                    .  $folder['name'] . '</a>';

                $tree .= $this->_treeDepth($all_folders, $folder['id']);

                $tree .= '</li>';

            }
            $tree .= '</ul>';
        }
        return $tree;
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
	 * Search action
	 */
	public function searchAction () 
	{
		if ($this->request_handler->responseType() == 'javascript') {
           $this->layout = 'default';
        }

        $conditions = array();
		
        $str_search = '';
        if (true === isset($this->params['url']['s'])) {
            $str_search = trim(Ncw_Library_Sanitizer::escape($this->params['url']['s']));
        }
        $conditions = array(
            'Folder.name LIKE \'%' . $str_search . '%\'',
        );

        $this->view->search = true;
        if (true === isset($this->params['url']['s'])) {
            $this->view->search_value = $this->params['url']['s'];
        } else {
            $this->view->search_value = '';
        }
		
        $this->view->folders = $this->paginate($conditions);
	}	
}
?>
