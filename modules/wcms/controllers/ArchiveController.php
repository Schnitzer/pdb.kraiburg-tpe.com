<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Archive class.
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
 * Archive class.
 *
 * @package netzcraftwerk
 */
class Wcms_ArchiveController extends Wcms_SitetypeController
{

    /**
     * has got no model
     *
     * @var boolean
     */
    public $has_model = false;

    /**
     * Read the articles
     *
     * @param int    $site_id       the site id
     * @param int    $language_id   the language id
     * @param string $language_code the language code
     * @param int    $num_articles  the number of articles
     *
     * @return array
     */
    public function readArticles ($site_id, $language_id, $language_code, $live, &$num_articles)
    {
        $this->base = Ncw_Configure::read('Project.url');

        // Read the settings
        $setting = new Wcms_Setting();
        $setting->unbindModel('all');
        $setting->setId(1);
        $setting->read(
            array(
                'fields' => array(
                    'Setting.language_id'
                )
            )
        );

        if (true === $live) {
            $model_additional = 'Published';
            $site = new Wcms_PublishedSite();
        } else {
            $model_additional = '';
            $site = new Wcms_Site();
        }
        $site->unbindModel('all');
        $site->bindModel(
            array(
                'has_one' => array(
                    $model_additional . 'Sitelanguage' => array(
                        'join_condition' => $model_additional . 'Site.id=' . $model_additional . 'Sitelanguage.site_id'
                    ),
                    $model_additional . 'Article' => array(
                        'join_condition' => $model_additional . 'Sitelanguage.id=' . $model_additional . 'Article.sitelanguage_id'
                    )
                )
            )
        );

        if (true === isset($_GET['as'], $_GET['ae'])) {
            $_GET['as'] = (int) $_GET['as'];
            $_GET['ae'] = (int) $_GET['ae'];
        } else {
            $_GET['as'] = 0;
            $_GET['ae'] = 10;
        }

        $sitetype = new Wcms_Sitetype();
        $sitetype = $sitetype->findBy(
            'name',
            'Article',
            array(
                'fields' => array(
                    'Sitetype.id'
                )
            )
        );

        $schedule = '(Site.schedule=0 || Site.schedule=1)';
        if (true === (boolean) $_GET['preview'] || true === $live) {
            $schedule = '(' . $model_additional . 'Site.schedule=0 || (' . $model_additional . 'Site.schedule=1 && '
                . '' . $model_additional . 'Site.publish <= NOW() && ' . $model_additional . 'Site.expire > NOW()))';
        }

        $found_sites = $site->findAllBy(
            'sitetype_id',
            $sitetype->getId(),
            array(
                'conditions' => array(
                    $model_additional . 'Site.parent_id' => $site_id,
                    $model_additional . 'Sitelanguage.language_id' => $language_id,
                    $schedule
                ),
                'fields' => array(
                    $model_additional . 'Site.id',
                    $model_additional . 'Site.name',
                    $model_additional . 'Site.parent_id',
                    $model_additional . 'Site.permalink',
                    $model_additional . 'Article.head',
                    $model_additional . 'Article.body',
                    $model_additional . 'Article.date',
                ),
                'order' => array(
                    $model_additional . 'Article.date DESC',
                    $model_additional . 'Site.position DESC',
                ),
                'limit' => $_GET['as'] . ',' . $_GET['ae']
            )
        );

        if (true === $live) {
            $site_table = 'wcms_published_site';
            $sitelanguage_table = 'wcms_published_sitelanguage';
        } else {
            $site_table = 'wcms_site';
            $sitelanguage_table = 'wcms_sitelanguage';
        }
        $query = $site->db->prepare(
            'SELECT count(1) FROM  `' . Ncw_Database::getConfig('prefix') . $site_table . '` AS s '
            . 'INNER JOIN `' . Ncw_Database::getConfig('prefix') . $sitelanguage_table . '` AS sl '
            . 'ON s.id=sl.site_id '
            . 'WHERE s.parent_id = :site_id && sl.language_id = :language_id'
        );
        $query->bindValue(':site_id', $site_id, PDO::PARAM_INT);
        $query->bindValue(':language_id', $language_id, PDO::PARAM_INT);
        $query->execute();
        $num_articles = array_pop(array_pop($query->fetchAll(PDO::FETCH_NUM)));

        $articles = array();
        foreach ($found_sites as $obj_site) {
            $head = $obj_site->{$model_additional . 'Article'}->getHead();
            $body = $obj_site->{$model_additional . 'Article'}->getBody();
            $date = $obj_site->{$model_additional . 'Article'}->getDate();
            if (true === empty($head)
                && true === empty($body)
            ) {
                // if no content for this language, then get the content of the master language
                $site->unbindModel('all');
                $site->bindModel(
                    array(
                        'has_one' => array(
                            $model_additional . 'Sitelanguage' => array(
                                'join_condition' => $model_additional . 'Site.id=' . $model_additional . 'Sitelanguage.site_id'
                            ),
                            $model_additional . 'Article' => array(
                                'join_condition' => $model_additional . 'Sitelanguage.id=' . $model_additional . 'Article.sitelanguage_id'
                            )
                        )
                    )
                );
                $found_site = $site->findBy(
                    'sitetype_id',
                    $sitetype->getId(),
                    array(
                        'conditions' => array(
                            $model_additional . 'Site.id' => $obj_site->getId(),
                            $model_additional . 'Sitelanguage.language_id' => $setting->getLanguageId()
                        ),
                        'fields' => array(
                            $model_additional . 'Site.id',
                            $model_additional . 'Site.name',
                            $model_additional . 'Site.parent_id',
                            $model_additional . 'Site.permalink',
                            $model_additional . 'Article.head',
                            $model_additional . 'Article.body',
                        )
                    )
                );
                $obj_site = $found_site;
                $head = $obj_site->{$model_additional . 'Article'}->getHead();
                $body = $obj_site->{$model_additional . 'Article'}->getBody();
                if (true === empty($head)
                    && true === empty($body)
                ) {
                    continue;
                }
            }
            $articles[] = array(
                'head' => $head,
                'body' => $body,
                'date' => $date,
                'url' => $this->makeUrlForWebsite(
                    $obj_site,
                    false,
                    $language_code,
                    true,
                    $live
                ),
            );
        }
        return $articles;
    }

