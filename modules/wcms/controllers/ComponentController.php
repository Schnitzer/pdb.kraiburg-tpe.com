<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ComponentController class.
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
 * ComponentController class.
 *
 * @package netzcraftwerk
 */
class Wcms_ComponentController extends Wcms_ModuleController
{

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
	public function allAction ($site_id)
	{
        list(
            $this->view->all_components,
            $this->view->arr_languages
        ) = $this->_readComponents($site_id);

        // languages count
        $language = new Wcms_Language();
        $language->unbindModel('all');
        $this->view->languages_num = $language->fetch('count');

        // permissions
        $this->view->permissions = array(
            '/wcms/component/new' => $this->acl->check('/wcms/component/new'),
            '/wcms/component/edit' => $this->acl->check('/wcms/component/edit'),
        );
	}

	/**
	 * New site action.
	 *
	 * @param int     $site_id
	 * @param boolean $dialog_mode
	 *
	 * @return void
	 */
	public function newAction ($site_id, $dialog_mode = false)
	{
		$template = new Wcms_Componenttemplate();

		if (true === (boolean) $dialog_mode) {
		    $in_list = array(
                'Componenttemplate.in_list' => 1
            );
		} else {
		    $in_list = array();
		}

		$this->view->arr_templates = $template->fetch(
            "list",
            array(
                "fields" => array(
                    "Componenttemplate.name",
                    "Componenttemplate.id"
                ),
                "conditions" => $in_list
            )
        );
		if ($site_id > 0) {
			$sitearea = new Wcms_Sitearea();
			$sitearea->bindModel(
                array(
                    'belongs_to' => array(
                        'Site' => array(
                            'join_condition' => 'Site.sitetemplate_id=Sitetemplate.id'
                        )
                    )
                )
            );
			$this->view->arr_sitearea = $sitearea->fetch(
                "list",
                array(
                    'conditions' => array('Site.id' => $site_id),
                    "fields" => array("Sitearea.name", "Sitearea.area"),
                    'order' => array('Sitearea.area')
                )
            );
		} else {
			$this->view->arr_sitearea = false;
		}
		$this->view->site_id = $site_id;

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

        // parent component list
        $this->view->parent_options = $this->Component->fetch(
            'list',
            array(
                'conditions' => array(
                    'Component.site_id' => $site_id,
                ),
                'fields' => array(
                    'Component.name',
                    'Component.id',
                ),
                'order' => array(
                    'Component.area',
                    'Component.parent_id',
                    'Component.position',
                )
            )
        );

        $this->view->dialog_mode = (boolean) $dialog_mode;
	}

	/**
	 * Save new component action
	 *
	 * @return void
	 */
	public function saveAction ()
	{
	    $this->view = false;

	    if (true === isset($this->data['Component'])) {
            if (true === $this->_newComponent($this->data['Component'])) {
                print '{"return_value" : true}';
            } else {
                print '{"return_value" : false}';
            }
        } else {
            print '{"return_value" : false}';
        }
	}

