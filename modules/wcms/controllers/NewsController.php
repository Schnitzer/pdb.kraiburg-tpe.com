<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Wcms_News class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschraenkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcrafrtwerk.com>
 * @copyright		Copyright 2007-2009, Netzcraftwerk UG
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * Wcms_News class.
 *
 * @package netzcraftwerk
 */
class Wcms_NewsController extends Wcms_ModuleController
{

	
	    /**
     * @var array
     */
    public $acl_publics = array(
        "termdetails"
    );
	
	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "News :: Website";

    /**
     * Components...
     *
     * @var array
     */
    public $components = array('Acl');

	
	public function getTerms($language)
	{

		// read the languages 
		if (true == isset($_SESSION["ls"])) {
			$ls = $_SESSION["ls"];
		} else {
			$ls = 'en';
		}
		$obj_language = new Wcms_Language();
		$arr_language = $obj_language->fetch('all', array('conditions' => array('shortcut' => strtolower($ls))));
		$language_id = 1;
		if (count($arr_language) > 0) {
			$language_id = $arr_language[0]->getId();
		}
		
		$newslanguage = new Wcms_PublishedNewslanguage();
		
		$newslanguage = $newslanguage->fetch(
            'all',
		    array(
                'conditions' => array('Language.id' => $language_id),
                'order' => array('Language.id')
            )
        );
		$arr_languages = array();
		$tmp = array();
		foreach ($newslanguage as $language) {
			$arr_languages[$language->getNewsId()][] = array(
				'headline' => strip_tags(str_replace('- ', '-', htmlspecialchars_decode($language->getHeadline())))			   
			);
			if ( strlen( trim( $language->getHeadline() ) ) > 1) {
				

				if ($language_id == 5) {
					$tmp_arr_str = explode("sollicitations de", $language->getHeadline());
					$tmp[] = $tmp_arr_str[0];
				} else {
					$tmp[] = strip_tags( trim( $language->getHeadline() ) );
				}
				

			}
		}
		return implode(',', $tmp);
	}
	
	/*
	*
	*/
	public function termdetailsAction_OUT ()
	{
		$this->view = false;
		
		
		// read the languages 
		if (true == isset($_SESSION["ls"])) {
			$ls = $_SESSION["ls"];
		} else {
			$ls = 'en';
		}
		$obj_language = new Wcms_Language();
		$arr_language = $obj_language->fetch('all', array('conditions' => array('shortcut' => strtolower($ls))));
		$language_id = 1;
		if (count($arr_language) > 0) {
			$language_id = $arr_language[0]->getId();
		}
		
		
		$str = Ncw_Library_Sanitizer::escape($this->params['url']['str']);
		
		$arr_str = explode('<', $str);
		$str = $arr_str[0];
		
		$obj_newslanguage = new Wcms_Newslanguage();
		$obj_newslanguage->unbindModel('all');
		
		$arr_newslanguage = $obj_newslanguage->fetch(
            'all',
		    array(
                'conditions' => array(
									'headline LIKE ' . trim($str),
									'language_id' => $language_id,
								)
            )
        );
		//var_dump($arr_newslanguage);

		
		if (count($arr_newslanguage) > 0) {
			echo '<div class="row">
							<div class="col-sm-24">
								<h4 class="ncw-color-blue">' . $arr_newslanguage[0]->getHeadline() . '</h4><br />
							</div>
						</div>';
			echo '<div class="row">
							<div class="col-sm-24">
								' . $arr_newslanguage[0]->getBody() . '
							</div>
						</div>';
			return null;
		} else {
			//echo 'false';
			return null;
		}
		
		
		var_dump($arr_newslanguage);
		
	}
	
	
	
