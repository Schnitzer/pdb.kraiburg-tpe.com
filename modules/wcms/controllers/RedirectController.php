<?php
/* SVN FILE: $Id$ */
/**
 * Contains the RedirectController class.
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
 * RedirectController class.
 *
 * @package netzcraftwerk
 */
class Wcms_RedirectController extends Wcms_SitetypeController
{

    /**
     * redirect main data action
     *
     * @param int $site_id
     *
     * @return void
     */
    public function dataMainAction ($site_id)
    {
        $redirect = $this->Redirect->findBy(
            'site_id',
            $site_id
        );
        if (false != $redirect) {
            $this->data['Redirect'] = $redirect->data();
            $this->view->link = $this->data['Redirect']['url'];
        } else {
            $this->view->link = '';
        }

        $linklist = array();
        $this->siteLinklist(
            $linklist,
            '',
            false
        );
        $this->view->linklist = $linklist;
    }

    /**
     * Update action
     *
     * @param int $sitelanguage_id
     *
     * @return void
     */
    public function updateAction ($site_id)
    {
        $this->view = false;
        if (true === isset($this->data['Redirect'])) {
            $this->Redirect->setSiteId($site_id);
            $this->Redirect->data($this->data['Redirect']);
            $this->Redirect->save();
        }
    }

    /**
     * Publish Action
     *
     * @param int $site_id
     *
     * @return void
     */
    public function publishAction ($site_id)
    {
        $this->view = false;

        $found_model = $this->Redirect->findBy(
            'site_id',
            $site_id
        );
        if (false !== $found_model) {
            $this->publishObject('Redirect', $found_model);
        }
    }

    /**
     * Unpublish Action
     *
     * @param int $site_id
     *
     * @return void
     */
    public function unpublishAction ($site_id)
    {
        $this->view = false;

        $this->loadModel('PublishedRedirect');
        $found_model = $this->PublishedRedirect->findBy(
            'site_id',
            $site_id,
            array(
                'fields' => array(
                    'PublishedRedirect.id'
                )
            )
        );
        if (false !== $found_model) {
            $this->PublishedRedirect->setId($found_model->getId());
            $this->PublishedRedirect->delete();
        }
    }

    /**
     * Delete action
     *
     * @param int $site_id
     *
     * @return void
     */
    public function deleteAction ($site_id)
    {
        $this->view = false;

        // unpublished
        $found_model = $this->Redirect->findBy(
            'site_id',
            $site_id,
            array(
                'fields' => array(
                    'Redirect.id'
                )
            )
        );
        if (false !== $found_model) {
            $this->Redirect->setId($found_model->getId());
            $this->Redirect->delete();
        }

        // published
        $this->loadModel('PublishedRedirect');
        $found_model = $this->PublishedRedirect->findBy(
            'site_id',
            $site_id,
            array(
                'fields' => array(
                    'PublishedRedirect.id'
                )
            )
        );
        if (false !== $found_model) {
            $this->PublishedRedirect->setId($found_model->getId());
            $this->PublishedRedirect->delete();
        }
    }

    /**
     * Website action
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

        $redirect = $this->Redirect->findBy(
            'site_id',
            $site_id
        );
        if (false != $redirect) {
            $redirect = $redirect->getUrl();
            if (false === empty($redirect)) {
                if (true === Ncw_Configure::read('App.rewrite')) {
                    $base = $this->base;
                } else {
                    $base = $this->base . '/index.php?url=';
                }
                $redirect = str_replace(
                    array('{project.url}', '{language.code}'),
                    array($base, $language_code),
                    $redirect
                );
                // redirect
                $header = new Ncw_Components_Header();
                $header->object->sendStatusCode(301);
                $header->object->redirect($redirect);
                exit();
            }
        }
        throw new Ncw_Exception('No redirection url set!');
    }
}
?>