	/**
	 * Edit site action.
	 *
	 */
	public function editAction ($id)
	{
		$this->Component->setId($id);
		$this->Component->unbindModel(array('has_many' => array('Componentlanguage')));
		$this->Component->bindModel(
            array(
                'has_many' => array(
                    'Componentlanguage' => array(
                        'unbind' => array(
                            'has_many' => array(
                                'Componenttext',
                                'Componentshorttext',
                                'Componentfile'
                            )
                        )
                    )
                )
            )
        );
		$this->Component->read();
		$this->data['Component'] = $this->Component->data();

        $this->view->component_id = $id;
        $this->view->site_id = $this->data['Component']['site_id'];
		$this->view->site_options = $this->siteSelectOptions($this->Component->getSiteId());

		// siteareas
		$sitearea = new Wcms_Sitearea();
		$sitearea->bindModel(
            array(
                'belongs_to' => array(
                    'Site' => array(
                        'join_condition' => 'Site.sitetemplate_id=Sitetemplate.id'
                    )
                )
            )
        );
		$this->view->arr_sitearea = $sitearea->fetch(
            "list",
            array(
                'conditions' => array(
                    'Site.id' => $this->data['Component']['site_id']
                ),
                "fields" => array("Sitearea.name", "Sitearea.area"),
                'order' => array('Sitearea.area')
            )
        );

        // schedule
        if (true === isset($this->data['Component']['schedule'])
            && $this->data['Component']['schedule'] == 1
        ) {
            $this->view->schedule = true;
        } else {
            $this->view->schedule = false;
        }
		if (true === empty($this->data['Component']['publish'])) {
		    $this->data['Component']['publish'] = '0000-00-00 00:00:00';
		}
	    if (true === empty($this->data['Component']['expire'])) {
            $this->data['Component']['expire'] = '0000-00-00 00:00:00';
        }
	    $component_publish = explode(' ', $this->data['Component']['publish']);
        if ($component_publish[0] == '0000-00-00') {
            $this->view->component_publish = array('', '');
        } else {
            $this->view->component_publish = $component_publish;
        }
        $component_expire = explode(' ', $this->data['Component']['expire']);
            if ($component_expire[0] == '0000-00-00') {
            $this->view->component_expire = array('', '');
        } else {
            $this->view->component_expire = $component_expire;
        }

		// component languages
		$componentlanguages = $this->Component->Componentlanguage;
		$arr_componentlanguages = array();
		foreach ($componentlanguages as $componentlanguage) {
		    if (true === $this->checkLanguageAccess($componentlanguage->Language->getId(), false)) {
                $arr_componentlanguages[] = $componentlanguage;
		    }
		}
		$this->view->componentlanguages = $arr_componentlanguages;

		// position list
		$position_list = $this->Component->fetch(
            'list',
            array(
                'conditions' => array(
                    'Component.site_id' => $this->Component->getSiteId(),
                    'Component.parent_id' => $this->Component->getParentId(),
                    'Component.area' => $this->Component->getArea()
                ),
                'fields' => array(
                    'Component.position',
                    'Component.name'
                ),
                'order' => array(
                    'Component.position'
                )
            )
        );
		$position_options = array('first position (1)' => 1);
		$count = 1;
		foreach ($position_list as $position => $name) {
			if ($position != $this->Component->getPosition()) {
				$position_options['after ' . $name . ' (' . ++$count . ')']
				   = ++$position;
			}
		}
		$this->view->position_options = $position_options;

		// parent component list
        $this->view->parent_options = $this->Component->fetch(
            'list',
            array(
                'conditions' => array(
                    'Component.id !=' => $id,
                    'Component.site_id' => $this->Component->getSiteId(),
                ),
                'fields' => array(
                    'Component.name',
                    'Component.id',
                ),
                'order' => array(
                    'Component.area',
                    'Component.parent_id',
                    'Component.position',
                )
            )
        );
        $this->view->parent_id = $this->Component->getParentId();
        $this->view->component_status = $this->Component->getStatus();

        // languages count
        $language = new Wcms_Language();
        $language->unbindModel('all');
        $this->view->languages_num = $language->fetch('count');

        // permissions
        $this->view->permissions = array(
            '/wcms/component/new' => $this->acl->check('/wcms/component/new'),
            '/wcms/component/delete' => $this->acl->check('/wcms/component/delete'),
            '/wcms/component/publish' => $this->acl->check('/wcms/component/publish'),
            '/wcms/component/unpublish' => $this->acl->check('/wcms/component/unpublish'),
            '/wcms/componentlanguage/new' => $this->acl->check('/wcms/componentlanguage/new'),
        );
	}

