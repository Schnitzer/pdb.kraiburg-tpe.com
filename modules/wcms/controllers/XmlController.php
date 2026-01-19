<?php
/* SVN FILE: $Id$ */
/**
 * Contains the XmlController class.
 *
 * PHP Version 5
 * Copyright (c) 2007 Netzcraftwerk GmbH
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author          Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright       Copyright 2007-2008, Netzcraftwerk GmbH
 * @link            http://www.netzcraftwerk.com
 * @package         netzcraftwerk
 * @since           Netzcraftwerk v 3.0.0.1
 * @version         Revision: $LastChangedRevision$
 * @modifiedby      $LastChangedBy$
 * @lastmodified    $LastChangedDate$
 * @license         http://www.netzcraftwerk.com/licenses/
 */
/**
 * XmlController class.
 *
 * @package netzcraftwerk
 */
class Wcms_XmlController extends Wcms_ModuleController
{

    /**
     * XmlController hasn't got a model.
     *
     * @var boolean
     */
    public $has_model = false;

    /**
     * Acls publics
     *
     * @var array
     */
    public $acl_publics = array('sitemap');

    /**
     * Before render
     *
     */
    public function beforeFilter ()
    {
        parent::beforeFilter();

        // Read the settings
        $this->_setting = new Wcms_Setting();
        $this->_setting->bindModel(array('belongs_to' => array('Language')));
        $this->_setting->setId(1);
        $this->_setting->read(
            array(
                'fields' => array(
                    'Setting.language_id',
                    'Setting.rewrite',
                    'Setting.master_language_copy',
                    'Setting.robots',
                    'Language.shortcut',
                )
            )
        );
    }

    /**
     * Component actions
     *
     */
    public function indexAction ()
    {
        if (true === isset($this->params['named']['id'])) {
            if (true === isset($this->params['named']['type'])
                && $this->params['named']['type'] == 'component'
            ) {
                $type = 'Component';
            } else {
                $type = 'Site';
            }

            $live = $this->_checkIsLive();
            if (true === $live) {
                $model = 'Published' . $type;
            } else {
                $model = $type;
            }

            $id = (int) $this->params['named']['id'];

            $this->loadModel($model);
            $this->{$model}->setId($id);

            if ($type === 'Component') {
                $this->{$model}->bindModel(
                    array(
                        'has_many' => array(
                            $model => array(
                                'foreign_key' => 'parent_id'
                            )
                        )
                    )
                );
            }

            $this->{$model}->read();

            $xml = '<' . strtolower($type) . '>';
            $xml .= $this->_buildXml($this->{$model}->associatedModels());
            $xml .= '</' . strtolower($type) . '>';

            $this->view->content = $xml;

        }
    }

    /**
     * Builds the xml
     *
     * @param Ncw_DataModel $model
     *
     * @return string
     */
    protected function _buildXml ($modellist)
    {
        //$xml = '<' . strtolower($name) . '>';
        foreach ($modellist as $model) {

            if ($model instanceof Ncw_ModelList) {
                $xml .= $this->_buildXml($model);
            } else {
                $xml .= '<' . strtolower($model->name);
                foreach ($model->data() as $key => $value) {
                    if ($key == 'content') {
                        continue;
                    }
                    $xml .= ' ' . $key . '="' . str_replace('"', '', $value) . '"';
                }
                $xml .=  '>';

                $xml .= $this->_buildXml($model->associatedModels());

                if (true === isset($model->data['content'])) {
                    $xml .= '<![CDATA[' . $model->getContent() . ']]>';
                }

                $xml .= '</' . strtolower($model->name) . '>';
            }
        }
        //$xml .= '</' . strtolower($name) . '>';
        return $xml;
    }

