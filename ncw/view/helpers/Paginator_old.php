<?php
/**
 * contains the Paginator helper
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
 * @subpackage Library
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
 * The Paginator class is used to make some wicked stuff with text.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Helpers_Paginator extends Ncw_Helper
{

    /**
     * The current action
     *
     * @var string
     */
    protected $_action = '';

    /**
     * The params
     *
     * @var array
     */
    protected $params = array();

    /**
     * Holds the default model for paged recordsets
     *
     * @var string
     */
    protected $_default_model = null;

    /**
     * The HTML helper
     *
     * @var Ncw_Helper_Html
     */
    protected $_html = null;

    /**
     * The Ajax helpr
     *
     * @var Ncw_Helper_Ajax
     */
    protected $_ajax = null;

    /**
     * Holds the default options for pagination links
     *
     * The values that may be specified are:
     *
     *  - `$options['format']` Format of the counter. Supported formats are 'range' and 'pages'
     *    and custom (default). In the default mode the supplied string is parsed and constants are replaced
     *    by their actual values.
     *    Constants: %page%, %pages%, %current%, %count%, %start%, %end% .
     *  - `$options['separator']` The separator of the actual page and number of pages (default: ' of ').
     *  - `$options['url']` Url of the action. See Router::url()
     *  - `$options['url']['sort']`  the key that the recordset is sorted.
     *  - `$options['url']['direction']` Direction of the sorting (default: 'asc').
     *  - `$options['url']['page']` Page # to display.
     *  - `$options['model']` The name of the model.
     *  - `$options['escape']` Defines if the title field for the link should be escaped (default: true).
     *  - `$options['update']` DOM id of the element updated with the results of the AJAX call.
     *     If this key isn't specified Paginator will use plain HTML links.
     *  - `$options['indicator']` DOM id of the element that will be shown when doing AJAX requests.
     *
     * @var array
     */
    public $options = array();

    /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    public function startup (Ncw_View &$view)
    {
        $this->params = $view->controller->params;
        $this->_default_model = $view->controller->name;
        $this->_action = $view->controller->action;
        $this->_html = $view->html;
        $this->_ajax = $view->ajax;
        $view->paginator = $this;
    }

    /**
     * Gets the current paging parameters from the resultset for the given model
     *
     * @param string $model Optional model name.  Uses the default if none is specified.
     *
     * @return array The array of paging parameters for the paginated resultset.
     */
    public function params ($model = null)
    {
        if (empty($model)) {
            $model = $this->defaultModel();
        }
        if (false === isset($this->params['paging'])
            || true === empty($this->params['paging'][$model])
        ) {
            return null;
        }
        return $this->params['paging'][$model];
    }

    /**
     * Sets default options for all pagination links
     *
     * @param mixed $options Default options for pagination links. If a string is supplied - it
     * is used as the DOM id element to update. See #options for list of keys.
     */
    public function options ($options = array())
    {
        if (true === is_string($options)) {
            $options = array('update' => $options);
        }

        if (false === empty($options['paging'])) {
            if (true === isset($this->params['paging'])) {
                $this->params['paging'] = array();
            }
            $this->params['paging'] = array_merge(
                $this->params['paging'],
                $options['paging']
            );
            unset($options['paging']);
        }
        $model = $this->defaultModel();

        if (false === empty($options[$model])) {
            if (true === isset($this->params['paging'][$model])) {
                $this->params['paging'][$model] = array();
            }
            $this->params['paging'][$model] = array_merge(
                $this->params['paging'][$model],
                $options[$model]
            );
            unset($options[$model]);
        }
        $this->options = array_filter(array_merge($this->options, $options));
    }

    /**
     * Gets the current page of the recordset for the given model
     *
     * @param string $model Optional model name.  Uses the default if none is specified.
     *
     * @return string The current page number of the recordset.
     */
    public function current ($model = null)
    {
        $params = $this->params($model);

        if (true === isset($params['page'])) {
            return $params['page'];
        }
        return 1;
    }

    /**
     * Gets the current key by which the recordset is sorted
     *
     * @param string $model Optional model name.  Uses the default if none is specified.
     * @param mixed $options Options for pagination links. See #options for list of keys.
     *
     * @return string The name of the key by which the recordset is being sorted, or
     *  null if the results are not currently sorted.
     */
    public function sortKey ($model = null, $options = array())
    {
        if (true === empty($options)) {
            $params = $this->params($model);
            $options = array_merge($params['defaults'], $params['options']);
        }

        if (true === isset($options['sort']) && false === empty($options['sort'])) {
            if (preg_match('/(?:\w+\.)?(\w+)/', $options['sort'], $result) && isset($result[1])) {
                if ($result[0] == $this->defaultModel()) {
                    return $result[1];
                }
            }
            return $options['sort'];
        } elseif (true === isset($options['order']) && true === is_array($options['order'])) {
            return key($options['order']);
        } elseif (true === isset($options['order']) && true === is_string($options['order'])) {
            if (true == preg_match('/(?:\w+\.)?(\w+)/', $options['order'], $result) && isset($result[1])) {
                return $result[1];
            }
            return $options['order'];
        }
        return null;
    }

    /**
     * Gets the current direction the recordset is sorted
     *
     * @param string $model Optional model name.  Uses the default if none is specified.
     * @param mixed $options Options for pagination links. See #options for list of keys.
     *
     * @return string The direction by which the recordset is being sorted, or
     *  null if the results are not currently sorted.
     */
    public function sortDir ($model = null, $options = array())
    {
        $dir = null;

        if (true === empty($options)) {
            $params = $this->params($model);
            $options = array_merge($params['defaults'], $params['options']);
        }

        if (true === isset($options['direction'])) {
            $dir = strtolower($options['direction']);
        } elseif (true === isset($options['order'])
            && true === is_array($options['order'])
        ) {
            $dir = strtolower(current($options['order']));
        }

        if ($dir == 'desc') {
            return 'desc';
        }
        return 'asc';
    }

    /**
     * Generates a "previous" link for a set of paged records
     *
     * Options:
     *
     * - `tag` The tag wrapping tag you want to use, defaults to 'span'
     * - `escape` Whether you want the contents html entity encoded, defaults to true
     * - `model` The model to use, defaults to PaginatorHelper::defaultModel()
     *
     * @param  string $title Title for the link. Defaults to '<< Previous'.
     * @param  mixed $options Options for pagination link. See #options for list of keys.
     * @param  string $disabled_title Title when the link is disabled.
     * @param  mixed $disabled_$disabled_optionstitle Options for the disabled pagination link. See #options for list of keys.
     *
     * @return string A "previous" link or $disabledTitle text if the link is disabled.
     */
    public function prev ($title = '<< Previous', $options = array(), $disabled_title = null, $disabled_options = array())
    {
        return $this->__pagingLink('Prev', $title, $options, $disabled_title, $disabled_options);
    }

    /**
     * Generates a "next" link for a set of paged records
     *
     * Options:
     *
     * - `tag` The tag wrapping tag you want to use, defaults to 'span'
     * - `escape` Whether you want the contents html entity encoded, defaults to true
     * - `model` The model to use, defaults to PaginatorHelper::defaultModel()
     *
     * @param string $title Title for the link. Defaults to 'Next >>'.
     * @param mixed $options Options for pagination link. See above for list of keys.
     * @param string $disabled_title Title when the link is disabled.
     * @param mixed $disabled_options Options for the disabled pagination link. See above for list of keys.
     *
     * @return string A "next" link or or $disabledTitle text if the link is disabled.
     */
    public function next ($title = 'Next >>', $options = array(), $disabled_title = null, $disabled_options = array())
    {
        return $this->__pagingLink('Next', $title, $options, $disabled_title, $disabled_options);
    }

    /**
     * Generates a sorting link. Sets named parameters for the sort and direction.  Handles
     * direction switching automatically.
     *
     * Options:
     *
     * - `escape` Whether you want the contents html entity encoded, defaults to true
     * - `model` The model to use, defaults to PaginatorHelper::defaultModel()
     *
     * @param string $title Title for the link.
     * @param string $key The name of the key that the recordset should be sorted.  If $key is null
     *   $title will be used for the key, and a title will be generated by inflection.
     * @param array $options Options for sorting link. See above for list of keys.
     *
     * @return string A link sorting default by 'asc'. If the resultset is sorted 'asc' by the specified
     *  key the returned link will sort by 'desc'.
     */
    public function sort ($title, $key = null, $options = array())
    {
        $options = array_merge(array('url' => array(), 'model' => null), $options);
        $url = $options['url'];
        unset($options['url']);

        if (true === empty($key)) {
            $key = $title;
        }
        $dir = 'asc';
        $sort_key = $this->sortKey($options['model']);
        $is_sorted = ($sort_key === $key || $sort_key === $this->defaultModel() . '.' . $key);

        if (true === $is_sorted
            && $this->sortDir($options['model']) === 'asc'
        ) {
            $dir = 'desc';
        }

        if (true === is_array($title)
            && true === array_key_exists($dir, $title)
        ) {
            $title = $title[$dir];
        }

        $url = array_merge(
            array('sort' => $key, 'direction' => $dir),
            $url,
            array('order' => null)
        );
        return $this->link($title, $url, $options);
    }

    /**
     * Generates a plain or Ajax link with pagination parameters
     *
     * Options
     *
     * - `update` The Id of the DOM element you wish to update.  Creates Ajax enabled links
     *    with the AjaxHelper.
     * - `escape` Whether you want the contents html entity encoded, defaults to true
     * - `model` The model to use, defaults to PaginatorHelper::defaultModel()
     *
     * @param string $title Title for the link.
     * @param mixed $url Url for the action. See Router::url()
     * @param array $options Options for the link. See #options for list of keys.
     *
     * @return string A link with pagination parameters.
     */
    public function link ($title, $url = array(), $options = array())
    {
        $options = array_merge(array('model' => null, 'escape' => true), $options);
        $model = $options['model'];
        unset($options['model']);

        $url['action'] = $this->_action;

        if (false === empty($this->options)) {
            $options = array_merge($this->options, $options);
        }
        if (true === isset($options['url'])) {
            $url = array_merge((array) $options['url'], (array) $url);
            unset($options['url']);
        }
        $url = $this->url($url, $model);

        $obj = isset($options['update']) ? 'ajax' : 'html';
        $url = array_merge(array('page' => $this->current($model)), $url);

        return $this->{'_' . $obj}->link(
            $title,
            $url,
            $options
        );
    }

    /**
     * Merges passed URL options with current pagination state to generate a pagination URL.
     *
     * @param array $options Pagination/URL options array
     * @param boolean $asArray Return the url as an array, or a URI string
     * @param string $model Which model to paginate on
     *
     * @return mixed By default, returns a full pagination URL string for use in non-standard contexts (i.e. JavaScript)
     */
    public function url ($options = array(), $model = null)
    {
        $paging = $this->params($model);
        $url = array_merge($paging['options'], $options);

        if (true === isset($url['order'])) {
            $sort = $direction = null;
            if (true === is_array($url['order'])) {
                list($sort, $direction) = array(
                    $this->sortKey($model, $url),
                    current($url['order'])
                );
            }
            unset($url['order']);
            $url = array_merge(
                $url,
                array('sort' => $sort, 'direction' => $direction)
            );
        }

        return $url;
    }

    /**
     * Protected method for generating prev/next links
     *
     */
    private function __pagingLink ($which, $title = null, $options = array(), $disabled_title = null, $disabled_options = array())
    {
        $check = 'has' . $which;
        $_defaults = array('url' => array(), 'step' => 1, 'escape' => true, 'model' => null, 'tag' => 'div');
        $options = array_merge($_defaults, (array) $options);
        $paging = $this->params($options['model']);

        if (false === $this->{$check}($options['model'])
            && (false === empty($disabled_title)
            || false === empty($disabled_options))
        ) {
            if (false === empty($disabled_title) && true !== $disabled_title) {
                $title = $disabled_title;
            }
            $options = array_merge($_defaults, (array) $disabled_options);
        } elseif (false === $this->{$check}($options['model'])) {
            return null;
        }

        foreach (array_keys($_defaults) as $key) {
            ${$key} = $options[$key];
            unset($options[$key]);
        }
        $url = array_merge(
            array(
                'page' => $paging['page'] + ($which == 'Prev' ? $step * -1 : $step)
            ),
            $url
        );

        if ($this->{$check}($model)) {
            return $this->link(
                $title,
                $url,
                array_merge(
                    $options,
                    array('escape' => $escape)
                )
            );
        } else {
            return $this->_html->tag($tag, $title, $options, $escape);
        }
    }

    /**
     * Returns true if the given result set is not at the first page
     *
     * @param string $model Optional model name. Uses the default if none is specified.
     * @return boolean True if the result set is not at the first page.
     */
    public function hasPrev ($model = null)
    {

        return $this->__hasPage($model, 'prev');
    }

    /**
     * Returns true if the given result set is not at the last page
     *
     * @param string $model Optional model name.  Uses the default if none is specified.
     * @return boolean True if the result set is not at the last page.
     */
    public function hasNext ($model = null)
    {
        return $this->__hasPage($model, 'next');
    }

    /**
     * Returns true if the given result set has the page number given by $page
     *
     * @param string $model Optional model name.  Uses the default if none is specified.
     * @param int $page The page number - if not set defaults to 1.
     * @return boolean True if the given result set has the specified page number.
     */
    public function hasPage ($model = null, $page = 1)
    {
        if (true === is_numeric($model)) {
            $page = $model;
            $model = null;
        }
        $paging = $this->params($model);
        return $page <= $paging['pageCount'];
    }

    /**
     * Protected method
     *
     */
    private function __hasPage ($model, $page)
    {
        $params = $this->params($model);
        if (false === empty($params)) {
            if ($params["{$page]Page"] == true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the default model of the paged sets
     *
     * @return string Model name or null if the pagination isn't initialized.
     */
    public function defaultModel ()
    {
        if ($this->_default_model != null) {
            return $this->_default_model;
        }
        if (empty($this->params['paging'])) {
            return null;
        }
        list($this->_default_model) = array_keys($this->params['paging']);
        return $this->_default_model;
    }

    /**
     * Returns a counter string for the paged result set
     *
     * Options
     *
     * - `model` The model to use, defaults to PaginatorHelper::defaultModel();
     * - `format` The format string you want to use, defaults to 'pages' Which generates output like '1 of 5'
     *    set to 'range' to generate output like '1 - 3 of 13'.  Can also be set to a custom string, containing
     *    the following placeholders `%page%`, `%pages%`, `%current%`, `%count%`, `%start%`, `%end%` and any
     *    custom content you would like.
     * - `separator` The separator string to use, default to ' of '
     *
     * @param mixed $options Options for the counter string. See #options for list of keys.
     *
     * @return string Counter string.
     */
    public function counter ($options = array())
    {
        if (true === is_string($options)) {
            $options = array('format' => $options);
        }

        $options = array_merge(
            array(
                'model' => $this->defaultModel(),
                'format' => 'pages',
                'separator' => 'of'
            ),
            $options
        );

        $paging = $this->params($options['model']);
        if ($paging['pageCount'] == 0) {
            $paging['pageCount'] = 1;
        }
        $start = 0;
        if ($paging['count'] >= 1) {
            $start = (($paging['page'] - 1) * $paging['options']['limit']) + 1;
        }
        $end = $start + $paging['options']['limit'] - 1;
        if ($paging['count'] < $end) {
            $end = $paging['count'];
        }

        switch ($options['format']) {
            case 'range':
                if (false === is_array($options['separator'])) {
                    $options['separator'] = array(' - ', $options['separator']);
                }
                $out = $start . $options['separator'][0] . $end . $options['separator'][1] . $paging['count'];
            break;
            case 'pages':
                $out = $paging['page'] . $options['separator'] . $paging['pageCount'];
            break;
            default:
                $replace = array(
                    '%page%' => $paging['page'],
                    '%pages%' => $paging['pageCount'],
                    '%current%' => $paging['current'],
                    '%count%' => $paging['count'],
                    '%start%' => $start,
                    '%end%' => $end
                );
                $out = str_replace(array_keys($replace), array_values($replace), $options['format']);
            break;
        }
        return $out;
    }

    /**
     * Returns a set of numbers for the paged result set
     * uses a modulus to decide how many numbers to show on each side of the current page (default: 8)
     *
     * Options
     *
     * - `before` Content to be inserted before the numbers
     * - `after` Content to be inserted after the numbers
     * - `model` Model to create numbers for, defaults to PaginatorHelper::defaultModel()
     * - `modulus` how many numbers to include on either side of the current page, defaults to 8.
     * - `separator` Separator content defaults to ' | '
     * - `tag` The tag to wrap links in, defaults to 'span'
     * - `first` Whether you want first links generated, set to an integer to define the number of 'first'
     *    links to generate
     * - `last` Whether you want last links generated, set to an integer to define the number of 'last'
     *    links to generate
     *
     * @param mixed $options Options for the numbers, (before, after, model, modulus, separator)
     *
     * @return string numbers string.
     */
    public function numbers ($options = array())
    {
        if ($options === true) {
            $options = array(
                'before' => ' | ', 'after' => ' | ',
                'first' => 'first', 'last' => 'last',
            );
        }

        $options = array_merge(
            array(
                'tag' => 'span',
                'before'=> null, 'after'=> null,
                'model' => $this->defaultModel(),
                'modulus' => '8', 'separator' => ' | ',
                'first' => null, 'last' => null,
            ),
            (array) $options
        );

        $params = array_merge(array('page'=> 1), (array) $this->params($options['model']));
        unset($options['model']);

        if ($params['pageCount'] <= 1) {
            return false;
        }

        extract($options);
        unset($options['tag'], $options['before'], $options['after'], $options['model'],
            $options['modulus'], $options['separator'], $options['first'], $options['last']);

        $out = '';

        if ($modulus && $params['pageCount'] > $modulus) {
            $half = intval($modulus / 2);
            $end = $params['page'] + $half;

            if ($end > $params['pageCount']) {
                $end = $params['pageCount'];
            }
            $start = $params['page'] - ($modulus - ($end - $params['page']));
            if ($start <= 1) {
                $start = 1;
                $end = $params['page'] + ($modulus  - $params['page']) + 1;
            }

            if ($first && $start > 1) {
                $offset = ($start <= (int)$first) ? $start - 1 : $first;
                if ($offset < $start - 1) {
                    $out .= $this->first($offset, array('tag' => $tag, 'separator' => $separator));
                } else {
                    $out .= $this->first($offset, array('tag' => $tag, 'after' => $separator, 'separator' => $separator));
                }
            }

            $out .= $before;

            for ($i = $start; $i < $params['page']; $i++) {
                $out .= $this->_html->tag($tag, $this->link($i, array('page' => $i), $options)) . $separator;
            }

            $out .= $this->_html->tag($tag, $params['page'], array('class' => 'current'));
            if ($i != $params['pageCount']) {
                $out .= $separator;
            }

            $start = $params['page'] + 1;
            for ($i = $start; $i < $end; $i++) {
                $out .= $this->_html->tag($tag, $this->link($i, array('page' => $i), $options)). $separator;
            }

            if ($end != $params['page']) {
                $out .= $this->_html->tag($tag, $this->link($i, array('page' => $end), $options));
            }

            $out .= $after;

            if ($last && $end < $params['pageCount']) {
                $offset = ($params['pageCount'] < $end + (int)$last) ? $params['pageCount'] - $end : $last;
                if ($offset <= $last && $params['pageCount'] - $end > $offset) {
                    $out .= $this->last($offset, array('tag' => $tag, 'separator' => $separator));
                } else {
                    $out .= $this->last($offset, array('tag' => $tag, 'before' => $separator, 'separator' => $separator));
                }
            }

        } else {
            $out .= $before;

            for ($i = 1; $i <= $params['pageCount']; $i++) {
                if ($i == $params['page']) {
                    $out .= $this->_html->tag($tag, $i, array('class' => 'current'));
                } else {
                    $out .= $this->_html->tag($tag, $this->link($i, array('page' => $i), $options));
                }
                if ($i != $params['pageCount']) {
                    $out .= $separator;
                }
            }

            $out .= $after;
        }

        return $out;
    }

    /**
     * Returns a first or set of numbers for the first pages
     *
     * Options:
     *
     * - `tag` The tag wrapping tag you want to use, defaults to 'span'
     * - `before` Content to insert before the link/tag
     * - `model` The model to use defaults to PaginatorHelper::defaultModel()
     * - `separator` Content between the generated links, defaults to ' | '
     *
     * @param mixed $first if string use as label for the link, if numeric print page numbers
     * @param mixed $options
     *
     * @return string numbers string.
     */
    public function first ($first = '<< first', $options = array())
    {
        $options = array_merge(
            array(
                'tag' => 'span',
                'after'=> null,
                'model' => $this->defaultModel(),
                'separator' => ' | ',
            ),
            (array) $options
        );

        $params = array_merge(
            array('page'=> 1),
            (array) $this->params($options['model'])
        );
        unset($options['model']);

        if ($params['pageCount'] <= 1) {
            return false;
        }
        extract($options);
        unset($options['tag'], $options['after'], $options['model'], $options['separator']);

        $out = '';

        if (true === is_int($first) && $params['page'] > $first) {
            if ($after === null) {
                $after = '...';
            }
            for ($i = 1; $i <= $first; $i++) {
                $out .= $this->_html->tag($tag, $this->link($i, array('page' => 1), $options));
                if ($i != $first) {
                    $out .= $separator;
                }
            }
            $out .= $after;
        } elseif ($params['page'] > 1) {
            $out = $this->_html->tag($tag, $this->link($first, array('page' => 1), $options)) . $after;
        }
        return $out;
    }

    /**
     * Returns a last or set of numbers for the last pages
     *
     * Options:
     *
     * - `tag` The tag wrapping tag you want to use, defaults to 'span'
     * - `before` Content to insert before the link/tag
     * - `model` The model to use defaults to PaginatorHelper::defaultModel()
     * - `separator` Content between the generated links, defaults to ' | '
     *
     * @param mixed $last if string use as label for the link, if numeric print page numbers
     * @param mixed $options Array of options
     *
     * @return string numbers string.
     */
    public function last ($last = 'last >>', $options = array())
    {
        $options = array_merge(
            array(
                'tag' => 'span',
                'before'=> null,
                'model' => $this->defaultModel(),
                'separator' => ' | ',
            ),
            (array) $options
        );

        $params = array_merge(array('page'=> 1), (array) $this->params($options['model']));
        unset($options['model']);

        if ($params['pageCount'] <= 1) {
            return false;
        }

        extract($options);
        unset($options['tag'], $options['before'], $options['model'], $options['separator']);

        $out = '';
        $lower = $params['pageCount'] - $last + 1;

        if (true === is_int($last) && $params['page'] < $lower) {
            if ($before === null) {
                $before = '...';
            }
            for ($i = $lower; $i <= $params['pageCount']; $i++) {
                $link_url['page'] = $i;
                $out .= $this->_html->tag($tag, $this->link($i, array('page' => $id), $options));
                if ($i != $params['pageCount']) {
                    $out .= $separator;
                }
            }
            $out = $before . $out;
        } elseif ($params['page'] < $params['pageCount']) {
            $out = $before . $this->_html->tag($tag, $this->link($last, array('page' => $params['pageCount']), $options));
        }
        return $out;
    }
}
?>
