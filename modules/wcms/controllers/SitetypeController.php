<?php
/* SVN FILE: $Id$ */
/**
 * Contains the SitetypeController class.
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
 * SitetypeController class.
 *
 * @package netzcraftwerk
 */
class Wcms_SitetypeController extends Wcms_ModuleController
{


    /**
     * Layout...
     */
    public $layout = 'blank';

    /**
     * Main tabs to create
     *
     * @var array
     */
    public $tabs = array();

    /**
     * acl publics
     *
     * @var array
     */
    public $acl_publics = array('beforeWebsiteRender', 'afterWebsiteRender');

    /**
     * Publish Action
     *
     * @param int $id
     *
     * @return void
     */
    public function publishAction ($id)
    {
        $this->view = false;
    }

    /**
     * Unpublish Action
     *
     * @param int $id
     *
     * @return void
     */
    public function unpublishAction ($id)
    {
        $this->view = false;
    }

    /**
     * Update action
     *
     * @param int $id
     *
     * @return void
     */
    public function updateAction ($id)
    {
        $this->view = false;
    }

    /**
     * Delete action
     *
     * @param int $id
     *
     * @return void
     */
    public function deleteAction ($id)
    {
        $this->view = false;
    }

    /**
     * Replaces tags in code
     *
     * @param string $code the code
     *
     * @return string
     */
    public function replaceSiteTags ($code)
    {
        return $code;
    }

    /**
     * Before Website Render action
     *
     * @param int     $site_id
     * @param int     $sitelanguage_id
     * @param int     $language_id
     * @param string  $language_code
     * @param boolean $live
     *
     * @return void
     */
    public function beforeWebsiteRenderAction ($site_id, $sitelanguage_id, $language_id, $language_code, $live = true)
    {
        $this->view = false;
    }

    /**
     * After Website Render action
     *
     * @param int     $site_id
     * @param int     $sitelanguage_id
     * @param int     $language_id
     * @param string  $language_code
     * @param boolean $live
     *
     * @return void
     */
    public function afterWebsiteRenderAction ($site_id, $sitelanguage_id, $language_id, $language_code, $live = true)
    {
        $this->view = false;
    }
}
?>