    /**
     * Replaces tags in code
     *
     * @param string $code the code
     *
     * @return string
     */
    public function replaceSiteTags ($code)
    {
        $tags = array(
            '/{archive\.articles}/',
            '/{archive\.article.head}/',
            '/{archive\.article.body}/',
            '/{archive\.article.date}/',
            '/{archive\.article.url}/',
            '/{archive\.previous}/',
            '={/archive\.previous}=',
            '/{archive\.next}/',
            '={/archive\.next}=',
            '={/archive\.articles}=',
            '/{archive\.site_id}/',
        );
        $replaced_tags_with = array(
            '<?php $num_articles = 0; $archive = new Wcms_ArchiveController(); foreach($archive->readArticles($site["id"], $language_id, $language_code, $live, $num_articles) as $article) { ?>',
            '<?php print $article["head"]; ?>',
            '<?php print $article["body"]; ?>',
            '<?php print $article["date"]; ?>',
            '<?php print $article["url"]; ?>',
            '<?php if ($_GET["as"] > 0) { ?><a href="?as=<?php print $_GET["as"] - 10; ?>&amp;ae=<?php print $_GET["as"]; ?>">',
            '</a><?php } ?>',
            '<?php if (($_GET["ae"] + 10) > $num_articles) { $ae = $num_articles; } else { $ae = $_GET["ae"] + 10; } if ($_GET["ae"] < $num_articles) { ?><a href="?as=<?php print $_GET["ae"]; ?>&amp;ae=<?php print $ae; unset($ae); ?>">',
            '</a><?php } ?>',
            '<?php } ?>',
            '<?php print $sitetype["archivesite"]["site_id"]; ?>',
        );
        $code = preg_replace($tags, $replaced_tags_with, $code);
        return $code;
    }
}
?>
