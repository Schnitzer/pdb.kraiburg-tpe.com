<?php
/* SVN FILE: $Id$ */
/**
 * Contains the SitelanguageController class.
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
 * SitelanguageController class.
 *
 * @package netzcraftwerk
 */
class Wcms_SitelanguageController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = 'Sites';

	/**
	 * Layout
	 *
	 * @var string
	 */
	public $layout = 'blank';

	/**
	 * New sitelanguage action.
	 *
	 * @param int   $site_id       the site id
	 * @param mixed $language_code the language code
	 *
	 * @return void
	 */
	public function newAction ($site_id, $language_code = false)
	{
		$this->view->site_id = $site_id;

		$this->Sitelanguage->unbindModel('all');
        $this->Sitelanguage->bindModel(
            array(
                'belongs_to' => array(
                    'Language'
                )
            )
        );
        $languages = $this->Sitelanguage->fetch(
            'list',
            array(
                'fields' => array(
                    'Language.id'
                ),
                'conditions' => array(
                    'Sitelanguage.site_id' => $site_id
                )
            )
        );
        $language_condition = array();
        foreach ($languages as $language_id) {
            $language_condition[] = 'Language.id !=' . $language_id;
        }

		$language = new Wcms_Language();
		$language->unbindModel('all');
        $languages = $language->fetch(
            "all",
            array(
                "fields" => array(
                    "Language.name",
                    "Language.id",
                    'Language.shortcut'
                ),
                'conditions' => $language_condition
            )
        );
        $list = array();
        foreach ($languages as $language) {
            $list[$language->getName()] = $language->getId();
            if ($language_code === $language->getShortcut()) {
                $this->data['Sitelanguage']['language_id'] = $language->getId();
            }
        }
        $this->view->arr_options = $list;
	}


     /**
     * Nach der neuanlage einer Sprache müssen die Seiteninhalte kopiert werden
     *
     */
    public function copyAllAction ()
    {
        //$this->view = false;
        $master_language = 1;
        $new_language_id = 10;
        echo 'Seiten kopieren';
        $obj_site = new Wcms_Site();
        $obj_site->unbindModel('all');
        $arr_sites = $obj_site->fetch('all');
        
        foreach ($arr_sites As $site) {
            if ($site->getId() != 164) {
                echo '<br />' . $site->getId() . ' name=' . $site->getId();
                
                $obj_sitelangugage = new Wcms_Sitelanguage();
                // original Seite lesen
                $arr_sitelanguage = $obj_sitelangugage->fetch('all', array('conditions' => array('site_id' =>$site->getId(), 'language_id' => $master_language)));
                
                if (count($arr_sitelanguage) > 0) {
                    echo $arr_sitelanguage[0]->getName();
                    echo $arr_sitelanguage[0]->getTitle();
                    echo $arr_sitelanguage[0]->getStatus();
                    echo $arr_sitelanguage[0]->getChangefreq();
                    echo $arr_sitelanguage[0]->getPriority();
                    
                    // Prüfen ob die Übersetzung schon vorhanden ist
                    $obj_sitelangugage_controle = new Wcms_Sitelanguage();
                    $arr_sitelanguage_controle = $obj_sitelangugage_controle->fetch('all', array('conditions' => array('site_id' =>$site->getId(), 'language_id' => $new_language_id)));
                    if (count($arr_sitelanguage_controle) < 1) {
                        // Duplikat anlegen 
                        $obj_sitelangugage_new = new Wcms_Sitelanguage();
                        $obj_sitelangugage_new->setLanguageId($new_language_id);
                        $obj_sitelangugage_new->setSiteId($site->getId());
                        $obj_sitelangugage_new->setName($arr_sitelanguage[0]->getName());
                        $obj_sitelangugage_new->setTitle($arr_sitelanguage[0]->getTitle());
                        $obj_sitelangugage_new->setStatus('new');
                        $obj_sitelangugage_new->setChangefreq($arr_sitelanguage[0]->getChangefreq());
                        $obj_sitelangugage_new->setPriority($arr_sitelanguage[0]->getPriority());
                        $obj_sitelangugage_new->setCreated(date('Y-m-d H:i:s'));
                        $obj_sitelangugage_new->create();
                        $obj_sitelangugage_new->save();
                        echo ' - angelegt';
                    } else {
                        echo ' Schon vorhanden';   
                    }
                }

                
            } else {
                 echo '<br /><b>' . $site->getId() . ' name=' . $site->getId() . ' nicht kopieren</b>';
            }
        }
    }

	/**
	 * Save new sitelanguage action
	 *
	 */
	public function saveAction ()
	{
	    $this->view = false;

	    $return = 'false';
        if (true === isset($this->data['Sitelanguage'])) {
            $this->data['Sitelanguage']['title'] = $this->data['Sitelanguage']['name'];
            $this->Sitelanguage->data($this->data['Sitelanguage']);
            $this->checkLanguageAccess($this->Sitelanguage->getLanguageId());
            if (true === $this->Sitelanguage->save()) {
                $return = 'true';
            }
        }
        print '{"return_value" : ' . $return . '}';
	}

	/**
	 * Edit sitelanguage action.
	 *
	 */
	public function editAction ($id)
	{
	    //$this->registerCss('backend');
		$this->Sitelanguage->setId($id);
		$this->checkLanguageAccess(
            $this->Sitelanguage->readField('language_id')
		);

        $this->Sitelanguage->bindModel(
            array('belongs_to' => array('Site'))
        );
        $this->Sitelanguage->read();
        $this->data['Sitelanguage'] = $this->Sitelanguage->data();

	    // sitetype
        $sitetype = new Wcms_Sitetype();
        $sitetype->setId($this->Sitelanguage->Site->getSitetypeId());
        list(
            $this->view->sitelanguagetype_tabs,
            $this->view->sitelanguagetype_content,
        ) = $this->sitetype(
            $sitetype->readField('sitelanguage_class'),
            $this->Sitelanguage->getId()
        );

		// read the site breadcrumb
        $this->view->url = $this->makeUrlForWebsite(
            $this->Sitelanguage->Site,
            false,
            $this->Sitelanguage->Language->getShortcut(),
            true,
            false
        );

		$this->view->sitelanguage_id = $id;
		$this->view->site_name = $this->Sitelanguage->Site->getName();
		$this->view->sitelanguage_name = $this->Sitelanguage->getName();
		$this->view->sitelanguage_status = $this->Sitelanguage->getStatus();
		$this->view->language_code = $this->Sitelanguage->Language->getShortcut();
		$this->view->language_name = $this->Sitelanguage->Language->getName();
		$this->view->is_home = (boolean) $this->Sitelanguage->getHome();
		$this->view->status = $this->Sitelanguage->getStatus();
		$this->view->changefreq = array(
		    T_('Always') => 'always',
		    T_('Hourly') => 'hourly',
		    T_('Dialy') => 'dialy',
		    T_('Weekly') => 'weekly',
		    T_('Monthly') => 'monthly',
		    T_('Yearly') => 'yearly',
		    T_('Never') => 'never',
		);
        $this->view->priority = array(
            '0.0',
            '0.1',
            '0.2',
            '0.3',
            '0.4',
            '0.5',
            '0.6',
            '0.7',
            '0.8',
            '0.9',
            '1.0',
        );
		$this->view->site_id = $this->data['Sitelanguage']['site_id'];

		$language = new Wcms_Language();
        $this->view->arr_options = $language->fetch(
            "list",
            array(
                "fields" => array("Language.name", "Language.id")
            )
        );

        // permissions
        $this->view->permissions = array(
            '/wcms/site/edit' => $this->acl->check('/wcms/site/edit'),
            '/wcms/sitelanguage/delete' => $this->acl->check('/wcms/sitelanguage/delete'),
            '/wcms/sitelanguage/publish' => $this->acl->check('/wcms/sitelanguage/publish'),
            '/wcms/sitelanguage/unpublish' => $this->acl->check('/wcms/sitelanguage/unpublish'),
        );
	}

	/**
	 * Updates the sitelanguage
	 *
	 */
	public function updateAction ()
	{
	   $this->view = false;

	   if (true === isset($this->data['Sitelanguage'])) {
            $this->Sitelanguage->data($this->data['Sitelanguage']);
            $this->Sitelanguage->saveFields(
                array(
                    'name',
                    'title',
                    'keywords',
                    'description',
                    'author',
                    'home',
                    'status',
                    'changefreq',
                    'priority',
                )
            );

            // update sitetype
            $site = new Wcms_Site();
            $site->setId($this->Sitelanguage->getSiteId());
            $site->unbindModel('all');
            $site->bindModel(
                array(
                    'belongs_to' => array('Sitetype')
	           )
            );
            $site->read(array('fields' => array('Sitetype.sitelanguage_class')));
            $this->sitetypeUpdate(
                $site->Sitetype->getSitelanguageClass(),
                $this->Sitelanguage->getId(),
                $this->data
            );

            print '{"return_value" : true}';
        } else {
            print '{"return_value" : false}';
        }
	}

	/**
	 * delete site action.
	 *
	 */
	public function deleteAction ($id)
	{
        $this->view = false;

    	$this->Sitelanguage->setId($id);

    	$this->checkLanguageAccess(
            $this->Sitelanguage->readField('language_id')
    	);

	    if (true === $this->Sitelanguage->delete()) {
            print '{return_value : true}';
        } else {
            print '{return_value : false}';
        }
	}

	/**
	 * publish action
	 *
	 * @param int $id
	 */
	public function publishAction ($id)
	{
		$this->view = false;

		// publish the sitelanguage
		$this->Sitelanguage->setId($id);

        $this->checkLanguageAccess(
           $this->Sitelanguage->readField('language_id')
        );

		$this->Sitelanguage->setStatus('published');
		$this->Sitelanguage->saveField('status');

		$this->Sitelanguage->unbindModel('all');
		$this->Sitelanguage->read();

		$this->publishObject('Sitelanguage', $this->Sitelanguage);

		$this->unpublishComponents($this->Sitelanguage);

		// publish the componentlanguages
		$componentlanguage = new Wcms_Componentlanguage();
		$componentlanguages = $componentlanguage->fetch(
            'all',
            array(
                'conditions' => array(
                    'Component.site_id' => $this->Sitelanguage->getSiteId(),
                    'Componentlanguage.language_id' => $this->Sitelanguage->getLanguageId(),
                    'Component.status !=' => 'unpublished'
                )
            )
        );

		foreach ($componentlanguages as $componentlanguage) {
			$this->publishObject('Componentlanguage', $componentlanguage);

			foreach ($componentlanguage->Componenttext as $componenttext) {
				$this->publishObject('Componenttext', $componenttext);
			}
			foreach ($componentlanguage->Componentshorttext as $componentshorttext) {
				$this->publishObject('Componentshorttext', $componentshorttext);
			}
			foreach ($componentlanguage->Componentfile as $componentfile) {
				$this->publishObject('Componentfile', $componentfile);
			}
			// publish the component
			$this->publishObject('Component', $componentlanguage->Component);
		}

	    // publish sitetype
        $sitetype = new Wcms_Sitetype();
        $site = new Wcms_Site();
        $site->setId($this->Sitelanguage->getSiteId());
        $sitetype->setId($site->readField('sitetype_id'));
        $sitetype->read();
        $this->sitetypePublish(
            $sitetype->getSitelanguageClass(),
            $id
        );

		$this->flushWebsiteCache();

        print '{"return_value" : true}';
	}

	/**
	 * Unpublish action
	 *
	 * @param int $id
	 */
	public function unpublishAction ($id)
	{
		$this->view = false;

		$this->Sitelanguage->setId($id);

        $this->checkLanguageAccess(
           $this->Sitelanguage->readField('language_id')
        );

		$this->Sitelanguage->setStatus('unpublished');
		$this->Sitelanguage->saveField('status');

		$this->Sitelanguage->unbindModel('all');
		$this->Sitelanguage->read();

		$this->unpublishComponents($this->Sitelanguage);

		$published_sitelanguage = new Wcms_PublishedSitelanguage();
		$published_sitelanguage->setId($this->Sitelanguage->getId());
		$published_sitelanguage->delete();

	    // unpublish sitetype
        $sitetype = new Wcms_Sitetype();
        $site = new Wcms_Site();
        $site->setId($this->Sitelanguage->getSiteId());
        $sitetype->setId($site->readField('sitetype_id'));
        $sitetype->read();
        $this->sitetypeUnpublish(
            $sitetype->getSitelanguageClass(),
            $id
        );

		$this->flushWebsiteCache();

		print '{"return_value" : true}';
	}
}
?>
