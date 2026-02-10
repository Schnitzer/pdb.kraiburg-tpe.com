<?php
/* SVN FILE: $Id$ */
/**
 * Contains the WebsiteController class.
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
 * WebsiteController class.
 *
 * @package netzcraftwerk
 */
class Wcms_WebsiteController extends Wcms_ModuleController
{

    /**
     * Use these components
     *
     * @var array
     */
    public $components = array('Acl', 'RequestHandler', 'Session');

    /**
     * ACL publics
     *
     * @var array
     */
    public $acl_publics = array('index', 'robots', 'search');

    /**
     * No helpers needed
     *
     * @var array
     */
    public $helpers = array();

	/**
	 * WebsiteController hasn't got a model.
	 *
	 * @var boolean
	 */
	public $has_model = false;

	/**
	 * Website needs no language pack.
	 *
	 * @var boolean
	 */
	public $language_packs = false;

	/**
	 * Auto render is false
	 *
	 * @var boolean
	 */
	public $auto_render = false;

    /**
     * The ACL object
     *
     * @var Ncw_Components_Acl
     */
    public $acl = null;

	/**
	 * The setting object.
	 *
	 * @var Wcms_Setting
	 */
	protected $_setting = null;

    /**
     * The Cache object
     *
     * @var Ncw_Helpers_Cache
     */
    protected $_cache = null;

    /**
     * The Text object
     *
     * @var Ncw_Helpers_Text
     */
    protected $_text = null;

    /**
     * The DB object
     *
     * @var Ncw_Database
     */
    protected $_db = null;

	/**
	 * True if the user is logged in
	 *
	 * @var boolean
	 */
	protected $_logged_in = false;

	/**
	 * The requested site
	 *
	 * @var string
	 */
	protected $_requested_site = '';

	/**
	 * The current language id
	 *
	 * @var int
	 */
	protected $_current_language_id = 0;

    /**
     * The current user
     *
     * @var int
     */
    protected $_current_user = array();

    /**
     * Current search string
     *
     * @var string
     */
    protected $_current_search_string = '';

    /**
     * Current search results
     *
     * @var array
     */
    protected $_current_search_results = array();

