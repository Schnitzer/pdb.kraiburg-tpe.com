<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Componentlanguage class.
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
 * Componentlanguage class.
 *
 * @package netzcraftwerk
 */
class Wcms_Componentlanguage extends Ncw_Model
{

	/**
	 * Has many associations
	 *
	 * @var array
	 */
	public $has_many = array(
	   'Componenttext',
	   'Componentshorttext',
	   'Componentfile',
	);

	/**
	 * Belongs to associations
	 *
	 * @var array
	 */
	public $belongs_to = array('Language', 'Component');

	/**
	 * Validation array.
	 *
	 * @var array
	 */
	public $validation = array(
        "component_id" => array("rules" => array("Integer")),
        "language_id" => array("rules" => array("Integer"), "required" => true),
        'link' => array('rules' => array('Url')),
	);

	/**
	 * After Save update the sitelanuage status to modified
	 *
	 */
	public function afterSave ()
	{
	    self::setSitelanguageStatus($this->getId());
	}

    /**
     * Before delete update the sitelanuage status to modified
     *
     */
    public function beforeDelete ()
    {
        self::setSitelanguageStatus($this->getId());
    }

    /**
     * Sets the status of the sitelanguage
     *
     * @param int $id the componentlanguage id
     *
     * @return viod
     */
    public static function setSitelanguageStatus ($id)
    {
        $componentlanguage = new Wcms_Componentlanguage();
        $stmt = $componentlanguage->db->prepare(
           'SELECT Sitelanguage.id, Sitelanguage.status '
           . 'FROM ' . Ncw_Database::getConfig('prefix') . 'wcms_componentlanguage AS Componentlanguage '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_component AS Component '
           . 'ON Componentlanguage.component_id=Component.id '
           . 'INNER JOIN ' . Ncw_Database::getConfig('prefix') . 'wcms_sitelanguage AS Sitelanguage '
           . 'ON Component.site_id=Sitelanguage.site_id '
           . 'WHERE Sitelanguage.language_id=Componentlanguage.language_id '
           . '&& Componentlanguage.id=:id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (true === isset($rows[0]['id'], $rows[0]['status'])
           && $rows[0]['status'] !== 'new'
           && $rows[0]['status'] !== 'unpublished'
        ) {
            $stmt = $componentlanguage->db->prepare(
               'UPDATE ' . Ncw_Database::getConfig('prefix') . 'wcms_sitelanguage AS Sitelanguage '
               . 'SET Sitelanguage.status=:status '
               . 'WHERE Sitelanguage.id=:id'
            );
            $stmt->bindValue(':id', $rows[0]['id'], PDO::PARAM_INT);
            $stmt->bindValue(':status', 'modified', PDO::PARAM_STR);
            $stmt->execute();
        }
    }
}

?>