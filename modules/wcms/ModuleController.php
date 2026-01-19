<?php
/* SVN FILE: $Id$ */
/**
 * Contains the ModuleController class.
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
 * ModuleController class.
 *
 * @package netzcraftwerk
 */
class Wcms_ModuleController extends AppController
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
     * Further Wcms translations. only for translation tool
     *
     * @return void
     */
    private function __wcmsTranslations ()
    {
        T_('Publishing...');
        T_('Published');
        T_('The item has been published successfully.');
        T_('Unpublishing...');
        T_('Unpublished');
        T_('The item has been unpublished successfully.');
        T_('Copied');
        T_('The item has been copied successfully.');

        T_('Move');
        T_('Do you really want to move this site?');
        T_('Moved');
        T_('The site has been moved successfully');
        T_('Rename');
        T_('Do you really want to rename this site?');
        T_('Renamed');
        T_('The site has been renamed successfully');
        T_('Edit content');
        T_('Close');
        T_("Do you want to save before the editor is closed?");
    }

    /**
     * before filter
     *
     */
    public function beforeFilter ()
    {
        parent::beforeFilter();
        if (Ncw_Configure::read('App.language') != 'en_EN') {
            $this->registerJs('locale/' . Ncw_Configure::read('App.language') . '/LC_MESSAGES/default');
        }
        $this->registerJs('ncw.wcms');
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

            $menu = array();
           // if (true === $this->acl->check('/wcms')) {
           //     $menu[] = $html->link(T_('Website'), array('controller' => 'site', 'action' => 'all'));
          //  }
           // if (true === $this->acl->check('/wcms')) {
           //     $menu[] = $html->link(T_('Site Structure'), array('controller' => 'site', 'action' => 'all'), array('class' => (($this->name == 'Site') ? 'opened' : '')));
           // }
           // if (true === $this->acl->check('/files/file/all')) {
           //     $menu[] = $html->link(T_('Files'), array('module' => 'files', 'controller' => 'folder', 'action' => 'all'));
           // }
            if (true === $this->acl->check('/wcms')) {
                $menu[] = $html->link(T_('Contentboxes'), array('controller' => 'contentboxgroup', 'action' => 'all'), array('class' => (($this->name == 'Site') ? 'opened' : '')));
            }
            if (true === $this->acl->check('/wcms')) {
                $menu[] = $html->link(T_('Terms'), array('controller' => 'news', 'action' => 'all'));
            }
            if (true === $this->acl->check('/wcms')) {
                $menu[] = $html->link(T_('Export Suchbegriffe'), array('controller' => 'stats', 'action' => 'exportSuchbegriffeCsv'));
            }
            if (true === $this->acl->check('/wcms')) {
                $menu[] = $html->link(T_('Export Download Stats'), array('controller' => 'stats', 'action' => 'exportStatsCsv'));
            }
          
           // if (true === $this->acl->check('/wcms')) {
           //     $menu[] = $html->link(T_('Statistics'), array('controller' => 'stats', 'action' => 'all'));
            //}
           // if (true === $this->acl->check('/wcms')) {
            //    $menu[] =  '<a href="https://pdb.kraiburg-tpe.com/tpepdb/pdf?allseries=true&l=de&datasheetmode=datasheet" target="_blank">Katalog DE</a>';
           //    $menu[] =  '<a href="https://pdb.kraiburg-tpe.com/tpepdb/pdf?allseries=true&l=en&datasheetmode=datasheet" target="_blank">Katalog EN</a>';
           // }
            /*if (true === $this->acl->check('/wcms/news/all')) {
                $menu[] = $html->link(T_('News'), array('controller' => 'news', 'action' => 'all'), array('class' => (($this->name == 'News') ? 'opened' : '')));
            }*/
           // $menu[] = $html->link(T_('Edit Website'), '/');

            $this->view->menu = $menu;

            if (true === $this->acl->check('/wcms/other')) {
                $this->view->extras_menu = array(
                    //$html->link(T_('Settings'), array('controller' => 'setting')),
                    $html->link(T_('Languages'), array('controller' => 'language', 'action' => 'all')),
                    //$html->link(T_('Site templates'), array('controller' => 'sitetemplate', 'action' => 'all')),
                    //$html->link(T_('Component templates'), array('controller' => 'componenttemplate', 'action' => 'all')),
                    //$html->link(T_('Navigation templates'), array('controller' => 'navtemplate', 'action' => 'all')),
                    //$html->link(T_('Javascript'), array('controller' => 'javascript', 'action' => 'all')),
                    //$html->link(T_('CSS'), array('controller' => 'css', 'action' => 'all')),
                    //$html->link(T_('Site Structure'), array('controller' => 'site', 'action' => 'all')),
                  
                    '<a href="https://pdb.kraiburg-tpe.com/special/curl/generate.php" target="_blank">PDB Header generieren</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=de" target="_blank">Katalog generieren DE</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseriesjoin?l=de" target="_blank">Katalog DE download</a>',
                    //'<a href="#" onclick="$(\'.ncw-main-tab-content\').html(\'<iframe></iframe>\'); $(\'.ncw-main-tab-content\').children(\'iframe\').attr(\'src\',\'/tpepdb/allseriesjoin?l=en\' );">Katalog EN download</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=en" target="_blank">Katalog generieren EN</a>',
                    '<a href="/tpepdb/allseriesjoin?l=en" target="_blank">Katalog EN download</a>',
                    //'<a href="#" onclick="$(\'.ncw-main-tab-content\').html(\'<iframe></iframe>\'); $(\'.ncw-main-tab-content\').children(\'iframe\').attr(\'src\',\'/tpepdb/allseriesjoin?l=en\' );">Katalog EN download</a>',
                  
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=zh" target="_blank" >Katalog generieren ZH</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseriesjoin?l=zh" target="_blank" >Katalog ZH download</a>',
                  
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=kr" target="_blank" >Katalog generieren KR</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseriesjoin?l=kr" target="_blank" >Katalog KR download</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=jp" target="_blank" >Katalog generieren JP</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseriesjoin?l=jp" target="_blank" >Katalog JP download</a>',
                  
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=pl" target="_blank">Katalog generieren PL</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseriesjoin?l=pl" target="_blank">Katalog PL download</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=it" target="_blank">Katalog generieren IT</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseriesjoin?l=it" target="_blank">Katalog IT download</a>',
                  
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=fr" target="_blank">Katalog generieren FR</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseriesjoin?l=fr" target="_blank">Katalog FR download</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseries?l=es" target="_blank">Katalog generieren SP</a>',
                    '<a href="https://pdb.kraiburg-tpe.com/tpepdb/allseriesjoin?l=es" target="_blank">Katalog SP download</a>',
                  
                   // '<a href="#" onclick="$(\'.ncw-main-tab-content\').html(\'<iframe></iframe>\'); $(\'.ncw-main-tab-content\').children(\'iframe\').attr(\'src\',\'/tpepdb/allseriesjoin?l=sp\' );">Katalog SP download</a>',
                  
                    //$html->link(T_('Contentboxes'), array('controller' => 'contentboxgroup', 'action' => 'all')),
                    //$html->link(T_('Terms'), array('controller' => 'news', 'action' => 'all')),
                );
            }
        }
    }

    /**
     * Checks if the user has got the permission to access elements of
     * the current language
     *
     * @param int     $id       the language id
     * @param boolean $redirect set to false if no redirect is wanted
     *
     * @return void
     */
    public function checkLanguageAccess ($id, $redirect = true)
    {
        if (false === $this->acl->check('/wcms/permissions/language/' . $id)) {
            if (false === $redirect) {
                return false;
            }
            $this->redirect(
                array(
                    'module' => 'core',
                    'controller' => 'user',
                    'action' => 'denied',
                )
            );
        }
        return true;
    }

    /**
     * Published an object
     *
     * @param string $type
     * @param Ncw_DataModel $object
     */
    public function publishObject ($type, Ncw_DataModel $object)
    {
        $db = Ncw_Database::getInstance();
        $id = $object->getId();
        $class_name = 'Wcms_Published' . $type;
        $published_object = new $class_name();
        $published_object->copyFrom($object);
        $published_object->setId($id);
        $published_object->unbindModel('all');
        $existing = $published_object->findBy(
            'id',
            $id,
            array(
                'fields' => array('Published' . $type . '.id')
            )
        );
        if (false === $existing) {
            $stmt = $db->prepare('INSERT INTO ' . $published_object->db_table_name . ' (id) VALUES (:id)');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
        $published_object->save(false, false);
    }

    /**
     * Site linklist
     *
     * @param string $language_code
     * @param int $language_code
     * @param boolean $mode
     * @param array $prefix
     * @param int $site_id
     * @param array $names
     * @param int $depth
     * @param boolean $absolute
     *
     * @return string
     */
    public function siteLinklist (&$linklist, $language_code = 'en', $mode = true, $prefix = '--', $site_id = 1, $names = array(), $depth = 0, $absolute = false)
    {
        $site = new Wcms_Site();
        $site->unbindModel('all');
        $sites = $site->fetch(
            'all',
            array(
                'conditions' => array(
                    'Site.parent_id =' => $site_id
                ),
                'fields' => array(
                    'Site.id',
                    'Site.name'
                ),
                'order' => array('Site.parent_id', 'Site.position')
            )
        );

        foreach ($sites as $site) {
            $names[$depth] = $site->getName();
            // Name, URL
            $linklist[$this->makeUrlForWebsite(
                $site,
                $names,
                $language_code,
                $mode,
                true,
                $absolute
            )] = array(
                'prefix' => str_repeat($prefix, $depth),
                'name' => $site->getName()
            );
            $this->siteLinklist($linklist, $language_code, $mode, $prefix, $site->getId(), $names, $depth + 1, $absolute);
        }
    }

    /**
     * Creates the website url for the given site
     *
     * @param mixed $site
     * @param mixed $breadcrumb
     * @param string $language_code
     * @param boolean $non_dynamic (optional)
     * @param boolean $live if preview mode then set to false
     * @param boolean $absolute if absolute path is needed
     *
     * @return string
     */
    public function makeUrlForWebsite ($site, $breadcrumb = false, $language_code, $non_dynamic = true, $live = true, $absolute = false)
    {
        if (true === $site instanceof Ncw_DataModel) {
            $site_id = $site->getId();
            $site_parent_id = $site->getParentId();
            $site_name = $site->getName();
            $site_permalink = $site->getPermalink();
        } else {
            $site_id = $site['id'];
            $site_parent_id = $site['parent_id'];
            $site_name = $site['name'];
            $site_permalink = $site['permalink'];
        }

        // if no breadcrumb is given, then the breadcrumb must be read
        if (false === $breadcrumb) {
            $breadcrumb = array();
            $this->readSiteBreadcrumb($breadcrumb, $site_parent_id, $live);
            foreach ($breadcrumb as &$breadcrumb_node) {
                $breadcrumb_node = $breadcrumb_node['name'];
            }
            $breadcrumb[] = $site_name;
        }

        $url = '';
        // remove empty breadcrumb nodes from the array.
        $empty_elements = array_keys($breadcrumb, '');
        foreach ($empty_elements as $e) {
            unset($breadcrumb[$e]);
        }
        $name = implode('/', $breadcrumb);
        // not permalink ?
        $id = '';
        if (false === $live
        || false === (boolean) $site_permalink
        ) {
            $id =  '-' .$site_id;
        }

        if (false === $absolute) {
            $base = Ncw_Configure::read('Project.relative_uri');
        } else {
            $base = $this->base;
        }
        // set up the url
        if (true === $non_dynamic) {
            $url = $language_code . '/'
              . str_replace(array(' '), array('-'), $name) . $id;
        } else {
            $url =  '{language.code}' . '/'
              . str_replace(array(' '), array('-'), $name) . $id;
        }
        if (true === Ncw_Configure::read('App.rewrite')) {
            $url = $base . '/' . $url;
        } else {
            $url = $base . '/index.php?url=' . $url;
        }
        return $url;
    }

    /**
     * Reads the breadcrumb for the given site id
     *
     * @param array $breadcrumb
     * @param int $parent_id
     * @param boolean $live
     *
     * @return void
     */
    public function readSiteBreadcrumb (&$breadcrumb, $parent_id, $live = true)
    {
        if ($parent_id > 1) {
            switch ($live) {
                case true:
                    $site = new Wcms_PublishedSite();
                    $site->unbindModel('all');
                    $sites = $site->fetch(
                        'all',
                        array(
                            'conditions' => array('PublishedSite.id' => $parent_id),
                            'fields' => array(
                                'PublishedSite.id',
                                'PublishedSite.name',
                                'PublishedSite.parent_id'
                            )
                        )
                    );
                    break;
                case false:
                    $site = new Wcms_Site();
                    $site->unbindModel('all');
                    $sites = $site->fetch(
                        'all',
                        array(
                            'conditions' => array('Site.id' => $parent_id),
                            'fields' => array(
                                'Site.id',
                                'Site.name',
                                'Site.parent_id'
                            )
                        )
                    );
                    break;
            }
            foreach ($sites as $site) {
                $breadcrumb[] = array('id' => $site->getId(), 'name' => $site->getName());
                $this->readSiteBreadcrumb($breadcrumb, $site->getParentId(), $live);
            }
        } else {
            $breadcrumb = array_reverse($breadcrumb);
        }
    }

    /**
     * Site select opionts
     *
     * @param mixed $not_this_site
     * @param int $parent_id
     * @param int $depth
     *
     * @return string
     */
    public function siteSelectOptions ($parent_id = 0, $not_this_site = false, $start_parent_id = 0, $depth = 0)
    {
        $site = new Wcms_Site();
        $site->unbindModel('all');
        $parent_options = $site->fetch(
            'all',
             array(
                 'fields' => array(
                     'Site.id',
                     'Site.name'
                 ),
                 'conditions' => array('Site.parent_id' => $start_parent_id),
                 'order' => array('Site.parent_id', 'Site.position')
             )
        );
        $options = '';
        foreach ($parent_options as $option) {
            if ($not_this_site == $option->getId()) {
                continue;
            }
            $selected = '';
            if ($parent_id == $option->getId()) {
                $selected = ' selected="selected"';
            }

            $id =   $option->getId();
            $name = $option->getName();
            if ($id === 1) {
                $name = T_('root');
            }

            $options .= '<option value="' . $option->getId() . '"' . $selected. '>'
                . str_repeat('--', $depth) . ' ' . $name . '</option>';
            $options .= $this->siteSelectOptions(
                $parent_id,
                $not_this_site,
                $option->getId(),
                $depth + 1
            );
        }
        return $options;
    }

    /**
     * Unpublish a sitelanguage with components.
     *
     * @param Wcms_Sitelanguage $sitelanguage
     */
    public function unpublishComponents (Wcms_Sitelanguage $sitelanguage)
    {
        // delete the current published component, componentlanguages and the value fields (text, shorttext, file) which belongs to this sitelanguage.
        $full_published_componentlanguage = new Wcms_PublishedComponentlanguage();
        $published_componentlanguages = $full_published_componentlanguage->fetch(
            'all',
            array(
                'conditions' => array(
                    'PublishedComponent.site_id' => $sitelanguage->getSiteId(),
                    'PublishedComponentlanguage.language_id' => $sitelanguage->getLanguageId()
                )
            )
        );
        foreach ($published_componentlanguages as $published_componentlanguage) {
            $full_published_componentlanguage = new Wcms_PublishedComponentlanguage();
            $full_published_componentlanguage->copyFrom($published_componentlanguage);
            $full_published_componentlanguage->delete();

            // check if the component has still languages, because if not then it can be deleted.
            $full_published_componentlanguage->unbindModel('all');
            $full_published_componentlanguage = $full_published_componentlanguage->fetch(
                'first',
                array(
                    'conditions' => array(
                        'PublishedComponentlanguage.component_id' => $published_componentlanguage->PublishedComponent->getId()
                    )
                )
            );
            if (false === $full_published_componentlanguage) {
                // delete the component because no language is live.
                $full_published_component = new Wcms_PublishedComponent();
                $full_published_component->copyFrom($published_componentlanguage->PublishedComponent);
                $full_published_component->delete();
            }
        }
    }

    /**
     * flush the website cache.
     *
     */
    public function flushWebsiteCache ()
    {
        $cache = new Ncw_Helpers_Cache();
        $cache->object->flush('website');
    }

    /**
     * Reads sitetypes tabs and data
     *
     * @param string $class
     * @param int    $obj_id
     *
     * @return array
     */
    public function sitetype ($class, $obj_id)
    {
        $tabs = array();
        $content = array(
            'data' => array(
                'main' => '',
                'meta' => '',
            ),
            'behaviour' => array(
                'main' => '',
                'miscellaneous' => '',
            ),
            'access' => '',
            'translations' => '',
        );
        $class = ucfirst($class);
        if (false === empty($class)) {
            $class_full = 'Wcms_' . $class . 'Controller';
            if (true === class_exists($class_full)) {
                $controller = new $class_full();
                $tabs = $controller->tabs;

                $content['data']['main'] = $this->_sitetypeCallAction('dataMain', $obj_id, $controller);
                $content['data']['meta'] = $this->_sitetypeCallAction('dataMeta', $obj_id, $controller);
                $content['behaviour']['main'] = $this->_sitetypeCallAction('behaviourMain', $obj_id, $controller);
                $content['behaviour']['miscellaneous'] = $this->_sitetypeCallAction('behaviourMiscellaneous', $obj_id, $controller);
                $content['access'] = $this->_sitetypeCallAction('access', $obj_id, $controller);
                $content['translations'] = $this->_sitetypeCallAction('translations', $obj_id, $controller);

                foreach ($tabs as $method => $tab_options) {
                    if (true === isset($tab_options['subtabs'])) {
                        if (false === isset($content[$method])) {
                            $content[$method] = array();
                        }
                        foreach ($tab_options['subtabs'] as $sub_method => $subtab_options) {
                            $content[$method][$sub_method] = $this->_sitetypeCallAction(
                                $sub_method,
                                $obj_id,
                                $controller
                            );
                        }
                    } else {
                        $content[$method] = $this->_sitetypeCallAction($method, $obj_id, $controller);
                    }
                }
            }
        }
        return array($tabs, $content);
    }

    /**
     * Updates sitetypes data
     *
     * @param string $class
     * @param int    $obj_id
     * @param array  $data
     *
     * @return array
     */
    public function sitetypeUpdate ($class, $obj_id, $data)
    {
        $class = ucfirst($class);
        if (false === empty($class)) {
            $params = $this->params;
            $params['pass'] = array($obj_id);
            $params['data'] = $data;

            unset(
                $params['controller'],
                $params['action']
            );

            return $this->requestAction(
                array(
                    'controller' => $class,
                    'action' => 'update',
                ),
                $params
            );
        }
    }

    /**
     * Publish sitetypes
     *
     * @param string $class
     * @param int    $obj_id
     *
     * @return array
     */
    public function sitetypePublish ($class, $obj_id)
    {
        $class = ucfirst($class);
        if (false === empty($class)) {
            $params = $this->params;
            $params['pass'] = array($obj_id);

            unset(
                $params['controller'],
                $params['action']
            );

            return $this->requestAction(
                array(
                    'controller' => $class,
                    'action' => 'publish',
                ),
                $params
            );
        }
    }

    /**
     * Unpublish sitetypes
     *
     * @param string $class
     * @param int    $obj_id
     *
     * @return array
     */
    public function sitetypeUnpublish ($class, $obj_id)
    {
        $class = ucfirst($class);
        if (false === empty($class)) {
            $params = $this->params;
            $params['pass'] = array($obj_id);

            unset(
                $params['controller'],
                $params['action']
            );

            return $this->requestAction(
                array(
                    'controller' => $class,
                    'action' => 'unpublish',
                ),
                $params
            );
        }
    }

    /**
     * Calls a sitetype action
     *
     * @param string         $action_name
     * @param int            $obj_id
     * @param Ncw_Controller $controller
     *
     * @return string
     */
    protected function _sitetypeCallAction ($action_name, $obj_id, $controller)
    {
        if (true === method_exists($controller, $action_name . 'Action')) {
            $params = $this->params;
            $params['pass'] = array($obj_id);
            $params[] = 'return';

            unset(
                $params['controller'],
                $params['action']
            );

            return $this->requestAction(
                array(
                    'controller' => $controller->name,
                    'action' => $action_name,
                ),
                $params
            );
        }
    }
}
?>