	/**
	 * Before render
	 *
	 */
	public function beforeFilter ()
	{
        // if the user is logged in.
        if (true === $this->_logged_in = $this->checkLogin(false)) {
            $this->_current_user = Ncw_Components_Session::readInAll('user');
            $this->acl->read($this->_current_user['id'], '');
        }

        $this->do_not_check = true;
	    parent::beforeFilter();

	    $this->_cache = new Ncw_Helpers_Cache();
	    $this->_cache = $this->_cache->object;

	    $this->_text = new Ncw_Helpers_Text();

		$this->_db = Ncw_Database::getInstance();

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
	 * Index action.
	 *
	 */
	public function indexAction ()
	{
	    $this->_requested_site = $requested_site = $this->params['named']['url'];

		$this->view = false;

		if (trim($requested_site, '/') === $this->_setting->Language->getShortcut()) {
			$this->_redirect301($this->base);
		}
		$live = true;
		$admin = false;

		// if the user is logged in.
		if (true === $this->_logged_in) {
            // if the user has the permissions to edit the site
            if (true === $this->acl->check('/wcms')) {
                $admin = true;
                $live = false;
                if (true === isset($_GET['live']) && 1 == $_GET['live']) {
                    $live = true;
                }
            }
		}

		// get the site attributes
		list(
            $id,
            $language_id,
            $language_code,
            $site_url
        ) = $this->_getSiteAttributes($requested_site, $live);
        // if no site was found then show the error page
		if ($id === 0) {
			$this->_error404();
		}

		$this->_current_language_id = $language_id;

        // get the languages
        $languages = $this->_getLanguages($live);

        // build the website
		$website = '';
		if (true === $admin) {
		    // preview website
            $website = $this->_buildPreviewWebsite(
                $id,
                $language_id,
                $language_code,
                $languages,
                $live
            );
		} else {
		    // live website

            // check if the language is activ
            $language_activ = false;
            foreach ($languages as $language) {
                if ($language['id'] === $language_id) {
                    $language_activ = true;
                }
            }
            if (false === $language_activ) {
                if ($language_id !== $this->_setting->getLanguageId()) {
                    $this->_redirect301(
                        $this->base . '/' . $this->_setting->Language->getShortcut()
                        . '/' . $site_url
                    );
                } else {
                    $this->_error404();
                }
            }
			$cache_id = $this->_cache->generateID(
                'page/' . $id . '/' . $language_id
			);
			if (true === $this->_cache->isCached($cache_id, 'website')
                && false === $this->_cache->isExpired($cache_id, 'website')
            ) {
				$website = $this->_cache->get($cache_id, 'website');
				if (true === Cache::isError($website)) {
					throw new Ncw_Exception($website);
				}
			} else {
				$website = $this->_buildWebsite(
				    $id,
				    $language_id,
				    $language_code,
				    $languages,
				    $site_url
				);
			}
		}

		// display the website.
		print $website;
	}

    /**
     * Robots txt action
     *
     */
    public function robotsAction ()
    {
        header('Content-Type: text/plain');
        print $this->_setting->getRobots();
    }

    /**
     * The search action
     *
     * @return viod
     */
    public function searchAction ()
    {
        $this->view = false;

        $results = $this->_getSearchResult();
        print json_encode($results);
    }

	/**
	 * Build the website
	 *
	 * @param int $id
	 * @param int $language_id
	 * @param string $language_code
	 * @param string $languages
	 * @param string $site_url
	 *
	 * @return string
	 */
	protected function _buildWebsite ($id, $language_id, $language_code, $languages, $site_url)
	{
	    $admin_code = false;
	    $live = true;
	    $site_copies = false;
		$obj_site = new Wcms_PublishedSite();

	    // copies stuff
		$obj_site->unbindModel('all');
        $obj_site->bindModel(
            array(
                'has_one' => array(
                    'PublishedSitelanguage' => array('foreign_key' => 'site_id'),
                    'Language' => array(
                        'join_condition' => 'PublishedSitelanguage.language_id=Language.id'
                    )
                )
            )
        );
        $count = $obj_site->fetch(
            'count',
            array(
                'conditions' => array(
                    'PublishedSitelanguage.site_id' => $id,
                    'Language.id' => $language_id,
                    '(PublishedSite.schedule=0 || (PublishedSite.schedule=1 && '
                    . 'PublishedSite.publish <= NOW() && PublishedSite.expire > NOW()))'
                ),
                'limit' => '1'
            )
        );

        if ($count === 0
           && true === (boolean) $languages[$language_id]['copies']
        ) {
            $language_id = $this->_setting->getLanguageId();
            $site_copies = true;
            // $language_code = $languages[$language_id]['shortcut'];
        }

		$obj_site->unbindModel('all');
		$obj_site->bindModel(
            array(
                'belongs_to' => array('Sitetemplate', 'Sitetype'),
                'has_one' => array(
                    'PublishedSitelanguage' => array('foreign_key' => 'site_id'),
                    'Language' => array(
                        'join_condition' => 'PublishedSitelanguage.language_id=Language.id'
                    )
                )
            )
        );
		$obj_site->setId($id);
		$success = $obj_site->read(
            array(
                'conditions' => array(
                    'Language.id' => $language_id,
                    '(PublishedSite.schedule=0 || (PublishedSite.schedule=1 && '
                    . 'PublishedSite.publish <= NOW() && PublishedSite.expire > NOW()))'
                ),
                'fields' => array(
                    'PublishedSite.id',
                    'PublishedSite.parent_id',
                    'PublishedSite.private',
                    'PublishedSite.schedule',
                    'PublishedSite.expire',
                    'PublishedSite.permalink',
                    'Sitetemplate.id',
                    'Sitetemplate.filename',
                    'PublishedSite.cache',
                    'PublishedSite.cache_exparation',
                    'PublishedSitelanguage.id',
                    'PublishedSitelanguage.name',
                    'PublishedSitelanguage.title',
                    'PublishedSitelanguage.keywords',
                    'PublishedSitelanguage.description',
                    'PublishedSitelanguage.author',
                    'PublishedSitelanguage.home',
                    'PublishedSitelanguage.modified',
                    'PublishedSitelanguage.created',
                    'Language.id',
                    'Language.shortcut',
                    'Language.copies',
                    'Sitetype.site_class',
                    'Sitetype.sitelanguage_class',
                )
            )
        );
		if (true === $success) {
			$admin = false;

            $site = array();
            $site['title'] = h($obj_site->PublishedSitelanguage->getTitle());
            $site['id'] = $obj_site->getId();
            $site['sitelanguage_id'] = $obj_site->PublishedSitelanguage->getId();
            $site['name'] = $obj_site->PublishedSitelanguage->getName();
            $site['keywords'] = $obj_site->PublishedSitelanguage->getKeywords();
            $site['description'] = $obj_site->PublishedSitelanguage->getDescription();
            $site['author'] = $obj_site->PublishedSitelanguage->getAuthor();
            $site['created'] = $obj_site->PublishedSitelanguage->getCreated();
            $site['modified'] = $obj_site->PublishedSitelanguage->getModified();
            $site['language_code'] = $obj_site->Language->getShortcut();
            $site['language_id'] = $obj_site->Language->getId();
            $site['copies'] = $obj_site->Language->getCopies();
            
            $site_id = $obj_site->getId();
            $sitelanguage_id = $obj_site->PublishedSitelanguage->getId();

		    // if the site is set to private
			// check if the user has got the permission.
		    if (true === (boolean) $obj_site->getPrivate()) {
			    $access = false;
                if (   true === $this->_logged_in
                    && true === $this->acl instanceof Ncw_Components_Acl
                    && true === $this->acl->check('/wcms/permissions/website/' . $obj_site->getId())
                ) {
                    $access = true;
                }
                if (false === $access) {
                    $this->_error403();
                }
			}

		    // if the site is a home site then cut of the site url
		    // and redirect to the new url
			if ((boolean) $obj_site->PublishedSitelanguage->getHome() === true
                && false === empty($site_url)
            ) {
				if ($language_code === $this->_setting->Language->getShortcut()) {
					$language_code = '';
				}
				$this->_redirect301($this->base . '/' . $language_code);
			}

            $this->_triggerCallback(
                'beforeWebsiteRender',
                $obj_site->getId(),
                $obj_site->PublishedSitelanguage->getId(),
                $language_id,
                $language_code,
                $obj_site->Sitetype
            );

		    if (true === (boolean) $obj_site->Language->getCopies()) {
                $fake_language_id = $this->_setting->getLanguageId();
            } else {
                $fake_language_id = $obj_site->Language->getId();
            }

            // Get the sites for the navigations
            list($sites, $breadcrumb) = $this->_getSites(
                $obj_site->getId(),
                $fake_language_id,
                $language_code
            );

            // get the site url
            list($site_url, $site_file) = $this->_getSiteUrl(
                $obj_site,
                $breadcrumb,
                $language_code,
                (boolean) $obj_site->PublishedSitelanguage->getHome()
            );

			// get the news content
			$news = $this->_getNews($obj_site->getId(), $fake_language_id);

			// get the content of each area
			$areas = $this->_getAreas($obj_site->getId(), $language_id, $language_code);
			// get the html output of each content.
			$areas_code = array();
			foreach ($areas as $area => $components) {
				foreach ($components as $position => $component) {
					ob_start();
					include (
                        ASSETS . DS . 'wcms' . DS . 'component_templates'
                        . DS . $component['template_id'] . '.phtml'
                    );
					$areas_code[$area][] = ob_get_clean();
				}
			}
			$areas = $areas_code;
			unset($areas_code);

			// Get the site template html code.
			if ($obj_site->Sitetemplate->getId() > 0) {
				$sitetype = $obj_site->Sitetype;

			$template_file = ASSETS . DS . 'wcms' . DS . 'site_templates' . DS . $obj_site->Sitetemplate->getFilename() . '.phtml';

			ob_start();
			include ($template_file);
			$output = ob_get_clean();

			$this->_triggerCallback(
				'afterWebsiteRender',
				$site_id,
				$sitelanguage_id,
				$language_id,
				$language_code,
				$sitetype
			);
				if (true === (boolean) $obj_site->getCache()) {
					if (true === (boolean) $obj_site->getSchedule()) {
						$cache_exparation = $obj_site->getExpire();
						$cache_exparation = explode(' ', $cache_exparation);
						$cache_exparation[0] = explode('-', $cache_exparation[0]);
						$cache_exparation[1] = explode(':', $cache_exparation[1]);
						$cache_exparation = (int) mktime(
                            $cache_exparation[1][0],
                            $cache_exparation[1][1],
                            $cache_exparation[1][2],
                            $cache_exparation[0][1],
                            $cache_exparation[0][2],
                            $cache_exparation[0][0]
                        ) - time();
					} else {
						$cache_exparation = (int) $obj_site->getCacheExparation();
					}
					$result = $this->_cache->save(
                        $this->_cache->generateID('page/' . $id . '/' . $language_id),
                        $output,
                        $cache_exparation,
                        'website'
                    );
					if (true === Cache::isError($result)) {
						throw new Ncw_Exception($result);
					}
				}
				return $output;
			}
		} else if ($language_code !== $this->_setting->Language->getShortcut()) {
			$this->_redirect301(
                $this->base . '/' . $this->_setting->Language->getShortcut()
                . '/' . $site_url
            );
		}
		$this->_error404();
		return false;
	}

		/**
	 * Gets the site attributes.
	 *
	 * @param string $requested_site
	 * @param boolean $live
	 *
	 * @return string
	 */
	protected function _getSiteAttributes ($requested_site, $live = true)
	{
		$id = 0;
		$language_id = 0;
		$language_code = '';
		$site_url = '';
		// if a specific site is requested then read the parameters
		if ($requested_site != '') {
			$matches = array();
			if (true == preg_match('=' . $this->_setting->getRewrite() . '=i', $requested_site, $matches)) {
                $language_code = $matches[1];
				if (false === empty($matches[3])) {
                    // check if requested url fits to a permalink
                    $permalink = new Wcms_Permalink();
                    $site_name = array();
                    foreach (explode('/', $matches[3]) as $part) {
                        $site_name[] = $part;
                    }
                    $permalink = $permalink->findBy('permalink', implode('/', $site_name));
                    if (false !== $permalink) {
                        $id = $permalink->getSiteId();
                    } else {
                        $parts = explode('-', $matches[3]);
                        $id = (int) array_pop($parts);
                    }
                    unset($permalink, $site_name, $part);
				}
				$site_url = $matches[3];
				// get the language id.
				$language = new Wcms_Language();
				$language->unbindModel('all');
				$language = $language->findBy('shortcut', $language_code);
				if (true === $language instanceof Ncw_DataModel) {
					$language_id = $language->getId();
				}
			}
			unset($matches, $requested_site);
			if ($id === 0 && $site_url === '') {
				$id = $this->_getHomesite($language_id, $live);
				// if the id is still 0 then redirect to the master home site
				if ($id === 0
				    && $language_code !== $this->_setting->Language->getShortcut()
				) {
					$this->_redirect301($this->base);
				}
			}
		} else {
			$language_id = $this->_setting->getLanguageId();
			$language_code = $this->_setting->Language->getShortcut();
			$id = $this->_getHomesite($language_id, $live);
			// if the id is still 0 then redirect to the master home site
			if ($id === 0
                && $language_code !== $this->_setting->Language->getShortcut()
            ) {
				$this->_redirect301($this->base);
			}
		}

		if ($id > 0 && $language_id > 0 && $language_code != '') {
			return array($id, $language_id, $language_code, $site_url);
		}

		$this->_error404();

		return false;
	}

	/**
	 * Read the home site.
	 *
	 * @param int $language_id
	 * @param boolean $live (optional)
	 *
	 * @return int
	 */
	protected function _getHomesite ($language_id, $live = true)
	{
		// Get home site
		switch ($live) {
		case true;
			$sitelanguage = new Wcms_PublishedSitelanguage();
			$sitelanguage = $sitelanguage->fetch('first', array('conditions' => array('PublishedSitelanguage.language_id' => $language_id, 'PublishedSitelanguage.home' => true), 'fields' => array('PublishedSitelanguage.site_id')));
			break;

		case false:
			$sitelanguage = new Wcms_Sitelanguage();
			$sitelanguage = $sitelanguage->fetch('first', array('conditions' => array('Sitelanguage.language_id' => $language_id, 'Sitelanguage.home' => true), 'fields' => array('Sitelanguage.site_id')));
	    }
		if (true === $sitelanguage instanceof Ncw_DataModel) {
			return $sitelanguage->getSiteId();
		}
		return 0;
	}

	/**
	 * Enter description here...
	 *
	 * @param boolean $live live mode on or not
	 *
	 * @return array
	 */
	protected function _getLanguages ($live = true)
	{
	    if (true === $live) {
	        $options = array(
                'conditions' => array(
                    'Language.active' => 1
	             ),
                 'order' => array('Language.position')
            );
	    } else {
	        $options = array('order' => array('Language.position'));
	    }

        $language = new Wcms_Language();
        $language->unbindModel('all');
        $languages = $language->fetch(
            'all',
            $options
        );
        $arr_language = array();
        foreach ($languages as $language) {
            $arr_language[$language->getId()] = array(
                'id' => $language->getId(),
                'name' => $language->getName(),
                'shortcut' => $language->getShortcut(),
                'copies' => $language->getCopies(),
            );
        }
        return $arr_language;
	}

	/**
	 * Get the site url
	 *
	 * @param Wcms_Site $site          the site object
	 * @param array     $breadcrumb    the breadcrumb array
	 * @param string    $language_code the language code
	 * @param boolean   $home          set to true if site is a home site
	 * @param boolean   $live          set to false if not
	 *
	 * @return string
	 */
	protected function _getSiteUrl ($site, $breadcrumb, $language_code, $home = false, $live = true)
	{
		if (true === $home) {
			if ($this->_setting->Language->getShortcut() === $language_code) {
                $url = $this->base;
			} else {
				$url = $this->base . '/' . $language_code;
			}
			return array(
                $url,
                ''
			);
		}

		$arr_breadcrumb = array();
		foreach ($breadcrumb as $breadcrumb_node) {
		    $arr_breadcrumb[] = $breadcrumb_node['url_name'];
		}

	    $url = $this->makeUrlForWebsite(
	        $site,
	        $arr_breadcrumb,
	        $language_code,
            true,
            $live,
            true
	    );

	    $file = str_replace($this->base . '/', '', $url);
	    $file = str_replace(Ncw_Configure::read('Project.relative_uri') . '/', '', $file);
	    $file = explode('/', trim($file, '/'));
	    $file = array_reverse($file);
	    array_pop($file);
	    $file = array_reverse($file);
	    $file = implode('/', $file);

	    return array(
	       $url,
	       $file
	    );
	}

	/**
	 * Get the sites.
	 *
	 * @param int $site_id
	 * @param int $parent_id
	 * @param int $language_id
	 * @param string $language_code
	 * @param boolean $live (optional)
	 *
	 * @return array the site objects
	 */
	protected function _getSites ($site_id, $language_id, $language_code, $live = true)
	{
		// read all sites
		switch ($live) {
			case true:
			    $sitelanguage_label = 'PublishedSitelanguage';
			    $site_navtemplate_label = 'PublishedSiteNavtemplate';
			    $sitetype_label_pre = 'Published';
				$site = new Wcms_PublishedSite();
				$site->unbindModel('all');
				$site->bindModel(
				    array(
				        'has_one' => array(
				            'PublishedSitelanguage' => array(
				                'join_condition' => 'PublishedSitelanguage.site_id=PublishedSite.id'
				            )
				        ),
				        'belongs_to' => array('Sitetype'),
				        'has_many' => array(
                            'PublishedSiteNavtemplate' => array(
				                'foreign_key' => 'site_id'
				            )
                        )
				    )
				);
				$sites = $site->fetch(
				    'all',
				    array(
				        'conditions' => array(
				            'PublishedSitelanguage.language_id' => $language_id,
				            'PublishedSite.id !=' => 1,
				            '(PublishedSite.schedule=0 || (PublishedSite.schedule=1 && PublishedSite.publish <= NOW() && PublishedSite.expire > NOW()))'
				        ),
				        'fields' => array(
				            'PublishedSite.id',
				            'PublishedSite.parent_id',
				            'PublishedSite.position',
				            'PublishedSite.name',
				            'PublishedSite.permalink',
				            'PublishedSitelanguage.id',
				            'PublishedSitelanguage.title',
				            'PublishedSitelanguage.name',
                            'Sitetype.site_class',
                            'Sitetype.sitelanguage_class',
				        ),
                        'order' => array(
                            'PublishedSite.parent_id',
                            'PublishedSite.position',
                        )
				    )
				);

				$breadcrumb = array();
                $this->readSiteBreadcrumb($breadcrumb, $site_id);
                foreach ($breadcrumb as &$breadcrumb_node) {
                    $breadcrumb_node = $breadcrumb_node['id'];
                }
				break;
			case false:
			    $sitelanguage_label = 'Sitelanguage';
			    $site_navtemplate_label = 'SiteNavtemplate';
			    $sitetype_label_pre = '';
				$site = new Wcms_Site();
				$site->unbindModel('all');
				$site->bindModel(
				    array(
				        'has_one' => array('Sitelanguage'),
				        'belongs_to' => array('Sitetype'),
				        'has_many' => array(
				            'SiteNavtemplate'
				        )
				    )
				);
				$sites =  $site->fetch(
                    'all',
				    array(
				        'conditions' => array(
				            'Sitelanguage.language_id' => $language_id,
				            'Site.id !=' => 1,
				        ),
				        'fields' => array(
				            'Site.id',
				            'Site.parent_id',
				            'Site.name',
				            'Site.position',
				            'Site.permalink',
				            'Sitelanguage.id',
				            'Sitelanguage.title',
				            'Sitelanguage.name',
				            'Sitetype.site_class',
				            'Sitetype.sitelanguage_class',
				        ),
				        'order' => array(
				            'Site.parent_id',
				            'Site.position',
				        )
				    )
				);

		        $breadcrumb = array();
                $this->readSiteBreadcrumb($breadcrumb, $site_id, $live);
                foreach ($breadcrumb as &$breadcrumb_node) {
                    $breadcrumb_node = $breadcrumb_node['id'];
                }
		}

	    $all_sites_unprepared = array();
        foreach ($sites as $site) {
            $navigations = array();
            foreach ($site->{$site_navtemplate_label} as $site_navtemplate) {
                $navigations[] = $site_navtemplate->getNavtemplateId();
            }

            $site_type = $sitelanguage_type = array();

            $site_class = $site->Sitetype->getSiteClass();
            if (false === empty($site_class)) {
                $controller_class = 'Wcms_' . ucfirst($site_class) . 'Controller';
                $controller_obj = new $controller_class();
                if (false !== $controller_obj->has_model) {
                    $site_class = 'Wcms_' . $sitetype_label_pre . ucfirst($site_class);
                    $sitetype_model = new $site_class();
                    if (true === isset($sitetype_model->read_fields)) {
                        $found_model = $sitetype_model->findBy(
                            'site',
                            $site->getId(),
                            array(
                                'fields' => $sitetype_model->read_fields
                            )
                        );
                        if (false !== $found_model) {
                            $site_type = $found_model->data();
                        }
                    }
                }
            }

            $sitelanguage_class = $site->Sitetype->getSitelanguageClass();
            if (false === empty($sitelanguage_class)) {
                $controller_class = 'Wcms_' . ucfirst($sitelanguage_class) . 'Controller';
                $controller_obj = new $controller_class();
                if (false !== $controller_obj->has_model) {
                    $sitelanguage_class = 'Wcms_' . $sitetype_label_pre . ucfirst($sitelanguage_class);
                    $sitetype_model = new $sitelanguage_class();
                    if (true === isset($sitetype_model->read_fields)) {
                        $found_model = $sitetype_model->findBy(
                            'sitelanguage_id',
                            $site->{$sitelanguage_label}->getId(),
                            array(
                                'fields' => $sitetype_model->read_fields
                            )
                        );
                        if (false !== $found_model) {
                            $sitelanguage_type = $found_model->data();
                        }
                    }
                }
            }
            unset($found_model, $controller_class, $controller_obj);

            $all_sites_unprepared[$site->getParentId()][$site->getPosition()] = array(
                'id' => $site->getId(),
                'url_name' => $site->getName(),
                'status' => $site->getStatus(),
                'parent_id' => $site->getParentId(),
                'permalink' => $site->getPermalink(),
                'name' => $site->{$sitelanguage_label}->getNameEncoded(),
                'title' => $site->{$sitelanguage_label}->getTitleEncoded(),
                'navigations' => $navigations,
                'sitetype' => array(
                    'site' => $site_type,
                    'sitelanguage' => $sitelanguage_type,
                ),
            );
        }

		$all_sites = array();
		$arr_breadcrumb = array();
		$this->_siteUrls($all_sites, $all_sites_unprepared, $arr_breadcrumb, $breadcrumb, $site_id, 1, $language_id, $language_code, $live);

		return array($all_sites, $arr_breadcrumb);
	}

	/**
	 * Adds the urls to the all sites array
	 *
	 * @param array $all_sites
	 * @param array $all_sites_unprepared
	 * @param array $breadcrumb
	 * @param int $site_id
	 * @param int $parent_id
	 * @param int $language_id
	 * @param string $language_shortcut
	 * @param boolean $live
	 * @param array $names
	 * @param int $depth
	 *
	 * @return void
	 */
    protected function _siteUrls (&$all_sites, $all_sites_unprepared, &$arr_breadcrumb, $breadcrumb, $site_id, $parent_id = 1, $language_id = 1, $language_shortcut = 'en', $live = true, $names = array(), $depth = 0)
    {
        if (true === isset($all_sites_unprepared[$parent_id])) {
            foreach ($all_sites_unprepared[$parent_id] as &$site) {

                $names[$depth] = $site['url_name'];

                $arr_site = array(
                    'id' => $site['id'],
                    'url' => $this->makeUrlForWebsite(
                        $site,
                        $names,
                        $language_shortcut,
                        true,
                        $live
                    ),
                    'url_name' => $site['url_name'],
                    'name' => $site['name'],
                    'title' => $site['title'],
                    'sitetype' => $site['sitetype'],
                    'highlight' => false
                );

                if ($site_id === $site['id']
                    || true === in_array($site['id'], $breadcrumb)
                ) {
                    $arr_site['highlight'] = true;
                    $arr_breadcrumb[] = $arr_site;
                }
                $all_sites[$depth + 1][$site['parent_id']][0][] =  $arr_site;
                foreach ($site['navigations'] as $navigation) {
                    $all_sites[$depth + 1][$site['parent_id']][$navigation][] =  $arr_site;
                }

                $site['url'] = $this->makeUrlForWebsite(
                    $site,
                    $names,
                    $language_shortcut
                );

                $this->_siteUrls($all_sites, $all_sites_unprepared, $arr_breadcrumb, $breadcrumb, $site_id, $site['id'], $language_id, $language_shortcut, $live, $names, $depth + 1);

            }
        }
    }

	/**
	 * Get the areas.
	 *
	 * @param int $site_id
	 * @param int $language_id
	 * @param string $language_code
	 * @param boolean $live
	 *
	 * @return array the area with the components.
	 */
	protected function _getAreas ($site_id, $language_id, $language_code, $live = true, $admin = false)
	{
		switch ($live) {
		case false:
			$tables = array(Ncw_Database::getConfig('prefix') . 'wcms_component', Ncw_Database::getConfig('prefix') . 'wcms_componentlanguage');
			$schedule = '';
			break;
		case true:
			$tables = array(Ncw_Database::getConfig('prefix') . 'wcms_published_component', Ncw_Database::getConfig('prefix') . 'wcms_published_componentlanguage');
			$schedule = '&& (Component.schedule=0 || (Component.schedule=1 && Component.publish <= NOW() && Component.expire > NOW()))';
		}
		try {
			$areas = array();
			// Read the components and build the areas array.
			$stmt = $this->_db->prepare("SELECT Component.id, Component.parent_id, Component.area, Componenttemplate.filename AS template_id, Component.position
										FROM " . $tables[0] . " AS Component
										INNER JOIN " . $tables[1] . " AS Componentlanguage
										ON Component.id=Componentlanguage.component_id
										INNER JOIN " . Ncw_Database::getConfig('prefix') . "wcms_componenttemplate AS Componenttemplate
										ON Component.componenttemplate_id=Componenttemplate.id
										WHERE (Component.site_id=:site_id
										      || Component.permanent=1) &&
											  Componentlanguage.language_id=:language_id
											 " . $schedule . "
										ORDER BY Component.position");
			if (false === $stmt) {
				$error_info = $this->_db->errorInfo();
				throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
			}
			$stmt->bindValue(":site_id", $site_id, PDO::PARAM_INT);
			$stmt->bindValue(":language_id", $language_id, PDO::PARAM_INT);
			if (false === $stmt->execute()) {
				$error_info = $stmt->errorInfo();
				throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
			}
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
			    $parent_id = '';
			    if (false === empty($row['parent_id'])) {
			        $parent_id = '-' . $row['parent_id'];
			    }
				$areas[$row['area'] . $parent_id][$row['position']]['id'] = $row['id'];
				$areas[$row['area'] . $parent_id][$row['position']]['template_id'] = $row['template_id'];
			}
			$this->_getTextFields($areas, $site_id, $language_id, $schedule, $live, $admin);
			$this->_getShorttextFields($areas, $site_id, $language_id, $schedule, $live, $admin);
			$this->_getFilefields($areas, $site_id, $language_id, $language_code, $schedule, $live, $admin);
			return $areas;
		} catch (Ncw_Exception $e) {
			if (Ncw_Configure::read('debug_mode') > 0) {
				$e->exitWithMessage();
			}
		}
		return false;
	}

	/**
	 * Gets the tect fields
	 *
	 * @param Array $areas
	 * @param int $site_id
	 * @param int $language_id
	 * @param string $schedule
	 * @param boolean $live
	 * @param boolean $admin
	 */
	protected function _getTextFields (&$areas, $site_id, $language_id, $schedule, $live, $admin)
	{
		switch ($live) {
			case false:
				$tables = array(Ncw_Database::getConfig('prefix') . 'wcms_component', Ncw_Database::getConfig('prefix') . 'wcms_componentlanguage', Ncw_Database::getConfig('prefix') . 'wcms_componenttext');
				break;
			case true:
				$tables = array(Ncw_Database::getConfig('prefix') . 'wcms_published_component', Ncw_Database::getConfig('prefix') . 'wcms_published_componentlanguage', Ncw_Database::getConfig('prefix') . 'wcms_published_componenttext');
		}
		$not_master = false;
		if ($language_id !== $this->_setting->getLanguageId()) {
			$not_master = true;
		}
		// Read the text fields
		$stmt = $this->_db->prepare("SELECT Component.area, Component.parent_id, Component.id component_id, Component.position,
										   Componentlanguage.id AS componentlanguage_id,
										   Componenttext.id AS componentcontent_id,
										   Componenttext.position AS content_position,
										   Componenttext.content
									FROM " . $tables[0] . " AS Component
									INNER JOIN " . $tables[1] . " AS Componentlanguage
									ON Component.id=Componentlanguage.component_id
									INNER JOIN " . $tables[2] . " AS Componenttext
									ON Componentlanguage.id=Componenttext.componentlanguage_id
									WHERE (Component.site_id=:site_id
                                           || Component.permanent=1) &&
										  Componentlanguage.language_id=:language_id
										  " . $schedule . "
									ORDER BY Componenttext.position");
		if (false === $stmt) {
			$error_info = $this->_db->errorInfo();
			throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
		}
		$stmt->bindValue(":site_id", $site_id, PDO::PARAM_INT);
		$stmt->bindValue(":language_id", $language_id, PDO::PARAM_INT);
		if (false === $stmt->execute()) {
			$error_info = $stmt->errorInfo();
			throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
		}
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			if (true === (boolean) $this->_setting->getMasterLanguageCopy()
                && true === $not_master
                && true === empty($row['content'])
            ) {
				// If the content is empty then get the master content.
				$stmt_master = $this->_db->prepare("SELECT Componenttext.content
												   FROM " . $tables[1] . " as Componentlanguage
									  		       INNER JOIN " . $tables[2] . " as Componenttext
									  			   ON Componentlanguage.id=Componenttext.componentlanguage_id
									  			   WHERE Componentlanguage.component_id=:component_id &&
													     Componentlanguage.language_id=:language_id &&
													     Componenttext.position=:position
												   LIMIT 1");
				if (false === $stmt_master) {
					$error_info = $this->_db->errorInfo();
					throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
				}
                $stmt_master->bindValue(":component_id", $row['component_id'], PDO::PARAM_INT);
				//$stmt_master->bindValue(":component_id", $areas[$row['area']][$row['position']]['id'], PDO::PARAM_INT);
				$stmt_master->bindValue(":language_id", $this->_setting->getLanguageId(), PDO::PARAM_INT);
				$stmt_master->bindValue(":position", $row['content_position'], PDO::PARAM_INT);
				if (false === $stmt_master->execute()) {
					$error_info = $stmt_master->errorInfo();
					throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
				}
				$row_master = $stmt_master->fetchAll(PDO::FETCH_ASSOC);
				if (true === isset($row_master[0])) {
				    $row['content'] = $row_master[0]['content'];
				}
				unset($row_master);
			}
	        $parent_id = '';
            if (false === empty($row['parent_id'])) {
                $parent_id = '-' . $row['parent_id'];
            }
		    if (true === empty($row['content'])
                && true === $admin
            ) {
                $row['content'] = T_('No Content');
            }

			$areas[$row['area'] . $parent_id][$row['position']]['text'][$row['content_position']]['content'] = $row['content'];
			$areas[$row['area'] . $parent_id][$row['position']]['text'][$row['content_position']]['componentcontent_id'] = $row['componentcontent_id'];
			$areas[$row['area'] . $parent_id][$row['position']]['componentlanguage_id'] = $row['componentlanguage_id'];
			$areas[$row['area'] . $parent_id][$row['position']]['componentcontent_id'] = $row['componentcontent_id'];
		}
	}

	/**
	 * Gets the shorttext fields.
	 *
	 * @param Array $areas
	 * @param int $site_id
	 * @param int $language_id
	 * @param string $schedule
	 * @param boolean $live
	 * @param boolean $admin
	 */
	protected function _getShorttextFields (&$areas, $site_id, $language_id, $schedule, $live, $admin)
	{
		switch ($live) {
			case false:
				$tables = array(Ncw_Database::getConfig('prefix') . 'wcms_component', Ncw_Database::getConfig('prefix') . 'wcms_componentlanguage', Ncw_Database::getConfig('prefix') . 'wcms_componentshorttext');
				break;
			case true:
				$tables = array(Ncw_Database::getConfig('prefix') . 'wcms_published_component', Ncw_Database::getConfig('prefix') . 'wcms_published_componentlanguage', Ncw_Database::getConfig('prefix') . 'wcms_published_componentshorttext');
		}
		$not_master = false;
		if ($language_id !== $this->_setting->getLanguageId()) {
			$not_master = true;
		}
		// read the shorttext fields.
		$stmt = $this->_db->prepare("SELECT Component.area, Component.parent_id, Component.id component_id, Component.position,
										   Componentlanguage.id AS componentlanguage_id,
										   Componentshorttext.id AS componentcontent_id,
										   Componentshorttext.content,
										   Componentshorttext.position AS content_position
									FROM " . $tables[0] . " as Component
								    INNER JOIN " . $tables[1] . " as Componentlanguage
						 		    ON Component.id=Componentlanguage.component_id
						  			INNER JOIN " . $tables[2] . " as Componentshorttext
						  			ON Componentlanguage.id=Componentshorttext.componentlanguage_id
						  			WHERE (Component.site_id=:site_id
                                           || Component.permanent=1) &&
										  Componentlanguage.language_id=:language_id
										  " . $schedule . "
						  			ORDER BY Componentshorttext.position");
		if (false === $stmt) {
			$error_info = $this->_db->errorInfo();
			throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
		}
		$stmt->bindValue(":site_id", $site_id, PDO::PARAM_INT);
		$stmt->bindValue(":language_id", $language_id, PDO::PARAM_INT);
		if (false === $stmt->execute()) {
			$error_info = $stmt->errorInfo();
			throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
		}
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			// If the content is empty then get the master content.
		    if (true === (boolean) $this->_setting->getMasterLanguageCopy()
                && true === $not_master
                && true === empty($row['content'])
            ) {
				$stmt_master = $this->_db->prepare("SELECT Componentshorttext.content
											FROM " . $tables[1] . " as Componentlanguage
								  			INNER JOIN " . $tables[2] . " as Componentshorttext
								  			ON Componentlanguage.id=Componentshorttext.componentlanguage_id
								  			WHERE Componentlanguage.component_id=:component_id &&
												  Componentlanguage.language_id=:language_id &&
												  Componentshorttext.position=:position
											LIMIT 1");
				if (false === $stmt_master) {
					$error_info = $this->_db->errorInfo();
					throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
				}
                $stmt_master->bindValue(":component_id", $row['component_id'], PDO::PARAM_INT);
				//$stmt_master->bindValue(":component_id", $areas[$row['area']][$row['position']]['id'], PDO::PARAM_INT);
				$stmt_master->bindValue(":language_id", $this->_setting->getLanguageId(), PDO::PARAM_INT);
				$stmt_master->bindValue(":position", $row['content_position'], PDO::PARAM_INT);
				if (false === $stmt_master->execute()) {
					$error_info = $stmt_master->errorInfo();
					throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
				}
				$row_master = $stmt_master->fetchAll(PDO::FETCH_ASSOC);
				if (true === isset($row_master[0])) {
				    $row['content'] = $row_master[0]['content'];
				}
				unset($row_master);
			}
		    $parent_id = '';
            if (false === empty($row['parent_id'])) {
                $parent_id = '-' . $row['parent_id'];
            }
            if (true === empty($row['content'])
                && true === $admin
            ) {
                $row['content'] = T_('No Content');
            }
			$areas[$row['area'] . $parent_id][$row['position']]['shorttext'][$row['content_position']]['content'] = $row['content'];
			$areas[$row['area'] . $parent_id][$row['position']]['shorttext'][$row['content_position']]['componentcontent_id'] = $row['componentcontent_id'];
			$areas[$row['area'] . $parent_id][$row['position']]['componentlanguage_id'] = $row['componentlanguage_id'];
			$areas[$row['area'] . $parent_id][$row['position']]['componentcontent_id'] = $row['componentcontent_id'];
		}
	}

	/**
	 * Gets the field fields.
	 *
	 * @param Array $areas
	 * @param int $site_id
	 * @param int $language_id
	 * @param string $language_code
	 * @param string $schedule
	 * @param boolean $live
	 * @param boolean $admin
	 */
	protected function _getFileFields (&$areas, $site_id, $language_id, $language_code, $schedule, $live, $admin)
	{
		switch ($live) {
			case false:
				$tables = array(
                    Ncw_Database::getConfig('prefix') . 'wcms_component',
                    Ncw_Database::getConfig('prefix') . 'wcms_componentlanguage',
                    Ncw_Database::getConfig('prefix') . 'wcms_componentfile'
                );
				break;
			case true:
				$tables = array(
                    Ncw_Database::getConfig('prefix') . 'wcms_published_component',
                    Ncw_Database::getConfig('prefix') . 'wcms_published_componentlanguage',
                    Ncw_Database::getConfig('prefix') . 'wcms_published_componentfile'
                );
		}
		$not_master = false;
		if ($language_id !== $this->_setting->getLanguageId()) {
			$not_master = true;
		}
		// read the file fields.
		$stmt = $this->_db->prepare("SELECT Component.area, Component.id component_id, Component.parent_id, Component.position,
										   Componentlanguage.id AS componentlanguage_id,
										   Componentfile.id AS componentcontent_id,
										   concat(Componentfile.file_id, '.', File.type) AS content,
										   Componentfile.alt,
										   Componentfile.title,
										   Componentfile.link,
										   Componentfile.target,
										   Componentfile.position AS content_position,
										   File.name
									FROM " . $tables[0] . " as Component
								    INNER JOIN " . $tables[1] . " as Componentlanguage
						 		    ON Component.id=Componentlanguage.component_id
						  			INNER JOIN " . $tables[2] . " as Componentfile
						  			ON Componentlanguage.id=Componentfile.componentlanguage_id
						  			LEFT JOIN " . Ncw_Database::getConfig('prefix') . "files_file as File
						  			ON Componentfile.file_id=File.id
						  			WHERE (Component.site_id=:site_id
                                           || Component.permanent=1) &&
										  Componentlanguage.language_id=:language_id
										  " . $schedule . "
						  			ORDER BY Componentfile.position");
		if (false === $stmt) {
			$error_info = $this->_db->errorInfo();
			throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
		}
		$stmt->bindValue(":site_id", $site_id, PDO::PARAM_INT);
		$stmt->bindValue(":language_id", $language_id, PDO::PARAM_INT);
		if (false === $stmt->execute()) {
			$error_info = $stmt->errorInfo();
			throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
		}
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			// If the content is empty then get the master content.
		    if (true === (boolean) $this->_setting->getMasterLanguageCopy()
                && true === $not_master
                && true === empty($row['content'])
            ) {
				$stmt_master = $this->_db->prepare("SELECT concat(Componentfile.file_id, '.', File.type) AS content,
														  Componentfile.alt,
														  Componentfile.title,
														  Componentfile.link,
														  Componentfile.target,
														  File.name
													FROM " . $tables[1] . " as Componentlanguage
										  			INNER JOIN " . $tables[2] . " as Componentfile
										  			ON Componentlanguage.id=Componentfile.componentlanguage_id
										  			LEFT JOIN " . Ncw_Database::getConfig('prefix') . "files_file as File
										  			ON Componentfile.file_id=File.id
										  			WHERE Componentlanguage.component_id=:component_id &&
														  Componentlanguage.language_id=:language_id &&
														  Componentfile.position=:position
													LIMIT 1");
				if (false === $stmt_master) {
					$error_info = $this->_db->errorInfo();
					throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
				}
                $stmt_master->bindValue(":component_id", $row['component_id'], PDO::PARAM_INT);
				//$stmt_master->bindValue(":component_id", $areas[$row['area']][$row['position']]['id'], PDO::PARAM_INT);
				$stmt_master->bindValue(":language_id", $this->_setting->getLanguageId(), PDO::PARAM_INT);
				$stmt_master->bindValue(":position", $row['content_position'], PDO::PARAM_INT);
				if (false === $stmt_master->execute()) {
					$error_info = $stmt_master->errorInfo();
					throw new Ncw_Exception('Select failed (' . $error_info[2] . ')', 1);
				}
				$row_master = $stmt_master->fetchAll(PDO::FETCH_ASSOC);
				if (true === isset($row_master[0])) {
    				$row['content'] = $row_master[0]['content'];
    				$row['alt'] = $row_master[0]['alt'];
    				$row['title'] = $row_master[0]['title'];
    				$row['link'] = $row_master[0]['link'];
    				$row['target'] = $row_master[0]['target'];
    				$row['name'] = $row_master[0]['name'];
				}
				unset($row_master);
			}

			if (false === empty($row['content'])) {
				$row['content'] = $this->base . '/' . ASSETS . '/files/uploads/' . $this->_text->cleanForUrl($row['name']) . '_' . $row['content'];
			} else {
				$row['content'] = $this->base . '/modules/wcms/web/images/example.jpg';
			}

			$row['link'] = str_replace(
                array('{project.url}', '{language.code}'),
                array(Ncw_Configure::read('Project.relative_uri'), $language_code),
                $row['link']
            );

		    $parent_id = '';
            if (false === empty($row['parent_id'])) {
                $parent_id = '-' . $row['parent_id'];
            }
			$areas[$row['area'] . $parent_id][$row['position']]['file'][$row['content_position']]['content'] =  $row['content'];
			$areas[$row['area'] . $parent_id][$row['position']]['file'][$row['content_position']]['alt'] =  $row['alt'];
			$areas[$row['area'] . $parent_id][$row['position']]['file'][$row['content_position']]['title'] =  $row['title'];
			$areas[$row['area'] . $parent_id][$row['position']]['file'][$row['content_position']]['link'] =  $row['link'];
			$areas[$row['area'] . $parent_id][$row['position']]['file'][$row['content_position']]['target'] =  $row['target'];
			$areas[$row['area'] . $parent_id][$row['position']]['file'][$row['content_position']]['componentcontent_id'] = $row['componentcontent_id'];
			$areas[$row['area'] . $parent_id][$row['position']]['componentlanguage_id'] = $row['componentlanguage_id'];
		}
	}

	/**
	 * Reads the news for the current site.
	 *
	 * @param int     $site_id
	 * @param int     $language_id
	 * @param boolean $live        (optional)
	 *
	 * @return array
	 */
	protected function _getNews ($site_id, $language_id, $live = true)
	{
	    switch ($live) {
	    case true:
	        $newslanguage_name = 'PublishedNewslanguage';
            $news = new Wcms_PublishedNews();
            $news->unbindModel('all');
            $news->bindModel(
                array(
                    'has_one' => array(
                        'PublishedNewssite' => array('foreign_key' => 'news_id'),
                        'PublishedNewslanguage' => array('foreign_key' => 'news_id')
                    )
                )
            );
            $found_news = $news->fetch(
                'all',
                array(
                    'conditions' => array(
                        'PublishedNewslanguage.language_id' => $language_id,
                        '(PublishedNews.schedule=0 '
                        . '|| (PublishedNews.schedule=1 && PublishedNews.publish <= NOW() '
                        . '&& PublishedNews.expire > NOW()))',
                        '(PublishedNewssite.site_id' => $site_id,
                        '|| PublishedNews.allsites)' => 1
                    ),
                    'fields' => array(
                        'PublishedNews.id',
                        'PublishedNewslanguage.headline',
                        'PublishedNewslanguage.body',
                    ),
                    'order' => 'PublishedNews.modified DESC'
                )
            );
            break;
	    case false;
	        $newslanguage_name = 'Newslanguage';
            $news = new Wcms_News();
            $news->unbindModel('all');
            $news->bindModel(
                array(
                    'has_one' => array('Newssite','Newslanguage')
                )
            );
            $found_news = $news->fetch(
                'all',
                array(
                    'conditions' => array(
                		'Newslanguage.language_id' => $language_id,
                        '(Newssite.site_id' => $site_id,
                        '|| News.allsites)' => 1
                    ),
                    'fields' => array(
                        'News.id',
                        'Newslanguage.headline',
                        'Newslanguage.body',
                    ),
                    'order' => 'News.modified DESC'
                )
            );
	    }
        $arr_news = array();

        $count = 0;
        foreach ($found_news as $obj_news) {
            $headline = $obj_news->{$newslanguage_name}->getHeadline();
            $body = $obj_news->{$newslanguage_name}->getBody();

            // if head and body are empty, then copy from the master language...
            if (true === (boolean) $this->_setting->getMasterLanguageCopy()
                && true === empty($headline)
                && true === empty($body)
            ) {
                switch ($live) {
                case true:
                    $news->unbindModel('all');
                    $news->bindModel(
                        array(
                            'has_one' => array(
                                'PublishedNewssite' => array('foreign_key' => 'news_id'),
                                'PublishedNewslanguage' => array('foreign_key' => 'news_id')
                            )
                        )
                    );
                    $obj_news = $news->fetch(
                        'first',
                        array(
                            'conditions' => array(
                                'PublishedNews.id' => $obj_news->getId(),
                                'PublishedNewslanguage.language_id' => $this->_setting->getLanguageId(),
                            ),
                            'fields' => array(
                                'PublishedNewslanguage.headline',
                                'PublishedNewslanguage.body',
                            )
                        )
                    );
                    break;
                case false:
                    $news->unbindModel('all');
                    $news->bindModel(
                        array(
                            'has_one' => array('Newssite','Newslanguage')
                        )
                    );
                    $obj_news = $news->fetch(
                        'first',
                        array(
                            'conditions' => array(
                                'News.id' => $obj_news->getId(),
                                'Newslanguage.language_id' => $this->_setting->getLanguageId(),
                            ),
                            'fields' => array(
                                'Newslanguage.headline',
                                'Newslanguage.body',
                            )
                        )
                    );
                }
                if (false === $obj_news) {
                    continue;
                }
                $headline = $obj_news->{$newslanguage_name}->getHeadline();
                $body = $obj_news->{$newslanguage_name}->getBody();
            }
            
	     $arr_news[$count] = array('head' => $headline, 'body' => $body);
        
		//$arr_news[$headline] = $body;
		
        
	    $count++;
        }
        return $arr_news;
	}

	/**
	 * Before Website Render
	 *
     * @param string        $callback        the callback to trigger
     * @param int           $site_id         the site id
     * @param int           $sitelanguage_id the sitelanguage id
     * @param int           $language_id     the language id
     * @param string        $language_code   the language code
     * @param Ncw_DataModel $sitetype        the sitetype object
     * @param boolean       $live            if in live mode
	 *
	 * @return array
	 */
	protected function _triggerCallback ($callback, $site_id, $sitelanguage_id, $language_id, $language_code, Ncw_DataModel $sitetype, $live = true)
	{
        $dispatcher = new Ncw_Dispatcher();

	    $data = array();

        $sitetype_site_class = $sitetype->getSiteClass();
        if (false === empty($sitetype_site_class)) {
            print $dispatcher->dispatch(
                array(
                    'module' => 'wcms',
                    'controller' => $sitetype_site_class,
                    'action' => $callback,
                ),
                array(
                    'return' => true,
                    'pass' => array(
                        $site_id,
                        $sitelanguage_id,
                        $language_id,
                        $language_code,
                        $live
                    )
                )
            );
        }

        $sitetype_sitelanguage_class = $sitetype->getSitelanguageClass();
        if (false === empty($sitetype_sitelanguage_class)) {
            print $dispatcher->dispatch(
                array(
                    'module' => 'wcms',
                    'controller' => $sitetype_sitelanguage_class,
                    'action' => $callback,
                ),
                array(
                    'return' => true,
                    'pass' => array(
                        $site_id,
                        $sitelanguage_id,
                        $language_id,
                        $language_code,
                        $live
                    )
                )
            );
        }

        return $data;
	}

	/**
	 * Build the preview website
	 *
	 * @param int $id
	 * @param int $language_id
	 * @param string $language_code
	 * @param string $languages
	 * @param boolean $live
	 *
	 * @return void
	 */
	protected function _buildPreviewWebsite ($id, $language_id, $language_code, $languages, $live = false)
	{
        // set the language
        Ncw_Configure::write('App.language', $this->_current_user['language']);
        $this->setLocale();

  	    $success = false;
	    $admin = true;
        $site_copies = false;

		$count = 0;
		while (false === $success && $count < 2) {
    		switch ($live) {
            case true:
                $obj_site = new Wcms_PublishedSite();
                $obj_site->unbindModel('all');
                $obj_site->bindModel(
                    array(
                        'belongs_to' => array('Sitetemplate', 'Sitetype'),
                        'has_one' => array(
                            'PublishedSitelanguage' => array('foreign_key' => 'site_id'),
                            'Language' => array(
                                'join_condition' => 'PublishedSitelanguage.language_id=Language.id'
                            )
                        )
                    )
                );
                $obj_site->setId($id);
                $success = $obj_site->read(
                    array(
                        'conditions' => array(
                            'Language.id' => $language_id,
                            '(PublishedSite.schedule=0 || (PublishedSite.schedule=1 && PublishedSite.publish <= NOW() && PublishedSite.expire > NOW()))'
                        ),
                        'fields' => array(
                            'PublishedSite.id',
                            'PublishedSite.name',
                            'PublishedSite.parent_id',
                            'PublishedSite.private',
                            'PublishedSite.schedule',
                            'PublishedSite.expire' => '(PublishedSite.schedule=0 || (PublishedSite.schedule=1 && PublishedSite.publish <= NOW() && PublishedSite.expire > NOW()))',
                            'Sitetemplate.id',
                            'Sitetemplate.filename',
                            'PublishedSite.cache',
                            'PublishedSite.cache_exparation',
                            'PublishedSitelanguage.id',
                            'PublishedSitelanguage.name',
                            'PublishedSitelanguage.title',
                            'PublishedSitelanguage.keywords',
                            'PublishedSitelanguage.description',
                            'PublishedSitelanguage.author',
                            'PublishedSitelanguage.home',
                            'PublishedSitelanguage.modified',
                            'PublishedSitelanguage.created',
                            'Language.id',
                            'Language.shortcut',
                            'Language.copies',
                            'Sitetype.site_class',
                            'Sitetype.sitelanguage_class',
                            'Sitetype.name',
                        )
                    )
                );
    		    if (true === $success) {
    		        break;
    		    } else {
    		        $live = false;
    		    }
            case false:
                $obj_site = new Wcms_Site();
                $obj_site->unbindModel('all');
                $obj_site->bindModel(
                    array(
                        'belongs_to' => array('Sitetemplate', 'Sitetype'),
                        'has_one' => array(
                            'Sitelanguage',
                            'Language' => array('join_condition' => 'Sitelanguage.language_id=Language.id')
                        )
                    )
                );
                $obj_site->setId($id);
                $success = $obj_site->read(
                    array(
                        'conditions' => array('Language.id' => $language_id),
                        'fields' => array(
                            'Site.id',
                            'Site.name',
                            'Site.parent_id',
                            'Sitetemplate.id',
                            'Sitetemplate.filename',
                            'Site.cache',
                            'Site.cache_exparation',
                            'Site.permalink',
                            'Site.status',
                            'Site.private',
                            'Site.schedule',
                            'Site.expire' => '(Site.schedule=0 || (Site.schedule=1 && Site.publish <= NOW() && Site.expire > NOW()))',
                            'Sitelanguage.id',
                            'Sitelanguage.name',
                            'Sitelanguage.title',
                            'Sitelanguage.keywords',
                            'Sitelanguage.description',
                            'Sitelanguage.author',
                            'Sitelanguage.home',
                            'Sitelanguage.modified',
                            'Sitelanguage.created',
                            'Sitelanguage.status',
                            'Language.id',
                            'Language.shortcut',
                            'Language.copies',
                            'Sitetype.site_class',
                            'Sitetype.sitelanguage_class',
                            'Sitetype.name',
                        )
                    )
                );
                // check if the user has got the permission to access the sitelanguage
                if (true === $success) {
                    if (true === $admin &&
                        false === $this->checkLanguageAccess($obj_site->Language->getId(), false)) {
                        $success = false;
                        $admin = false;
                        $_GET['preview'] = 1;
                    }
                }
                break;
    		}
    		++$count;

		    if (false === $success
                && true === (boolean) $languages[$language_id]['copies']
            ) {
                $language_id = $this->_setting->getLanguageId();
                $site_copies = true;
                // $language_code = $languages[$language_id]['shortcut'];
            }
		}
	    $sitelanguage = 'Sitelanguage';
        if (true === $live) {
            $admin = false;
            $sitelanguage = 'PublishedSitelanguage';
        }

        if (true === isset($_GET['preview']) && 1 == $_GET['preview']) {
            $admin = false;
        }

		if (true === $success) {
            $site = array();
            $site['title'] = h($obj_site->{$sitelanguage}->getTitle());
            $site['id'] = $obj_site->getId();
            $site['sitelanguage_id'] = $obj_site->{$sitelanguage}->getId();
            $site['name'] = $obj_site->{$sitelanguage}->getName();
            $site['keywords'] = $obj_site->{$sitelanguage}->getKeywords();
            $site['description'] = $obj_site->{$sitelanguage}->getDescription();
            $site['author'] = $obj_site->{$sitelanguage}->getAuthor();
            $site['created'] = $obj_site->{$sitelanguage}->getCreated();
            $site['modified'] = $obj_site->{$sitelanguage}->getModified();
            $site['language_code'] = $obj_site->Language->getShortcut();
            $site['language_id'] = $obj_site->Language->getId();
            $site['copies'] = $obj_site->Language->getCopies();

            $this->_triggerCallback(
                'beforeWebsiteRender',
                $obj_site->getId(),
                $obj_site->{$sitelanguage}->getId(),
                $language_id,
                $language_code,
                $obj_site->Sitetype,
                $live
            );

		    if (true === (boolean) $obj_site->Language->getCopies()) {
                $fake_language_id = $this->_setting->getLanguageId();
            } else {
                $fake_language_id = $obj_site->Language->getId();
            }

            // Get the sites for the navigations
            list($sites, $breadcrumb) = $this->_getSites(
                $obj_site->getId(),
                $fake_language_id,
                $language_code,
                $live
            );

            // get the site url
            list($site_url, $site_file) = $this->_getSiteUrl(
                $obj_site,
                $breadcrumb,
                $language_code,
                (boolean) $obj_site->{$sitelanguage}->getHome(),
                $live
            );

			// the news content
			$news = $this->_getNews($obj_site->getId(), $fake_language_id, $live);

			// the content of each area
			$areas = $this->_getAreas(
                $obj_site->getId(),
                $language_id,
                $language_code,
                $live,
                $admin
            );
			// get the html output of each content.
			$areas_code = array();
			foreach ($areas as $area => $components) {
				foreach ($components as $position => $component) {
					ob_start();
					include (
					   ASSETS . DS . 'wcms' . DS . 'component_templates'
					   . DS . $component['template_id'] . '.phtml'
					);
					$areas_code[$area][] = ob_get_clean();
				}
			}
			$areas = $areas_code;
			unset($areas_code);

			// If we are in admin mode then get the admin html code.
			list(
                $admin_code,
                $admin_css
            ) = $this->_getAdminCode(
                $obj_site->getId(),
                $obj_site,
                $language_id,
                $language_code,
                $languages,
                $site_file,
                $live
            );

			// Get the site template html code.
			ob_start();
			if ($obj_site->Sitetemplate->getId() > 0) {
			    $sitetype = $obj_site->Sitetype;

				ob_start();
				$filename = $obj_site->Sitetemplate->getFilename();

				include(ASSETS . DS . 'wcms' . DS . 'site_templates' . DS . $filename . '.phtml');
				$output =  ob_get_clean();

				$this->_triggerCallback(
                    'afterWebsiteRender',
                    $site['id'],
                    $site['sitelanguage_id'],
                    $site['language_id'],
                    $site['language_code'],
                    $sitetype,
                    $live
				);

				return $output;
			} else {
				throw new Ncw_Exception('No Sitetemplate');
			}
		} else if ($language_code !== $this->_setting->Language->getShortcut()) {
			$this->_redirect301(
                $this->base . '/' . $this->_setting->Language->getShortcut()
                . '/' . $site_url
            );
		}
		$this->_error404();
		return false;
	}

	/**
	 * Returns the admin code.
	 *
	 * @param int       $site_id
	 * @param Wcms_Site $site
	 * @param int       $language_id
	 * @param string    $language_code
	 * @param array     $languages
	 * @param string    $site_file
	 * @param boolean   $live
	 *
	 * @return string
	 */
	protected function _getAdminCode ($site_id, $site, $language_id, $language_code, $languages, $site_file, $live = false)
	{
	    $this->view = new Ncw_View($this);
        $this->html = new Ncw_Helpers_Html();
        $this->html->startup($this->view);

        $this->javascript = new Ncw_Helpers_Javascript();
        $this->javascript->startup($this->view);

        $asset = new Ncw_Helpers_Asset();
        $asset->startup($this->view);

        // admin css
        $this->registerCss(
          array(
              'wcms',
              'windows',
              'sites',
              'website_admin',
          )
        );

        $this->html->css('layout.less.css', null, null, false);

        $admin_css = $this->view->asset->css();

        /* $admin_css = '<link rel="stylesheet" type="text/css" href="' . $this->base . '/' . $this->theme_path . '/web/css/layout.less.css" media="screen" />'
          . '<link rel="stylesheet" type="text/css" href="' . $this->base . '/' . $this->theme_path . '/web/css/dateTimePicker.css" media="screen" />'
          . '<link rel="stylesheet" type="text/css" href="' . $this->base . '/' . MODULES . '/wcms/web/css/wcms.css" media="screen" />'
          . '<link rel="stylesheet" type="text/css" href="' . $this->base . '/' . MODULES . '/wcms/web/css/windows.css" media="screen" />'
          . '<link rel="stylesheet" type="text/css" href="' . $this->base . '/' . MODULES . '/wcms/web/css/sites.css" media="screen" /'
          . '<link rel="stylesheet" type="text/css" href="' . $this->base . '/' . MODULES . '/wcms/web/css/website_admin.css" media="screen" />'; */


        // admin footer
		ob_start();
		print $this->_createMenu($site, $language_code, $languages, $site_file, $live);
		print '<br class="ncw-clear" /><br /><br /><br />';

		print $this->view->element('javascript', false);

		print '
        <script type="text/javascript" src="' . $this->base . '/' . THEMES . '/default/web/javascript/lib/jquery-ui-1.8.2.custom.min.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/lib/jstree/jquery.tree.min.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/lib/jstree/plugins/jquery.tree.contextmenu.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/lib/jstree/plugins/jquery.tree.cookie.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/lib/tiny_mce/tiny_mce_gzip.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/locale/' . Ncw_Configure::read('App.language') . '/LC_MESSAGES/default.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/lib/jquery.windows-engine.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/files/web/javascript/lib/uploadify/swfobject"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/files/web/javascript/lib/uploadify/jquery.uploadify.v2.1.0.min "></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/files/web/javascript/ncw.files.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.tinymce.gzip.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.site.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.site.tree.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.sitelanguage.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.component.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.componentlanguage.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.tinymce.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/files/web/javascript/ncw.files.file.js"></script>
        <script type="text/javascript" src="' . $this->base . '/modules/files/web/javascript/ncw.files.folder.js"></script>
		<script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.website_admin.js"></script>
		<script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.website_admin.windows.js"></script>';

		print '<script type="text/javascript">'
		. 'ncw.wcms.website_admin.siteId = ' . $site_id . '; '
		. 'ncw.wcms.website_admin.languageId = ' . $language_id . '; '
		. 'ncw.wcms.website_admin.languageCode = "' . $language_code . '"; '
		. 'ncw.wcms.website_admin.adminMode = 1; '
		. 'ncw.wcms.website_admin.PERMISSIONS = {}; '
		. 'ncw.wcms.website_admin.PERMISSIONS.Site = {}; '
		. 'ncw.wcms.website_admin.PERMISSIONS.Component = {}; '
		. 'ncw.wcms.website_admin.PERMISSIONS.Component.edit = ' . ($this->acl->check('/wcms/component/edit') ? 'true' : 'false') . '; '
        . 'ncw.wcms.website_admin.PERMISSIONS.Component.addNew = ' . ($this->acl->check('/wcms/component/new') ? 'true' : 'false') . '; '
        . 'ncw.wcms.website_admin.PERMISSIONS.Componentfile = {}; '
        . 'ncw.wcms.website_admin.PERMISSIONS.Componentfile.editMeta = ' . ($this->acl->check('/wcms/componentfile/editMeta') ? 'true' : 'false') . '; '
	    . '</script>';

	    print '<script type="text/javascript" src="' . $this->base . '/modules/wcms/web/javascript/ncw.wcms.website_admin.tinymce.js"></script>';

	    include_once $this->view->partial('footer', false);
	    print $this->view->element('container', false);
	    print $this->view->element('debug', false);

        print '<script type="text/javascript">
            $("#ncw-loading-overlay").height($(document).height());
        </script>';

		$admin_code = ob_get_clean();

		unset($this->html, $this->javascript, $this->view);

        return array($admin_code, $admin_css);
	}

	/**
	 * Creates the menu.
	 *
	 * @param Wcms_Site $site          the site
	 * @param string    $language_code the language code
	 * @param array     $languages     the languages
	 * @param array     $site_file     the site file
	 * @param boolean   $live          true if the life version must be displayed
	 *
	 * @return void
	 */
	protected function _createMenu ($site, $language_code, $languages, $site_file, $live = false)
	{
	    $html = '';

	    $preview = false;
        if (true === isset($_GET['preview'])) {
           $preview = (boolean) $_GET['preview'];
        }

        if (true === $live) {
            $sitelanguage_status = $site_status = 'published';
            $is_home = (boolean) $site->PublishedSitelanguage->getHome();
        } else {
            $site_status = $site->getStatus();
            $sitelanguage_status = $site->Sitelanguage->getStatus();
            $is_home = (boolean) $site->Sitelanguage->getHome();
        }

        $schedule_status = false;
        if (true === (boolean) $site->getSchedule()
            && true === (boolean) $site->getExpire()
        ) {
            $schedule_status = 'Scheduled';
        } else if (false === (boolean) $site->getExpire()) {
            $schedule_status = 'Expired';
        }

        $sitetype_name = $site->Sitetype->getName();

        $html .= '
          <div id="ncw-editbar">
            <div id="ncw-wrap">
              <span id="ncw-editbar-sitename">Site: <div title="' . $site->getName() . '">' . $site->getName() . '</div></span>
              <ul>';

        if (count($languages) > 1) {
            $html .=  '<li class="ncw-editmenu-node">
                    <ul id="ncw-editmenu-languages">
                      <li class="ncw-editmenu-languages">
                        <a herf="#" title="" id="ncw_editmenu-languages-link">
                            <img src="' . $this->base . '/' . $this->theme_path . '/web/images/country_flags/' . $language_code . '.gif" />
                        </a>
                      </li>
                      <ul id="ncw-edit-languages-menu">
                      ';

                        foreach ($languages as $language) {
                            $html .=  '<li>
                                  <a href="' . $this->base . '/' . $language['shortcut'] . '/' . $site_file . '">'
                                    . $this->html->image('country_flags/' . $language['shortcut'] . '.gif', false) . ' ' . $language['name'] . '</a>
                                </li>';
                        }
               $html .=  '</ul>
                    </ul>
                </li>';
            }

        $html .= '
                <li class="ncw-editmenu-node">
                  <div id="ncw-editbar-type-' . strtolower($sitetype_name) . '" title="' . T_('Site type') . ': ' . T_($sitetype_name) . '">' . T_($sitetype_name) . '</div>
                </li>
                <li class="ncw-editmenu-node">
                  <div id="ncw-editbar-status-' . $sitelanguage_status . '" title="' . T_('Site status') . ': ' . ucfirst(T_($sitelanguage_status)) . '">' . ucfirst(T_($sitelanguage_status)) . '</div>
                </li>';

	    if (false !== $is_home) {
            $html .= '<li class="ncw-editmenu-node">
                      <div id="ncw-editbar-home" title="' . T_('Home site') . '"></div>
                    </li>';
        }

	    if (true === (boolean) $site->getPrivate()) {
            $html .= '<li class="ncw-editmenu-node">
                      <div id="ncw-editbar-private" title="' . T_('Is private') . '"></div>
                    </li>';
        }

        if (false !== $schedule_status) {
            $html .= '<li class="ncw-editmenu-node">
                      <div id="' . ($schedule_status == 'Scheduled' ? 'ncw-editbar-visible"' : 'ncw-editbar-invisible') . '" title="' . T_('Site schedule status is') . ': ' . T_($schedule_status) . '"></div>
                    </li>';
        }

	    if (true === Ncw_Configure::read('App.rewrite')) {
            $url = $this->base . '/' . $this->_requested_site . '?';
        } else {
            $url = $this->base . '/index.php?url=' . $this->_requested_site . '&amp;';
        }

        if ($site_status !== 'unpublished'
            && $site_status !== 'new'
            && $sitelanguage_status != 'unpublished'
            && $sitelanguage_status != 'new'
            && $schedule_status !== 'Expired'
        ) {
            $html .= '<li class="ncw-editmenu-node">
                    <a href="' . $url . 'live=1"' . ($live && !$preview ? ' class="ncw-editmenu-node-clicked"' : '') . ' title="' . T_('Show site in live mode') . '" id="ncw-editbar-live_mode">Live Mode</a>
                </li>';
        } else {
            $html .= '<li class="ncw-editmenu-node">
                    <div id="ncw-editbar-live_mode" class="ncw-editmenu-node-deactiv" title="' . T_('You cannot show this site in live mode') . '">' . T_('Live Mode') . '</div>
                </li>';
        }

        $html .= '<li class="ncw-editmenu-node">
                  <a href="' . $url . 'preview=1"' . (!$live && $preview ? ' class="ncw-editmenu-node-clicked"' : '') . ' title="' . T_('Show site in preview mode') . '" id="ncw-editbar-preview_mode">' . T_('Preview Mode') . '</a>
                </li>
                <li class="ncw-editmenu-node">
                  <a href="' . $url . 'preview=0&amp;live=0"' . (!$live && !$preview ? ' class="ncw-editmenu-node-clicked"' : '') . ' title="' . T_('Show site in edit mode') . '" id="ncw-editbar-edit_mode">' . T_('Edit Mode') . '</a>
                </li>';

        if (true === $this->acl->check('/wcms')) {
            $html .= '
                    <li class="ncw-editmenu-node">
                      <a href="#" title="' . T_('Opens the site window') . '" id="ncw-editbar-edit-content_mode">' . T_('Edit') . '</a>
                    </li>';
        }
        if (true === $this->acl->check('/wcms/site/publish')) {
            $html .= '
                    <li class="ncw-editmenu-node">
                      <a href="#" rel="site/' . $site->getId() . '" title="' . T_('Publish the site') . '" id="ncw-editbar-publish" class="ncw-publish-trigger">' . T_('Publish') . '</a>
                    </li>';
        }
        /*if (true === $this->acl->check('/wcms/site/unpublish')
            && ($sitelanguage_status == 'published'
            || $sitelanguage_status == 'modified')
        ) {
            print '
                    <li class="ncw-editmenu-node">
                      <a href="#" rel="site/' . $site->getId() . '" title="' . T_('Unpublish the site') . '" id="ncw-editbar-unpublish" class="ncw-unpublish-trigger">' . T_('Unpublish') . '</a>
                    </li>';
        }*/

        $html .= '
                <!-- <li class="ncw-editmenu-node">
                  <a href="#" title="' . T_('Refresh the site') . '" id="ncw-editbar-refresh" onclick="window.location.reload()">' . T_('Refresh') . '</a>
                </li> -->
              </ul>';


        $html .= '
              <ul id="ncw-editmenu-more">
                <li class="ncw-editmenu-node">
                  <a herf="#" title="" id="ncw_editmenu-more-link">' . T_('More') . '</a>
                </li>
                <ul id="ncw-edit-more-menu" class="ncw-sub-menu">';

        if (true === $this->acl->check('/wcms/site/new')) {
            $html .= '
                      <li>
                        <a href="#" rel="site/' . $site->getId() . '" title="' . T_('Copy the site') . '" class="ncw-copy-trigger">' . T_('Copy') . '</a>
                      </li>';
	    }

        if (true === $this->acl->check('/wcms/site/delete')) {
            $html .= '
                  <li>
                    <a href="#" rel="wcms/site/' . $site->getId() . '" title="' . T_('Delete the site') . '" class="ncw-delete-trigger-ajax">' . T_('Delete') . '</a>
                  </li>';
        }

	    if (true === $this->acl->check('/wcms/site/unpublish')
            && ($sitelanguage_status == 'published'
            || $sitelanguage_status == 'modified')
        ) {
            $html .= '
                  <li>
                    <a href="#" rel="site/' . $site->getId() . '" title="' . T_('Unpublish the site') . '" class="ncw-unpublish-trigger">' . T_('Unpublish') . '</a>
                  </li>';
        }

	    $html .= '
                </ul>
              </ul>';

	    $html .= '
            </div>
        </div>';

        // application permissions
	    $this->layout = 'default';
	    parent::beforeRender();
	    $this->applications = $this->view->applications;

        $this->view->menu[1] = '<a id="ncw-window-site-structure" href="#">' . T_('Site Structure') . '</a>';
        $this->view->menu[2] = '<a id="ncw-window-files" href="#">' . T_('Files') . '</a>';
        $this->menu = $this->view->menu;

        if (true === $this->acl->check('/wcms/other')) {
            $this->extras_menu = $this->view->extras_menu;
        }

        return $html;
	}

	/**
	 * Error 403 Action
	 *
	 */
	protected function _error403 ()
	{
		$this->view = false;
		$header = new Ncw_Components_Header();
		$header = $header->object;
		$header->sendStatusCode(403);
		$header->setHeader('Connection', 'close');
		list($sites) = $this->_getSites(
		  1,
		  $this->_setting->getLanguageId(),
		  $this->_setting->Language->getShortcut()
		);
		require_once ('assets/wcms/errors/403.phtml');
		$this->_stop();
	}

    /**
     * Error 404 Action
     *
     */
    protected function _error404 ()
    {
        $this->view = false;
        $header = new Ncw_Components_Header();
        $header = $header->object;
        $header->sendStatusCode(404);
        $header->setHeader('Connection', 'close');
        list($sites) = $this->_getSites(
            1,
            $this->_setting->getLanguageId(),
            $this->_setting->Language->getShortcut()
        );
        require_once ('assets/wcms/errors/404.phtml');
        $this->_stop();
    }

	/**
	 * Makes a 301 permanently moved redirect
	 *
	 * @param string $url
	 */
	protected function _redirect301 ($url)
	{
		$header = new Ncw_Components_Header();
		$header = $header->object;
		$header->sendStatusCode(301);
		$header->redirect($url);
		$this->_stop();
	}

	/**
	 * Search
	 *
	 */
	protected function _search ()
	{
        $search_string = '';
	    if (true === isset($_POST['Search']['q'])
           && false === empty($_POST['Search']['q'])
        ) {
            $search_string = $_POST['Search']['q'];
        } else if (true === isset($this->params['url']['q'])
           && false === empty($this->params['url']['q'])
        ) {
            $search_string = $this->params['url']['q'];
        }

        if (true === isset($_POST['Search']['l'])
            && false === empty($_POST['Search']['l'])
        ) {
            $language_id = $_POST['Search']['l'];
        } else if (true === isset($this->params['url']['l'])
           && false === empty($this->params['url']['l'])
        ) {
            $language_id = $this->params['url']['l'];
        } else if ($this->_current_language_id > 0) {
            $language_id = $this->_current_language_id;
        } else {
            $language_id = $this->_setting->getLanguageId();
        }
        $results = array();
        if (false === empty($search_string)
        ) {
            // if the user is logged in.
            if (true === $this->_logged_in = $this->checkLogin(false)) {
                $this->acl->read($this->_current_user['id'], '');
            }

            $this->_current_search_string = h($search_string);
            $language_id = (int) $language_id;

            $language = new Wcms_Language();
            $language->setId($language_id);
            $language->read(array('fields' => array('Language.shortcut')));
            $language_code = $language->getShortcut();

            $this->_searchInSiteProperties($language_id, $language_code, $results);
            if (count($results) < 10) {
                $this->_searchInshorttexts($language_id, $language_code, $results);
            }
            if (count($results) < 10) {
                $this->_searchInTexts($language_id, $language_code, $results);
            }
            if (count($results) < 10) {
                $this->_searchInSitetypes($language_id, $language_code, $results);
            }

            // highlich the search string in the content and truncate the content
            // to 160 chars.
            foreach ($results as &$result) {
                $result['name'] = $this->_text->highlight(
                   $result['name'],
                   $this->_current_search_string
                );
                $result['content'] = $this->_text->highlight(
                   $this->_text->truncate(
                       Ncw_Library_Sanitizer::html($result['content'], true),
                       160,
                       '...'
                   ),
                   $this->_current_search_string
                );
                $result['content'] = preg_replace('/([a-z]{1})([A-Z]{1})/', '$1. $2', $result['content']);
            }
        }
        $this->_current_search_results = $results;
	}

	/**
	 * Check if is searched
	 *
	 * @return boolean
	 */
	protected function _isSearched ()
	{
	    if ((true === isset($_POST['Search']['q'])
           && false === empty($_POST['Search']['q']))
           || (true === isset($this->params['url']['q'])
           && false === empty($this->params['url']['q']))
        ) {
            return true;
        }
        return false;
	}

    /**
     * Check if is searched
     *
     * @return boolean
     */
    protected function _getSearchResult ()
    {
        if (false === $this->_searchHasResult()) {
            $this->_search();
        }
        return $this->_current_search_results;
    }

    /**
     * Check if is searched
     *
     * @return boolean
     */
    protected function _searchHasResult ()
    {
        if (false === empty($this->_current_search_results)) {
            return true;
        }
        return false;
    }

    /**
     * Check if is searched
     *
     * @return boolean
     */
    protected function _getSearchTerm ()
    {
        $search_string = '';
        if (true === isset($_POST['Search']['q'])
           && false === empty($_POST['Search']['q'])
        ) {
            $search_string = $_POST['Search']['q'];
        } else if (true === isset($this->params['url']['q'])
           && false === empty($this->params['url']['q'])
        ) {
            $search_string = $this->params['url']['q'];
        }
        return $search_string;
    }

    /**
     * Searches in the text fields
     *
     * @param int    $language_id   the language id
     * @param string $language_code the language code
     * @param Array  $results       the results array
     *
     * @return void
     */
    protected function _searchInTexts ($language_id, $language_code, &$results)
    {
        $site = new Wcms_PublishedSite();
        // search in texts
        $stmt = $this->_db->prepare(
           'SELECT Site.id, Site.parent_id, Site.name, Site.permalink, Sitelanguage.title, '
           . 'Sitelanguage.description, Site.private '
           . 'FROM ' . Ncw_Database::getConfig('prefix') . 'wcms_published_componenttext AS Componenttext '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_componentlanguage AS Componentlanguage '
           . 'ON Componenttext.componentlanguage_id=Componentlanguage.id '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_component AS Component '
           . 'ON Componentlanguage.component_id=Component.id '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_site AS Site '
           . 'ON Component.site_id=Site.id '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_sitelanguage AS Sitelanguage '
           . 'ON Site.id=Sitelanguage.site_id '
           . 'WHERE MATCH (Componenttext.content) AGAINST (:search_string) '
           . '&& Sitelanguage.language_id=:language_id '
           . 'LIMIT 10'
        );
        $stmt->bindValue(':search_string', $this->_current_search_string, PDO::PARAM_STR);
        $stmt->bindValue(':language_id', $language_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if (count($results) > 10) {
                break;
            }
            // if the site is set to private
            // check if the user has got the permission.
            if (true === (boolean) $row['private']) {
                $access = false;
                if (   true === $this->_logged_in
                    && true === $this->acl instanceof Ncw_Components_Acl
                    && true === $this->acl->check('/wcms/sites/' . $row['id'])
                ) {
                    $access = true;
                }
                if (false === $access) {
                    continue;
                }
            }

            $site->setId($row['id'], false);
            $site->setParentId($row['parent_id'], false);
            $site->setName($row['name'], false);
            $site->setPermalink($row['permalink'], false);

            if (false === empty($row['description'])) {
                $row['content'] = $row['description'];
            }  else {
                $row['content'] = $this->_readSiteContent($row['id'], $language_id, $language_code);
            }

            $results[$row['id']] = array(
                'url' => $this->makeUrlForWebsite(
                    $site,
                    false,
                    $language_code
                ),
                'name' => $row['title'],
                'content' => $row['content']
            );
        }
    }

    /**
     * Searches in shorttext fields
     *
     * @param int    $language_id   the language id
     * @param string $language_code the language code
     * @param Array  $results       the results array.
     *
     * @return void
     */
    protected function _searchInShorttexts ($language_id, $language_code, &$results)
    {
        $site = new Wcms_PublishedSite();
        // search in shorttexts
        $stmt = $this->_db->prepare(
           'SELECT Site.id, Site.parent_id, Site.name, Site.permalink, Sitelanguage.title, '
           . 'Sitelanguage.description, Site.private '
           . 'FROM ' . Ncw_Database::getConfig('prefix') . 'wcms_published_componentshorttext AS Componentshorttext '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_componentlanguage AS Componentlanguage '
           . 'ON Componentshorttext.componentlanguage_id=Componentlanguage.id '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_component AS Component '
           . 'ON Componentlanguage.component_id=Component.id '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_site AS Site '
           . 'ON Component.site_id=Site.id '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_sitelanguage AS Sitelanguage '
           . 'ON Site.id=Sitelanguage.site_id '
           . 'WHERE MATCH (Componentshorttext.content) AGAINST (:search_string) '
           . '&& Sitelanguage.language_id=:language_id '
           . 'LIMIT 10'
        );
        $stmt->bindValue(':search_string', $this->_current_search_string, PDO::PARAM_STR);
        $stmt->bindValue(':language_id', $language_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if (count($results) > 10) {
                break;
            }
            if (true === isset($results[$row['id']])) {
                continue;
            }
            // if the site is set to private
            // check if the user has got the permission.
            if (true === (boolean) $row['private']) {
                $access = false;
                if (   true === $this->_logged_in
                    && true === $this->acl instanceof Ncw_Components_Acl
                    && true === $this->acl->check('/wcms/sites/' . $row['id'])
                ) {
                    $access = true;
                }
                if (false === $access) {
                    continue;
                }
            }

            $site->setId($row['id'], false);
            $site->setParentId($row['parent_id'], false);
            $site->setName($row['name'], false);
            $site->setPermalink($row['permalink'], false);

            if (false === empty($row['description'])) {
                $row['content'] = $row['description'];
            }  else {
                $row['content'] = $this->_readSiteContent($row['id'], $language_id, $language_code);
            }

            $results[$row['id']] = array(
                'url' => $this->makeUrlForWebsite(
                    $site,
                    false,
                    $language_code
                ),
                'name' => $row['title'],
                'content' => $row['content']
            );
        }
    }

    /**
     * Searches in site properties
     *
     * @param int    $language_id   the language id
     * @param string $language_code the language code
     * @param Array  $results       the results array.
     *
     * @return void
     */
    protected function _searchInSiteProperties ($language_id, $language_code, &$results)
    {
        $site = new Wcms_PublishedSite();
        // search in site properties
        $stmt = $this->_db->prepare(
           'SELECT Site.id, Site.parent_id, Site.name, Site.permalink, Sitelanguage.title, '
           . 'Sitelanguage.description, Site.private '
           . 'FROM ' . Ncw_Database::getConfig('prefix') . 'wcms_published_site AS Site '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_sitelanguage AS Sitelanguage '
           . 'ON Site.id=Sitelanguage.site_id '
           . 'WHERE (Site.name LIKE :search_string '
           . '|| Sitelanguage.name LIKE :search_string '
           . '|| Sitelanguage.title LIKE :search_string '
           . '|| Sitelanguage.description LIKE :search_string '
           . '|| Sitelanguage.keywords LIKE :search_string) '
           . '&& Sitelanguage.language_id=:language_id '
           . 'LIMIT 10'
        );
        $stmt->bindValue(':search_string', '%' . $this->_current_search_string . '%', PDO::PARAM_STR);
        $stmt->bindValue(':language_id', $language_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if (count($results) > 10) {
                break;
            }
            if (true === isset($results[$row['id']])) {
                continue;
            }
            // if the site is set to private
            // check if the user has got the permission.
            if (true === (boolean) $row['private']) {
                $access = false;
                if (   true === $this->_logged_in
                    && true === $this->acl instanceof Ncw_Components_Acl
                    && true === $this->acl->check('/wcms/sites/' . $row['id'])
                ) {
                    $access = true;
                }
                if (false === $access) {
                    continue;
                }
            }

            $site->setId($row['id'], false);
            $site->setParentId($row['parent_id'], false);
            $site->setName($row['name'], false);
            $site->setPermalink($row['permalink'], false);

            if (false === empty($row['description'])) {
                $row['content'] = $row['description'];
            }  else {
                $row['content'] = $this->_readSiteContent($row['id'], $language_id, $language_code);
            }

            $results[$row['id']] = array(
                'url' => $this->makeUrlForWebsite(
                    $site,
                    false,
                    $language_code
                ),
                'name' => $row['title'],
                'content' => $row['content']
            );
        }
    }

    /**
     * Search in site types
     *
     * @param int    $language_id   the language id
     * @param string $language_code the language code
     * @param array  $results       the search results
     */
    protected function _searchInSitetypes ($language_id, $language_code, &$results)
    {
        $site = new Wcms_PublishedSite();
        $sitetype = new Wcms_Sitetype();
        $sitetypes = $sitetype->fetch('all');
        foreach ($sitetypes as $sitetype) {
            $site_class = $sitetype->getSiteClass();
            if (false === empty($site_class)) {
                $site_class .= 'site';
                $stmt = $this->_db->prepare('EXPLAIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_' . $site_class);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($rows) > 0) {
                    $conditions = array();
                    foreach ($rows as $row) {
                        $conditions[] = 'Sitetype.' . $row['Field'] . ' LIKE :search_string';
                    }
                    $conditions = implode(' || ', $conditions);

                    $stmt = $this->_db->prepare(
                       'SELECT Site.id, Site.parent_id, Site.name, Site.permalink, Sitelanguage.title, '
                       . 'Sitelanguage.description, Site.private '
                       . 'FROM ' . Ncw_Database::getConfig('prefix') . 'wcms_published_' . $site_class . ' AS Sitetype '
                       . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_site AS Site '
                       . 'ON Sitetype.site_id=Site.id '
                       . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_sitelanguage AS Sitelanguage '
                       . 'ON Site.id=Sitelanguage.site_id '
                       . 'WHERE (' . $conditions . ') '
                       . '&& Sitelanguage.language_id=:language_id '
                       . 'LIMIT 10'
                    );
                    $stmt->bindValue(':search_string', '%' . $this->_current_search_string . '%', PDO::PARAM_STR);
                    $stmt->bindValue(':language_id', $language_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        if (count($results) > 10) {
                            break;
                        }
                        if (true === isset($results[$row['id']])) {
                            continue;
                        }
                        // if the site is set to private
                        // check if the user has got the permission.
                        if (true === (boolean) $row['private']) {
                            $access = false;
                            if (   true === $this->_logged_in
                                && true === $this->acl instanceof Ncw_Components_Acl
                                && true === $this->acl->check('/wcms/sites/' . $row['id'])
                            ) {
                                $access = true;
                            }
                            if (false === $access) {
                                continue;
                            }
                        }

                        $site->setId($row['id'], false);
                        $site->setParentId($row['parent_id'], false);
                        $site->setName($row['name'], false);
                        $site->setPermalink($row['permalink'], false);

                        if (false === empty($row['description'])) {
                            $row['content'] = $row['description'];
                        }  else {
                            $row['content'] = $this->_readSiteContent($row['id'], $language_id, $language_code);
                        }

                        $results[$row['id']] = array(
                            'url' => $this->makeUrlForWebsite(
                                $site,
                                false,
                                $language_code
                            ),
                            'name' => $row['title'],
                            'content' => $row['content']
                        );
                    }
                }
            }

            $sitelanguage_class = $sitetype->getSitelanguageClass();
            if (false === empty($sitelanguage_class)) {
                $sitelanguage_class .= 'sitelanguage';
                $stmt = $this->_db->prepare('EXPLAIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_' . $sitelanguage_class);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($rows) > 0) {
                    $conditions = array();
                    foreach ($rows as $row) {
                        $conditions[] = 'Sitetype.' . $row['Field'] . ' LIKE :search_string';
                    }
                    $conditions = implode(' || ', $conditions);

                    $stmt = $this->_db->prepare(
                       'SELECT Site.id, Site.parent_id, Site.name, Site.permalink, Sitelanguage.title, '
                       . 'Sitelanguage.description, Site.private '
                       . 'FROM ' . Ncw_Database::getConfig('prefix') . 'wcms_published_' . $sitelanguage_class . ' AS Sitetype '
                       . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_sitelanguage AS Sitelanguage '
                       . 'ON Sitetype.sitelanguage_id=Sitelanguage.id '
                       . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_published_site AS Site '
                       . 'ON Sitelanguage.site_id=Site.id '
                       . 'WHERE (' . $conditions . ') '
                       . '&& Sitelanguage.language_id=:language_id '
                       . 'LIMIT 10'
                    );
                    $stmt->bindValue(':search_string', '%' . $this->_current_search_string . '%', PDO::PARAM_STR);
                    $stmt->bindValue(':language_id', $language_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        if (count($results) > 10) {
                            break;
                        }
                        if (true === isset($results[$row['id']])) {
                            continue;
                        }
                        // if the site is set to private
                        // check if the user has got the permission.
                        if (true === (boolean) $row['private']) {
                            $access = false;
                            if (   true === $this->_logged_in
                                && true === $this->acl instanceof Ncw_Components_Acl
                                && true === $this->acl->check('/wcms/sites/' . $row['id'])
                            ) {
                                $access = true;
                            }
                            if (false === $access) {
                                continue;
                            }
                        }

                        $site->setId($row['id'], false);
                        $site->setParentId($row['parent_id'], false);
                        $site->setName($row['name'], false);
                        $site->setPermalink($row['permalink'], false);

                        if (false === empty($row['description'])) {
                            $row['content'] = $row['description'];
                        }  else {
                            $row['content'] = $this->_readSiteContent($row['id'], $language_id, $language_code);
                        }

                        $results[$row['id']] = array(
                            'url' => $this->makeUrlForWebsite(
                                $site,
                                false,
                                $language_code
                            ),
                            'name' => $row['title'],
                            'content' => $row['content']
                        );
                    }
                }
            }
        }
    }

    /**
     * Reads the site content.
     *
     * @param int $site_id          the site id
     * @param int $language_id      the language id
     * @param string $language_code the language code
     *
     * @return string
     */
    protected function _readSiteContent ($site_id, $language_id, $language_code)
    {
        $content = '';
        $areas = $this->_getAreas($site_id, $language_id, $language_code);
        foreach ($areas as $area) {
            if (strlen($content) > 200) {
                break;
            }
            foreach ($area as $component) {
                if (true === isset($component['shorttext'])) {
                    foreach ($component['shorttext'] as $shorttext) {
                         $content .= $shorttext['content'] . ' ';
                    }
                }
                if (true === isset($component['text'])) {
                    foreach ($component['text'] as $text) {
                         $content .= $text['content'] . ' ';
                    }
                }
            }
        }
        return $content;
    }
}
?>
