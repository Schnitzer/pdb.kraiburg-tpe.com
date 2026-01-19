<?php
/* SVN FILE: $Id$ */
/**
 * Contains the SiteController class.
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
 * SiteController class.
 *
 * @package netzcraftwerk
 */
class Wcms_SiteController extends Wcms_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = 'Site Structure :: Website';

    /**
     * Layout
     *
     * @var string
     */
    public $layout = 'blank';

	/**
	 * Show sites action.
	 *
	 */
	public function allAction ()
	{
	    if (false === $this->request_handler->isAjax()) {
	       $this->layout = 'default';
	       $this->view->window_mode = false;
	    } else {
	       $this->view->window_mode = true;
	    }

        $this->registerJs(
            array(
                'lib/jstree/jquery.tree.min',
                'lib/jstree/plugins/jquery.tree.contextmenu',
                'lib/jstree/plugins/jquery.tree.cookie',
                'lib/tiny_mce/tiny_mce_gzip',
                'ncw.wcms.tinymce.gzip',
                'ncw.wcms.site',
                'ncw.wcms.site.tree',
                'ncw.wcms.sitelanguage',
                'ncw.wcms.component',
                'ncw.wcms.componentlanguage',
                'ncw.wcms.tinymce',
            )
        );

        $this->registerCss(
            array(
                'wcms',
                'sites',
            )
        );

        // languages
        $language = new Wcms_Language();
        $language->unbindModel('all');
        $languages = $language->fetch(
            'array',
            array('fields' => array(
                    'Language.id',
                    'Language.name',
                    'Language.shortcut'
                )
            )
        );
        $arr_language = array();
        foreach ($languages as $language) {
            if (true === $this->checkLanguageAccess($language['Language']['id'], false)) {
                $arr_language[] = $language['Language'];
            }
        }
        $this->view->languages = $arr_language;
        $this->view->languages_num = count($arr_language);

        // permissions
        $this->view->permissions = array(
            '/wcms/site/new' => $this->acl->check('/wcms/site/new'),
            '/wcms/site/edit' => $this->acl->check('/wcms/site/edit'),
            '/wcms/site/notifySearchEnginges' => $this->acl->check('/wcms/site/notfiySearchEnginges'),
            '/wcms/sitelanguage/new' => $this->acl->check('/wcms/sitelanguage/new'),
            '/wcms/sitelanguage/edit' => $this->acl->check('/wcms/sitelanguage/edit'),
            '/wcms/component/all' => $this->acl->check('/wcms/component/all'),
            '/wcms/component/edit' => $this->acl->check('/wcms/component/edit'),
            '/wcms/component/new' => $this->acl->check('/wcms/component/new'),
        );
	}

	/**
	 * New site action.
	 *
	 * @param int $parent_id the parent id
	 *
	 * @return void
	 */
	public function newAction ($parent_id = 1)
	{
		$this->view->parent_options = $this->siteSelectOptions(
            $parent_id,
            $this->Site->getId()
        );

        // nav template select
        $navtemplates = new Wcms_Navtemplate();
        $navtemplates->unbindModel('all');
        $this->view->arr_navtemplates = $navtemplates->fetch(
            'list',
            array(
                'fields' => array(
                    'Navtemplate.name', 'Navtemplate.id'
                ),
                'conditions' => array(
                    'Navtemplate.in_list' => 1
                )
            )
        );

        // site type select
        $sitetype = new Wcms_Sitetype();
        $sitetypes = $sitetype->fetch(
            "list",
            array(
                "fields" => array(
                    "Sitetype.name",
                    "Sitetype.id"
                )
            )
        );
	    foreach ($sitetypes as $key => $value) {
            unset($sitetypes[$key]);
            $sitetypes[T_($key)] = $value;
        }
        $this->view->sitetypes = $sitetypes;

        // sitetemplates
        $template = new Wcms_Sitetemplate();
        $this->view->arr_sitetemplates = $template->fetch(
            "list",
            array(
                "fields" => array("Sitetemplate.name", "Sitetemplate.id"),
                'conditions' => array(
                    'Sitetemplate.sitetype_id' => current($sitetypes)
                )
            )
        );

        // count languages
        $language = new Wcms_Language();
        $this->view->num_languages = $language->fetch('count');

        $languages = $language->fetch(
            'array',
            array('fields' => array(
                    'Language.id',
                    'Language.name',
                    'Language.shortcut',
                    'Language.copies',
                )
            )
        );
        $arr_language = array();
        foreach ($languages as $language) {
            if (true === $this->checkLanguageAccess($language['Language']['id'], false)
                && false === (boolean) $language['Language']['copies']
            ) {
                $arr_language[] = $language['Language'];
            }
        }
        $this->view->languages = $arr_language;
	}

	/**
	 * save a new site action
	 *
	 * @return void
	 */
	public function saveAction ()
	{
	    $this->view = false;

	    $return = 'false';

	    if (true === isset($this->data['Site'])) {
            $this->data['Sitelanguage']['title'] = $this->data['Sitelanguage']['name'];

            $this->Site->data($this->data['Site']);
            $this->Site->setPermalink(1);

            // get the position
            $position = $this->Site->fetch(
                'list',
                array(
                    'fields' => array('position'),
                    'conditions' => array('parent_id' => $this->Site->getParentId()),
                    'order' => array('position DESC'),
                    'limit' => '1'
                )
            );
            if (false !== $position) {
                $this->Site->setPosition(array_pop($position) + 1);
            } else {
                $position = 1;
            }

            $this->Site->Sitelanguage = new Ncw_ModelList();
            if (true === isset($this->data['Sitelanguage']['languages'])
                && true === is_array($this->data['Sitelanguage']['languages'])
            ) {
                foreach ($this->data['Sitelanguage']['languages'] as $language_id => $value) {
                    $sitelanguage = new Wcms_Sitelanguage();
                    $sitelanguage->data($this->data['Sitelanguage']);
                    $sitelanguage->setLanguageId($language_id);
                    $this->Site->Sitelanguage->addModel($sitelanguage);
                }
                unset($sitelanguage);
            } else {
                $this->Site->Sitelanguage->addModel(new Wcms_Sitelanguage());
                $this->Site->Sitelanguage[0]->data($this->data['Sitelanguage']);
                $setting = new Wcms_Setting();
                $setting->setId(1);
                $this->Site->Sitelanguage[0]->setLanguageId($setting->readField('language_id'));
            }

            // navigation associations
	        if (true === isset($this->data['SiteNavtemplate'])) {
                $this->Site->SiteNavtemplate = new Ncw_ModelList();
                $count = 0;
                foreach ($this->data['SiteNavtemplate'] as $site_navtemplate) {
                    $this->Site->SiteNavtemplate->addModel(new Wcms_SiteNavtemplate());
                    $this->Site->SiteNavtemplate[$count]->data($site_navtemplate);
                    ++$count;
                }
            }

            if (true === $this->Site->validate()) {
                $this->Site->save(false);
                $return = 'true';
            }
        }

        print '{"return_value" : ' . $return . '}';
	}

	/**
	 * Edit site action.
	 *
	 */
	public function editAction ($id)
	{
		$this->Site->setId($id);
		$this->Site->unbindModel(
            array(
                'has_many' => array(
                    'Component',
                    'Site',
                    'Newssite'
                )
            )
        );
        $this->Site->bindModel(
            array(
                'belongs_to' => array('Sitetype')
            )
        );
		$this->Site->read();

		// set site data
		$this->data['Site'] = $this->Site->data();
		if (true === isset($this->data['Site']['schedule'])
            && $this->data['Site']['schedule'] == 1
        ) {
			$this->view->schedule = true;
		} else {
			$this->view->schedule = false;
		}
		if (true === isset($this->data['Site']['cache'])
            && $this->data['Site']['cache'] == 1
        ) {
			$this->view->cache = true;
		} else {
			$this->view->cache = false;
		}
		$site_publish = explode(' ', $this->data['Site']['publish']);
		if ($site_publish[0] == '0000-00-00') {
		    $this->view->site_publish = array('', '');
		} else {
		    $this->view->site_publish = $site_publish;
		}
		$site_expire = explode(' ', $this->data['Site']['expire']);
	        if ($site_expire[0] == '0000-00-00') {
            $this->view->site_expire = array('', '');
        } else {
            $this->view->site_expire = $site_expire;
        }
		$this->view->site_id = $id;
		$this->view->site_name = $this->Site->getName();
		$this->view->site_status = $this->Site->getStatus();
		$this->view->parent_id = $this->data['Site']['parent_id'];

		// sitetype
		list(
            $this->view->sitetype_tabs,
            $this->view->sitetype_content,
        ) = $this->sitetype(
            $this->Site->Sitetype->getSiteClass(),
            $this->Site->getId()
        );

		// Site parent select
		$this->view->parent_options = $this->siteSelectOptions(
            $this->Site->getParentId(),
            $this->Site->getId()
        );

        // position select
        $position_list = $this->Site->fetch(
            'list',
            array(
                'conditions' => array(
                    'Site.parent_id' => $this->Site->getParentId(),
                ),
                'fields' => array(
                    'Site.position',
                    'Site.name'
                ),
                'order' => array(
                    'Site.position'
                )
            )
        );
        $position_options = array(T_('first position') . ' (1)' => 1);
        $count = 1;
        foreach ($position_list as $position => $name) {
            if ($position != $this->Site->getPosition()) {
                $position_options[T_('after ') . $name . ' (' . ++$count . ')']
                   = ++$position;
            }
        }
        $this->view->position_options = $position_options;
        $this->view->current_position = $this->Site->getPosition();

		// site template select
		$template = new Wcms_Sitetemplate();
		$this->view->arr_sitetemplates = $template->fetch(
            "list",
            array(
                "fields" => array(
                    "Sitetemplate.name", "Sitetemplate.id"
                ),
                'conditions' => array(
                    'Sitetemplate.sitetype_id' => $this->Site->getSitetypeId()
                )
            )
        );

		// nav template select
		$navtemplates = new Wcms_Navtemplate();
		$navtemplates->unbindModel('all');
		$this->view->arr_navtemplates = $navtemplates->fetch(
            'list',
            array(
                'fields' => array(
                    'Navtemplate.name', 'Navtemplate.id'
                ),
                'conditions' => array(
                    'Navtemplate.in_list' => 1
                )
            )
        );

		$set_navtemplates = array();
		foreach ($this->Site->SiteNavtemplate as $site_navtemplate) {
            $set_navtemplates[] = $site_navtemplate->getNavtemplateId();
		}
		$this->view->set_navtemplates = $set_navtemplates;

        // private groups
        $usergroup = new Core_Usergroup();
        $this->view->group_options = $usergroup->fetch(
            'all',
            array(
                'conditions' => array('id !=' => 1),
                'fields' => array(
                    'Usergroup.id',
                    'Usergroup.name',
                    'Usergroup.level' => 'count(p.id)-1'
                )
            )
        );
        $stmt = $this->Site->db->prepare(
            'SELECT Usergroup.id, Usergroup.name '
           .'FROM ' . Ncw_Database::getConfig('prefix') . 'acos AS acos '
           .'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'aros_acos AS aros_acos '
           .'ON acos.id=aros_acos.aco_id '
           .'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'core_usergroup AS Usergroup '
           .'ON aros_acos.aro_id=Usergroup.id '
           .'WHERE acos.alias=:alias'
        );
        $stmt->bindValue(':alias', '/wcms/permissions/website/' . $id, PDO::PARAM_STR);
        $stmt->execute();
        $this->view->groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // site type select
        $sitetype = new Wcms_Sitetype();
        $sitetypes = $sitetype->fetch(
            "list",
            array(
                "fields" => array(
                    "Sitetype.name", "Sitetype.id"
                )
            )
        );
        foreach ($sitetypes as $key => $value) {
            unset($sitetypes[$key]);
            $sitetypes[T_($key)] = $value;
        }
        $this->view->sitetypes = $sitetypes;
        unset($sitetypes);

        // languages count
        $language = new Wcms_Language();
        $language->unbindModel('all');
        $language_num = $language->fetch('count');
        $this->view->languages_num = $language_num;

        // sitelanguages
        if ($language_num > 1) {
            $language_code = 'xx';
            $sitelanguages = $this->Site->Sitelanguage;
            $arr_sitelanguages = array();
            foreach ($sitelanguages as $sitelanguage) {
                if (true === $this->checkLanguageAccess($sitelanguage->Language->getId(), false)) {
                    $arr_sitelanguages[] = $sitelanguage;
                }
            }
            $this->view->sitelanguages = $arr_sitelanguages;
            unset($arr_sitelanguages);
        } else {
            $this->data['Sitelanguage'] = $this->Site->Sitelanguage[0]->data();
            $this->view->changefreq = array(
                'Always' => 'always',
                'Hourly' => 'hourly',
                'Dialy' => 'dialy',
                'Weekly' => 'weekly',
                'Monthly' => 'monthly',
                'Yearly' => 'yearly',
                'Never' => 'never',
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
            $language_code = $this->Site->Sitelanguage[0]->Language->getShortcut();

            // sitelanguagetype tab
            list(
                $this->view->sitelanguagetype_tabs,
                $this->view->sitelanguagetype_content,
            ) = $this->sitetype(
                $this->Site->Sitetype->getSitelanguageClass(),
                $this->Site->Sitelanguage[0]->getId()
            );
        }

        // get the url
        $this->view->url = $this->makeUrlForWebsite(
            $this->Site,
            false,
            $language_code,
            true,
            false
        );

        // permissions
        $this->view->permissions = array(
            '/wcms/site/new' => $this->acl->check('/wcms/site/new'),
            '/wcms/site/publish' => $this->acl->check('/wcms/site/publish'),
            '/wcms/site/unpublish' => $this->acl->check('/wcms/site/unpublish'),
            '/wcms/site/delete' => $this->acl->check('/wcms/site/delete'),
            '/wcms/sitelanguage/new' => $this->acl->check('/wcms/sitelanguage/new'),
            '/wcms/sitelanguage/edit' => $this->acl->check('/wcms/sitelanguage/edit'),
            '/wcms/component/all' => $this->acl->check('/wcms/component/all'),
        );
	}

	/**
	 * Update a site
	 *
	 * @return void
	 */
	public function updateAction ()
	{
	    $this->view = false;

        if (true === isset($this->data['Site'])) {
            // schedule stuff
            if (true === isset($this->data['Site']['schedule'])
                && $this->data['Site']['schedule'] == 1
            ) {
                $this->data['Site']['publish'] = $this->data['Site']['publish_date']
                   . ' ' .  $this->data['Site']['publish_time'] . ':00';
                $this->data['Site']['expire'] = $this->data['Site']['expire_date']
                   . ' ' .  $this->data['Site']['expire_time'] . ':00';
            } else {
                $this->data['Site']['publish'] = '';
                $this->data['Site']['expire'] = '';
            }
            unset(
                $this->data['Site']['publish_date'],
                $this->data['Site']['publish_time'],
                $this->data['Site']['expire_date'],
                $this->data['Site']['publish_time']
            );

            $reset_old_positions = false;
            // if parent id is not set
            if (false === isset($this->data['Site']['parent_id'])) {
                $this->Site->setId($this->data['Site']['id']);
                $this->data['Site']['parent_id'] = $this->Site->readField('parent_id');
            } else if ($this->data['Site']['parent_id'] != $this->data['Site']['current_parent_id']) {
                // if the parent id has changed the position must be reset.
                $this->Site->unbindModel('all');
                $position = $this->Site->fetch(
                    'list',
                    array(
                        'conditions' => array(
                            'Site.parent_id' => $this->data['Site']['parent_id']
                        ),
                        'fields' => array(
                            'Site.position'
                        ),
                        'order' => array('Site.position DESC'),
                        'limit' => '1'
                    )
                );
                if (false !== $position) {
                    $this->data['Site']['position'] = array_pop($position) + 1;
                } else {
                    $this->data['Site']['position'] = 1;
                }

                // also reset the old depths positions
                $reset_old_positions = true;
            }

            $this->Site->data($this->data['Site']);

            // delete the old navigation template associations
            $site_navtemplate = new Wcms_SiteNavtemplate();
            $query = $site_navtemplate->db->prepare('DELETE FROM ' . $site_navtemplate->db_table_name . ' WHERE site_id = :site_id');
            $query->bindValue(':site_id', $this->Site->getId(), PDO::PARAM_INT);
            $query->execute();

            if (true === isset($this->data['SiteNavtemplate'])) {
                $this->Site->SiteNavtemplate = new Ncw_ModelList();
                $count = 0;
                foreach ($this->data['SiteNavtemplate'] as $site_navtemplate) {
                    $this->Site->SiteNavtemplate->addModel(new Wcms_SiteNavtemplate());
                    $this->Site->SiteNavtemplate[$count]->data($site_navtemplate);
                    $this->Site->SiteNavtemplate[$count]->setSiteId($this->Site->getId());
                    ++$count;
                }
            }

            // save
            $this->Site->save();

            // sort sites
            $this->Site->sort();
            if (true === $reset_old_positions) {
                $site = new Wcms_Site();
                $site->setParentId($this->data['Site']['current_parent_id']);
                $site->sort();
            }

            // update sitetype
            $sitetype = new Wcms_Sitetype();
            $sitetype->setId($this->Site->getSitetypeId());
            $this->sitetypeUpdate(
                $sitetype->readField('site_class'),
                $this->Site->getId(),
                $this->data
            );

            // if sitelanguage data is set
            if (true === isset($this->data['Sitelanguage'])) {
                $sitelanguage = new Wcms_Sitelanguage();
                $sitelanguage->data($this->data['Sitelanguage']);
                $sitelanguage->saveFields(
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

                // update sitelanguagetype
                $this->sitetypeUpdate(
                    $sitetype->readField('sitelanguage_class'),
                    $sitelanguage->getId(),
                    $this->data
                );
            }

            print '{"return_value" : true}';
        } else {
            print '{"return_value" : false}';
        }
	}

	/**
	 * Tree move action
	 *
	 * @param int $id the site id
	 *
	 * @return void
	 */
	public function moveAction ($id)
	{
	    $this->view = false;

        $return = 'false';
        if (true === isset($this->data['Site'])) {
            $this->Site->unbindModel('all');
            $site = $this->Site->fetch(
                'first',
                array(
                    'fields' => array(
                        'Site.parent_id',
                    ),
                    'conditions' => array(
                        'Site.id' => $id
                    )
                )
            );
            $curr_parent_id = $site->getParentId();

            switch ($this->data['Site']['type']) {
            case 'inside':
                $this->data['Site']['parent_id'] = $this->data['Site']['ref_id'];

                // position
                $this->Site->unbindModel('all');
                $position = $this->Site->fetch(
                    'list',
                    array(
                        'conditions' => array(
                            'Site.parent_id' => $this->data['Site']['parent_id']
                        ),
                        'fields' => array(
                            'Site.position'
                        ),
                        'order' => array('Site.position DESC'),
                        'limit' => '1'
                    )
                );
                if (false !== $position) {
                    $this->data['Site']['position'] = array_pop($position) + 1;
                } else {
                    $this->data['Site']['position'] = 1;
                }
                break;
            default:
                $this->Site->unbindModel('all');
                $site = $this->Site->fetch(
                    'first',
                    array(
                        'fields' => array(
                            'Site.parent_id',
                            'Site.position',
                        ),
                        'conditions' => array(
                            'Site.id' => $this->data['Site']['ref_id']
                        )
                    )
                );
                $this->data['Site']['parent_id'] = $site->getParentId();
                $this->data['Site']['position'] = $site->getPosition();
                if ($this->data['Site']['type'] === 'after') {
                    $this->data['Site']['position']++;
                }
            }
            unset($this->data['Site']['type'], $this->data['Site']['ref_id'], $site);

            $this->Site->setId($id);
            $this->Site->data($this->data['Site']);
            if (true === $this->Site->saveFields(array('parent_id', 'position', 'status'))) {
                $return = 'true';
                $this->Site->sort();

                if ($curr_parent_id != $this->data['Site']['parent_id']) {
                    $site = new Wcms_Site();
                    $site->setParentId($curr_parent_id);
                    $site->sort();
                }
            }
        }

        print '{"return_value" : ' . $return . '}';
	}

	/**
	 * Tree rename action
	 *
	 * @param int $id the site id
	 *
	 * @return void
	 */
	public function renameAction ($id)
	{
	    $this->view = false;

	    $return = 'false';
        if (true === isset($this->data['Site'])) {
            $this->Site->setId($id);
            $this->Site->data($this->data['Site']);
            if (true === $this->Site->saveFields(array('name', 'status'))) {
                $return = 'true';
            }
        }

        print '{"return_value" : ' . $return . '}';
	}

	/**
	 * delete site action.
	 *
	 */
	public function deleteAction ($id)
	{
        $this->view = false;

        $this->Site->setId($id);
        if (true === $this->Site->delete()) {
            // sort sites
            $this->Site->sort();
            print '{"return_value" : true}';
        } else {
            print '{"return_value" : false}';
        }
	}

	/**
	 * Copies a site with its translations and components
	 *
	 * @param int $id the site id
	 *
	 * @return void
	 */
	public function copyAction ($id)
	{
        $this->view = false;

        $this->Site->setId($id);
        $this->Site->unbindModel('all');
        $this->Site->bindModel(
            array(
                'has_many' => array('Sitelanguage', 'Component')
            )
        );
        $this->Site->read();

        $site = new Wcms_Site();
        $site->data($this->Site->data());
        $site->setName('copy-of-' . $site->getName());
        $site->setStatus('new');
        $site->create();

        $sitelanguage_list = new Ncw_ModelList();
        foreach ($this->Site->Sitelanguage as $sitelanguage_data) {
            $sitelanguage = new Wcms_Sitelanguage();
            $sitelanguage->copyFrom($sitelanguage_data);
            $sitelanguage->read();
            $sitelanguage_data = $sitelanguage;

            $sitelanguage = new Wcms_Sitelanguage();

            $sitelanguage->data($sitelanguage_data->data());
            $sitelanguage->create();
            $sitelanguage->setSiteId(0);
            $sitelanguage->setStatus('new');
            $sitelanguage_list->addModel($sitelanguage);
        }
        $site->Sitelanguage = $sitelanguage_list;

        $component_list = new Ncw_ModelList();
        foreach ($this->Site->Component as $component_data) {
            $component = Wcms_ComponentController::copyComponent(
                $component_data,
                true
            );
            $component_list->addModel($component);
        }
        $site->Component = $component_list;

        $site->save(false);

        // sort sites
        $this->Site->sort();

        print '{"return_value" : true}';
	}

	/**
	 * Make the linklist for the TinyMCE Editor.
	 *
	 * @param string $language_code
	 */
	public function linklistAction ($language_code = 'en')
	{
		$this->view = false;

		$text = new Ncw_Helpers_Text();

		$arr_links = array();

        // site links
        $site_linklist = array();
		$this->siteLinklist($site_linklist, $language_code);
		foreach ($site_linklist as $link => $values) {
            $arr_links[] = '["' . $values['prefix'] . $values['name']
                . '", "' . $link . '"]';
		}

		// files
		$arr_links[] = '["", ""]';
		$arr_links[] = '["---------------------Files---------------------", ""]';
		$file = new Files_File();
		$file->unbindModel('all');
		$file->bindModel(array('belongs_to' => array('Folder')));
		$files = $file->fetch(
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
				)
		    )
		);
		$folder_id = 0;
		foreach ($files as $file) {
			if ($folder_id != $file->Folder->getId()) {
				$arr_links[] = '["---' .$file->Folder->getName() . '---", ""]';
			}
			$arr_links[] = '["' . $file->getName() . '.'
                . $file->getType() . '", "'
                . str_replace('http://' . Ncw_Configure::read('Project.domain'), '', $this->base) . '/' . ASSETS . '/files/uploads/'
                . $text->cleanForUrl($file->getName()) . '_'
                . $file->getId() . '.' . $file->getType() . '"]';
			$folder_id = $file->Folder->getId();
		}

		$linklist = 'var tinyMCELinkList = new Array(';
		$linklist .= implode(',', $arr_links);
		$linklist .= ');';

		header('Content-type: text/javascript');
		print $linklist;
	}

	/**
	 * Search link action
	 *
	 * @return string
	 */
	public function searchDialogAction ()
	{
	    $this->layout = 'default';
	}
	
	/**
	 * Search dialog search action
	 */
	public function searchDialogSearchAction ()
	{
        if ($this->request_handler->responseType() == 'javascript') {
           $this->layout = 'default';
        }

        $str_search = '';
        if (true === isset($this->params['url']['s'])) {
            $str_search = trim(Ncw_Library_Sanitizer::escape($this->params['url']['s']));
        }

	    $language_code = 'en';
	    if (true === $this->session->check('language_code')) {
	        $language_code = $this->session->read('language_code');
	    }
		$this->view->language_code = $language_code;
        
        $this->paginate = array(
        	'order' => array('Site.parent_id', 'Site.position'),
            'fields' => array(
                'Site.id',
                'Site.name',
                'Site.parent_id',
            ),
    	);
		
		$sites = $this->paginate(
			array(
                'Site.id !=' => 1,
        	    'Site.name LIKE \'%' . $str_search . '%\'',
            )
		);
		
		$sites_arr = array();
		foreach ($sites as $site) {
	        $site_url = $this->makeUrlForWebsite(
	            $site,
	            false,
	            $language_code,
	            true,
	            true,
	            false,
	            true
	        );
			
	        /*preg_match(
	            '/index\.php\?url=' . $language_code . '\/([a-zA-Z\/-_0-9]+)/',
	            $site_url,
	            $matches
	        );*/	
			
			$sites_arr[] = array(
				'id' => $site->getId(),
				'name' => $site->getNameEncoded(),
				'url' => $site_url,
			);	
		}
		
		$this->view->sites = $sites_arr;
	}

	/**
	 * Adds a new ACO for this site if not done yet.
	 * And allows the given group to access this site.
	 *
	 * @param int $id           the site id
	 * @param int $usergroup_id the usergroup id
	 *
	 * @return void
	 */
	public function addGroupAction ($id, $usergroup_id)
	{
	    $this->view = false;

        $aco = '/wcms/permissions/website/' . $id;
        $this->acl->addACO($aco);
        $this->acl->allow($usergroup_id, $aco);

        $usergroup = new Core_Usergroup();
        $usergroup->setId($usergroup_id);
        $name = $usergroup->readField('name');

        print '{"return_value" : true, usergroup : { name : "' . $name . '" } }';
	}

	/**
	 * Removes a usergroup from the list of groups
	 * which can access this site.
	 *
	 * @param int $id           the site id
	 * @param int $usergroup_id the usergroup id
	 *
	 * @return void
	 */
	public function removeGroupAction ($id, $usergroup_id)
	{
	    $this->view = false;

	    $aco = '/wcms/permissions/website/' . $id;
	    $this->acl->remove($usergroup_id, $aco);

        print '{"return_value" : true}';
	}

	/**
	 * Publish action
	 *
     * @param int $id the site id
     *
     * @return viod
	 */
	public function publishAction ($id)
	{
		$this->view = false;

		// publish the sites structure
		$this->_publishSitesStructure();

		// publish the site data
        $this->Site->setId($id);

        $this->Site->setStatus('published');
        $this->Site->saveField('status');

        $this->Site->unbindModel('all');
        $this->Site->bindModel(
            array(
                'belongs_to' => array('Sitetype'),
            )
        );
        $this->Site->read(
            array(
                'fields' => array(
                    'Site.sitetemplate_id',
                    'Site.sitetype_id',
                    'Site.private',
                    'Site.name',
                    'Site.schedule',
                    'Site.publish',
                    'Site.expire',
                    'Site.cache',
                    'Site.cache_exparation',
                    'Site.permalink',
                    'Sitetype.site_class',
                    'Sitetype.sitelanguage_class',
                )
            )
        );

        $published_site = new Wcms_PublishedSite();
        $published_site->data($this->Site->data());
        $published_site->saveFields(
            array(
                'sitetemplate_id',
                'sitetype_id',
                'private',
                'name',
                'schedule',
                'publish',
                'expire',
                'cache',
                'cache_exparation',
                'permalink',
            ),
            false
        );

        // publish the site navtemplate associations
        $this->_unpublishSiteNavtemplates($id);
        $site_navtemplate = new Wcms_SiteNavtemplate();
        $site_navtemplates = $site_navtemplate->findAllBy('site_id', $id);
        foreach ($site_navtemplates as $site_navtemplate) {
            $this->publishObject('SiteNavtemplate', $site_navtemplate);
        }

	    // publish sitetype
        $this->sitetypePublish(
            $this->Site->Sitetype->getSiteClass(),
            $id
        );

        // languages count
        $language = new Wcms_Language();
        $language->unbindModel('all');
        if ($language->fetch('count') < 2) {
    	    // publish the sitelanguage
    	    $sitelanguage = new Wcms_Sitelanguage();
    	    $sitelanguage->unbindModel('all');
    	    $obj_sitelanguage = $sitelanguage->findBy(
    	       'site_id',
    	       $id,
    	       array(
    	           'fields' => array(
    	               'Sitelanguage.id',
    	               'Sitelanguage.language_id',
    	           )
    	       )
    	    );
    	    $sitelanguage->copyFrom($obj_sitelanguage);

            $this->checkLanguageAccess(
               $sitelanguage->readField('language_id')
            );

            $sitelanguage->setStatus('published');
            $sitelanguage->saveField('status');

            $sitelanguage->unbindModel('all');
            $sitelanguage->read();

            $this->publishObject('Sitelanguage', $sitelanguage);

            $this->unpublishComponents($sitelanguage);

            // publish the componentlanguages
            $componentlanguage = new Wcms_Componentlanguage();
            $componentlanguages = $componentlanguage->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Component.site_id' => $sitelanguage->getSiteId(),
                        'Componentlanguage.language_id' => $sitelanguage->getLanguageId(),
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
            $this->sitetypePublish(
                $this->Site->Sitetype->getSitelanguageClass(),
                $obj_sitelanguage->getId()
            );
        }

        $this->flushWebsiteCache();

        print '{"return_value" : true}';
	}

	/**
	 * Unpublishes a site
	 *
	 * @param int $id the site id
	 *
	 * @return void
	 */
	public function unpublishAction ($id) {
	    $this->view = false;

        $published_site = new Wcms_PublishedSite();
        $published_site->setId($id);

        $sitetype_id = $published_site->readField('sitetype_id');

        // empty the site data
        $published_site->setSitetemplateId('', false);
        $published_site->setSitetypeId('', false);
        $published_site->setPrivate('', false);
        $published_site->setName('', false);
        $published_site->setSchedule('', false);
        $published_site->setPublish('', false);
        $published_site->setExpire('', false);
        $published_site->setCache('', false);
        $published_site->setCacheExparation('', false);

        $published_site->save(false, false);

        // set the status to unpublished
        $site = new Wcms_Site();
        $site->setId($id);
        $site->setStatus('unpublished');
        $site->saveField('status');

        $this->_unpublishSiteNavtemplates($id);

	    // unpublish sitetype
	    $sitetype = new Wcms_Sitetype();
	    $sitetype->setId($sitetype_id);
        $this->sitetypeUnpublish(
            $sitetype->readField('site_class'),
            $id
        );

        // languages count
        $language = new Wcms_Language();
        $language->unbindModel('all');
        if ($language->fetch('count') < 2) {
            // unpublish sitelanguage
            $sitelanguage = new Wcms_Sitelanguage();
            $sitelanguage->unbindModel('all');
            $obj_sitelanguage = $sitelanguage->findBy(
               'site_id',
               $id,
               array(
                   'fields' => array(
                       'Sitelanguage.id',
                       'Sitelanguage.language_id',
                   )
               )
            );
            $sitelanguage->copyFrom($obj_sitelanguage);

            $this->checkLanguageAccess(
               $sitelanguage->readField('language_id')
            );

            $sitelanguage->setStatus('unpublished');
            $sitelanguage->saveField('status');

            $sitelanguage->unbindModel('all');
            $sitelanguage->read();

            $this->unpublishComponents($sitelanguage);

            $published_sitelanguage = new Wcms_PublishedSitelanguage();
            $published_sitelanguage->setId($sitelanguage->getId());
            $published_sitelanguage->delete();

            // unpublish sitetype
            $this->sitetypeUnpublish(
                $sitetype->readField('sitelanguage_class'),
                $obj_sitelanguage->getId()
            );
        }

        $this->flushWebsiteCache();

        print '{"return_value" : true}';
	}

	/**
	 * Unpublish the sitenavtemplates
	 *
	 * @param int $site_id the site id
	 *
	 * @return void
	 */
	private function _unpublishSiteNavtemplates ($site_id)
	{
	    // unpublish the site navtemplate associations
        $published_site_navtemplate = new Wcms_PublishedSiteNavtemplate();
        $published_site_navtemplates = $published_site_navtemplate->findAllBy(
            'site_id',
            $site_id,
            array(
                'fields' => array('PublishedSiteNavtemplate.id')
            )
        );
        foreach ($published_site_navtemplates as $published_site_navtemplate_read_only) {
            $published_site_navtemplate->setId($published_site_navtemplate_read_only->getId());
            $published_site_navtemplate->delete();
        }
	}

    /**
     * Publishes the site structure
     *
     */
    private function _publishSitesStructure ()
    {
        $this->Site->db->query('DELETE FROM ' . Ncw_Database::getConfig('prefix') . 'wcms_permalink');
        $permalink = new Wcms_Permalink();

        // publish the sites
        $this->Site->unbindModel('all');
        $sites = $this->Site->fetch(
            'all',
            array(
                'fields' => array(
                    'Site.id',
                    'Site.parent_id',
                    'Site.permalink',
                    'Site.position',
                )
            )
        );

        $published_site = new Wcms_PublishedSite();
        $published_site->unbindModel('all');
        $published_sites = $published_site->fetch(
            'list',
            array('fields' => array('PublishedSite.id'))
        );

        $sql = 'INSERT INTO ' . $published_site->db_table_name . ' (';
        $fields = array('id', 'parent_id', 'permalink', 'position');
        $sql .= implode(',', $fields);
        $sql .= ') VALUES ';
        $rows = array();
        foreach ($sites as $site) {
            if (true === isset($published_sites[$site->getId()])) {
                $stmt = $this->Site->db->prepare('UPDATE ' . $published_site->db_table_name
                    . ' SET parent_id=:parent_id, position=:position WHERE id=:id');
                $stmt->bindValue(':id', $site->getId(), PDO::PARAM_INT);
                $stmt->bindValue(':parent_id', $site->getParentId(), PDO::PARAM_INT);
                $stmt->bindValue(':position', $site->getPosition(), PDO::PARAM_INT);
                $stmt->execute();
                unset($published_sites[$site->getId()]);
            } else {
                $row_sql = '(';
                $values = array();
                foreach ($site as $value) {
                    $values[] = '\'' . $value . '\'';
                }
                $row_sql .= implode(',', $values);
                $row_sql .= ')';
                $rows[] = $row_sql;
            }

            // set the permalink if needed
            $permalink->create();
            if (true === (boolean) $site->getPermalink()) {
                $permalink->setSiteId($site->getId());

                // read the site breadcrumb
                $breadcrumb = array();
                $this->readSiteBreadcrumb($breadcrumb, $site->getId(), false);
                foreach ($breadcrumb as &$breadcrumb_node) {
                    $breadcrumb_node = $breadcrumb_node['name'];
                }

                $permalink->setPermalink(implode('/', $breadcrumb));
                $permalink->save();
            } else if ($permalink->getId() > 0) {
                $permalink->setSiteId($site->getId());
                $permalink->delete();
            }
        }
        if (count($rows) > 0) {
            $sql .= implode(',', $rows);
            $sql .= ';';
            $this->Site->db->query($sql);
        }

        foreach ($published_sites as $site_to_delete) {
            $stmt = $this->Site->db->prepare('DELETE FROM ' . $published_site->db_table_name . ' WHERE id=:id');
            $stmt->bindValue(':id', $site_to_delete, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /**
     * Builds a tree
     *
     * @param int $language_id the language id
     *
     * @return string the tree
     */
    public function treeAction ($language_id = false)
    {
        $sitecontainer = false;

        if (false === $language_id) {
            // get the master language id
            $setting = new Wcms_Setting();
            $setting->setId(1);
            $language_id = $setting->readField('language_id');
            $sitecontainer = true;
        }

        // read the sites
        $this->Site->unbindModel('all');
        $this->Site->bindModel(
            array(
                'belongs_to' => array(
                    'Sitetype'
                )
            )
        );
        $sites = $this->Site->fetch(
            'all',
            array(
              'conditions' => array('Site.id !=' => 1),
              'fields' => array(
                  'Site.id',
                  'Site.name',
                  'Site.status',
                  'Site.parent_id',
                  'Site.schedule',
                  'Site.publish' => '(Site.schedule=0 || (Site.schedule=1 && Site.publish <= NOW() && Site.expire > NOW()))',
                  'Site.private',
                  'Site.position',
                  'Site.cache',
                  'Sitetype.name',
              ),
              'order' => array('Site.parent_id', 'Site.position')
            )
        );
        $all_sites = array();
        foreach ($sites as $site) {
            $all_sites[$site->getParentId()][$site->getPosition()] = array(
                'id' => $site->getId(),
                'name' => $site->getName(),
                'schedule' => $site->getSchedule(),
                'publish' => $site->getPublish(),
                'status' => $site->getStatus(),
                'private' => $site->getPrivate(),
                'parent_id' => $site->getParentId(),
                'permalink' => $site->getPermalink(),
                'cache' => $site->getCache(),
                'sitetype_name' => $site->Sitetype->getName(),
            );
        }

        // read the site languages
        $sitelanguage = new Wcms_Sitelanguage();
        $sitelanguages = $sitelanguage->fetch(
            'array',
            array(
                'fields' => array(
                    'Sitelanguage.id',
                    'Sitelanguage.site_id',
                    'Sitelanguage.name',
                    'Sitelanguage.home',
                    'Sitelanguage.status',
                    'Language.shortcut',
                    'Language.name'
                ),
                'conditions' => array(
                    'Sitelanguage.language_id' => $language_id
                ),
                'order' => array('Language.id')
            )
        );
        $arr_sitelanguages = array();
        foreach ($sitelanguages as $sitelanguage) {
            $arr_sitelanguages[$sitelanguage['Sitelanguage']['site_id']][$sitelanguage['Language']['shortcut']] = array(
                'id' => $sitelanguage['Sitelanguage']['id'],
                'name' => $sitelanguage['Sitelanguage']['name'],
                'home' => $sitelanguage['Sitelanguage']['home'],
                'status' => $sitelanguage['Sitelanguage']['status'],
                'language' => array(
                    'shortcut' => $sitelanguage['Language']['shortcut'],
                    'name' => $sitelanguage['Language']['name'],
                ),
            );
        }

        // get the language
        $language = new Wcms_Language();

        // languages count
        $language->unbindModel('all');
        $languages_num = $language->fetch('count');

        $language->unbindModel('all');
        $language = $language->findBy(
            'id',
            $language_id,
            array(
                'fields' => array(
                    'Language.name',
                    'Language.shortcut',
                )
            )
        );
        if (false === $language) {
            exit();
        }
        $language_shortcut = $language->getShortcut();

        $tree = $this->_treeDepth($all_sites, $arr_sitelanguages, 1, $language_id, $language_shortcut, $languages_num, $sitecontainer);

        $this->view->sitecontainer = $sitecontainer;
        $this->view->language_name = $language->getName();
        $this->view->language_shortcut = $language_shortcut;
        $this->view->tree = $tree;
    }

    /**
     * Gets a tree depths
     *
     * @param array $all_sites
     * @param array $arr_sitelanguages
     * @param int $parent_id
     * @param int $language_id
     * @param string $language_shortcut
     * @param boolean $sitecontainer
     * @param array $names
     * @param int $depth
     *
     * @return string
     */
    private function _treeDepth ($all_sites, $arr_sitelanguages, $parent_id, $language_id, $language_shortcut, $languages_num, $sitecontainer = false, $names = array(), $depth = 0)
    {
        $tree = '';
        if (true === isset($all_sites[$parent_id])) {
            $tree .= '<ul>';
            foreach ($all_sites[$parent_id] as $site) {

                $names[$depth] = $site['name'];

                $tree .= '<li id="ncw-site-' . $site['id']
                    . '" rel="' . strtolower($site['sitetype_name'])
                    . '" name="'
                    . $this->makeUrlForWebsite(
                        $site,
                        $names,
                        $language_shortcut
                    )
                    . '">';

                if ($languages_num > 1 && true === $sitecontainer) {
                    $tree .= '<a href="#" class="ncw-'
                        . $site['status']
                        . ' sitecontainer"'
                        .'" lang="sitecontainer"><ins>&nbsp;</ins>'
                        . $site['name'];
                } else {
                    if ($languages_num < 2) {
                        if ($site['status'] == 'published'
                            && $arr_sitelanguages[$site['id']][$language_shortcut]['status'] == 'published'
                        ) {
                            $status = 'published';
                        } else if ($site['status'] == 'modified'
                            || $arr_sitelanguages[$site['id']][$language_shortcut]['status'] == 'modified'
                        ) {
                            $status = 'modified';
                        } else {
                            $status = $arr_sitelanguages[$site['id']][$language_shortcut]['status'];
                        }

                    } else {
                        $status = $arr_sitelanguages[$site['id']][$language_shortcut]['status'];
                    }
                    if (true === isset($arr_sitelanguages[$site['id']][$language_shortcut])) {
                        $tree .= '<a href="#" class="ncw-'
                            . $status
                            . ' ' . $language_shortcut
                            . '" id="ncw-sitelanguage-' . $arr_sitelanguages[$site['id']][$language_shortcut]['id']
                            . '" lang="' . $language_shortcut
                            . '"><ins>&nbsp;</ins>'
                            . $site['name'];

                            if (true === (boolean) $arr_sitelanguages[$site['id']][$language_shortcut]['home']) {
                                $tree .= ' <img src="' . $this->base . '/' . $this->theme_path . '/web/images/icons/16px/house.png" style="height: 8px; width: 8px;" />';
                            }
                    } else {
                        $tree .= '<a href="#" class="ncw-not-translated '
                            . $language_shortcut
                            . '" lang="' . $language_shortcut
                            . '" rel="ncw-not-translated"><ins>&nbsp;</ins>'
                            . $site['name'];
                    }
                }

                if (true === (boolean) $site['schedule']
                    && true === (boolean) $site['publish']
                ) {
                    $tree .= ' <img src="' . $this->base . '/' . $this->theme_path . '/web/images/icons/16px/clock.png" style="height: 8px; width: 8px;" />';
                } else if (false === (boolean) $site['publish']) {
                    $tree .= ' <img src="' . $this->base . '/' . $this->theme_path . '/web/images/icons/16px/clock_red.png" style="height: 8px; width: 8px;" />';
                }
                if (true === (boolean) $site['private']) {
                    $tree .= ' <img src="' . $this->base . '/' . $this->theme_path . '/web/images/icons/16px/lock.png" style="height: 8px; width: 8px;" />';
                }
                if (true === (boolean) $site['cache']) {
                    $tree .= ' <img src="' . $this->base . '/' . $this->theme_path . '/web/images/icons/16px/page_white_zip.png" style="height: 8px; width: 8px;" />';
                }

                $tree .= '</a>';

                $tree .= $this->_treeDepth($all_sites, $arr_sitelanguages, $site['id'], $language_id, $language_shortcut, $languages_num, $sitecontainer, $names, $depth + 1);

                $tree .= '</li>';

            }
            $tree .= '</ul>';
        }
        return $tree;
    }

    /**
     * Notifies the search engine that the sitemap has changed.
     *
     */
    public function notifySearchEnginesAction ()
    {
        // notify the search engines that the site has changed.
        require_once 'modules/wcms/vendor/sitemap/PingServiceComposite.php';
        require_once 'modules/wcms/vendor/sitemap/pingservices/Google.php';
        require_once 'modules/wcms/vendor/sitemap/pingservices/Yahoo.php';
        require_once 'modules/wcms/vendor/sitemap/pingservices/Bing.php';
        require_once 'modules/wcms/vendor/sitemap/pingservices/Ask.php';

        $ping_service_composite = new Sitemap_PingServiceComposite();
        $ping_service_composite->setSitemapUrl(PROJECT_URL . DS . 'sitemap.xml');
        $ping_service_composite->addPingService(new Sitemap_Pingservices_Google());
        $ping_service_composite->addPingService(new Sitemap_Pingservices_Yahoo());
        $ping_service_composite->addPingService(new Sitemap_Pingservices_Bing());
        $ping_service_composite->addPingService(new Sitemap_Pingservices_Ask());

        $ping_service_composite->ping();
    }
}
?>
