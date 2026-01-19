<?php
/* SVN FILE: $Id$ */
/**
 * Contains the SoapController  class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author             Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright        Copyright 2007-2008, Netzcraftwerk UG (haftungsbeschränkt)
 * @link            http://www.netzcraftwerk.com
 * @package            netzcraftwerk
 * @since            Netzcraftwerk v 3.0.0.1
 * @version            Revision: $LastChangedRevision$
 * @modifiedby        $LastChangedBy$
 * @lastmodified    $LastChangedDate$
 * @license            http://www.netzcraftwerk.com/licenses/
 */
/**
 * SoapController  class.
 *
 * @package netzcraftwerk
 */
class Tpepdb2_SoapController extends Tpepdb2_ModuleController
{

    /**
     * @var array
     */
    public $acl_publics = array(
        "getRegions",
        "getMarkets",
        "getApplications",
        "getAdvantages",
        "getSeriesList",
        "getSeriesDetails",
        "textSearch",
        "checkLogin",
        "checkLogin2"
    );

    /**
     * Layout
     *
     * @var string
     */
    public $layout = 'blank';

    /**
     * No model used
     *
     * @var boolean
     */
    public $has_model = false;

    /**
     *
     */
    public function getRegionsAction ()
    {
        $this->view = false;

        if (false == isset($_GET["l"])) {
            return null;
        }

        $language_id = (int) $_GET["l"];

        $values = array("regions" => array());
        $values["regions"][] = array("text" => Wcms_ContentboxController::getContenbox('pdb---search---region---emea', $language_id), "id" => 1);
        $values["regions"][] = array("text" => Wcms_ContentboxController::getContenbox('pdb---search---region---americas', $language_id), "id" => 2);
        $values["regions"][] = array("text" => Wcms_ContentboxController::getContenbox('pdb---search---region---asia-pacific', $language_id), "id" => 3);

        echo json_encode($values);
    }
	
   /**
     * Gibt die Markets zurück
    */
    public function getMarketsAction ()
    {
    	
        $this->view = false;

        if (false == isset($_GET["l"])) {
            return null;
        }
        $language_id = (int) $_GET["l"];

        if (false == isset($_GET["r"])) {
            return null;
        }

        $just_count = trim(Ncw_Library_Sanitizer::clean($_GET["just_count"]));

        $region_id = (int) $_GET["r"];
        $region = "";
        if ($region_id > 0) {			
            $region = "INNER JOIN `ncw_tpepdb2_serie_region` AS sr ON sr.serie_id = s.id
            WHERE sr.region_id=:region_id";
        }

        $db = Ncw_Database::getInstance();

        $language = new Wcms_Language();
        $language->setId($language_id);
        $language = $language->readField("shortcut");

        if (false === $this->_checkIfLanguageExists("markets", $language)) {
            $language = "en";
        }

        $sth = $db->prepare(
            "SELECT DISTINCT ma." . $language . ", ma.num
            FROM `ncw_tpepdb2_markets` AS ma
            INNER JOIN `ncw_tpepdb2_serie_markets` AS sma ON sma.markets_id = ma.id
            INNER JOIN `ncw_tpepdb2_serie` AS s ON sma.serie_id = s.id
            " . $region . "
            ORDER BY ma." . $language . "
            "
        );
        if ($region_id > 0) {
            $sth->bindValue(":region_id", $region_id);
        }
        $sth->execute();

        $results = $sth->fetchAll();
        if ($just_count == 1) {
            echo count($results);
        } else {
    		$values = array("markets" => array());
            if (count($results) > 0) {
                foreach ($results as $result) {
                    $values["markets"][] = array("text" => str_replace(array("<P>", "</P>"), "", $result[$language]), "id" => $result["num"]);
                }
            } else {
                return null;
            }
    		
    		echo $this->_make_object($values);
        }
		
    }
    
    /**
     *
     */
    public function getApplicationsAction ()
    {
        $this->view = false;

        if (false == isset($_GET["l"])) {
            return null;
        }
        $language_id = (int) $_GET["l"];

        if (false == isset($_GET["r"])) {
            return null;
        }

        $just_count = trim(Ncw_Library_Sanitizer::clean($_GET["just_count"]));
        
        $region_id = (int) $_GET["r"];
        $region_join = $region_clause = "";
        if ($region_id > 0) {
            $region_join = "INNER JOIN `ncw_tpepdb2_serie_region` AS sr ON sr.serie_id = s.id";
            $region_clause = " AND sr.region_id=" . $region_id;
        }

        if (false == isset($_GET["ma"])) {
            return null;
        }

        $market = (int) $_GET["ma"];

        $db = Ncw_Database::getInstance();

        $language = new Wcms_Language();
        $language->setId($language_id);
        $language = $language->readField("shortcut");

        if (false === $this->_checkIfLanguageExists("anwendungsbereiche", $language)) {
            $language = "en";
        }

        $query = "SELECT DISTINCT aw." . $language . ", aw.num
            FROM `ncw_tpepdb2_anwendungsbereiche` AS aw
            INNER JOIN `ncw_tpepdb2_serie_anwendungsbereiche` AS saw ON saw.anwendungsbereiche_id = aw.id
            INNER JOIN `ncw_tpepdb2_serie` AS s ON saw.serie_id = s.id
            " . $region_join . "
            INNER JOIN `ncw_tpepdb2_serie_markets` AS sma ON sma.serie_id = s.id
            INNER JOIN `ncw_tpepdb2_markets` AS ma ON sma.markets_id = ma.id
            WHERE ma.num=" . $market . " " . $region_clause . "
            ORDER BY aw." . $language . "
            ";

        //echo "<br />". $query . "<br /><br />";
            
        $sth = $db->prepare(
            $query
        );

        $sth->execute();

        $results = $sth->fetchAll();
        if ($just_count == 1) {
            echo count($results);
        } else {
            $values = array("applications" => array());
            if (count($results) > 0) {
                foreach ($results as $result) {
                    $values["applications"][] = array("text" => str_replace(array("<P>", "</P>"), "", $result[$language]), "id" => $result["num"]);
                }
            } else {
                return null;
            }
    
            echo $this->_make_object($values);
        }
    }

