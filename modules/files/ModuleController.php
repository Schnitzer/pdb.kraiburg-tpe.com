<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ModuleController class.
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
 * ModuleController class.
 *
 * @package netzcraftwerk
 */
class Files_ModuleController extends AppController
{

    /**
     * General translations. only for translation tool
     *
     * @return void
     */
    private function __generalTranslations ()
    {
        T_('Applications');
        T_('Logout');
        T_('Profile');
    }

    /**
     * Before filter
     *
     */
    public function beforeFilter ()
    {
        parent::beforeFilter();
        if (Ncw_Configure::read('App.language') != 'en_EN') {
            $this->registerJs('locale/' . Ncw_Configure::read('App.language') . '/LC_MESSAGES/default');
        }
        $this->registerJs('ncw.files');
    }

	/**
	 * Init
	 *
	 */
	public function beforeRender ()
	{
        if ($this->layout === 'default') {
            parent::beforeRender();

            $html = new Ncw_Helpers_Html();
            $html->startup($this->view);

            $this->view->menu = array(
                $html->link(T_('Files'), array('controller' => 'folder', 'action' => 'all')),
                $html->link(T_('Show files'), array('controller' => 'folder', 'action' => 'all'), array('class' => (($this->name == 'Folder') ? 'opened' : ''))),
            );
        }
	}

    /**
     * Folder select opionts
     *
     * @param mixed $not_this_site
     * @param int $parent_id
     * @param int $depth
     *
     * @return string
     */
    public function folderSelectOptions ($parent_id = 0, $not_this_folder = false, $start_parent_id = 0, $depth = 0)
    {
        $folder = new Files_Folder();
        $folder->unbindModel('all');
        $parent_options = $folder->fetch(
            'all',
             array(
                 'fields' => array(
                     'Folder.id',
                     'Folder.name'
                 ),
                 'conditions' => array('Folder.parent_id' => $start_parent_id),
                 'order' => array('Folder.parent_id', 'Folder.name')
             )
        );
        $options = '';
        foreach ($parent_options as $option) {
            if ($not_this_folder == $option->getId()) {
                continue;
            }
            $selected = '';
            if ($parent_id == $option->getId()) {
                $selected = ' selected="selected"';
            }
			$name = $option->getName();
			if ($option->getId() == 1) {
				$name = T_('First level');
			}
            $options .= '<option value="' . $option->getId() . '"' . $selected. '>' . str_repeat('--', $depth) . ' ' . $name . '</option>';
            $options .= $this->folderSelectOptions($parent_id, $not_this_folder, $option->getId(), $depth + 1);
        }
        return $options;
    }
}
?>
