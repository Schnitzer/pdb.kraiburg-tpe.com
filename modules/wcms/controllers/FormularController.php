<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Formularsite class.
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
 * Formularsite class.
 *
 * @package netzcraftwerk
 */
class Wcms_FormularController extends Wcms_SitetypeController
{

    /**
     * Main tabs
     *
     * @var array
     */
    public $tabs = array(
        'data' => array(
            'subtabs' => array(
                'form' => array(
                    'name' => 'Form'
                ),
                'recipients' => array(
                    'name' => 'Email Recipients'
                ),
                'oncomplete' => array(
                    'name' => 'On Complete'
                ),
                'submissions' => array(
                    'name' => 'Submissions'
                ),
            ),
        ),
    );

    /**
     *
     */
    public function formAction ($site_id)
    {
        /*if ($sitetype_id > 0) {
            $this->Formularsite->setId($sitetype_id);
            $this->Formularsite->read();
            $this->data['Formularsite'] = $this->Formularsite->data();
        }*/
    }

    /**
     *
     */
    public function recipientsAction ($site_id)
    {

    }

    /**
     *
     */
    public function oncompleteAction ($site_id)
    {

    }

    /**
     *
     */
    public function submissionsAction ($site_id)
    {

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
            '/{formular}/',
        );
        $replaced_tags_with = array(
            ''
        );
        $code = preg_replace($tags, $replaced_tags_with, $code);
        return $code;
    }
}
?>