    /**
     * Materialvorteile
     */
    public function getAdvantagesAction ()
    {
        $this->view = false;

        if (false == isset($_GET["l"])) {
            return null;
        }
        $language_id = (int) $_GET["l"];

        if (false == isset($_GET["r"])) {
            return null;
        }

        $just_count = trim(Ncw_Library_Sanitizer::clean($_GET["just_count"]));

        $region_id = (int) $_GET["r"];
        $region_join = $region_clause = "";
        if ($region_id > 0) {
            $region_join = "INNER JOIN `ncw_tpepdb2_serie_region` AS sr ON sr.serie_id = s.id";
            $region_clause = " AND sr.region_id=" . $region_id;
        }

        if (false == isset($_GET["ap"])) {
            return null;
        }
        $application = (int) $_GET["ap"];

        $db = Ncw_Database::getInstance();

        $language = new Wcms_Language();
        $language->setId($language_id);
        $language = $language->readField("shortcut");

        if (false === $this->_checkIfLanguageExists("materialvorteile", $language)) {
            $language = "en";
        }

        $query =             "SELECT DISTINCT mv." . $language . ", mv.num
            FROM `ncw_tpepdb2_materialvorteile` AS mv
            INNER JOIN `ncw_tpepdb2_serie_materialvorteile` AS smv ON smv.materialvorteile_id = mv.id
            INNER JOIN `ncw_tpepdb2_serie` AS s ON smv.serie_id = s.id
            " . $region_join . "
            INNER JOIN `ncw_tpepdb2_serie_anwendungsbereiche` AS saw ON saw.serie_id = s.id
            INNER JOIN `ncw_tpepdb2_anwendungsbereiche` AS aw ON saw.anwendungsbereiche_id = aw.id
            WHERE aw.num = " . $application . " " . $region_clause . "
            ORDER BY mv." . $language . "
            ";

        //echo $query;

        $sth = $db->prepare(
            $query
        );

        $sth->execute();

        $results = $sth->fetchAll();
        
        if ($just_count == 1) {
            echo count($results);
        } else {
    		$values = array("advantages" => array());
            foreach ($results as $result) {
                if ($result[$language] == NULL) {
                    continue;
                }
                $values["advantages"][] = array("text" => str_replace(array("<P>", "</P>"), "", $result[$language]), "id" => $result["num"]);
            }
    
            echo $this->_make_object($values);
        }
    }

     
    /**
	 * Gibt eine Liste von Serien zurück 
	 */
    public function getSeriesListAction ()
    {
        // searchViaProperties
        $this->view = false;
      	
        if (false == isset($_GET["l"])) {
            return null;
        }
        if (false == isset($_GET["r"])) {
            return null;
        }

        foreach ($_GET As $unit => $value) {  
			$$unit = trim(Ncw_Library_Sanitizer::clean($value));
		}
		
		$language_id = (int) $l;
		
		$str_where = '';

		// Wenn es Märkte
		$str_innerjoin_ma = "";
		if (true == isset($ma)) {
			if ($ma > 0) {
    			$str_innerjoin_ma = '			
    			LEFT JOIN `ncw_tpepdb2_serie_markets` AS sma
    			ON sma.serie_id = s.id		
                INNER JOIN `ncw_tpepdb2_markets` AS ma
                ON sma.markets_id = ma.id  	
    			';
    			$str_where .= " AND ma.num=" . $ma;
            }
		}
				
		// Wenn es Materialvorteile gibt
		$str_innerjoin_mv = "";
		if (true == isset($mv)) {
			if ($mv > 0) {
    			$str_innerjoin_mv = '			
    			LEFT JOIN `ncw_tpepdb2_serie_materialvorteile` AS smv
    			ON smv.serie_id = s.id
                INNER JOIN `ncw_tpepdb2_materialvorteile` AS mv
                ON smv.materialvorteile_id = mv.id	
    			';
    			$str_where .= " AND mv.num=" . $mv;
            }
		}
		
		// Wenn es $anwendungsbereiche_id gibt
		$str_innerjoin_anwendungsbereiche = "";
		if (true == isset($anwendungsbereiche_id)) {
			if ($anwendungsbereiche_id > 0) {
    			$str_innerjoin_anwendungsbereiche = '			
    			LEFT JOIN `ncw_tpepdb2_serie_anwendungsbereiche` AS sa
    			ON sa.serie_id = s.id
                INNER JOIN `ncw_tpepdb2_anwendungsbereiche` AS a
                ON sa.anwendungsbereiche_id = a.id	
    			';
    			$str_where .= " AND a.num=" . $anwendungsbereiche_id;
            }
		}
		
        if (strlen($username) > 0) {
            $login = $this->_checkLogin($username, $password);
        }
        //if (false == $login) {
            $str_wherelogin_series .= " AND sr.region_id=" . $r;
        //}

    	$obj_model = new Tpepdb2_Serie();
 
 
         $sql = "
            SELECT DISTINCT 
            s.id,
            s.name,
            sv.text1 description_short,
            sv.description description,
            brand.name brandname
            
            FROM `ncw_tpepdb2_serie` AS s
            
            INNER JOIN `ncw_tpepdb2_serie_values` AS sv 
            ON sv.serie_id = s.id
            
            LEFT JOIN ncw_tpepdb2_serie_region As sr
            ON s.id = sr.serie_id
            
            INNER JOIN `ncw_wcms_language` As lang
            ON lang.shortcut = sv.language
            
			INNER JOIN ncw_tpepdb2_brand As brand
			ON brand.id = s.brand_id
            
            "
            . $str_innerjoin_brands
            . $str_innerjoin_ma
            . $str_innerjoin_mv 
            . $str_innerjoin_anwendungsbereiche
            . "
            
            WHERE lang.id = " . $l . "
            
            " . $str_where . "
            " . $str_wherelogin_series . "
            
           
            LIMIT 0,1000
            
            ";
	    
		//echo '<br />' . $sql . '<br /><br /><br />';		
	
        $query = $obj_model->db->prepare($sql);
		$query->execute();
        $searchresult = $query->fetchAll();
 
        if ($just_count == '1') {
            echo count($searchresult);
        } else {
            if (count($searchresult) > 0) {
                
                $values = array("series" => array());
                foreach ($searchresult as $result) {
    
                    $values["series"][] = array (
                        "id"=>$result['id'],
                        "name"=>$result['name'],
                        "description_short" => str_replace(array("<P>", "</P>"), "", $result['description_short']),
                        "description" => str_replace(array("<P>", "</P>"), "", $result['description']),
                        "brand" => $result['brandname']
                    );
                }
            }
            echo $this->_make_object($values);  
        }
    }

