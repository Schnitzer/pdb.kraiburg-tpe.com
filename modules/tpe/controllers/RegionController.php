<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Region class.
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
 * RegionController class.
 *
 * @package netzcraftwerk
 */
class Tpe_RegionController extends Tpe_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "TPE :: Regions";

	/**
	 * ACL publics
	 *
	 * @var array
	 */
	public $acl_publics = array('ajaxCountries');

	/**
	 * show all Regions
	 *
	 */
	public function allAction ()
	{
		$arr_all_regions = $this->Region->fetch(
            'all',
            array('order' => array('Region.name'))
        );
		$this->view->arr_all_regions = $arr_all_regions;
	}

	/**
	 * new Region
	 *
	 */
	public function newAction ()
	{

		if (true === isset($this->data['Region'])) {
			$this->Region->data($this->data['Region']);
			if (true === $this->Region->save()) {
				$this->redirect(array("action" => "all"));
			}
		}
	}

	/**
	 * edit a Region
	 *
	 */
	public function editAction ($id)
	{
        $this->registerJS(
            array(
                'ncw.tpe.regions',
            )
        );

		$region_country = new Tpe_RegionCountry();
		$region_country->unbindModel(
            array('has_many' => array('Region', 'Contact_Country'))
        );
		$this->Region->setId($id);
		if (true === isset($this->data['Region'])) {
			$this->Region->data($this->data['Region']);
			$this->Region->save();
		} else {
			$this->Region->read();
			$this->data['Region'] = $this->Region->data();
		}

		$this->view->region_id = $id;

		$obj_site = new Wcms_Site();
		$obj_site->unbindModel('all');
		$this->view->arr_options = $obj_site->fetch(
            'all',
            array(
                'fields' => array(
                    'Site.id',
                    'Site.name'
                ),
                'conditions' => array('Site.id != 1')
            )
        );
		$this->view->site_id = $this->Region->getSiteId();

		$arr_countries_id = $region_country->fetch(
            'all',
            array(
                'fields' => array('RegionCountry.country_id'),
                'conditions' => array('RegionCountry.region_id' => $id)
            )
        );
		if (count($arr_countries_id) > 0) {
			$arr_conditions = array();
			foreach ($arr_countries_id as $country) {
				$arr_conditions[] = 'Country.id = ' . $country->getCountryId();
			}
			$conditions = implode(' || ', $arr_conditions);
			// new Contats_group object
			$obj_contry = new Contacts_Country();
			$this->view->arr_all_countries = $obj_contry->fetch(
                'all',
                array('conditions' => array($conditions))
            );
		} else {
			$this->view->arr_all_countries = array();
		}

        $region_country = new Tpe_RegionCountry();
        $region_country->unbindModel(array('has_many' => array('Region', 'Contact_Country')));
        $arr_countries = $region_country->fetch('RegionCountry.country_id');
        $str_conditions = '';
        $ct = 0;

        $obj_country = new Contacts_Country();
        if (true === isset($arr_countries[0])) {
            foreach ($arr_countries as $country) {
                if ($ct == 0) {
                    $str_conditions.= " Country.id !='" . $country->getCountryId() . "'";
                    $ct++;
                } else {
                    $str_conditions.= " && Country.id !='" . $country->getCountryId() . "'";
                }
            }
            // read group id and name
            $arr_options = $obj_country->fetch(
                'list',
                array(
                    'fields' => array('Country.name', 'Country.id'),
                    'conditions' => array($str_conditions), 'order' => array('Country.name'))
            );
        } else {
            $arr_options = $obj_country->fetch(
                'list',
                array(
                    'fields' => array('Country.name', 'Country.id'),
                    'order' => array('Country.name')
                )
            );
        }
        $this->view->arr_country_options = $arr_options;
	}

	/**
	 * Delete Region
	 *
	 */
	public function deleteAction ($id)
	{
		if ($id > 0) {
			$this->Region->setId($id);
			$this->Region->delete();
		}
		$this->view = false;
		$this->redirect(array('action' => 'all'));
	}

	/**
	 * add a country to region
	 *
	 * @param int $id         the region id
	 * @param int $country_id the country id
	 *
	 * @return void
	 */
	public function addCountryAction ($id, $country_id)
	{
	    $this->view = false;

	    $region_country = new Tpe_RegionCountry();
		$region_country->setRegionId($id);
		$region_country->setCountryId($country_id);
		$region_country->save();

        $country = new Contacts_Country();
        $country->setId($country_id);
        $name = $country->readField('name');

        print '{"return_value" : true, "country" : { "name" : "' . $name . '" } }';
	}

	/**
	 * delete a country from region
	 *
	 * @param int $id the region id
	 * @param int $country_id the country id
	 *
	 * @return void
	 */
	public function removeCountryAction ($id, $country_id)
	{
		$this->view = false;

		$obj_region_country = new Tpe_RegionCountry();
		$region_country = $obj_region_country->findBy(
             'country_id',
		     $country_id,
            array(
                'fields' => array('RegionCountry.id'),
                'conditions' => array('RegionCountry.region_id' => $id)
            )
		);
		$obj_region_country->setId($region_country->getId());
		$obj_region_country->delete();

        print '{"return_value" : true}';
	}

	/**
	 * Returns the countries of the given region
	 *
	 * @param int    $id            the region id
	 * @param string $language_code the language_code
	 *
	 * @return void
	 */
	public function ajaxCountriesAction($id, $language_code)
	{
		$this->view = false;
		if ($id > 0) {
            $urls = array();
			$region_country = new Tpe_RegionCountry();
			$arr_countries_id = $region_country->fetch('all', array('fields' => array('RegionCountry.country_id'), 'conditions' => array('RegionCountry.region_id' => $id)));
			if (count($arr_countries_id) > 0) {
				$arr_conditions = array();
				foreach ($arr_countries_id as $country) {
					$arr_conditions[] = 'Country.id = ' . $country->getCountryId();
				}
				$conditions = implode(' || ', $arr_conditions);
				// new Contats_group object
				$obj_contry = new Contacts_Country();
				$obj_contry->unbindModel('all');
                $countries = $obj_contry->fetch('all', array('conditions' => array($conditions), 'fields' => array('Country.id', 'Country.name', 'Country.code')));

                require_once 'I18Nv2/I18Nv2.php';
                require_once 'I18Nv2/Country.php';
                if ($language_code == 'jp') {
                    $tmp_language_code = 'ja';
                } else if ($language_code == 'kr') {
                    $tmp_language_code = 'ko';
                } else {
                    $tmp_language_code = $language_code;
                }
                $I18Nv2_country = new I18Nv2_Country($tmp_language_code, 'utf-8');

                $arr_countries = array();
                foreach ($countries as $country) {
                    $translated_country_name = $I18Nv2_country->getName(strtolower($country->getCode()));
                    if (false === empty($translated_country_name)) {
                        $arr_countries[$country->getId()] = $translated_country_name;
                    } else {
                        $arr_countries[$country->getId()] = $country->getName();
                    }
                }

				$arr_result = array();
				foreach ($arr_countries as $country_id => $country_name) {
					$obj_countrysite = new Tpe_Countrysite();
					if ($countrysite = $obj_countrysite->findBy('country_id', $country_id)) {
						$siteid = $countrysite->getSiteId();
					} else {
						$this->Region->setId($id);
						$this->Region->read();
						$siteid = $this->Region->getSiteId();
					}
					if (false === isset($urls[$siteid])) {
    					$site = new Wcms_PublishedSite();
    					$site->setId($siteid);
    					$site->unbindModel('all');
    					$site->read();
    					$urls[$siteid] = $this->makeUrlForWebsite($site, false, $language_code);
    				}
					$arr_result['country'][] = array('id' => $country->getId(), 'name' => $country_name, 'site' => $siteid, 'url' => $urls[$siteid]);
				}
			} else {
				$arr_result = array();
			}
			echo json_encode($arr_result);
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
}
?>
