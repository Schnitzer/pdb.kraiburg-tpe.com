<?php
/**
 * Contains the Ncw_Bootstrap class.
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
 * Include the Configure class.
 */
require_once 'ncw/Exception.php';
/**
 * Include the Configure class.
 */
require_once 'ncw/Object.php';
/**
 * Include the Configure class.
 */
require_once 'ncw/Configure.php';
/**
 * Include the folder configurations.
 */
require_once 'ncw/config/paths.php';
/**
 * Include the core configurations.
 */
require_once 'config/core.php';
/**
 * Include the php ini configurations.
 */
require_once 'ncw/config/php.php';
// Only included FirePHP if the debug mode is on.
if (Ncw_Configure::read('debug_mode') > 0) {
    /**
     * Include the FirePHP class
     */
    include_once 'ncw/vendor/firephp/lib/FirePHPCore/fb.php';
}
/**
 * Include basic functions
 */
include_once 'ncw/basics.php';
/**
 * Ncw_Bootstrap class
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_Bootstrap extends Ncw_Object
{

    /**
     * Timer object
     *
     * @var Ncw_Library_Timer
     */
    public static $timer = null;

    /**
     * Register autoloader and start the dispatcher
     *
     */
    public function __construct ()
    {
        // redirect example.org to www.exampl­e.org
        if (true === Ncw_Configure::read('App.only_www')
            && Ncw_Configure::read('Project.domain') !== 'localhost'
            && $_SERVER['SERVER_NAME'] == Ncw_Configure::read('Project.domain')
        ) {
           header('HTTP/1.1 301 Moved Permanently');
           header('Location: http://www.' . Ncw_Configure::read('Project.domain') . $_SERVER['REQUEST_URI']);
           $this->_stop();
        }

        // relative uri
        Ncw_Configure::write('Project.relative_uri', str_replace('http://' . Ncw_Configure::read('Project.domain'), '', Ncw_Configure::read('Project.url')));

    	try {
	        // Register the autoload function
	        self::_registerAutoload();
	        // make sure to catch every exception.
	        set_exception_handler(array('Ncw_Exception', 'exceptionHandler'));
	        // If debug mode is on.
	        if (Ncw_Configure::read('debug_mode') > 0) {
	            error_reporting(E_ALL | E_NOTICE);
	            self::$timer = new Ncw_Library_Timer();
	            // Start the time benchmark
	            self::$timer->start();
	        } else {
	            error_reporting(0);
	        }

	        // Dispatch
	        $dispatcher = new Ncw_Dispatcher();
	        $dispatcher->dispatch();
    	} catch (Exception $e) {
    		if (Ncw_Configure::read('debug_mode') > 0) {
                print $e->getMessage();
    		}
    	}
    }

    /**
     * Autoload function
     *
     * @param string $class_name the class name
     *
     * @return void
     */
    public static function autoload ($class_name = '')
    {
        try {
            if (false !== strpos($class_name, 'Swift')) {
                return false;
            }
            $class_map = self::_classMap();
            if (true === isset($class_map[$class_name])) {
                if (false === include_once $class_map[$class_name]) {
                    throw new Ncw_Exception(
                        $class_map[$class_name] . ' does not exist!'
                    );
                }
            } else {
                $path = explode('_', $class_name);
                $file = array_pop($path) . '.php';
                foreach ($path as &$part) {
                    $part[0] = strtolower($part[0]);
                }
                if ($file !== 'AppController.php'
                    && $file !== 'ModuleController.php'
                ) {
                    if (false !== strpos($file, 'Controller')) {
                        $path[] = 'controllers';
                    } else if (true === in_array('components', $path)) {
                        $path[1] = 'controllers' . DS . 'components';
                    } else if (true === in_array('helpers', $path)) {
                        $path[1] = 'views' . DS . 'helpers';
                    } else {
                        $path[] = 'models';
                    }
                }
                if (false === include_once MODULES . DS . implode(DS, $path) . DS . $file) {
                    throw new Ncw_Exception(
                        MODULES . DS . implode(DS, $path)
                        . DS . $file . ' does not exist!'
                        );
                }
            }
        } catch (Ncw_Exception $e) {
            if (Ncw_Configure::read('debug_mode') > 0) {
                $e->exitWithMessage();
            }
        }
        return true;
    }

    /**
     * Configure autoloading.
     *
     * @return void
     */
    protected static function _registerAutoload ()
    {
        spl_autoload_register(array('Ncw_Bootstrap', 'autoload'));
    }

    /**
     * Returns the ncw class map.
     *
     * @return Array
     */
    protected static function _classMap ()
    {
        // return self::_createMap();
        return array(
			'Ncw_Components_Captcha' => 'ncw/controller/components/Captcha.php',
            'Ncw_Components_Session' => 'ncw/controller/components/Session.php',
            'Ncw_Components_Request' => 'ncw/controller/components/Request.php',
			'Ncw_Components_Header' => 'ncw/controller/components/Header.php',
			'Ncw_Components_Email' => 'ncw/controller/components/Email.php',
			'Ncw_Components_File' => 'ncw/controller/components/File.php',
			'Ncw_Components_Acl' => 'ncw/controller/components/Acl.php',
			'Ncw_Components_Folder' => 'ncw/controller/components/Folder.php',
            'Ncw_Components_Crypter' => 'ncw/controller/components/Crypter.php',
            'Ncw_Components_RequestHandler' => 'ncw/controller/components/RequestHandler.php',
            'Ncw_Component' => 'ncw/controller/Component.php',
            'Ncw_Components' => 'ncw/controller/Components.php',
            'Ncw_Controller' => 'ncw/controller/Controller.php',

            'Ncw_Model' => 'ncw/model/Model.php',
            'Ncw_TreeModel' => 'ncw/model/TreeModel.php',
            'Ncw_ModelList' => 'ncw/model/ModelList.php',
            'Ncw_DataModel' => 'ncw/model/DataModel.php',

			'Ncw_Helpers_Ajax' => 'ncw/view/helpers/Ajax.php',
			'Ncw_Helpers_Javascript' => 'ncw/view/helpers/Javascript.php',
			'Ncw_Helpers_Form' => 'ncw/view/helpers/Form.php',
            'Ncw_Helpers_Cache' => 'ncw/view/helpers/Cache.php',
            'Ncw_Helpers_Html' => 'ncw/view/helpers/Html.php',
            'Ncw_Helpers_Text' => 'ncw/view/helpers/Text.php',
            'Ncw_Helpers_Paginator' => 'ncw/view/helpers/Paginator.php',
            'Ncw_Helpers_Asset' => 'ncw/view/helpers/Asset.php',
            'Ncw_Helper' => 'ncw/view/Helper.php',
            'Ncw_View' => 'ncw/view/View.php',
            'Ncw_MediaView' => 'ncw/view/MediaView.php',

			'Ncw_Validations_MaxLength' => 'ncw/validations/MaxLength.php',
			'Ncw_Validations_Ip' => 'ncw/validations/Ip.php',
			'Ncw_Validations_NotEmpty' => 'ncw/validations/NotEmpty.php',
			'Ncw_Validations_Between' => 'ncw/validations/Between.php',
			'Ncw_Validations_Numeric' => 'ncw/validations/Numeric.php',
			'Ncw_Validations_Username' => 'ncw/validations/Username.php',
			'Ncw_Validations_Boolean' => 'ncw/validations/Boolean.php',
			'Ncw_Validations_Integer' => 'ncw/validations/Integer.php',
			'Ncw_Validations_Email' => 'ncw/validations/Email.php',
			'Ncw_Validations_Decimal' => 'ncw/validations/Decimal.php',
			'Ncw_Validations_Equal' => 'ncw/validations/Equal.php',
			'Ncw_Validations_Date' => 'ncw/validations/Date.php',
			'Ncw_Validations_Url' => 'ncw/validations/Url.php',
			'Ncw_Validations_Unique' => 'ncw/validations/Unique.php',
			'Ncw_Validations_Time' => 'ncw/validations/Time.php',
			'Ncw_Validations_Phonenumber' => 'ncw/validations/Phonenumber.php',
			'Ncw_Validations_DateTime' => 'ncw/validations/DateTime.php',
			'Ncw_Validations_AlphaNumeric' => 'ncw/validations/AlphaNumeric.php',
			'Ncw_Validations_ExactLength' => 'ncw/validations/ExactLength.php',
			'Ncw_Validations_MinLength' => 'ncw/validations/MinLength.php',
			'Ncw_Validations_Password' => 'ncw/validations/Password.php',
			'Ncw_Validations_Letter' => 'ncw/validations/Letter.php',
			'Ncw_Validations_InList' => 'ncw/validations/InList.php',
			'Ncw_Validations_RegExp' => 'ncw/validations/RegExp.php',
			'Ncw_Validations_Alpha' => 'ncw/validations/Alpha.php',

			'Ncw_Library_Log_Logger_Firebug' => 'ncw/library/log/logger/Firebug.php',
			'Ncw_Library_Log_Logger_File' => 'ncw/library/log/logger/File.php',
			'Ncw_Library_Log_Logger_Composite' => 'ncw/library/log/logger/Composite.php',
			'Ncw_Library_Log_Log' => 'ncw/library/log/Log.php',
			'Ncw_Library_Timer' => 'ncw/library/Timer.php',
			'Ncw_Library_LogFactory' => 'ncw/library/LogFactory.php',
			'Ncw_Library_Sanitizer' => 'ncw/library/Sanitizer.php',

			'Ncw_Dispatcher' => 'ncw/Dispatcher.php',
			'Ncw_Database' => 'ncw/Database.php',
			'Ncw_Validator' => 'ncw/Validator.php',
			'Ncw_Router' => 'ncw/Router.php',
			'Ncw_Exception' => 'ncw/Exception.php',
			'Ncw_Bootstrap' => 'ncw/Bootstrap.php',
			'Ncw_Validation' => 'ncw/Validation.php',
			'Ncw_Describer' => 'ncw/Describer.php',
			'Ncw_Object' => 'ncw/Object.php',
			'Ncw_Configure' => 'ncw/Configure.php',
        );
    }

    /**
     * Creates the class map
     *
     * @param string $folder the folder to read
     *
     * @return Array
     */
    protected static function _createMap ($folder = NCW)
    {
        $files = array();
        if (true === is_resource($handle = opendir($folder))) {
            while (false !== $file = readdir($handle)) {
                if ($file != "."
                    && $file != ".."
                    && $file != '.svn'
                    && $file != 'external'
                ) {
                    $path = $folder . DS . $file;
                    if (true === is_dir($path)) {
                        $files = array_merge(self::createMap($path), $files);
                    } else {
                        $class_name = '';
                        foreach (explode(DS, $folder) as $part) {
                            $class_name .= ucfirst($part) . '_';
                        }
                        $class_name .= str_replace('.php', '', $file);
                        $files[$class_name] = $path;
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }
}
?>