    /**
     * Gibt eine Liste von Serien zurück 
     */
    public function getSeriesDetailsAction ()
    {
        $this->view = false;
        
        if (false == isset($_GET["l"])) {
            return null;
        }
        if (false == isset($_GET["sid"])) {
            return null;
        }

        foreach ($_GET As $unit => $value) {  
            $$unit = trim(Ncw_Library_Sanitizer::clean($value));
        }
        

        
        $language_id = (int) $l;
        $sid = (int) $sid;
        
        $str_where = " AND s.id=" . $sid;

        // Wenn es Märkte
        $str_innerjoin_ma = "";
        if (true == isset($mas)) {
            
            $str_innerjoin_ma = '           
            INNER JOIN `ncw_tpepdb2_serie_markets` AS ma
            ON ma.serie_id = s.id           
            ';
            //$str_where .= " AND ma.markets_id=" . $ma;
        }
        
        
        $obj_model = new Tpepdb2_Serie();
 
 
         $sql = "
            SELECT DISTINCT 
            s.id,
            s.name,
            sv.text1 description_short,
            sv.description description,
            brand.name brandname,
            lang.shortcut langshortcut
            
            FROM `ncw_tpepdb2_serie` AS s
            
            INNER JOIN `ncw_tpepdb2_serie_values` AS sv 
            ON sv.serie_id = s.id
            
            INNER JOIN `ncw_wcms_language` As lang
            ON lang.shortcut = sv.language
            
            INNER JOIN ncw_tpepdb2_brand As brand
            ON brand.id = s.brand_id
            
            "
            . $str_innerjoin_ma
            .
            "
            
            WHERE lang.id = " . $l . "
            
            " . $str_where . "
            
           
            LIMIT 0,100
            
            ";
        
        //echo '<br />' . $sql . '<br /><br /><br />';      
        
        $query = $obj_model->db->prepare($sql);
        $query->execute();
        $searchresult = $query->fetchAll();
 
        if (count($searchresult) > 0) {
            
            $values = array("series" => array());
            foreach ($searchresult as $result) {
                // Auslesen der Compound dieser Serie        
                
                $arr_compounds = $this->_getSeriesCompounds($sid, $language_id, $result['langshortcut']);
                
                $arr_languages = array('en', 'de', 'fr', 'es', 'pt', 'it', 'zh', 'kr', 'jp');
                $arr_datashets = array();
                foreach ($arr_languages As $language) {
                    $arr_datashets[$language] = 'http://www.kraiburg-tpe.com/tpepdb/pdf?sid=' . $sid . '&l=' . $language;
                }
                
                // Serienwerte 
                $values["series"][] = array (
                    "id"=>$result['id'],
                    "name"=>$result['name'],
                    "description_short" => str_replace(array("<P>", "</P>"), "", $result['description_short']),
                    "description" => str_replace(array("<P>", "</P>"), "", $result['description']),
                    "brand" => $result['brandname'],
                    "datasheet_url" => $arr_datashets,
                    "compounds" => $arr_compounds
                );
            }
        }
        echo $this->_make_object($values);
            
    }
    