    /**
     * Builds a sitemap xml
     *
     * @return string the tree
     */
    public function sitemapAction ()
    {
        try {
            $this->view = false;

            // read the sites
            $site = new Wcms_PublishedSite();
            $site->unbindModel('all');
            $sites = $site->fetch(
                'all',
                array(
                  'conditions' => array('PublishedSite.id !=' => 1),
                  'fields' => array(
                      'PublishedSite.id',
                      'PublishedSite.name',
                      'PublishedSite.parent_id',
                      'PublishedSite.permalink',
                  ),
                  'order' => array('PublishedSite.parent_id', 'PublishedSite.position')
                )
            );
            $all_sites = array();
            foreach ($sites as $site) {
                $all_sites[$site->getParentId()][] = array(
                    'id' => $site->getId(),
                    'name' => $site->getName(),
                    'parent_id' => $site->getParentId(),
                    'permalink' => $site->getPermalink(),
                );
            }

            // read the site languages
            $sitelanguage = new Wcms_PublishedSitelanguage();
            $sitelanguages = $sitelanguage->fetch(
                'array',
                array(
                    'fields' => array(
                        'PublishedSitelanguage.id',
                        'PublishedSitelanguage.site_id',
                        'PublishedSitelanguage.modified',
                        'PublishedSitelanguage.changefreq',
                        'PublishedSitelanguage.priority',
                        'Language.shortcut'
                    ),
                    'order' => array('Language.id')
                )
            );
            $arr_sitelanguages = array();
            foreach ($sitelanguages as $sitelanguage) {
                $arr_sitelanguages[$sitelanguage['PublishedSitelanguage']['site_id']][$sitelanguage['Language']['shortcut']] = array(
                    'id' => $sitelanguage['PublishedSitelanguage']['id'],
                    'modified' => $sitelanguage['PublishedSitelanguage']['modified'],
                    'changefreq' => $sitelanguage['PublishedSitelanguage']['changefreq'],
                    'priority' => $sitelanguage['PublishedSitelanguage']['priority'],
                    'language' => array(
                        'shortcut' => $sitelanguage['Language']['shortcut'],
                    ),
                );
            }

            require_once MODULES . DS . 'wcms' . DS . 'vendor' . DS . 'sitemap' . DS . 'Sitemap.php';
            $sitemap = new Sitemap_Sitemap();

            $this->_sitemapDepth($all_sites, $arr_sitelanguages, 1, $sitemap);

            print $sitemap->create();

        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * Gets a tree depths
     *
     * @param array $all_sites
     * @param array $arr_sitelanguages
     * @param int $parent_id
     * @param Sitemap_Sitemap $sitemap
     * @param array $names
     * @param int $depth
     *
     * @return string
     */
    protected function _sitemapDepth ($all_sites, $arr_sitelanguages, $parent_id = 1, &$sitemap, $names = array(), $depth = 0)
    {
        if (true === isset($all_sites[$parent_id])) {
            foreach ($all_sites[$parent_id] as $site) {

                $names[$depth] = $site['name'];

                foreach ($arr_sitelanguages[$site['id']] as $language_shortcut => $sitelanguage) {
                    $sitemap_site = new Sitemap_Site();

                    $sitemap_site->setLoc(
                        $this->makeUrlForWebsite(
                            $site,
                            $names,
                            $language_shortcut,
                            true,
                            true,
                            true
                        )
                    );
                    $sitemap_site->setLastmod(
                        str_replace(' ', 'T', $sitelanguage['modified']) . '+00:00'
                    );
                    $changefreq = $sitelanguage['changefreq'];
                    if (false === empty($changefreq)) {
                        $sitemap_site->setChangefreq($changefreq);
                    }
                    $sitemap_site->setPriority((float) $sitelanguage['priority']);

                    $sitemap->addSite($sitemap_site);
                }

                $this->_sitemapDepth($all_sites, $arr_sitelanguages, $site['id'], $sitemap, $names, $depth + 1);

            }
        }
    }

    /**
     * Check if live mode
     *
     */
    protected function _checkIsLive ()
    {
        $live = true;
        // if the user is logged in.
        if (true === $this->checkLogin(false)) {
            $user = Ncw_Components_Session::readInAll('user');
            $this->acl = new Ncw_Components_Acl();
            $this->acl->read($user['id'], '');
            // if the user has the permissions to edit the site
            if (true === $this->acl->check('/wcms')) {
                $live = false;
                if (true === isset($_GET['live']) && 1 == $_GET['live']) {
                    $live = true;
                }
            }
        }
        return $live;
    }
}
?>
