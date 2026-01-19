<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ModuleController class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk UG (haftungsbeschränkt)
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
class Tpepdb2_ModuleController extends AppController
{

    /**
     * General translations. only for translation tool
     *
     * @return void
     */
    private function __generalTranslations ()
    {
        T_('Applications');
        T_('Other');
        T_('Logout');
        T_('Profile');

        T_('Alert');
        T_('Choose your options!');
        T_('Confirm');
        T_('Make a decision!');
        T_('Do you really want to delete this object?');
        T_('Saved');
        T_('Your data has been saved successfully.');
        T_('Error');
        T_('An error has occurred!');
        T_('Notify');
        T_('Be aware that...');
        T_('Custom');
        T_('Saving...');
        T_('Deleting...');
        T_('The item has been deleted successfully');
    }

    /**
     * Before filter
     *
     */
    public function beforeFilter ()
    {
        parent::beforeFilter();
    }

    /**
     * before Render
     *
     */
    public function beforeRender ()
    {
        if ($this->layout === 'default') {
            parent::beforeRender();

            $html = new Ncw_Helpers_Html();
            $html->startup($this->view);
        }
    }
  
  		protected function _language_3stellen($language_id)
		{
			if ($language_id > 0) {
				if ($language_id == '1') {
					return 'eng';
				}
				if ($language_id == '2') {
					return 'ger';
				}
				if ($language_id == '5') {
					return 'fre';
				}
				if ($language_id == '8') {
					return 'ita';
				}
				if ($language_id == '11') {
					return 'pol';
				}
				if ($language_id == '9') {
					return 'por';
				}

				if ($language_id == '4') {
					return 'spa';
				}
				if ($language_id == '7') {
					return 'jpn';
				}
				if ($language_id == '3') {
					return 'chi';
				}
				if ($language_id == '10') {
					return 'kor';
				}
			} else {
				if ($_SESSION['language'] == 'en') {
					return 'eng';
				}
				if ($_SESSION['language'] == 'de') {
					return 'ger';
				}
				if ($_SESSION['language'] == 'fr') {
					return 'fre';
				}
				if ($_SESSION['language'] == 'it') {
					return 'ita';
				}
				if ($_SESSION['language'] == 'pl') {
					return 'pol';
				}
				if ($_SESSION['language'] == 'pt') {
					return 'por';
				}
				if ($_SESSION['language'] == 'es') {
					return 'spa';
				}
				if ($_SESSION['language'] == 'sp') {
					return 'spa';
				}
				if ($_SESSION['language'] == 'jp') {
					return 'jpn';
				}
				if ($_SESSION['language'] == 'zh') {
					return 'chi';
				}
				if ($_SESSION['language'] == 'kr') {
					return 'kor';
				}
			}

			return 'eng';
		}
  
  
  			protected function _language_2stellen($language_id)
		{
			if ($language_id > 0) {
				if ($language_id == '1') {
					return 'en';
				}
				if ($language_id == '2') {
					return 'de';
				}
				if ($language_id == '5') {
					return 'fr';
				}
				if ($language_id == '8') {
					return 'it';
				}
				if ($language_id == '11') {
					return 'po';
				}
				if ($language_id == '9') {
					return 'pt';
				}

				if ($language_id == '4') {
					return 'es';
				}
				if ($language_id == '7') {
					return 'jp';
				}
				if ($language_id == '3') {
					return 'zh';
				}
				if ($language_id == '10') {
					return 'kr';
				}
			} else {
				if ($_SESSION['language'] == 'en') {
					return 'en';
				}
				if ($_SESSION['language'] == 'de') {
					return 'de';
				}
				if ($_SESSION['language'] == 'fr') {
					return 'fr';
				}
				if ($_SESSION['language'] == 'it') {
					return 'it';
				}
				if ($_SESSION['language'] == 'pl') {
					return 'pl';
				}
				if ($_SESSION['language'] == 'pt') {
					return 'pt';
				}

				if ($_SESSION['language'] == 'es') {
					return 'es';
				}
				if ($_SESSION['language'] == 'jp') {
					return 'sp';
				}
				if ($_SESSION['language'] == 'zh') {
					return 'ch';
				}
				if ($_SESSION['language'] == 'kr') {
					return 'kr';
				}
			}

			return 'eng';
		}
  
}
?>
