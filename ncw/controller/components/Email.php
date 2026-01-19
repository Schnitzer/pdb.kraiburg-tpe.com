<?php
/**
 * Contains the Email component class
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  1997-2008 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version    SVN: $Id$
 * @link       http://www.netzcraftwerk.com
 * @since      File available since Release 0.1
 * @modby      $LastChangedBy$
 * @lastmod    $LastChangedDate$
 */
/**
 * Include the swift classes.
 */
require_once 'ncw/vendor/swift/swift_required.php';
/**
 * Email component class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Components_Email extends Ncw_Component
{

	/**
	 * The controller
	 *
	 * @var Ncw_Controller
	 */
	public $controller = null;

	/**
	 * The layout
	 *
	 * @var string
	 */
    public $layout = 'default';

    /**
     * open, ssl, tls
     *
     * @var string
     */
    public $smtp_type = 'open';

    /**
     * The smtp username
     *
     * @var string
     */
    public $smtp_username = '';

    /**
     * The smtp password
     *
     * @var string
     */
    public $smtp_password = '';

    /**
     * specify host or leave blank to auto-detect
     *
     * @var string
     */
    public $smtp_host = '';

    /**
     * null to auto-detect, otherwise specify
     * (e.g.: 25 for open, 465 for ssl, etc.)
     *
     * @var string
     */
    public $smtp_port = null;

    /**
     * The sendmail cmd
     *
     * @var string
     */
    public $sendmail_cmd = '/usr/sbin/sendmail -bs';

    /**
     * From email...
     *
     * @var string
     */
    public $from = '';

    /**
     * From name...
     *
     * @var string
     */
    public $from_name = '';

    /**
     * To Recipients (string or array
     * key => value pairs that represent email address/name. e.g.:#
     * array('bob@google.com'=>'Bob Smith', 'joe@yahoo.com'=>'Joe Shmoe')
     * )
     *
     * @var string
     */
    public $to = array();

    /**
     * Cc Recipients (string or array
     * key => value pairs that represent email address/name. e.g.:#
     * array('bob@google.com'=>'Bob Smith', 'joe@yahoo.com'=>'Joe Shmoe')
     * )
     *
     * @var string
     */
    public $cc = array();

    /**
     * Bcc Recipients (string or array
     * key => value pairs that represent email address/name. e.g.:#
     * array('bob@google.com'=>'Bob Smith', 'joe@yahoo.com'=>'Joe Shmoe')
     * )
     *
     * @var string
     */
    public $bcc = array();

    /**
     * Startup
     *
     * @param Ncw_Controller &$controller the controller
     *
     * @return void
     */
    public function startup (Ncw_Controller &$controller)
    {
    	$controller->email = $this;
        $this->controller = &$controller;
    }

    /**
     * Connect via...
     *
     * @param string $method the method (smtp, sendmail, native)
     *
     * @return Swift_Mailer
     */
    protected function _connect ($method)
    {
        // Create the appropriate Swift mailer
        // object based upon the connection type.
        switch ($method) {
        case 'smtp':
            return $this->_connectSMTP();
        case 'sendmail':
            return $this->_connectSendmail();
        case 'native': default:
            return $this->_connectNative();
        }
    }

    /**
     * Connect via native mail
     *
     * @return Swift_Mailer
     */
    protected function _connectNative()
    {
		//Create the Transport
		$transport = Swift_MailTransport::newInstance();

		//Create the Mailer using your created Transport
		return Swift_Mailer::newInstance($transport);
    }

    /**
     * Connect via sendmail
     *
     * @return Swift_Mailer
     */
    protected function _connectSendmail()
    {
		// Create the Transport
		$transport = Swift_SendmailTransport::newInstance($this->sendmail_cmd);

		// Create the Mailer using your created Transport
		return Swift_Mailer::newInstance($transport);
    }

    /**
     * Connect via smtp
     *
     * @return Swift_Mailer
     */
    protected function _connectSMTP ()
    {
		$transport = Swift_SmtpTransport::newInstance()
		            ->setHost($this->smtp_host)
		            ->setUsername($this->smtp_username)
		            ->setPassword($this->smtp_password);

        if ($this->smtp_port !== null) {
            $transport->setPort($this->smtp_port);
        }

		if ($this->smtp_type === 'ssl' || $this->smtp_type === 'tsl') {
			$transport->setEncryption($this->smtp_type);
		}

		return Swift_Mailer::newInstance($transport);
    }

    /**
     * Gets the body text view
     *
     * @param string $view the view name
     *
     * @return string
     */
    protected function _getBodyText ($view)
    {
    	$view = $view . '_text';
        if (false === file_exists(MODULES . DS . $this->controller->module_name . DS . 'views' . DS . $this->controller->view_path . DS . $view . '.phtml')) {
            return false;
        }

        // Temporarily store vital variables used by the controller.
        $tmp_layout = $this->controller->layout;
        $tmp_layout_path = $this->controller->layout_path;
        $tmp_action = $this->controller->action;
        $tmp_output = $this->controller->output;
        $tmp_render = $this->controller->auto_render;

        // Render the plaintext email body.
        $this->controller->layout = $this->layout;
        $this->controller->layout_path = 'email' . DS . 'text';
        $this->controller->output = '';
        $body = $this->controller->render($view);

        // Restore the layout, view, output,
        // and auto_render values to the controller.
        $this->controller->layout_path = $tmp_layout_path;
        $this->controller->layout = $tmp_layout;
        $this->controller->action = $tmp_action;
        $this->controller->output = $tmp_output;
        $this->controller->auto_render = $tmp_render;

        return $body;
    }

    /**
     * Gets the body html view
     *
     * @param string $view the view name
     *
     * @return string
     */
    protected function _getBodyHTML ($view)
    {
    	$view = $view . '_html';
    	if (false === file_exists(MODULES . DS . $this->controller->module_name . DS . 'views' . DS. $this->controller->view_path . DS . $view . '.phtml')) {
            return false;
    	}

        // Temporarily store vital variables used by the controller.
        $tmp_layout = $this->controller->layout;
        $tmp_layout_path = $this->controller->layout_path;
        $tmp_action = $this->controller->action;
        $tmp_output = $this->controller->output;
        $tmp_render = $this->controller->auto_render;

        // Render the HTML email body.
        $this->controller->layout = $this->layout;
        $this->controller->layout_path = 'email' . DS . 'html';
        $this->controller->output = '';
        $body = $this->controller->render($view);

        // Restore the layout, view, output,
        // and auto render values to the controller.
        $this->controller->layout_path = $tmp_layout_path;
        $this->controller->layout = $tmp_layout;
        $this->controller->action = $tmp_action;
        $this->controller->output = $tmp_output;
        $this->controller->auto_render = $tmp_render;

        return $body;
    }

    /**
     * Send an Email
     *
     * @param string $subject   the message subject
     * @param string $method    the send method
     * @param string $view      the view name, set to false
     *      if body is given manually
     * @param mixed  $body_html set string if you want to set a html body manually
     * @param mixed  $body_text set string if you want to set a text body manually
     *
     * @return boolean
     */
    public function send ($subject = '', $method = 'smtp', $view = 'email', $body_html = false, $body_text = false)
    {
        try {
	    	// Create the message, and set the message subject.
	        $message = Swift_Message::newInstance();

	        $message->setSubject($subject);

	        $text_is_body = true;

	        // Append the HTML and plain text bodies.
	        if (false === $body_html
                && false !== ($body_html = $this->_getBodyHTML($view))
	        ) {
	        	$text_is_body = false;
	            $message->setBody(
                    $body_html,
                    'text/html'
	            );
	        } else {
	        	$text_is_body = false;
                $message->setBody($body_html, 'text/html');
	        }

	        if (false === $body_text
                && false !== ($body_text = $this->_getBodyText($view))
	        ) {
	        	if (false === $text_is_body) {
                    $message->addPart($body_text, 'text/plain');
	        	} else {
                    $message->setBody($body_text, 'text/plain');
	        	}
	        } else {
	            if (false === $text_is_body) {
                    $message->addPart($body_text, 'text/plain');
                } else {
                    $message->setBody($body_text, 'text/plain');
                }
	        }

	        // Set the from address/name.
	        $message->setFrom(array($this->from => $this->from_name));

	        // Create the recipient list.
	        $message->setTo($this->to);
	        $message->setCc($this->cc);
	        $message->setBcc($this->bcc);

	        $mailer = $this->_connect($method);

	        $result = $mailer->send($message);

	        return $result;
        } catch (Exception $e) {
        	if (DEBUG_MODE > 0) {
                throw new Ncw_Exception($e->getMessage());
        	}
        }
    }
}
?>
