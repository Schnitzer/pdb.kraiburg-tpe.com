<?php
/* SVN FILE: $Id$ */
/**
 * Contains the File controller class.
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
 * FileController class.
 *
 * @package netzcraftwerk
 */
class Contacts_FileController extends Contacts_ModuleController
{

	/**
	 * New file
	 *
	 * @return void
	 */
    public function saveAction ()
    {
        $this->view = false;
        $return = 'false';

        if (true === isset($this->data['File'])) {
            $this->File->data($this->data['File']);
            if (true === $this->File->save()) {
                $return = 'true';
            }
        }

        print '{ "return_value" : ' . $return . ', "file" : { "id" : ' . $this->File->getId() . ' } }';
    }

    /**
     * Deletes a file
     *
     * @param int $id the file id
     *
     * @return void
     */
    public function deleteAction ($id)
    {
        $this->view = false;

        $this->File->setId($id);
        $this->File->delete();

        print '{ "return_value" : true }';
    }
}
?>
