<?php
/**
 * contains the Javascript helper class
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * This file is a file from cakephp. It was copied from Netzcraftwerk and restructured for our purposes
 * Redistributions of cakephp files must retain the following copyright notice.
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
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
 * Javascript helper class.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Helper
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Helpers_Ajax extends Ncw_Helper
{

    /**
     * View object
     *
     * @var Ncw_View
     */
    protected $_view = null;

    /**
     * Names of AJAX options.
     *
     * @var array
     */
    public $ajax_options = array(
        'update', 'callback', 'confirm'
    );

    /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    public function startup (Ncw_View &$view)
    {
        $this->_view =& $view;
        $view->ajax = $this;
    }

    /**
     * Returns link to remote action
     *
     * Returns a link to a remote action defined by <i>options[url]</i>
     * (using the url() format) that's called in the background using
     * XMLHttpRequest. The result of that request can then be inserted into a
     * DOM object whose id can be specified with <i>options[update]</i>.
     *
     * Examples:
     * <code>
     *  link("Delete this post",
     * array("update" => "posts", "url" => "delete/{$postid->id}"));
     *  link(imageTag("refresh"),
     *      array("update" => "emails", "url" => "list_emails" ));
     * </code>
     *
     * By default, these remote requests are processed asynchronous during
     * which various callbacks can be triggered (for progress indicators and
     * the likes).
     *
     * Example:
     * <code>
     *  link (word,
     *      array("url" => "undo", "n" => word_counter),
     *      array("complete" => "undoRequestCompleted(request)"));
     * </code>
     *
     * The callbacks that may be specified are:
     *
     * - <i>loading</i>::       Called when the remote document is being
     *                          loaded with data by the browser.
     * - <i>loaded</i>::        Called when the browser has finished loading
     *                          the remote document.
     * - <i>interactive</i>::   Called when the user can interact with the
     *                          remote document, even though it has not
     *                          finished loading.
     * - <i>complete</i>:: Called when the XMLHttpRequest is complete.
     *
     * If you for some reason or another need synchronous processing (that'll
     * block the browser while the request is happening), you can specify
     * <i>options[type] = synchronous</i>.
     *
     * You can customize further browser side call logic by passing
     * in Javascript code snippets via some optional parameters. In
     * their order of use these are:
     *
     * - <i>confirm</i>:: Adds confirmation dialog.
     * -<i>condition</i>::  Perform remote request conditionally
     *                      by this expression. Use this to
     *                      describe browser-side conditions when
     *                      request should not be initiated.
     * - <i>before</i>::        Called before request is initiated.
     * - <i>after</i>::     Called immediately after request was
     *                      initiated and before <i>loading</i>.
     *
     * @param string $title Title of link
     * @param string $href Href string "/products/view/12"
     * @param array $options        Options for JavaScript function
     * @param string $confirm       Confirmation message. Calls up a JavaScript confirm() message.
     * @param boolean $escapeTitle  Escaping the title string to HTML entities
     *
     * @return string               HTML code for link to remote action
     */
    public function link ($title, $href = null, $options = array(), $confirm = null, $escape_title = true)
    {
        if (false === isset($href)) {
            $href = $title;
        }
        if (false === isset($options['url'])) {
            $options['url'] = $href;
        }

        if (true === isset($confirm)) {
            $options['confirm'] = $confirm;
            unset($confirm);
        }
        $html_options = $this->__getHtmlOptions($options, array('url'));

        $html_defaults = array('id' => 'link' . intval(mt_rand()), 'onclick' => '');
        $html_options = array_merge($html_defaults, $html_options);

        $html_options['onclick'] .= ' event.returnValue = false; return false;';
        $return = $this->_view->html->link($title, $href, $html_options, false, $escape_title);
        $callback = $this->remoteFunction($options);
        $script = $this->_view->javascript->event("#{$html_options['id']}", "click", $callback);

        if (true === is_string($script)) {
            $return .= $script;
        }
        return $return;
    }

    /**
     * Creates JavaScript function for remote AJAX call
     *
     * This function creates the javascript needed to make a remote call
     * it is primarily used as a helper for AjaxHelper::link.
     *
     * @param array $options options for javascript
     * @return string html code for link to remote action
     *
     * @see AjaxHelper::link() for docs on options parameter.
     */
    public function remoteFunction ($options)
    {
        if (true === isset($options['update'])) {
            $func = "$('{$options['update']}').load(";
        } else {
            $func = "$.get(";
        }

        $func .= "'" . $this->url(isset($options['url']) ? $options['url'] : "") . "'";

        if (true === isset($options['callback'])) {
            $func .= ", function(event) {" . $options['callback'] . "}";
        }
        $func .= ")";

        if (true === isset($options['confirm'])) {
            $func = "if (confirm('" . $this->_view->javascript->escapeString($options['confirm'])
                . "')) { $func; } else { event.returnValue = false; return false; }";
        }
        return $func;
    }

    /**
     * Detects Ajax requests
     *
     * @return boolean True if the current request is a Prototype Ajax update call
     */
    public function isAjax ()
    {
        return (true === isset($this->_view->params['isAjax']) && true === $this->_view->params['isAjax']);
    }

    /**
     * Private Method to return a string of html options
     * option data as a JavaScript options hash.
     *
     * @param array $options    Options in the shape of keys and values
     * @param array $extra  Array of legal keys in this options context
     * @return array Array of html options
     *
     * @access private
     */
    private function __getHtmlOptions ($options, $extra = array())
    {
        foreach (array_merge($this->ajax_options, $extra) as $key) {
            if (true === isset($options[$key])) {
                unset($options[$key]);
            }
        }
        return $options;
    }
}

?>