	/**
	 * updates a component
	 *
	 * @return void
	 */
	public function updateAction ()
	{
	    $this->view = false;

        if (true === isset($this->data['Component'])) {
            if (true === isset($this->data['Component']['schedule'])
                && $this->data['Component']['schedule'] == 1
            ) {
                $this->data['Component']['publish'] = $this->data['Component']['publish_date']
                    . ' ' .  $this->data['Component']['publish_time'] . ':00';
                $this->data['Component']['expire'] = $this->data['Component']['expire_date']
                    . ' ' .  $this->data['Component']['expire_time'] . ':00';
            } else {
                $this->data['Component']['publish'] = '';
                $this->data['Component']['expire'] = '';
            }
            $this->Component->data($this->data['Component']);
            $this->Component->saveFields(
                array(
                    'name',
                    'site_id',
                    'parent_id',
                    'area',
                    'position',
                    'permanent',
                    'schedule',
                    'publish',
                    'expire'
                )
            );
            $this->Component->sort();

            if (true === isset($this->data['Componenttext'])) {
                $componenttext = new Wcms_Componenttext();
                foreach ($this->data['Componenttext'] as $text) {
                    $componenttext->create();
                    $componenttext->data($text);
                    $componenttext->saveFields(array('content'));
                }
            }
            if (true === isset($this->data['Componentshorttext'])) {
                $componentshorttext = new Wcms_Componentshorttext();
                foreach ($this->data['Componentshorttext'] as $shorttext) {
                    $componentshorttext->create();
                    $componentshorttext->data($shorttext);
                    $componentshorttext->saveFields(array('content'));
                }
            }
            if (true === isset($this->data['Componentfile'])) {
                $componentfile = new Wcms_Componentfile();
                foreach ($this->data['Componentfile'] as $file) {
                    $componentfile->create();
                    $componentfile->data($file);
                    $componentfile->saveFields(
                        array(
                            'file_id',
                            'alt',
                            'title',
                            'link',
                            'target'
                        )
                    );
                }
            }

            print '{"return_value" : true}';
        } else {
            print '{"return_value" : false}';
        }
	}

	/**
	 * delete site action.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function deleteAction ($id)
	{
		$this->view = false;

		$this->Component->setId($id);
		if (true === $this->Component->delete()) {
            $this->Component->sort();
            print '{"return_value": true}';
		} else {
            print '{"return_value": false}';
		}
	}

	/**
	 * Copy component action
	 *
	 * @param int $id the component id
	 *
	 * @return void
	 */
	public function copyAction ($id)
	{
	    $this->view = false;

	    $this->Component->setId($id);
	    $this->Component->read();

	    $component = self::copyComponent($this->Component);
	    $component->save(false);
	    $component->sort('created');

	    print '{"return_value" : true}';
	}

	/**
	 * Sort the components
	 *
	 */
	public function sortAction ()
	{
		$this->view = false;

		$return = 'false';
		if (true === isset($this->data['Component'][0])) {
		    $return = 'true';
			$count = 1;
			foreach ($this->data['Component'] as $component) {
				$this->Component->setId($component['id']);
				$this->Component->setPosition($count);
				if (false === $this->Component->saveField('position')) {
					$return = 'false';
				}
				++$count;
			}
		}
		print '{"return_value": ' . $return . '}';
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
        $this->Component->setId($id);

        $this->Component->setStatus('published');
        $this->Component->saveField('status');

        $this->Component->unbindModel('all');
        $this->Component->read();

        $this->publishObject('Component', $this->Component);

        // publish the componentlanguages
        $componentlanguage = new Wcms_Componentlanguage();
        $componentlanguage->unbindModel(array('belongs_to' => array('PublishedComponent')));
        $componentlanguages = $componentlanguage->fetch(
            'all',
            array(
                'conditions' => array(
                    'Component.id' => $id
                )
            )
        );

        foreach ($componentlanguages as $componentlanguage) {
            if ($this->checkLanguageAccess($componentlanguage->getLanguageId(), false)) {
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
            }
        }

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

        // delete the current published component, componentlanguages and the value fields (text, shorttext, file) which belongs to this sitelanguage.
        $full_published_componentlanguage = new Wcms_PublishedComponentlanguage();
        $published_componentlanguages = $full_published_componentlanguage->fetch(
            'all',
            array(
                'conditions' => array(
                    'PublishedComponent.id' => $id
                )
            )
        );
        foreach ($published_componentlanguages as $published_componentlanguage) {
            if ($this->checkLanguageAccess($published_componentlanguage->getLanguageId(), false)) {
                $full_published_componentlanguage = new Wcms_PublishedComponentlanguage();
                $full_published_componentlanguage->copyFrom($published_componentlanguage);
                $full_published_componentlanguage->delete();
            }
        }

        // unpublish component
        $full_published_component = new Wcms_PublishedComponent();
        $full_published_component->setId($id);
        $full_published_component->delete();

        $this->Component->setId($id);
        $this->Component->setStatus('unpublished');
        $this->Component->saveField('status');

        $this->flushWebsiteCache();

        print '{"return_value" : true}';
    }