    /**
	 * Gibt eine Liste von Materialen aus
     * Wenn Verknüpft mit Serie und allen anderen Bestandteilen
	 */
	public function textSearchAction ()
	{
		$this->view = false;
		
      	
        if (false == isset($_GET["l"])) {
            return null;
        }

        foreach ($_GET As $unit => $value) {  
			$$unit = trim(Ncw_Library_Sanitizer::clean($value));
		}
        
        $login = false;
        if (strlen($username) > 0) {
            $login = $this->_checkLogin($username, $password);
            //echo $username . $password;
        }
		
        $str_text = urldecode($str_text);
        $str_text = str_replace('&#45;', '-', $str_text);
        $language_id = (int) $l;
        if ($r != 'all') {
          $region_id = (int) $r;
			  }
		    $str_wherelogin = '';
        $str_wherelogin_series = '';
        	
        if (false == $login) {
        	  if ($r == 0) {
              $str_wherelogin .= " AND c.status = 'portfolio' ";
              
            } else {
              $str_wherelogin .= " AND c.status = 'portfolio' AND sr.region_id =" . $region_id;
              $str_wherelogin_series .= " AND sr.region_id=" . $region_id;
            }
        }
		//echo $str_where;
		


		$obj_model = new Tpepdb2_Compound();
        
        $column_order = array(
            "103" => 0,
            "106" => 1,
            "104" => 2,
            "101" => 3,
            "102" => 4,
            "77" => 5,
            "242" => 6
        );
        $column_count = 0;
        
        $obj_model = new Tpepdb2_Compound();
 
        // Auslesen der Labels
        $sqlLabel = "
            SELECT DISTINCT 
            
            label.attribute,
            label.translation,
            lang.shortcut
            
            FROM `ncw_tpepdb2_label` AS label

            INNER JOIN `ncw_wcms_language` As lang
            ON lang.shortcut = label.language
 
            WHERE 
            
            lang.id = " . $language_id . "
            
            AND label.type = 'compound'
            
            ";
            
            $query = $obj_model->db->prepare($sqlLabel);
            $query->execute();
            $searchresult = $query->fetchAll();
            
            $arr_labels = array();
            $lang_shortcut = '';
            if (count($searchresult) > 0) {
                $values = array("compounds" => array());
                foreach ($searchresult as $result) {
                    $arr_labels[$result['attribute']] = $result['translation'];
                    $lang_shortcut = $result['shortcut'];
                }
            }
            
        if (true == isset($debug)) {
            if ($debug == 1 || $debug == 3) {
                echo $lang_shortcut;
                echo '<br />' . $sqlLabel . '<br /><br /><br />';
            }
        }
        
        $sql = "
            SELECT DISTINCT 
            s.id s_id,
            s.name s_name,
            sv.text1 sv_description_short,
            sv.description sv_description,
            brand.name brandname
            
            FROM ncw_tpepdb2_serie As s
            
            INNER JOIN `ncw_tpepdb2_serie_values` AS sv 
            ON sv.serie_id = s.id
            
            LEFT JOIN ncw_tpepdb2_serie_region As sr
            ON s.id = sr.serie_id
            
            INNER JOIN ncw_tpepdb2_brand As brand
            ON brand.id = s.brand_id

            INNER JOIN `ncw_wcms_language` As lang
            ON lang.shortcut = sv.language
            
            WHERE 
            lang.id = " . $language_id . "            
            AND sv.language = '" . $lang_shortcut . "'
            AND s.name LIKE '%" . $str_text . "%'
            
            " . $str_wherelogin_series . "
            
            ";
        
        if (true == isset($debug)) {
            if ($debug == 2 || $debug == 3) {
                echo '<br />' . $sql . '<br /><br /><br />';  
            }
        }
    
        $query = $obj_model->db->prepare($sql);
        $query->execute();
        $searchresult = $query->fetchAll();
        
        if (count($searchresult) > 0) {
            $values = array('compounds' => array(), 'series' => array());
            $arr_languages = array('en', 'de', 'fr', 'es', 'pt', 'it', 'zh', 'kr', 'jp');
            $arr_datashets = array();
            foreach ($arr_languages As $language) {
                $arr_datashets[$language] = 'http://www.kraiburg-tpe.com/tpepdb/pdf?l=' . $language . '&sid=' . $result['s_id'];
            }
            foreach ($searchresult as $result) {
                $values["series"][] = array (
                    "id"=>$result['s_id'],
                    "name"=>$result['s_name'],
                    "description_short" => str_replace(array("<P>", "</P>"), "", $result['sv_description_short']),
                    "description" => str_replace(array("<P>", "</P>"), "", $result['sv_description']),
                    "brand" => $result['brandname'],
                    "datasheet_url" => $arr_datashets,
                );
            }

            
            echo $this->_make_object($values);
            return true;
        } 
            
        $max_results = 101;
        // Der Suchquery / Auslesen der Compoundwerte und Value
        $sql = "
            SELECT DISTINCT 
            
            c.id c_id,
            c.name c_name,
            s.id s_id,
            s.name s_name,
            sv.text1 sv_description_short,
            sv.description sv_description,
            brand.name brandname,
            c.safetydata safetydata,
            cv.srs_farbe_db,
            cv.103,
            cv.103_norm,
            cv.103_unit,
            cv.106,
            cv.106_norm,
            cv.106_unit,
            cv.104,
            cv.104_norm,
            cv.104_unit,
            cv.101,
            cv.101_norm,
            cv.101_unit,
            cv.102,
            cv.102_norm,
            cv.102_unit,
            cv.77,
            cv.77_norm,
            cv.77_unit,
            cv.242,
            cv.242_norm,
            cv.242_unit
            
            FROM `ncw_tpepdb2_compound` AS c
            
            INNER JOIN `ncw_tpepdb2_compound_values` AS cv 
            ON cv.compound_id = c.id

            LEFT JOIN ncw_tpepdb2_serie As s
            ON s.id = c.serie_id
            
            LEFT JOIN `ncw_tpepdb2_serie_values` AS sv 
            ON sv.serie_id = s.id AND sv.language = '" . $lang_shortcut . "'
            
            LEFT JOIN ncw_tpepdb2_brand As brand
            ON brand.id = s.brand_id
            
            LEFT JOIN ncw_tpepdb2_serie_region As sr
            ON s.id = sr.serie_id
            
            LEFT JOIN ncw_tpepdb2_serie_materialvorteile As smv
            ON s.id = smv.serie_id
            
            LEFT JOIN ncw_tpepdb2_materialvorteile As mv
            ON mv.id = smv.materialvorteile_id
            
            LEFT JOIN ncw_tpepdb2_serie_anwendungsbereiche As sawb
            ON s.id = sawb.serie_id
            
            LEFT JOIN ncw_tpepdb2_anwendungsbereiche As awb
            ON awb.id = sawb.anwendungsbereiche_id
            
            LEFT JOIN ncw_tpepdb2_serie_markets As sma
            ON s.id = sma.serie_id
            
            LEFT JOIN ncw_tpepdb2_markets As ma
            ON awb.id = sma.markets_id
                        
            INNER JOIN `ncw_wcms_language` As lang
            ON lang.shortcut = cv.language
 
            WHERE 
            lang.id = " . $language_id . "
            AND cv.language = '" . $lang_shortcut . "'
            
            
            AND (
                c.name LIKE '%" . $str_text . "%'
            
                OR s.name LIKE '%" . $str_text . "%'
                
                OR mv.".$lang_shortcut." LIKE '%" . $str_text . "%'
                
                OR awb.".$lang_shortcut." LIKE '%" . $str_text . "%'
                
                OR ma.".$lang_shortcut." LIKE '%" . $str_text . "%'
            )
            
            " . $str_wherelogin . "
            
            ORDER by s.name
           
            LIMIT 0, " . $max_results . "
            
            ";
        
        if (true == isset($debug)) {
            if ($debug == 2 || $debug == 3) {
                echo '<br />' . $sql . '<br /><br /><br />';  
            }
        }
    
        $query = $obj_model->db->prepare($sql);
        $query->execute();
        $searchresult = $query->fetchAll();
        
        if ($just_count == 1) {
            echo count($searchresult);
        } else {
            if (count($searchresult) > 0 && count($searchresult) < $max_results) {
                $values = array('compounds' => array(), 'series' => array());
                $s_id_tmp = 0;
                foreach ($searchresult as $result) {
                    // Auslesen der Compound dieser Serie
                    //if ($result['s_id'] < 1) {
                        
                        $arr_languages = array('en', 'de', 'fr', 'es', 'pt', 'it', 'zh', 'kr', 'jp');
                        $arr_datashets = array();
                        foreach ($arr_languages As $language) {
                            $arr_datashets[$language] = 'http://www.kraiburg-tpe.com/tpepdb/pdf?l=' . $language . '&cid=' . $result['c_id'] . '&sessid=' . session_id();
                        }
                        // Compoundwerte 
                        $values['compounds'][] = array (
                            "compound_id"=> $result['c_id'],
                            "compound_name"=> $result['c_name'],
                            "serie_id"=> $result['s_id'],
                            "serie_name"=> $result['s_name'],
                            "safetydata"=> $result['safetydata'],
                            "safetydata_url" => "http://www.kraiburg-tpe.com/tpepdb/safetydata?s=" . $result['safetydata'] . "&l=" .$lang_shortcut . "&t=" . $result['c_name'] . "&r=",
                            "safetydata_arr" => $this->_getSafetydataArray($result['c_name'], $result['safetydata'], $language_id),
                            "related_documents_arr" => $this->_getRelatedDocuments($result['c_id']),                        
                            "serie_datasheet_url" => 'http://www.kraiburg-tpe.com/tpepdb/pdf?&sid=' . $result['s_id'] . '&l=' . $lang_shortcut,
                            "datasheet_url" => $arr_datashets,
                            "color" => $result['srs_farbe_db'],
                            "103" => array('value' => $result['103'], 'unit' => $result['103_unit'], 'norm' => $result['103_norm'], 'label' => $arr_labels['103']),
                            "106" => array('value' => $result['106'], 'unit' => $result['106_unit'], 'norm' => $result['106_norm'], 'label' => $arr_labels['106']),
                            "104" => array('value' => $result['104'], 'unit' => $result['104_unit'], 'norm' => $result['104_norm'], 'label' => $arr_labels['104']),
                            "101" => array('value' => $result['101'], 'unit' => $result['101_unit'], 'norm' => $result['101_norm'], 'label' => $arr_labels['101']),
                            "102" => array('value' => $result['102'], 'unit' => $result['102_unit'], 'norm' => $result['102_norm'], 'label' => $arr_labels['102']),
                            "77" => array('value' => $result['77'], 'unit' => $result['77_unit'], 'norm' => $result['77_norm'], 'label' => $arr_labels['77']),
                            "242" => array('value' => $result['242'], 'unit' => $result['242_unit'], 'norm' => $result['242_norm'], 'label' => $arr_labels['242'])
                            
                        );
                    //}
                    // Serienwerte 
                    if ($s_id_tmp != $result['s_id']) {
                        
                        $arr_languages = array('en', 'de', 'fr', 'es', 'pt', 'it', 'zh', 'kr', 'jp');
                        $arr_datashets = array();
                        foreach ($arr_languages As $language) {
                            $arr_datashets[$language] = 'http://www.kraiburg-tpe.com/tpepdb/pdf?sid=' . $result['s_id'] . '&l=' . $language;
                        }
                
                        
                        $values["series"][] = array (
                            "id"=>$result['s_id'],
                            "name"=>$result['s_name'],
                            "description_short" => str_replace(array("<P>", "</P>"), "", $result['sv_description_short']),
                            "description" => str_replace(array("<P>", "</P>"), "", $result['sv_description']),
                            "brand" => $result['brandname'],
                            "datasheet_url" => $arr_datashets,
                        );
                        $s_id_tmp = $result['s_id'];
                    }
                }
            } else if (count($searchresult) > $max_results -1) {
                $values = array('warning' => 'to_many_results');
            }
    		echo $this->_make_object($values);
		} // End else Just Count
		
	}




