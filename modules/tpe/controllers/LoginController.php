<?php
/* SVN FILE: $Id$ */
/**
 * Contains the LoginControlle class.
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
 * LoginControlle class.
 *
 * @package netzcraftwerk
 */
class Tpe_LoginController extends Tpe_ModuleController
{

	public $helpers = array('Html');

	public $acl_publics = array('check');

    /**
	 * Index action.
	 *
	 * @param string $username   the user name
	 * @param string $session_id the session id
	 *
	 * @return void
	 */
	public function checkAction ($username = '', $session_id = '')
	{
		$this->view = false;
	    $this->_garbageCollector();

	    $u = '';
	    $sid = '';
	    if (true === isset($_GET['u'])) {
	        $u = $_GET['u'];
	    }
	    if (true === isset($_GET['sid'])) {
            $sid = $_GET['sid'];
        }

	    $login = $this->Login->findBy('sess_id', $session_id);
        if (false === empty($username)
            && false === empty($session_id)
            && false !== $login
            && $login->getUser() == $username) {
            print 1;
            return;
        }
        $login = $this->Login->findBy('sess_id', $sid);
	    if (false !== $login && $login->getUser() == $u) {
            print 1;
            return;
        }
	}

	/**
	 * Enter description here...
	 *
	 */
	private function _garbageCollector ()
	{
	    $login_for_delete = new Tpe_Login();
	    $logins = $this->Login->fetch('all', array('conditions' => array('Login.date < (NOW() - 86400)')));
	    foreach ($logins as $login) {
	        $login_for_delete->copyFrom($login);
	        $login_for_delete->delete();
	    }
	}
}
?>