	/**
	 * Reads the components which are associated with
	 * the given site id
	 *
	 * @param int $site_id the site id
	 *
	 * @return array
	 */
	private function _readComponents ($site_id)
	{
        $site = new Wcms_Site();
        $site->setId($site_id);
        $this->view->site_name = $site->readField('name');
        $this->view->site_id = $site_id;

        $this->Component->unbindModel('all');
        $this->Component->bindModel(
            array(
                'belongs_to' => array(
                    'Componenttemplate',
                    'Site' => array(
                        'join_condition' => 'Component.site_id=Site.id'
                    ),
                    'Sitetemplate' => array(
                        'join_condition' => 'Site.sitetemplate_id=Sitetemplate.id'
                    ),
                    'Sitearea' => array(
                        'join_condition' => 'Sitetemplate.id=Sitearea.sitetemplate_id'
                    )
                )
            )
        );
        $components = $this->Component->fetch(
            "all",
            array(
                "conditions" => array(
                    "Component.site_id" => $site_id,
                    'Component.area=Sitearea.area'
                ),
                'fields' => array(
                    'Component.id',
                    'Component.name',
                    'Component.site_id',
                    'Component.parent_id',
                    'Component.permanent',
                    'Component.status',
                    'Componenttemplate.name',
                    'Site.name',
                    'Sitearea.id',
                    'Sitearea.name',
                ),
                'order' => array(
                    'Component.site_id',
                    'Component.area',
                    'Component.parent_id',
                    'Component.position',
                )
            )
        );

        // read the languages
        $componentlanguage = new Wcms_Componentlanguage();
        $componentlanguage->unbindModel(
            array(
                'belongs_to' => array('Component'),
                'has_many' => array(
                    'Componenttext',
                    'Componentshorttext',
                    'Componentfile'
                )
            )
        );
        $componentlanguages = $componentlanguage->fetch(
            'all',
            array(
                'fields' => array(
                    'Componentlanguage.id',
                    'Componentlanguage.component_id',
                    'Language.id',
                    'Language.shortcut',
                    'Language.name'
                 ),
                 'order' => array('Language.id')
            )
        );
        $languages = array();
        foreach ($componentlanguages as $componentlanguage) {
            $languages[$componentlanguage->getComponentId()][$componentlanguage->Language->getId()] = array(
                'id' => $componentlanguage->getId(),
                'shortcut' => $componentlanguage->Language->getShortcut(),
                'name' => $componentlanguage->Language->getName()
            );
        }
        return array($components, $languages);
	}

	/**
	 * New component method
	 *
	 * @param array $data the component data array
	 *
	 * @return boolean
	 */
    private function _newComponent ($data)
    {
        $return = true;

        // get the last position
        $this->Component->unbindModel('all');
        $position = $this->Component->fetch(
            'list',
            array(
                'fields' => array(
                    'Component.position'
                ),
                'conditions' => array(
                    'Component.site_id' => $data['site_id'],
                    'Component.area' => $data['area']
                ),
                'order' => array('Component.position DESC'),
                'limit' => '1'
            )
        );
        $position = (int) array_pop($position);

        $this->Component->data($data);
        for ($component_count = 0; $component_count < $data['num']; $component_count++) {
            $this->Component->create();
            $this->Component->setPosition($position + 1, false);
            $this->Component->Componentlanguage = new Ncw_ModelList();

            if (true === isset($data['languages'])
                && true === is_array($data['languages'])
            ) {
                foreach ($data['languages'] as $language_id => $value) {
                    $componentlanguage = new Wcms_Componentlanguage();
                    $componentlanguage->setLanguageId($language_id);
                    $this->Component->Componentlanguage->addModel($componentlanguage);
                }
                unset($componentlanguage);
            } else {
                $this->Component->Componentlanguage->addModel(new Wcms_Componentlanguage());
                $setting = new Wcms_Setting();
                $setting->setId(1);
                $language_id = $setting->readField('language_id');
                $this->Component->Componentlanguage[0]->setLanguageId($language_id);
                if (true === isset($data['language_id'])
                    && (int) $data['language_id'] !== $language_id
                ) {
                    $this->Component->Componentlanguage->addModel(new Wcms_Componentlanguage());
                    $this->Component->Componentlanguage[1]->setLanguageId($data['language_id']);
                }
            }
            if (true === $this->Component->validate()) {
                $this->Component->save(false);
                foreach ($this->Component->Componentlanguage as $componentlanguage) {
                    // Add the text, shortext and file contents
                    $template = new Wcms_Componenttemplate();
                    $template->setId($this->Component->getComponenttemplateId());
                    $template->read();
                    // The text fields
                    $num = $template->getText();
                    for ($count = 1; $count <= $num; ++$count) {
                        $text = new Wcms_Componenttext();
                        $text->data(array("componentlanguage_id" => $componentlanguage->getId(), "position" => $count));
                        $text->save();
                    }
                    // The shorttext fields
                    $num = $template->getShorttext();
                    for ($count = 1; $count <= $num; ++$count) {
                        $shorttext = new Wcms_Componentshorttext();
                        $shorttext->data(array("componentlanguage_id" => $componentlanguage->getId(), "position" => $count));
                        $shorttext->save();
                    }
                    // The file fields
                    $num = $template->getFile();
                    for ($count = 1; $count <= $num; ++$count) {
                        $file = new Wcms_Componentfile();
                        $file->data(array("componentlanguage_id" => $componentlanguage->getId(), "position" => $count));
                        $file->save();
                    }
                }
            } else {
                $return = false;
                break;
            }
        }
        $this->Component->sort('created');

        return $return;
    }

