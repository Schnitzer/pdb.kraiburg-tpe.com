<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ComponentfileController class.
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
 * ComponentfileController class.
 *
 * @package netzcraftwerk
 */
class Wcms_ComponentfileController extends Wcms_ModuleController
{

	/**
     * Components...
     *
     * @var array
     */
    public $components = array('Acl');

	/**
	 * edit meta data action.
	 *
	 * @param int $id
	 * @param string $language_code
	 */
	public function editMetaAction ($id, $language_code)
	{
		$this->layout = 'blank';

		$this->Componentfile->setId($id);
		$this->Componentfile->read(
            array(
                'fields' => array(
                    'Componentfile.alt',
                    'Componentfile.title',
                    'Componentfile.link',
                    'Componentfile.target'
                )
            )
        );
		$this->data['Componentfile'] = $this->Componentfile->data();
		$this->view->id = $id;

		$linklist = array();
		$this->siteLinklist(
            $linklist,
            $language_code,
            false
		);
		$this->view->linklist = $linklist;

		$this->view->link = $this->Componentfile->getLink();
		$this->view->targets = array(
		  T_('Open in this window / frame') => '_self',
		  T_('Open in new window (_blank)') => '_blank',
		  T_('Open in new parent window / frame (_parent)') => '_parent',
		  T_('Open in top frame (replaces all frames) (_top)') => '_top',
	   );
	}

	/**
	 * save meta data action.
	 *
	 * @param int $id
	 */
	public function saveMetaAction ($id)
	{
		$this->view = false;
		$this->Componentfile->setId($id);
		$this->Componentfile->data($this->data['Componentfile']);
		print '{"return_value" : ' . $this->Componentfile->saveFields(array('alt', 'title', 'link', 'target')) . '}';
	}

    public function blablaAction ()
    {
        $this->view = false;
        $this->loadModel('PublishedComponenttext');

        $componenttexts = $this->PublishedComponenttext->fetch(
            'all',
            array(
                'fields' => array(
                    'PublishedComponenttext.id',
                    'PublishedComponenttext.content',
                ),
                /*'conditions' => array(
                    'PublishedComponenttext.componentlanguage_id' => 443
                )*/
            )
        );

        print count($componenttexts);

        foreach ($componenttexts as $componenttext) {
            $content = $componenttext->getContent();
            $content = preg_replace('/(src|href)="([.\/a-zA-Z0-9_-]+)\/([.\/a-zA-Z0-9_-]+)"/i', '$1="/assets/files/uploads/$3"', $content);

            $this->PublishedComponenttext->setId($componenttext->getId());
            $this->PublishedComponenttext->setContent($content);
            $this->PublishedComponenttext->saveField('content');
        }
    }
}
?>
