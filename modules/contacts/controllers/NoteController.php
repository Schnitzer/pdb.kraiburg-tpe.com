<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Note controller class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
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
 * NoteController class.
 *
 * @package netzcraftwerk
 */
class Contacts_NoteController extends Contacts_ModuleController
{

	/**
	 * New note
	 *
	 * @return void
	 */
    public function saveAction ()
    {
        $this->view = false;
        $return = 'false';

        if (true === isset($this->data['Note'])) {
            $this->Note->data($this->data['Note']);
            if (true === $this->Note->save()) {
                $return = 'true';
            }
        }

        print '{ "return_value" : ' . $return . ', "note" : { "id" : ' . $this->Note->getId() . ' } }';
    }

    /**
     * Update the note
     *
     * @return void
     */
    public function updateAction ()
    {
        $this->view = false;
        $return = 'false';

        if (true === isset($this->data['Note'])) {
            $this->Note->data($this->data['Note']);
            if (true === $this->Note->save()) {
                $return = 'true';
            }
        }

        print '{ "return_value" : ' . $return . ' }';
    }

    /**
     * Deletes a note
     *
     * @param int $id the note id
     *
     * @return void
     */
    public function deleteAction ($id)
    {
        $this->view = false;

        $this->Note->setId($id);
        $this->Note->delete();

        print '{ "return_value" : true}';
    }
}
?>
