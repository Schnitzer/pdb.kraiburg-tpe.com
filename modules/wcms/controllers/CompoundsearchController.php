<?php
/* SVN FILE: $Id$ */
/**
 * Contains the Compoundsearch class.
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
 * Compoundsearch class.
 *
 * @package netzcraftwerk
 */
class Wcms_CompoundsearchController extends Wcms_SitetypeController
{

    /**
     * has got no model
     *
     * @var boolean
     */
    public $has_model = false;

    /**
     * Acls publics
     *
     * @var array
     */
    public $acl_publics = array(
        'beforeWebsiteRender',
        'afterWebsiteRender',
        'searchCompounds',
        'showCompound',
    );

    /**
     * Search compounds
     *
     * @return string
     */
    public function searchCompoundsAction ($search_string)
    {
        $this->view = false;

        $compounds = $this->requestAction(
            array(
                'module' => 'tpepdb',
                'controller' => 'compound',
                'action' => 'search',
            ),
            array(
                'url' => array(
                    's' => Ncw_Library_Sanitizer::escape($search_string)
                )
            )
        );
        $html = '';
        if (count($compounds) == 1) {
        //    return '<script type="text/javascript">window.location.href="http://www.kraiburg-tpe.com/en/products/new-product-database-179?c_id=' . $compounds[0]->getId() . '";</script>';
        }
        $count = 0;
        $page = 1;
        $pagenavigation = '';
        if (count($compounds) < 1) {
        	//$html .= T_('No Data Found');
            $url = $_SERVER['REQUEST_URI'];
            $ex_url = explode('/', $url);
            if ($ex_url['1'] == 'de') {
                $html .= Wcms_ContentboxController::getContenbox('no_data_found', '2');
            } else
            if ($ex_url['1'] == 'es') {
                $html .= Wcms_ContentboxController::getContenbox('no_data_found', '4');
            } else
            if ($ex_url['1'] == 'zh') {
                $html .= Wcms_ContentboxController::getContenbox('no_data_found', '3');
            } else
            if ($ex_url['1'] == 'fr') {
                $html .= Wcms_ContentboxController::getContenbox('no_data_found', '5');
            } else
            if ($ex_url['1'] == 'jp') {
                $html .= Wcms_ContentboxController::getContenbox('no_data_found', '7');
            } else
            if ($ex_url['1'] == 'pt') {
                $html .= Wcms_ContentboxController::getContenbox('no_data_found', '9');
            } else {
                $html .= Wcms_ContentboxController::getContenbox('no_data_found', '1');
            }
            
            
            
        } else if (count($compounds) > 299) {	
            echo T_('To many results. Please specify your request.');
        } else {
        	echo T_('Compounds found') . ': ' . count($compounds);
	        foreach ($compounds as $compound) {
	        	if ( true == is_int($count / 30) ) {
	        		if ($page != 1) {
	        			$html .= '</div>';
	        			$html .= '<div id="ncw-product-page-' . $page . '" class="ncw-product-page ncw-product-page-hidden">';
	        			$pagenavigation .= '<div class="ncw-product-page-navigation-items" id="ncw-product-page-navigation-item-' . $page . '" onclick="pageinate_products(' . $page . ');">' . $page . '</div>';
	        		} else {
	                    $html .= '<div id="ncw-product-page-' . $page . '" class="ncw-product-page ncw-product-page-visible">';
	                    $pagenavigation .= '<div class="ncw-product-page-navigation-items ncw-product-page-navigation-items-active" id="ncw-product-page-navigation-item-' . $page . '" onclick="pageinate_products(' . $page . ');">' . $page . '</div>';
	        		}
	        		
	        		
	        		$page++;
	        	}
	            $html .= '<a href="?c_id=' . $compound->getId() . '">' . $compound->getName() . '</a><br />';
	            $count++;
	        }
	        $html .= '</div>';
	        // navigation bar
	        if ($page > 2) {
	            $html = '<div class="ncw-product-page-navigation">' . $pagenavigation . '</div>' . $html;
	        }
        }
        
        return $html;
    }

    /**
     * Show a compound
     *
     * @param int    $compound_id the compound id
     * @param string $language_code the language code
     *
     * @return string
     */
    public function showCompoundAction ($compound_id, $language_code, $language_id)
    {
        $this->view = false;
        $special_language = false;
        if (isset($_GET['special_language'])) {
            $special_language = $_GET['special_language'];
        }

        print $this->requestAction(
            array(
                'module' => 'tpepdb',
                'controller' => 'compound',
                'action' => 'generateHtml',
            ),
            array(
                'pass' => array(
                    $compound_id,
                    $language_code,
                    $language_id,
                    $special_language
                ),
                'form' => $this->params['form']
            )
        );
    }

    /**
     * Replaces tags in code
     *
     * @param string $code the code
     *
     * @return string
     */
    public function replaceSiteTags ($code)
    {
        $tags = array(
            '/{compoundsearch\.is_search}/',
            '/{compoundsearch\.search}/',
        );
        $replaced_tags_with = array(
            '<?php if (true === isset($_GET["tpepdb"]["search"]["name"])) { ?>',
            '<?php $controller = new Wcms_CompoundsearchController(); $controller->searchCompounds($_GET["tpepdb"]["search"]["name"]); ?>'
        );
        $code = preg_replace($tags, $replaced_tags_with, $code);
        return $code;
    }
}
?>
