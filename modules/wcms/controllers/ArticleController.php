<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Article class.
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
 * Article class.
 *
 * @package netzcraftwerk
 */
class Wcms_ArticleController extends Wcms_SitetypeController
{

    public $tabs = array(
        'data' => array(
            'subtabs' => array(
                'article' => array(
                    'name' => 'Article'
                )
            )
        )
    );

	/**
	 * edit article data action
	 *
	 */
    public function articleAction ($sitelanguage_id)
    {

        $article = $this->Article->findBy(
            'sitelanguage_id',
            $sitelanguage_id
        );

        if (false !== $article) {
            $this->data['Article'] = $article->data();
        }
        $this->view->sitelanguage_id = $sitelanguage_id;
        
    }

    /**
     * Update action
     *
     * @param int $sitelanguage_id
     *
     * @return void
     */
    public function updateAction ($sitelanguage_id)
    {
        $this->view = false;
        if (true === isset($this->data['Article'])) {
            $this->Article->setSitelanguageId($sitelanguage_id);
            $this->Article->data($this->data['Article']);
            $this->Article->save();
        }
    }

    /**
     * Publish Action
     *
     * @param int $sitelanguage_id
     *
     * @return void
     */
    public function publishAction ($sitelanguage_id)
    {
        $this->view = false;

        $found_model = $this->Article->findBy(
            'sitelanguage_id',
            $sitelanguage_id
        );
        if (false !== $found_model) {
            $this->publishObject('Article', $found_model);
        }
    }

    /**
     * Unpublish Action
     *
     * @param int $sitelanguage_id
     *
     * @return void
     */
    public function unpublishAction ($sitelanguage_id)
    {
        $this->view = false;

        $this->loadModel('PublishedArticle');
        $found_model = $this->PublishedArticle->findBy(
            'sitelanguage_id',
            $sitelanguage_id,
            array(
                'fields' => array(
                    'PublishedArticle.id'
                )
            )
        );
        if (false !== $found_model) {
            $this->PublishedArticle->setId($found_model->getId());
            $this->PublishedArticle->delete();
        }
    }

    /**
     * Delete action
     *
     * @param int $sitelanguage_id
     *
     * @return void
     */
    public function deleteAction ($sitelanguage_id)
    {
        $this->view = false;

        // unpublished
        $found_model = $this->Article->findBy(
            'sitelanguage_id',
            $sitelanguage_id,
            array(
                'fields' => array(
                    'Article.id'
                )
            )
        );
        if (false !== $found_model) {
            $this->Article->setId($found_model->getId());
            $this->Article->delete();
        }

        // published
        $this->loadModel('PublishedArticle');
        $found_model = $this->PublishedArticle->findBy(
            'sitelanguage_id',
            $sitelanguage_id,
            array(
                'fields' => array(
                    'PublishedArticle.id'
                )
            )
        );
        if (false !== $found_model) {
            $this->PublishedArticle->setId($found_model->getId());
            $this->PublishedArticle->delete();
        }
    }

    /**
     * Replaces tags in code
     *
     * @param string $code the code
     *
     * @return string
     */
    public function replaceTags ($code)
    {
        $tags = array(
            '/{article\.head}/',
        );
        $replaced_tags_with = array(
            '<?php print $sitetype["article"]["head"]; ?>',
        );
        $code = preg_replace($tags, $replaced_tags_with, $code);
        return $code;
    }
}
?>