    /**
     * Copies a component model
     *
     * @param Wcms_Component $component_to_copy the model to copy
     * @param boolean        $reset_site_id     if the site id must be reseted
     *          set to true
     *
     * @return Wcms_Component
     */
    public static function copyComponent ($component_to_copy, $reset_site_id = false)
    {
        $component = new Wcms_Component();
        $component->data($component_to_copy->data());
        $component->setStatus('new');
        if (true === $reset_site_id) {
            $component->setSiteId(0);
        } else {
            $component->setName('Copy of ' . $component->getName());

            // get the last position
            $component->unbindModel('all');
            $position = $component->fetch(
                'list',
                array(
                    'fields' => array(
                        'Component.position'
                    ),
                    'conditions' => array(
                        'Component.site_id' => $component->getSiteId()
                    ),
                    'order' => array('Component.position DESC'),
                    'limit' => '1'
                )
            );
            $position = (int) array_pop($position) + 1;

            $component->setPosition($position);
        }
        $component->create();

        $componentlanguage_list = new Ncw_ModelList();

        foreach ($component_to_copy->Componentlanguage as $componentlanguage_data) {
            $componentlanguage = new Wcms_Componentlanguage();
            $componentlanguage->copyFrom($componentlanguage_data);

            $componentlanguage_data = $componentlanguage;
            $componentlanguage = new Wcms_Componentlanguage();

            $componenttext_list = new Ncw_ModelList();
            foreach ($componentlanguage_data->Componenttext as $componenttext_data) {
                $componenttext = new Wcms_Componenttext();
                $componenttext->data($componenttext_data->data());
                $componenttext->setComponentlanguageId(0);
                $componenttext->create();
                $componenttext_list->addModel($componenttext);
            }
            $componentlanguage->Componenttext = $componenttext_list;

            $componentshorttext_list = new Ncw_ModelList();
            foreach ($componentlanguage_data->Componentshorttext as $componentshorttext_data) {
                $componentshorttext = new Wcms_Componentshorttext();
                $componentshorttext->data($componentshorttext_data->data());
                $componentshorttext->setComponentlanguageId(0);
                $componentshorttext->create();
                $componentshorttext_list->addModel($componentshorttext);
            }
            $componentlanguage->Componentshorttext = $componentshorttext_list;

            $componentfile_list = new Ncw_ModelList();
            foreach ($componentlanguage_data->Componentfile as $componentfile_data) {
                $componentfile = new Wcms_Componentfile();
                $componentfile->data($componentfile_data->data());
                $componentfile->setComponentlanguageId(0);
                $componentfile->create();
                $componentfile_list->addModel($componentfile);
            }
            $componentlanguage->Componentfile = $componentfile_list;

            $componentlanguage->data($componentlanguage_data->data());
            $componentlanguage->create();
            $componentlanguage->setComponentId(0);

            $componentlanguage_list->addModel($componentlanguage);
        }

        $component->Componentlanguage = $componentlanguage_list;

        return $component;
    }
}
?>
