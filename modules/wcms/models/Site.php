<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Site class.
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
 * Site class.
 *
 * @package netzcraftwerk
 */
class Wcms_Site extends Ncw_Model
{

	/**
	 * Validation array.
	 *
	 * @var Array
	 */
	public $validation = array(
        "name" => array("rules" => array('RegExp' => '/^[A-Za-z-_0-9]+$/', 'MaxLength' => 100), "required" => true),
        "parent_id" => array("rules" => array("Integer"), "required" => true),
        "sitetemplate_id" => array("rules" => array("Integer")),
        "navtemplate_id" => array("rules" => array("Integer")),
        "position" => array("rules" => array("Integer"), "required" => true),
        "private" => array("rules" => array("Boolean")),
        "schedule" => array("rules" => array("Boolean")),
        "publish" => array("rules" => array("DateTime")),
        "expire" => array("rules" => array("DateTime")),
        "cache" => array("rules" => array("Boolean")),
        "cache_exparation" => array("rules" => array("Integer")),
        "permalink" => array("rules" => array("Boolean")),
        'status' => array('rules' => array('InList' => array('new', 'modified', 'published', 'unpublished'))),
	);

	/**
	 * Has many associations
	 *
	 * @var array
	 */
	public $has_many = array(
	   'Sitelanguage',
	   'Component',
	   'Site' => array('foreign_key' => 'parent_id'),
	   'Newssite',
	   'SiteNavtemplate'
	);

    /**
     * sorts the sites of a depth
     *
     * @param string $second_sort (optional)
     */
    public function sort ($second_sort = 'modified')
    {
        try {
            $this->unbindModel('all');
            $sites = $this->fetch(
                'all',
                array(
                    'conditions' => array(
                        'Site.parent_id' => $this->getParentId(),
                    ),
                    'fields' => array('Site.id'),
                    'order' => array(
                        'Site.position',
                        'Site.' . $second_sort . ' DESC'
                    )
                )
            );
            $position = 0;
            foreach ($sites as $obj_site) {
                $stmt = $this->db->prepare(
                    'UPDATE ' . $this->db_table_name
                    . ' SET position=:position WHERE id=:id'
                );
                $stmt->bindValue(':position', ++$position);
                $stmt->bindValue(':id', $obj_site->getId());
                if (false === $stmt->execute()) {
                    $error_info = $stmt->errorInfo();
                    throw new Ncw_Exception($error_info[2], 1);
                }
            }
        } catch (Ncw_Exception $e) {
            if (DEBUG_MODE > 0) {
                $e->exitWithMessage();
            }
        }
    }

	/**
	 * Before save set the status
	 *
	 * @return void
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
	 * Before the site is deleted check if it is unpublished.
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
                        'Sitetype.site_class',
                    ),
                    'conditions' => array(
                        'Sitetype.site_class !=' => ''
                    )
                )
            );
            foreach ($sitetypes as $sitetype_site_class) {
                $sitetype_site_class = ucfirst($sitetype_site_class);
                $params = array();
                $params['pass'] = array($this->getId());

                unset(
                    $params['controller'],
                    $params['action']
                );

                return $this->requestAction(
                    array(
                        'controller' => $sitetype_site_class,
                        'action' => 'delete',
                    ),
                    $params
                );
            }
        }
    }
}
?>