				/*
		* Holt die Details der Bibliothek
		*/
		public function termdetailsAction()
		{
			
			$this->view = false;


			// read the languages 
			if (true == isset($_SESSION["ls"])) {
				$ls = $_SESSION["ls"];
			} else {
				$ls = 'en';
			}
			$obj_language = new Wcms_Language();
			$arr_language = $obj_language->fetch('all', array('conditions' => array('shortcut' => strtolower($ls))));
			$language_id = 1;
			if (count($arr_language) > 0) {
				$language_id = $arr_language[0]->getId();
			}


			$str = Ncw_Library_Sanitizer::escape($this->params['url']['str']);

			$str = str_replace('de -40 °C', 'de -40&nbsp;°C', $str);
			if ($language_id == 4) {
				$tmp_arr_str = explode("carga desde", $str);
				$str = $tmp_arr_str[0];
			}
			if ($language_id == 5) {
				$tmp_arr_str = explode("sollicitations de", $str);
				$str = $tmp_arr_str[0];
			}
			
			

			if (true == strstr($str, 'PV 3930 Florida')) {
				$str = 'PV 3930 Florida';
			}
			if (true == strstr($str, 'PV 3929 Kalahari')) {
				$str = 'PV 3929 Kalahari';
			}

			$arr_str = explode('<', $str);
			$str = $arr_str[0];
			
			$obj_db = new Wcms_PublishedNewslanguage();

			if (strlen(trim($str)) < 2) {
				$str = '-------------------------------------------NO------';
			}

			$str_query = "
				SELECT 
				
				headline ,
				body 
				FROM ncw_wcms_published_newslanguage
				
				WHERE headline LIKE '%" . $str . "%'
				AND language_id = " . $language_id . "
				";

			if ($_GET['mode'] == 'debug') {
				echo $str_query;
				//  
			}

			$dbquery = $obj_db->db->prepare($str_query);
			$dbquery->execute();
			$arr_newslanguage = $dbquery->fetchAll();
			

			if (count($arr_newslanguage) < 1 ) {
				//$str_tmp = html_entity_decode($str);
				$str_tmp = str_replace('&nbsp;', ' ', $str);
				$str_tmp = str_replace('&deg;', '°', $str_tmp);
				
				$str_query = "
					SELECT 

					headline ,
					body 
					FROM ncw_wcms_published_newslanguage

					WHERE headline LIKE '%" . $str_tmp . "%'
					AND language_id = " . $language_id . "
					";

					if ($_GET['mode'] == 'debug') {
						echo $str_query;
					}

				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();
				$arr_newslanguage = $dbquery->fetchAll();
				
			}

			if (count($arr_newslanguage) < 1 ) {
				$str = htmlentities($str);
				$str_query = "
					SELECT 

					headline ,
					body 
					FROM ncw_wcms_published_newslanguage

					WHERE headline LIKE '%" . $str . "%'
					AND language_id = " . $language_id . "
					";

				//echo $str_query;

				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();
				$arr_newslanguage = $dbquery->fetchAll();
				
			}

			
			
			
			if (count($arr_newslanguage) > 0) {
				echo '<div class="row">
								<div class="col-sm-20">
									<h4 class="ncw-color-blue">' . $arr_newslanguage[0]['headline'] . '</h4><br />
								</div>
							</div>';
				echo '<div class="row">
								<div class="col-sm-24">
									' . str_replace('testsystem', 'pdb', $arr_newslanguage[0]['body']) . '
								</div>
							</div>';
				return null;
			} else {
				echo $str;
				return null;
			}
		}
	
	/**
	 * shows all existing news
	 *
	 */
	public function allAction ()
	{
	    $this->registerCss(array('wcms'));

		// read the languages
		$this->News->unbindModel('all');
		$newslanguage = new Wcms_Newslanguage();
		$newslanguage = $newslanguage->fetch(
            'all',
		    array(
                'fields' => array(
                    'Newslanguage.news_id',
                    'Language.shortcut',
                    'Language.name'
                ),
                'order' => array('Language.id')
            )
        );
		$arr_languages = array();
		foreach ($newslanguage as $language) {
			$arr_languages[$language->getNewsId()][] = array(
                'shortcut' => $language->Language->getShortcut(),
			    'name' => $language->Language->getName()
			);
		}
		$this->view->arr_languages = $arr_languages;
		$arr_news = $this->News->fetch("all");
		$this->view->news = $arr_news;

        $this->view->permissions = array(
            '/wcms/news/new' => $this->acl->check('/wcms/news/new'),
            '/wcms/news/edit' => $this->acl->check('/wcms/news/edit'),
        );
	}

	/**
	 * new News
	 *
	 */
	public function newAction ()
	{
		if (isset($this->data['News'])) {
			$this->News->data($this->data['News']);

			$this->News->Newslanguage = new Ncw_ModelList();
			if (true === isset($this->data['Newslanguage']['languages'])
                && true === is_array($this->data['Newslanguage']['languages'])
            ) {
                foreach ($this->data['Newslanguage']['languages'] as $language_id => $value) {
                    $sitelanguage = new Wcms_Newslanguage();
                    $sitelanguage->setLanguageId($language_id);
                    $this->News->Newslanguage->addModel($sitelanguage);
                }
                unset($sitelanguage);
            } else {
                $this->News->Newslanguage->addModel(new Wcms_Newslanguage());
                $setting = new Wcms_Setting();
                $setting->setId(1);
                $this->News->Newslanguage[0]->setLanguageId($setting->readField('language_id'));
            }

			if (true === $this->News->save()) {
				$this->redirect(
				    array(
				        'action' => 'edit',
				        'id' => $this->News->getId()
				    )
				);
			}
		}

        $this->registerJS(
            array(
                'ncw.wcms.news'
            )
        );

        $language = new Wcms_Language();
        $this->view->num_languages = $language->fetch('count');

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
	}