    /**
     * Prüft ob eine Username Passwortkombination vorhanden ist
     * wenn ja dann true zurück User anmelden
     */
    public function checkLoginAction ()
    {
        
        if (false == isset($_GET["username"])) {
            return null;
        }
        if (false == isset($_GET["password"])) {
            return null;
        }


        foreach ($_GET As $unit => $value) {  
            $$unit = trim(Ncw_Library_Sanitizer::clean($value));
        }

        $login = $this->_checkLogin($username, $password);
        
        if ($login == true) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    
    private function _checkLogin($username, $password)
    {
        $obj_user = Core_UserController::validateLogin($username, $password);
        if (true === $obj_user instanceof Core_User) {
            Core_UserController::login($obj_user);

            $usergroup_user = new Core_UsergroupUser();
            $usergroup = $usergroup_user->fetch(
                'list',
                array(
                    'fields' => array('Usergroup.name'),
                    'conditions' => array(
                        'User.id' => $obj_user->getId(),
                        'Usergroup.parent_id' => 20
                    ),
                    'LIMIT' => 1
                )
            );

            $usr_role = array_pop(explode(';', array_pop($usergroup)));

            Ncw_Components_Session::writeInAll(
                'PDB',
                array(
                    'USRID' => $obj_user->getId(),
                    'USRNAME' => $obj_user->getName(),
                    'ROLE' => $usr_role,
                    'LANG' => $language_code,
                )
            );
            
            return true;
            
        }
        return false;
    }
	
    /**
     * Liest alle Compounds einer Serie aus
     * Gibt alle Werte 
     */
    protected function _getSeriesCompounds ($sid, $language, $language_shortcut)
    {
        $obj_model = new Tpepdb2_Serie();
 
        $language_id = (int) $language;
        $sid = (int) $sid;
 
        $column_order = array(
            "103" => 0,
            "106" => 1,
            "104" => 2,
            "101" => 3,
            "102" => 4,
            "77" => 5,
            "242" => 6
        );
        $column_count = 0;
        
        $obj_model = new Tpepdb2_Compound();
 
        // Auslesen der Labels
        $sqlLabel = "
            SELECT DISTINCT 
            
            label.attribute,
            label.translation
            
            FROM `ncw_tpepdb2_label` AS label

 
            WHERE 
            
            label.language = '" . $language_shortcut . "'
            
            AND label.type = 'compound'
            
            ";
 
        //echo '<br />' . $sqlLabel . '<br /><br /><br />';  
 
        $query = $obj_model->db->prepare($sqlLabel);
        $query->execute();
        $searchresult = $query->fetchAll();
        
        $arr_labels = array();
        if (count($searchresult) > 0) {
            $values = array("compounds" => array());
            foreach ($searchresult as $result) {
                $arr_labels[$result['attribute']] = $result['translation'];
            }
        }
        
        //var_dump($arr_labels);
        
        // Der Suchquery / Auslesen der Compoundwerte und Value
        $sql = "
            SELECT DISTINCT 
            
            c.id,
            c.name name,
            c.safetydata safetydata,
            cv.srs_farbe_db,
            cv.103,
            cv.103_norm,
            cv.103_unit,
            cv.106,
            cv.106_norm,
            cv.106_unit,
            cv.104,
            cv.104_norm,
            cv.104_unit,
            cv.101,
            cv.101_norm,
            cv.101_unit,
            cv.102,
            cv.102_norm,
            cv.102_unit,
            cv.77,
            cv.77_norm,
            cv.77_unit,
            cv.242,
            cv.242_norm,
            cv.242_unit
            
            FROM `ncw_tpepdb2_compound` AS c
            
            INNER JOIN `ncw_tpepdb2_compound_values` AS cv 
            ON cv.compound_id = c.id
           
            
            INNER JOIN `ncw_wcms_language` As lang
            ON lang.shortcut = cv.language
 
            WHERE 
            
            lang.id = " . $language_id . "
            
            AND c.serie_id = " . $sid . "
            
            
           
            LIMIT 0,100
            
            ";
        
        //echo '<br />' . $sql . '<br /><br /><br />';  
    
        $query = $obj_model->db->prepare($sql);
        $query->execute();
        $searchresult = $query->fetchAll();
        
        if (count($searchresult) > 0) {
            
            $values = array();
            foreach ($searchresult as $result) {
                // Auslesen der Compound dieser Serie        
                $arr_languages = array('en', 'de', 'fr', 'es', 'pt', 'it', 'zh', 'kr', 'jp');
                $arr_datashets = array();
                foreach ($arr_languages As $language) {
                    $arr_datashets[$language] = 'http://www.kraiburg-tpe.com/tpepdb/pdf?cid=' . $result['id'] . '&l=' . $language;
                }
                
                $arr_safetydata = array('en', 'de', 'fr', 'es', 'pt', 'it', 'zh', 'kr', 'jp');
                
                // Serienwerte 
                $values[] = array (
                    "id"=> $result['id'],
                    "name"=> $result['name'],
                    "safetydata"=> $result['safetydata'],
                    "safetydata_arr" => $this->_getSafetydataArray($result['name'], $result['safetydata'], $language_id), 
                    "safetydata_url" => "http://www.kraiburg-tpe.com/tpepdb/safetydata?s=" . $result['safetydata'] . "&l=" .$lang_shortcut . "&t=" . $result['c_name'] . "&r=1",
                    "related_documents_arr" => $this->_getRelatedDocuments($result['id']),
                    "datasheet_url" => $arr_datashets,
                    "color" => $result['srs_farbe_db'],
                    "103" => array('value' => $result['103'], 'unit' => $result['103_unit'], 'norm' => $result['103_norm'], 'label' => $arr_labels['103']),
                    "106" => array('value' => $result['106'], 'unit' => $result['106_unit'], 'norm' => $result['106_norm'], 'label' => $arr_labels['106']),
                    "104" => array('value' => $result['104'], 'unit' => $result['104_unit'], 'norm' => $result['104_norm'], 'label' => $arr_labels['104']),
                    "101" => array('value' => $result['101'], 'unit' => $result['101_unit'], 'norm' => $result['101_norm'], 'label' => $arr_labels['101']),
                    "102" => array('value' => $result['102'], 'unit' => $result['102_unit'], 'norm' => $result['102_norm'], 'label' => $arr_labels['102']),
                    "77" => array('value' => $result['77'], 'unit' => $result['77_unit'], 'norm' => $result['77_norm'], 'label' => $arr_labels['77']),
                    "242" => array('value' => $result['242'], 'unit' => $result['242_unit'], 'norm' => $result['242_norm'], 'label' => $arr_labels['242'])
                    
                );
            }
        }
        
        return $values;
    }

    /**
     * @param string $type
     * @param string $language
     */
    protected function _checkIfLanguageExists ($type, $language)
    {
        $db = Ncw_Database::getInstance();

        $sth = $db->prepare(
            "SELECT ma." . $language . "
            FROM `ncw_tpepdb2_" . $type . "` AS ma
            ORDER BY ma." . $language . " DESC
            LIMIT 1
            "
        );
        $sth->execute();

        $result = $sth->fetch();
        if (true === isset($result[$language])) {
            return true;
        } else {
            return false;
        }
    }
	
    
    private function _getSafetydataArray($compound, $safetydata, $language_id)
    {

        $str_compoundname_for_url = '';
        if (true == isset($compound["name"])) {
            $str_compoundname_for_url = str_replace('-', '_', $compound); 
        }

                        
        $sds_lang = array("en", "us", "de", "asia", "fr", "zh", "it", "kr",  "es", "jp");
        $arr_return = array();
        foreach ($sds_lang as $sds_item) {
            $sds_path = ASSETS . DS . "tpepdb2" . DS ."safetydata" . DS . $safetydata . "_" . $sds_item . ".pdf";
            $sds_path = Ncw_Configure::read('Project.url') . "/tpepdb/safetydata?s=" . $safetydata . "&l=" . $sds_item . "&t=" . $str_compoundname_for_url;

            $str_region = ' EMEA ';
            $str_language_addon = '';
            if ($sds_item == 'us') {
                $str_region = ' Americas ';
            }

            if ($sds_item == 'asia' || $sds_item == 'zh' || $sds_item == 'kr'  || $sds_item == 'jp') {
                $str_region = ' Asia ';
            }

            if ($sds_item == 'asia') {
                $str_language_addon = ' English ';
            }
            
            $str_label =  Wcms_ContentboxController::getContenbox('pdb---datasheet---safety-data-sheet', $language_id) . $str_region . ' (' . $sds_item . '' . $str_language_addon . ')';
            $str_label = trim(str_replace('asia English', 'en', $str_label));

            $arr_return[] = array('url' => $sds_path, 'label' => $str_label);
        }
        return $arr_return;
    }

    /**
     * 
     */
    private function _getRelatedDocuments($compound_id, $language_id = 0)
    {
        $obj_compound_document = new Tpepdb2_CompoundDocument();
        $arr_compound_document = $obj_compound_document->fetch('all', array('conditions' => array('compound_id' => $compound_id)));
        
        $arr_return = array();
        
        if (count($arr_compound_document) > 0) {
            foreach ($arr_compound_document As $compounddocument) {
                $obj_document = new Tpepdb2_Document();
                $obj_document->unbindModel('all');
                $obj_document->setId($compounddocument->getDocumentId());
                $obj_document->read();
                $document_label = $obj_document->getName();
                $document_label = explode("_", $document_label);
                $document_label = strtoupper($document_label[3]);
                if ($document_label == "DER") {
                    $document_label = "CSTB Homologation";
                }
                if (true == strstr($obj_document->getName(), '.pdf')) {
                    $arr_return[] = array('url' => 'http://www.kraiburg-tpe.com/assets/tpepdb2/imported_documents/' .$obj_document->getName(), 'label' => $document_label);
                }
            }
        }
        
        $obj_compound = new Tpepdb2_Compound();
        $obj_compound->setId($compound_id);
        $obj_compound->read();
        if ($obj_compound->getSerieId() > 0) {
            $obj_compound_document = new Tpepdb2_SerieDocument();
            $arr_compound_document = $obj_compound_document->fetch('all', array('conditions' => array('serie_id' => $obj_compound->getSerieId())));
            
            if (count($arr_compound_document) > 0) {
                foreach ($arr_compound_document As $compounddocument) {
                    $obj_document = new Tpepdb2_Document();
                    $obj_document->unbindModel('all');
                    $obj_document->setId($compounddocument->getDocumentId());
                    $obj_document->read();
                    $document_label = $obj_document->getName();
                    $document_label = explode("_", $document_label);
                    $document_label = strtoupper($document_label[2]);
                    if ($document_label == "DER") {
                        $document_label = "CSTB Homologation";
                    }
                    if (true == strstr($obj_document->getName(), '.pdf')) {
                        $arr_return[] = array('url' => 'http://www.kraiburg-tpe.com/assets/tpepdb2/imported_documents/' .$obj_document->getName(), 'label' => $document_label);
                    }
                }
            }
        }
        
        
        return $arr_return;
    }
    
	/**
	 * Macht aus einem Array ein Objekt und gibt alles Json Encoded zurück
	 */
	protected function _make_object($array)
	{
        //print json_encode($values);
		$object = new stdClass();

		foreach ($array as $key => $value)
		{
		    $object->$key = $value;
		}

		return json_encode($object);
	}
	
    /**
     * hat der User das Recht die Non Portfolio Compounds zu sehen
     * gibt true zurück wenn der user das Recht hat
     * false wenn nicht
     */
    public static function _rights ()
    {
        $user = Ncw_Components_Session::readInAll('user');
        $obj_acl = new Ncw_Components_Acl();

        $obj_acl->read($user['id'], '');

        if (false === $obj_acl->check('/tpepdb2/non_portfolio')) {
            return false;
        } else {
            return true;
        }
    }
	
}
?>
