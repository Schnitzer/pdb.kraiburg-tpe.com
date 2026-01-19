<?php
/**
 * contains the Text helper
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
 * The Text class is used to make some wicked stuff with text.
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Helpers_Text extends Ncw_Helper
{

    /**
     * Startup
     *
     * @param Ncw_View &$view the view
     *
     * @return void
     */
    public function startup (Ncw_View &$view)
    {
        $view->text = $this;
    }

    /**
     * Cleans the given string so you can
     * use it in a url path.
     *
     * @param string $string    the string
     * @param string $separator (optional)
     *
     * @return string
     */
    public function cleanForUrl ($string, $separator = "-")
    {
        // Replace &.
        $string = str_replace("&", "", $string);
        // Replace double whitespaces
        $string = str_replace("  ", " ", $string);
        // Replace the whitespaces by the seperator
        $string = str_replace(" ", $separator, $string);
        // Replace the accents.
        $string = self::replaceAccents($string);
        // upper to lower chars
        $string = strtolower($string);
        // If the filter_var is available
        if (true === function_exists("filter_var")) {
            // Remove all not allowed chars.
            $string = filter_var($string, FILTER_SANITIZE_URL);
        }
        return $string;
    }

    /**
     * Replaces all accents in a string.
     *
     * @param string $string the string
     *
     * @todo zum laufen bringen
     *
     * @return string
     */
    public function replaceAccents ($string)
    {
        // Remove the accents
        return strtr(
            $string,
            "ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ",
            "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy"
        );
    }

    /**
     * Highlights a given phrase in a text. You can specify any expression in highlighter that
     * may include the \1 expression to include the $phrase found.
     *
     * @param string $text Text to search the phrase in
     * @param string $phrase The phrase that will be searched
     * @param string $highlighter The piece of html with that the phrase will be highlighted
     * @param boolean $consider_html If true, will ignore any HTML tags, ensuring that only the correct text is highlighted
     *
     * @return string The highlighted text
     */
    public function highlight ($text, $phrase, $highlighter = '<span class="highlight">\1</span>', $consider_html = false)
    {
        if (true === empty($phrase)) {
            return $text;
        }

        if (true === is_array($phrase)) {
            $replace = array();
            $with = array();

            foreach ($phrase as $key => $value) {
                $key = $value;
                $value = $highlighter;
                $key = '(' . $key . ')';
                if (true === $consider_html) {
                    $key = '(?![^<]+>)' . $key . '(?![^<]+>)';
                }
                $replace[] = '|' . $key . '|iu';
                $with[] = empty($value) ? $highlighter : $value;
            }

            return preg_replace($replace, $with, $text);
        } else {
            $phrase = '(' . $phrase . ')';
            if (true === $consider_html) {
                $phrase = '(?![^<]+>)' . $phrase . '(?![^<]+>)';
            }

            return preg_replace('|'.$phrase.'|iu', $highlighter, $text);
        }
    }

    /**
     * Strips given text of all links (<a href=....)
     *
     * @param string $text Text
     * @return string The text without links
     * @access public
    */
    public function stripLinks ($text)
    {
        return preg_replace('|<a\s+[^>]+>|im', '', preg_replace('|<\/a>|im', '', $text));
    }

    /**
     * Adds links (<a href=....) to a given text, by finding text that begins with
     * strings like http:// and ftp://.
     *
     * @param string $text Text to add links to
     * @param array $htmlOptions Array of HTML options.
     * @return string The text with links
     * @access public
     */
    public function autoLinkUrls ($text, $htmlOptions = array())
    {
        $options = 'array(';
        foreach ($htmlOptions as $option => $value) {
                $value = var_export($value, true);
                $options .= "'$option' => $value, ";
        }
        $options .= ')';

        $text = preg_replace_callback('#(?<!href="|">)((?:http|https|ftp|nntp)://[^ <]+)#i', create_function('$matches',
            '$Html = new HtmlHelper(); $Html->tags = $Html->loadConfig(); return $Html->link($matches[0], $matches[0],' . $options . ');'), $text);

        return preg_replace_callback('#(?<!href="|">)(?<!http://|https://|ftp://|nntp://)(www\.[^\n\%\ <]+[^<\n\%\,\.\ <])(?<!\))#i',
            create_function('$matches', '$Html = new HtmlHelper(); $Html->tags = $Html->loadConfig(); return $Html->link($matches[0], "http://" . strtolower($matches[0]),' . $options . ');'), $text);
    }

    /**
     * Adds email links (<a href="mailto:....) to a given text.
     *
     * @param string $text Text
     * @param array $htmlOptions Array of HTML options.
     * @return string The text with links
     * @access public
     */
    public function autoLinkEmails ($text, $htmlOptions = array())
    {
        $options = 'array(';

        foreach ($htmlOptions as $option => $value) {
            $options .= "'$option' => '$value', ";
        }
        $options .= ')';

        return preg_replace_callback('#([_A-Za-z0-9+-]+(?:\.[_A-Za-z0-9+-]+)*@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*)#',
                        create_function('$matches', '$Html = new HtmlHelper(); $Html->tags = $Html->loadConfig(); return $Html->link($matches[0], "mailto:" . $matches[0],' . $options . ');'), $text);
    }

    /**
     * Convert all links and email adresses to HTML links.
     *
     * @param string $text Text
     * @param array $htmlOptions Array of HTML options.
     *
     * @return string The text with links
     */
    public function autoLink ($text, $htmlOptions = array())
    {
        return $this->autoLinkEmails($this->autoLinkUrls($text, $htmlOptions), $htmlOptions);
    }

    /**
     * Cuts the given string to the wanted length.
     * The string will be cut off clean, thats means
     * a last word which fits into the length will be
     * shown full.
     *
     * @param string  $text the string
     * @param string  $length the string length
     * @param string  $ending the string ending
     * @param boolean $exact
     * @param boolean $consider_html
     *
     * @return string
     */
    public function truncate ($text, $length = 100, $ending = '...', $exact = true, $consider_html = false)
    {
        if (true === is_array($ending)) {
            extract($ending);
        }
        if (true === $consider_html) {
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen($ending);
            $openTags = array();
            $truncate = '';
            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                        $pos = array_search($closeTag[1], $openTags);
                        if ($pos !== false) {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }
                $truncate .= $tag[1];

                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
                if ($contentLength + $totalLength > $length) {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                $left--;
                                $entitiesLength += mb_strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
                    break;
                } else {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if ($totalLength >= $length) {
                    break;
                }
            }

        } else {
            if (mb_strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = mb_substr($text, 0, $length - strlen($ending));
            }
        }
        if (!$exact) {
            $spacepos = mb_strrpos($truncate, ' ');
            if (isset($spacepos)) {
                if (true === $consider_html) {
                    $bits = mb_substr($truncate, $spacepos);
                    preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                    if (!empty($droppedTags)) {
                        foreach ($droppedTags as $closingTag) {
                            if (!in_array($closingTag[1], $openTags)) {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    }
                }
                $truncate = mb_substr($truncate, 0, $spacepos);
            }
        }

        $truncate .= $ending;

        if (true === $consider_html) {
            foreach ($openTags as $tag) {
                $truncate .= '</'.$tag.'>';
            }
        }

        return $truncate;
    }

    /**
     * Alias for truncate().
     *
     * @return mixed
     */
    public function trim ()
    {
        $args = func_get_args();
        return call_user_func_array(array(&$this, 'truncate'), $args);
    }

    /**
     * Extracts an excerpt from the text surrounding the phrase with a number of characters on each side determined by radius.
     *
     * @param string $text String to search the phrase in
     * @param string $phrase Phrase that will be searched for
     * @param integer $radius The amount of characters that will be returned on each side of the founded phrase
     * @param string $ending Ending that will be appended
     *
     * @return string Modified string
     */
    public function excerpt ($text, $phrase, $radius = 100, $ending = "...")
    {
        if (empty($text) or empty($phrase)) {
            return $this->truncate($text, $radius * 2, $ending);
        }

        $phraseLen = strlen($phrase);
        if ($radius < $phraseLen) {
            $radius = $phraseLen;
        }

        $pos = strpos(strtolower($text), strtolower($phrase));

        $startPos = 0;
        if ($pos > $radius) {
            $startPos = $pos - $radius;
        }

        $textLen = strlen($text);

        $endPos = $pos + $phraseLen + $radius;
        if ($endPos >= $textLen) {
            $endPos = $textLen;
        }

        $excerpt = substr($text, $startPos, $endPos - $startPos);
        if ($startPos != 0) {
            $excerpt = substr_replace($excerpt, $ending, 0, $phraseLen);
        }

        if ($endPos != $textLen) {
            $excerpt = substr_replace($excerpt, $ending, -$phraseLen);
        }

        return $excerpt;
    }

    /**
     * Creates a comma separated list where the last two items are joined with 'and', forming natural English
     *
     * @param array $list The list to be joined
     *
     * @return string
     */
    public function toList($list, $and = 'and')
    {
        $return = '';
        $count = count($list) - 1;
        $counter = 0;
        foreach ($list as $i => $item) {
            $return .= $item;
            if ($count > 0 && $counter < $count) {
                $return .= ($counter < $count - 1 ? ', ' : " {$and} ");
            }
            $counter++;
        }
        return $return;
    }
}
?>