	/**
	 * to edit one news
	 *
	 * @param int $id (id of the news)
	 */
	public function editAction ($id)
	{
        $this->registerJS(
            array(
                'ncw.wcms.news',
            )
        );
        $this->registerCss(
            array(
                'wcms',
            )
        );

		$this->News->setId($id);

		if (true === isset($this->data['News'])) {
			if (true === isset($this->data['News']['schedule'])
                && $this->data['News']['schedule'] == 1)
            {
				$this->data['News']['publish'] = $this->data['News']['publish_date'] . ' ' .  $this->data['News']['publish_time'] . ':00';
				$this->data['News']['expire'] = $this->data['News']['expire_date'] . ' ' .  $this->data['News']['expire_time'] . ':00';
			} else {
				$this->data['News']['publish'] = '';
				$this->data['News']['expire'] = '';
			}
			$this->News->data($this->data['News']);
			$this->News->save();
		}

		$this->News->read();

		$this->view->news_id = $id;
		$this->data['News'] = $this->News->data();
		// news loanguges
		$this->view->arr_all_languages = $this->News->Newslanguage;
		// news sites
		$this->view->arr_all_sites = $this->News->Newssite;
		$this->view->news_status = $this->News->getStatus();

		if (true === empty($this->data['News']['publish'])) {
		    $this->data['News']['publish'] = '0000-00-00 00:00:00';
		}
	    if (true === empty($this->data['News']['expire'])) {
            $this->data['News']['expire'] = '0000-00-00 00:00:00';
        }
	    $news_publish = explode(' ', $this->data['News']['publish']);
        if ($news_publish[0] == '0000-00-00') {
            $this->view->news_publish = array('', '');
        } else {
            $this->view->news_publish = $news_publish;
        }
        $news_expire = explode(' ', $this->data['News']['expire']);
            if ($news_expire[0] == '0000-00-00') {
            $this->view->news_expire = array('', '');
        } else {
            $this->view->news_expire = $news_expire;
        }

		if (true === isset($this->data['News']['schedule'])
            && $this->data['News']['schedule'] == 1
        ) {
			$this->view->schedule = true;
		} else {
			$this->view->schedule = false;
		}

        $this->view->permissions = array(
            '/wcms/news/new' => $this->acl->check('/wcms/news/new'),
            '/wcms/news/publish' => $this->acl->check('/wcms/news/publish'),
            '/wcms/news/unpublish' => $this->acl->check('/wcms/news/unpublish'),
            '/wcms/newssite/new' => $this->acl->check('/wcms/newssite/new'),
            '/wcms/newssite/delete' => $this->acl->check('/wcms/newssite/delete'),
            '/wcms/newslanguage/new' => $this->acl->check('/wcms/newslanguage/new'),
            '/wcms/newslanguage/edit' => $this->acl->check('/wcms/newslanguage/edit'),
            '/wcms/newslanguage/delete' => $this->acl->check('/wcms/newslanguage/delete'),
        );
	}

	/**
	 * Delete news
	 *
	 * @param int $id (id of the news)
	 */
	public function deleteAction ($id)
	{
	    $this->view = false;

		$this->News->setId($id);
		$this->News->delete();

		$this->redirect(
            array(
                'action' => 'all',
            )
		);
	}

    /**
     * publish news
     *
     * @param int $id
     *
     * @return void
     */
    public function publishAction ($id)
    {
        $this->view = false;

        // publish the sitelanguage
        $this->News->setId($id);

        $published_news = new Wcms_PublishedNews();
        $published_news->setId($this->News->getId());
        $published_news->delete();

        $this->News->setStatus('published');
        $this->News->saveField('status');

        $this->News->unbindModel('all');
        $this->News->read();

        $this->publishObject('News', $this->News);

        // publish newsites
        $newssite = new Wcms_Newssite();
        $newssites = $newssite->fetch(
            'all',
            array(
                'conditions' => array(
                    'Newssite.news_id' => $this->News->getId()
                )
            )
        );
        foreach ($newssites as $newssite) {
            $this->publishObject('Newssite', $newssite);
        }

        $this->flushWebsiteCache();

        print '{"return_value" : true}';
    }

    /**
     * unpublish news
     *
     * @param int $id
     * @param int $newslanguage_id
     *
     * @return void
     */
    public function unpublishAction ($id)
    {
        $this->view = false;

        $this->News->setId($id);

        $this->News->setStatus('unpublished');
        $this->News->saveField('status');

        $this->News->unbindModel('all');
        $this->News->read(array('fields' => array('id')));

        $published_news = new Wcms_PublishedNews();
        $published_news->setId($this->News->getId());
        $published_news->delete();

        $this->flushWebsiteCache();

        print '{"return_value" : true}';
    }
}
?>
