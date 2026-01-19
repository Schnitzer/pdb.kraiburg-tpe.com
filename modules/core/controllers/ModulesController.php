<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ModulesController class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author             Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright        Copyright 2007-2008, Netzcraftwerk GmbH
 * @link            http://www.netzcraftwerk.com
 * @package            netzcraftwerk
 * @since            Netzcraftwerk v 3.0.0.1
 * @version            Revision: $LastChangedRevision$
 * @modifiedby        $LastChangedBy$
 * @lastmodified    $LastChangedDate$
 * @license            http://www.netzcraftwerk.com/licenses/
 */
/**
 * ModulesController class.
 *
 * @package netzcraftwerk
 */
class Core_ModulesController extends Core_ModuleController
{

    /**
     * The page title
     *
     * @var string
     */
    public $page_title = "Module Management";

    /**
     * Use these components
     *
     * @var array
     */
    public $components = array('Acl', 'Folder', 'File');

    /**
     * shows the installed modules
     */
    public function indexAction ()
    {
        $all_requirements = array();
        foreach ($this->installed_modules as $module_values) {
            $requirements = explode(';', $module_values['requirements']);
            foreach ($requirements as $requirement) {
                if (true === empty($requirement)) {
                    continue;
                }
                $all_requirements[$requirement][] = $module_values['name'];
            }
        }
        $this->view->module_requirements = $all_requirements;
        
        $this->view->modules = $this->installed_modules;
        
        $this->registerJs(
            array(
                'ncw.core.modules',
            )
        );
    }

    public function activateAction ($module_id)
    {
        $this->view = false;
        $module_id = (int) $module_id;
        $this->Modules->setId($module_id);
        $this->Modules->setActive(true);
        $this->Modules->saveField('active');
    }
    
    public function deactivateAction ($module_id)
    {
        $this->view = false;
        $module_id = (int) $module_id;
        $this->Modules->setId($module_id);
        $this->Modules->setActive(false);
        $this->Modules->saveField('active');
    }

    /**
     * Show uninstalled modules action.
     *
     */
    public function showUninstalledModulesAction ()
    {
        $modules = array();
        if (true === is_resource($handle = opendir(MODULES))) {
            while (false !== ($file = readdir($handle))) {
                $xml_file = MODULES . DS . $file . DS . "install.xml";
                if ($file != "." 
                    && $file != ".." 
                    && true === is_file($xml_file)
                ) {
                    if (false === isset($this->installed_modules[$file])) {
                        $can_install = true;
                        $xml = simplexml_load_file($xml_file);
                        $requires = explode(';', $xml->attributes()->requires);
                        $missing_requirements = array();
                        foreach ($requires as $requirement) {
                            if (false === empty($requirement) 
                                && false === isset($this->installed_modules[$requirement])
                                ) {
                                $can_install = false;
                                $missing_requirements[] = $requirement;
                            }
                        }
                        $modules[] = array(
                            'name' => $xml->attributes()->name,
                            'version' => $xml->attributes()->version,
                            'folder_name' => $file,
                            'requires' => $xml->attributes()->requires,
                            'missing_requirements' => implode(', ', $missing_requirements),
                            'can_install' => $can_install,
                        );
                    }
                }
            }
            closedir($handle);
        }
        $this->view->modules = $modules;
        
        $this->registerJs(
            array(
                'ncw.core.modules',
            )
        );
    }
    /**
     * Downloads a file
     */
    protected function _downloadFile ($url, $path) {
        set_time_limit(0);
        $newfname = $path;
        $file = fopen ($url, "rb");
        if ($file) {
            $newf = fopen ($newfname, "wb");
        
            if ($newf)
            while(!feof($file)) {
                fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
            }
        }
        
        if ($file) {
            fclose($file);
        }
          
        if ($newf) {
              fclose($newf);
        }
     }
    
    /**
     * Show the Module which can be imported
     * 
     */
    public function showModuleToImportAction ()
    {
        $modules = array();
        
        $this->loadModel('Setting');
        $this->Setting->setId(1);
        $module_repo_url = $this->Setting->readField('module_repo_url');
        $module_repo_key = $this->Setting->readField('module_repo_key');        
        
        $content = file_get_contents(
            $module_repo_url
            . "index.php?key=". $module_repo_key . "&mode=0" 
        );
        if (false === empty($content)) {
            $imported_modules = array();
            if ($handle = opendir(MODULES)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file == '.' || $file == '..' || false == is_dir(MODULES . DS . $file)) {
                        continue;
                    }                    
                    $imported_modules[] = $file;
                }
                closedir($handle);
            }
            
            if ($content != 'Forbidden') {
                $json_content = json_decode($content);
                foreach ($json_content as $module) {
                    if (false == in_array($module->name, $imported_modules)) {
                        $modules[] = array(
                            'name' => $module->name,
                            'version' => $module->version
                        );
                    }
                }                
            }
        }
        $this->view->modules = $modules;
        
        $this->registerJs(
            array(
                'ncw.core.modules',
            )
        );
    }
    
    /**
     * Import a module
     * 
     */
    public function importModuleAction ($module_name)
    {
        $this->view = false;

        $module_name = Ncw_Library_Sanitizer::clean($module_name);

        $this->loadModel('Setting');
        $this->Setting->setId(1);
        $module_repo_url = $this->Setting->readField('module_repo_url');
        $module_repo_key = $this->Setting->readField('module_repo_key');
        
        $content = file_get_contents(
            $module_repo_url
            . "index.php?" . $module_repo_key . "&mode=2" 
            . "&module=" . $module_name
        );
        if ($content != '0') {
            $path = 'tmp/files/' . md5($content) . '.zip';
            
            $this->_downloadFile(
                $module_repo_url . $content,
                $path
            );

            $zip = new ZipArchive;
            $res = $zip->open($path);
            if ($res === TRUE) {
                $zip->extractTo(MODULES . DS . $module_name);
                $zip->close();
            }

            unlink($path);
            print '{"return_value" : true}';
        } else {
            print '{"return_value" : false}';
        }
    }
}
?>
