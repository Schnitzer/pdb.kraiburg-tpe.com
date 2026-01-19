<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Sitelanguage class.
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
 * Sitelanguage class.
 *
 * @package netzcraftwerk
 */
class Wcms_Sitelanguage extends Ncw_Model
{

	/**
	 * Belongs to associations
	 *
	 * @var array
	 */
	public $belongs_to = array('Language');

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
        "site_id" => array("rules" => array("Integer")),
        "language_id" => array("rules" => array("Integer"), "required" => true),
        "name" => array("rules" => array("NotEmpty", "MaxLength" => 100), "required" => true),
        "title" => array("rules" => array("NotEmpty", "MaxLength" => 255), "required" => true),
        "keywords" => array("rules" => array("MaxLength" => 1255)),
        "description" => array("rules" => array("MaxLength" => 1255)),
        "author" => array("rules" => array("MaxLength" => 100)),
        'home' => array("rules" => array('Boolean')),
        'status' => array('rules' => array('InList' => array('new', 'modified', 'published', 'unpublished'))),
        'changefreq' => array('rules' => array('InList' => array('always','hourly','dialy','weekly','monthly','yearly','never'))),
        'priority' => array('rules' => array('MaxLength' => 5)),
	);

    /**
     * Before save set the status
     *
     */
    public function beforeSave ()
    {
        if ($this->getId() > 0) {
            $status = $this->readField('status');
            if (($this->getStatus() === 'published' && $status !== 'published')
                || ($this->getStatus() === 'unpublished' && $status !== 'unpublished')
                || ($this->getStatus() === 'published' && $status === 'published')
                || ($this->getStatus() === 'unpublished' && $status === 'unpublished')
            ) {
                return;
            }
            if ($status !== 'new' && $status !== 'unpublished') {
                $this->setStatus('modified');
            } else {
                $this->setStatus($status);
            }
        }
    }

    /**
     * Before the sitelanguage is deleted check if it is unpublished.
     *
     * @return boolean
     */
    public function beforeDelete ()
    {
        $status = $this->readField('status');
        if ($status === 'unpublished'
            || $status === 'new'
        ) {
            return true;
        }
        return false;
    }

    /**
     * after delete also delete the sitetype entries
     *
     * @param boolean $deleted is true if deleted
     *
     * @return void
     */
    public function afterDelete ($deleted)
    {
        if (true === $deleted) {
            $sitetype = new Wcms_Sitetype();
            $sitetypes = $sitetype->fetch(
                'list',
                array(
                    'fields' => array(
                        'Sitetype.sitelanguage_class',
                    ),
                    'conditions' => array(
                        'Sitetype.sitelanguage_class !=' => ''
                    )
                )
            );
            foreach ($sitetypes as $sitetype_sitelanguage_class) {
                $sitetype_sitelanguage_class = ucfirst($sitetype_sitelanguage_class);
                $params = array();
                $params['pass'] = array($this->getId());

                unset(
                    $params['controller'],
                    $params['action']
                );

                return $this->requestAction(
                    array(
                        'controller' => $sitetype_sitelanguage_class,
                        'action' => 'delete',
                    ),
                    $params
                );
            }
        }
    }
}
?>
