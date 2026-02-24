<?php

require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlBrand.php';
require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlSerie.php';
require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlCompound.php';

class TpePdbXmlFile {

    /**
     * @var string
     */
    protected $_file = "";

    /**
     * @var int
     */
    protected $_file_timestamp = 0;

    /**
     * @var bool
     */
    protected $_is_portfolio = false;

    /**
     * @var SimpleXMLElement
     */
    protected $_loaded_xml_file = null;

    /**
     * @var int
     */
    protected $_import_time = 0;

    /**
     * @param string $file
     */
    public function __construct ($file, $file_timestamp)
    {
        $this->_file = (string) $file;
        $this->_file_timestamp = $file_timestamp;

        $file_parts = explode("_", str_replace(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS, "", $file));
        if ($file_parts[0] == "PORTFOLIO") {
            $this->_is_portfolio = true;
        } else {
            $this->_is_portfolio = false;
        }
    }



    /**
     * @return int
     */
    protected function _importBrand ()
    {
        if (false === $this->_is_portfolio) {
            $structure = "CATALOG/NODES/NODE";
        } else {
            $structure = TpePdbXmlImporter::$BRAND_XML_NODE_STRUCTURE;
        }

        $brand = new TpePdbXmlBrand(
            $this->_parseXmlElement(
                explode("/", $structure)
            ),
            $this->_is_portfolio
        );
        return $brand->import($this->_import_time);
    }
  
    public function importPDB20 ($mode)
    {
      if ($mode == 'fussnoten') {
        $this->_importFussnoten();
      }
      if ($mode == 'stammpruefmerkmale') {
        $this->_importStammpruefmerkmale();
      }
      
      if ($mode == 'phrasen') {
        $this->_importPhrasen();
      }
      if ($mode == 'verarbeitungshinweise') {
        $this->_importVerarbeitungshinweise();
      }
      
      if ($mode == 'compound') {
        $this->_importCompound();
      }
			
      if ($mode == 'serie') {
        $this->_importSerie();
      }
			
    }
  	
		/*
		* Gibt nur aus . der XML Datei Compound im Head die ID also
		Portfolio oder nonportfolio zurück
		*/
		protected function _importCompoundstatus ()
		{
    	$show_query = false;
			$structure = TpePdbXmlImporter::$COMPOUND_XML_NODE_STRUCTURE;
			$xml_code = trim(file_get_contents($this->_file));
			$this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);
			$structure_array = $this->_parseXmlElement(explode("/", TpePdbXmlImporter::$COMPOUND_XML_NODE_STRUCTURE_PORTFOLIONONPORTFOLIO));
			$status = $structure_array->{'ID'}[0];
			//echo '
			//Status';
			$status = strtolower($status);
			if ($status == 'nonportfol' || $status == 'nonportfolio') {
				$status = 'non_portfolio';
			}
			return $status;
		}
	
		/* Ermittelt die hinterlegte Brand Id
			Es wird nur die 
		*/
		protected function _getBrandId($str)
		{
			$str_query_get = "SELECT * FROM `ncw_tpepdb2_brand` WHERE name='" . $str . "'";
			$obj_db = new Tpepdb2_Label();
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();
			$brand_id = 0;
			//echo $str_query_get;
			if (count($arr_get) > 0) {
			 return $arr_get[0]['id'];
			}
			$str_query_get = "SELECT * FROM `ncw_tpepdb2_brand` WHERE name_zh='" . $str . "'";
			$obj_db = new Tpepdb2_Label();
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();
			$brand_id = 0;
			//echo $str_query_get;
			if (count($arr_get) > 0) {
			 return $arr_get[0]['id'];
			}
			return false;
		}
	
	
			/* Ermittelt die serie_Id
			anhand der internal_id
		*/
		protected function _getSerieId($str)
		{
			$str_query_get = "SELECT id FROM `ncw_tpepdb2_serie` WHERE internal_id='" . $str . "'";
			$obj_db = new Tpepdb2_Label();
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();

			//echo $str_query_get;
			if (count($arr_get) > 0) {
			 return $arr_get[0]['id'];
			}
			return false;
		}
	
		/*
		* Gibt nur aus . der XML Datei Compound im Head die ID also
		Portfolio oder nonportfolio zurück
		*/
		protected function _importCompoundserieInternalNumber ()
		{
			
    	$show_query = false;
			$structure = TpePdbXmlImporter::$COMPOUND_XML_NODE_STRUCTURE;
			$xml_code = trim(file_get_contents($this->_file));
			$this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);
			$structure_array = $this->_parseXmlElement(explode("/", TpePdbXmlImporter::$COMPOUND_XML_NODE_STRUCTURE_SERIES));
			echo 'Serie_id = ' . $structure_array->{'NODE'}[0]->attributes()['ID'];
			return $structure_array->{'NODE'}[0]->attributes()['ID'];
		}
	
	
		/*
		* Übersetzt die Sprachkürzel von SAP ger in de z.B.
		*/	
		protected function _language_2stellen($language_id, $autoenglish = true)
		{
			
				if ($language_id == 'eng') {
					return 'en';
				}
				if ($language_id == 'ger') {
					return 'de';
				}
				if ($language_id == 'fre') {
					return 'fr';
				}
				if ($language_id == 'ita') {
					return 'it';
				}
				if ($language_id == 'pol') {
					return 'pl';
				}
				if ($language_id == 'por') {
					return 'pt';
				}

				if ($language_id == 'spa') {
					return 'es';
				}
				if ($language_id == 'jpn') {
					return 'jp';
				}
				if ($language_id == 'chi') {
					return 'zh';
				}
				if ($language_id == 'kor') {
					return 'kr';
				}
				if ($autoenglish == true) {
					return 'en';
				} else {
					return '';	
				}


		}
	
		/*
		* Importiert die jeweilige serie_....xml
		*/	
		protected function _importSerie ()
    {
			$show_query = false;
			$obj_db = new Tpepdb2_Label();
				
			$now = date('Y-m-d H:i:s');

			$xml_code = trim(file_get_contents($this->_file));
			$this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);
			$structure_array = $this->_parseXmlElement(explode("/", TpePdbXmlImporter::$SERIE_XML_NODE_STRUCTURE));
			
			// SAP Nummer der Serie ermitteln mit dieser werden dann die Compounds verknüpft
			$internal_serie_id = $structure_array->attributes()['ID'];
			
			$serie_name = $structure_array->{'CLASSIFICATION'}->{'ATTRIBUTE'}[0]->{'VALUE'}[0]->{'VALUE_TEXTS'}[0]->{'VALUE_TEXT'}[0];
			// Liese aus der XML Datei nur die erste Zeile der Brandbezeichnungen aus (in der Regel fra) dieser Ausdruck wird dann verglichen und die Brand ID in der DB gespeichert
			$brand_name = $structure_array->{'CLASSIFICATION'}->{'ATTRIBUTE'}[1]->{'VALUE'}[0]->{'VALUE_TEXTS'}[0]->{'VALUE_TEXT'}[1];
			// Brand ID ermitteln
			echo 'BrandName=' . $brand_name;
			$brand_id = $this->_getBrandId($brand_name);
			
			
			$str_query_get = "SELECT * FROM `ncw_tpepdb2_serie` WHERE internal_id='".$internal_serie_id."'";
			$obj_db = new Tpepdb2_Label();
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();


			
			$now = date('Y-m-d H:i:s');
			// Wenn es die Serie schon gibt
			if (count($arr_get) > 0) {
				// Die ID des Compound in der DB Tabelle ncw_tpepdb2_compound
				$serie_id = $arr_get[0]['id'];
				//echo $compound_id;
				$str_query = "UPDATE ncw_tpepdb2_serie SET brand_id='" . $brand_id . "', name='" . $serie_name . "',  modified='" . $now . "', last_import='" . $now . "' WHERE id='" . $serie_id . "'";
				echo $str_query;
				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();
				
			} else { // Wenn es die Serie noch nicht gibt
				$str_query = "INSERT INTO ncw_tpepdb2_serie (brand_id, name, internal_id, created, last_import, status, pdb20) VALUES ('" . $brand_id . "', '" . $serie_name . "', '" . $internal_serie_id . "','" . $now . "', '" . $now . "', 'portfolio', '1' )";
				//echo $str_query;
				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();
				
				// Serie Id herausfinden

				$str_query_get = "SELECT * FROM `ncw_tpepdb2_serie` WHERE `name` LIKE '".$serie_name."'  && pdb20='1'";
				$dbquery = $obj_db->db->prepare($str_query_get);
				$dbquery->execute();
				$arr_get = $dbquery->fetchAll();
				$serie_id = $arr_get[0]['id'];
			}
			

			// Serien kommentare schreiben
			//ncw_tpepdb2_serie_values
				
				// Löscht die alten Einträge
				$query_delete =  "DELETE  FROM `ncw_tpepdb2_serie_values` WHERE internal_id = '".$internal_serie_id."'";
				$dbquery = $obj_db->db->prepare($query_delete);
				$dbquery->execute();
				
				foreach($structure_array->{'DESCRIPTIONS_LONG'}->{'DESCRIPTION_LONG'} As $beschreibung ) {
					if ($beschreibung->attributes()['type'] == 'WLBM-0001') {
						$lang = $this->_language_2stellen($beschreibung->attributes()['lang']);
						$description1 = $beschreibung;
							$description1 = str_replace('≤', '<=', $description1);
							$description1 = str_replace("'", '′', $description1);
							$description1 = str_replace("&apos;", '′', $description1);
						
						
						$description2 = '';
						$str_query = "INSERT INTO ncw_tpepdb2_serie_values (serie_id, language, internal_id, created, description, text1) VALUES ('" . $serie_id . "', '" . $lang . "', '" . $internal_serie_id . "','" . $now . "', '" . $description1 . "', '" . $description2 . "')";
						//echo $str_query . '
						//';
						$dbquery = $obj_db->db->prepare($str_query);
						$dbquery->execute();
					}
					if ($beschreibung->attributes()['type'] == 'WLBM-ZCM1') {
						$lang = $this->_language_2stellen($beschreibung->attributes()['lang']);
						$description2 = $beschreibung;
						$description2 = str_replace("'", '′', $description2);
						$description2 = str_replace("&apos;", '′', $description2);
						$description2 = strip_tags($description2);
						
						$str_query = "UPDATE ncw_tpepdb2_serie_values SET text1='" . $description2 . "' WHERE internal_id='" . $internal_serie_id . "' && language='" . $lang . "'";
						//echo $str_query . '
						//';
						
						$dbquery = $obj_db->db->prepare($str_query);
						$dbquery->execute();
					}
					if ($beschreibung->attributes()['type'] == 'WLBM-ZCM2') {
						$lang = $this->_language_2stellen($beschreibung->attributes()['lang']);
						$description2 = $beschreibung;
						
						$description2 = str_replace("'", '′', $description2);
						$description2 = str_replace("&apos;", '′', $description2);
						$description2 = strip_tags($description2);
						
						$str_query = "UPDATE ncw_tpepdb2_serie_values SET text2='" . $description2 . "' WHERE internal_id='" . $internal_serie_id . "' && language='" . $lang . "'";
						//echo $str_query . '
						//';
						
						$dbquery = $obj_db->db->prepare($str_query);
						$dbquery->execute();
					}
					
					//echo 'DRIN';
					//TPE_RD_4000_001     Anwendungsbereiche typical applications

					
				}
			
				// REGIONEN
				$str_query = "DELETE FROM ncw_tpepdb2_serie_region WHERE serie_id='" . $serie_id . "'";
				//echo $str_query;
				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();
				foreach ($structure_array->{'CLASSIFICATION'}->{'ATTRIBUTE'} As $structure_Attributes) {
					$attribute_id = $structure_Attributes->attributes()['internal_id'];
					//echo $attribute_id;

					if ($attribute_id == 'TPE_RD_1000_010_REGION') {
						foreach($structure_Attributes->{'VALUE'} As $region) {
							$regin_str = $region;

							if ($regin_str == 'TPE-300000000004980') {
								$region_id = 1;
							}
							if ($regin_str == 'TPE-300000000004981') {
								$region_id = 2;
							}
							if ($regin_str == 'TPE-300000000004982') {
								$region_id = 3;
							}
							
							if ($regin_str == 'TPE-300000000005135') { // Europe, the Middle East and Afrika
								$region_id = 1;
							}
							if ($regin_str == 'TPE-300000000005136') { // Americas
								$region_id = 2;
							}
							if ($regin_str == 'TPE-300000000005137') { // Asia Pacific
								$region_id = 3;
							}
			
							$str_query = "INSERT INTO ncw_tpepdb2_serie_region (serie_id, region_id, created, modified) VALUES ('" . $serie_id . "', '" . $region_id . "', '" . $now . "', '" . $now . "')";
							//echo '
							//Region:' . $str_query;
							$dbquery = $obj_db->db->prepare($str_query);
							$dbquery->execute();
						}
					}
				}// End foreach Attributes

				// DOKUMENTE

			// Löschen der bisherigen Einträge			
			$str_query = "
				DELETE FROM ncw_tpepdb2_serie_document WHERE 
						serie_id='" . $serie_id . "'
						";
			$query = $obj_db->db->prepare($str_query);
			$query->execute();

			// XML Struktur Dokumente durchsuchen
      foreach ($structure_array->{'DOCUMENTS'} As $structure_DOCUMENTS) {
				
        foreach ($structure_DOCUMENTS->{'DOCUMENT'} As $document) {
				//	echo '
				//	';
					//var_dump($document);
          $type = $document->attributes()['type'];
					$lang = $document->attributes()['lang'];
					$lang = $this->_language_2stellen($lang, $autoenglish = false);
          $value = $document->{'VALUE'};
					//echo $value;
					$text = '';
					$headline = '';
					// verschiebt das Dokument in der Ordnerstruktur in den Ordner
					// assets/tpepdb2/imported_documents
					$this->_relatedDocumentSeriePDB20($value, $type, $headline, $text, $serie_id, $lang);
        }
      }
			
		}
	
    /**
     * @return int
     *IMPORT eines Compounds PDB2.0 Jun - Aug 2019
		 
     */
	  protected function _importCompound ()
    {
	

	//echo 'FILE=' . $file;

     $show_query = false;
		$structure = TpePdbXmlImporter::$COMPOUND_XML_NODE_STRUCTURE;
		$xml_code = trim(file_get_contents($this->_file));
		$this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);
		$structure_array = $this->_parseXmlElement(explode("/", TpePdbXmlImporter::$COMPOUND_XML_NODE_STRUCTURE));
        //var_dump($structure_array);
        
      // Compound Name lesen
      $material_number = $structure_array->{'MATERIAL_NUMBER'}[0];
      echo '
			' . $material_number;
			

			
      // Auslesen ob Portfolio oder non_portfolio
			$compoundstatus = $this->_importCompoundstatus();
			//echo $compoundstatus;
			$serie_id = 0;
			if ($compoundstatus == 'portfolio') {
				// Internal Number der Serie ermitteln
				$compoundserie_internal_id = $this->_importCompoundserieInternalNumber();
				// DB eigenen serie Id ermitteln diese dient zur Verknüpfung mit den Compounds
				$serie_id = $this->_getSerieId($compoundserie_internal_id);
			} else {
				$compoundserie_internal_id = '';
			}
			
			
			
			// Prüfen ob das compound schon vorhanden ist
			$str_query_get = "SELECT * FROM `ncw_tpepdb2_compound` WHERE name='".$material_number."'";
			//echo $str_query_get;
			$obj_db = new Tpepdb2_Label();
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();


			if (strtolower($compoundstatus) == 'top') {
				//$compoundstatus = 'top400';
				$compoundstatus = 'portfolio';
			}
			$now = date('Y-m-d H:i:s');
			// Wenn es den Compound schon gibt
			if (count($arr_get) > 0) {
				// Die ID des Compound in der DB Tabelle ncw_tpepdb2_compound
				$compound_id = $arr_get[0]['id'];
				//echo $compound_id;
				$str_query = "UPDATE ncw_tpepdb2_compound SET status='" . $compoundstatus . "', serie_id='" . $serie_id . "', internal_serie_id='" . $compoundserie_internal_id . "',  modified='" . $now . "', last_import='" . $now . "', pdb20='1' WHERE id='" . $compound_id . "'";
				//echo $str_query;
				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();
				
			} else { // Wenn es den Compound noch nicht gibt
				
				
				$str_query = "INSERT INTO ncw_tpepdb2_compound (status, serie_id, created, last_import, name, pdb20) VALUES ('" . $compoundstatus . "', '" . $serie_id . "', '" . $now . "', '" . $now . "', '" . $material_number . "', '1' )";
				//echo '
				//' . $str_query;
				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();
				// Compound_id herausfinden
				$str_query_get = "SELECT * FROM `ncw_tpepdb2_compound` WHERE name='".$material_number."'";
			  $dbquery = $obj_db->db->prepare($str_query_get);
				$dbquery->execute();
				$arr_get = $dbquery->fetchAll();
				$compound_id = $arr_get[0]['id'];
				
			}
			
			// Compound Description ist eigentlich nur für Top400 vorgesehen
			// könnte allerdings überall verwendet werden
			//SELECT * FROM `ncw_tpepdb2_compounddescription`
			$str_delete_description = "DELETE FROM `ncw_tpepdb2_compounddescription` WHERE compound_id=" . $compound_id;
			$dbquery = $obj_db->db->prepare($str_delete_description);
			$dbquery->execute();
			
    foreach ($structure_array->{'DESCRIPTIONS_LONG'} As $descriptions_long) {
				
        foreach ($descriptions_long->{'DESCRIPTION_LONG'} As $attribute) { // Alle Sprachversionen durchgehen
			if ($attribute->attributes()['type'] == 'WLBM-ZCM5') {
          		$lang = $attribute->attributes()['lang'];
					$str_insert_description = "INSERT INTO ncw_tpepdb2_compounddescription (compound_id, description, lang, modified) VALUES ('" . $compound_id . "', '" . trim($attribute) . "', '" . $lang . "', '" . date('Y-m-d H:i:s') . "')";

					
					$dbquery = $obj_db->db->prepare($str_insert_description);
					$dbquery->execute();
					
					//echo '
					//' . $str_insert_description;
					
			}

			if ($attribute->attributes()['type'] == 'WLBM-ZCM6') { // WLBM-ZCM6 ist der zusätzliche Text in SAP
				$lang = $attribute->attributes()['lang'];

				  // Prüfen ob der Eintrag schon vorhanden ist
				  $str_read_description400 = "SELECT * FROM ncw_tpepdb2_compoundtextextra400 WHERE compound_id = '" . $compound_id . "' AND lang = '" . $lang . "'";
				  //echo $str_read_description400;
				  $dbqueryR = $obj_db->db->prepare($str_read_description400);
				  $dbqueryR->execute();
				  $arr_get = $dbqueryR->fetchAll();
				  $isId = $arr_get[0]['id'];
				  if ($isId > 0) {
					$str_insert_description = "UPDATE ncw_tpepdb2_compoundtextextra400 SET description = '" . trim($attribute) . "', modified = '" . date('Y-m-d H:i:s') . "' WHERE id = '".$isId."'";
				  } else {
					$str_insert_description = "INSERT INTO ncw_tpepdb2_compoundtextextra400 (compound_id, description, lang, modified) VALUES ('" . $compound_id . "', '" . trim($attribute) . "', '" . $lang . "', '" . date('Y-m-d H:i:s') . "')";
				  }
				  $dbquery = $obj_db->db->prepare($str_insert_description);
				  $dbquery->execute();
				  //echo '
				  //' . $str_insert_description;
			}
		}
	}
			
      // $structure_CLASSIFICATION
	// Alle Eigenschaften des Compounds werden in der Texstpalte tags in ncw_tpepdb_compounds gesammelt gespeichert
	$str_tag_compound = array();
	
	$str_query = "DELETE FROM ncw_tpepdb2_compound_regulations WHERE compound_id='" . $compound_id . "'";
	$dbquery = $obj_db->db->prepare($str_query);
	$dbquery->execute();
	// DELETE APPROVAL
	$str_query = "DELETE FROM ncw_tpepdb2_compound_approval WHERE compound_id='" . $compound_id . "'";
	$dbquery = $obj_db->db->prepare($str_query);
	$dbquery->execute();
	
	$str_query = "DELETE FROM ncw_tpepdb2_compound_typicalapplication WHERE compound_id='" . $compound_id . "'";
	$dbquery = $obj_db->db->prepare($str_query);
	$dbquery->execute();
	
	$str_query = "DELETE FROM ncw_tpepdb2_compound_materialadvantages WHERE compound_id='" . $compound_id . "'";
	$dbquery = $obj_db->db->prepare($str_query);
	$dbquery->execute();

	$str_query = "DELETE FROM ncw_tpepdb2_compound_region WHERE compound_id='" . $compound_id . "'";
	$dbquery = $obj_db->db->prepare($str_query);
	$dbquery->execute();

	$str_query = "DELETE FROM ncw_tpepdb2_compound_region20 WHERE compound_id='" . $compound_id . "'";
	$dbquery = $obj_db->db->prepare($str_query);
	$dbquery->execute();

	$str_query = "DELETE FROM ncw_tpepdb2_compound_corigin WHERE compound_id='" . $compound_id . "'";
	$dbquery = $obj_db->db->prepare($str_query);
	$dbquery->execute();
	

	$str_query = "UPDATE ncw_tpepdb2_compound SET brandnameen='', brandnamezh='' WHERE id='" . $compound_id . "'";
	//echo $str_query;
	$dbquery = $obj_db->db->prepare($str_query);
	$dbquery->execute();

    foreach ($structure_array->{'CLASSIFICATION'} As $structure_CLASSIFICATION) {
      //  echo $structure_material_number;
        //var_dump($structure_CLASSIFICATION);
        foreach ($structure_CLASSIFICATION->{'ATTRIBUTE'} As $attribute) { // TPE_RD_1000_002_BRAND, TPE_RD_2000_005_COLOR, TPE_RD_3000_001_SDS
          $attribute_id = $attribute->attributes()['internal_id'];

			// Brand auslesen
			if ($attribute_id == 'TPE_RD_1000_002_BRAND') {
				$brand_en = '';
				$brand_zh = '';
				foreach ($attribute->{'VALUE'}->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $VALUE_TEXT) {
					$text = $VALUE_TEXT;
					$lang = $VALUE_TEXT->attributes()['lang'];
					if ($lang == 'eng') {
						$brand_en = $text;
					} else if ($lang == 'chi') {
						$brand_zh = $text;
					}
				}
						
				$str_query = "UPDATE ncw_tpepdb2_compound SET brandnameen='" . $brand_en . "', brandnamezh='" . $brand_zh . "' WHERE id='" . $compound_id . "'";
				//echo $str_query;
				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();	
			}
          
          	// Farbe 
          	if ($attribute_id == 'TPE_RD_2000_005_COLOR') {
				$str_query = "DELETE FROM ncw_tpepdb2_compound_color WHERE compound_id='" . $compound_id . "'";
				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();
				$internal_id = trim($attribute->{'VALUE'});
				//echo $internal_id;
				$str_tag_compound[] = $internal_id;
				foreach ($attribute->{'VALUE'}->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $VALUE_TEXT) {
					$text = $VALUE_TEXT;
								
					$lang = $VALUE_TEXT->attributes();
					//$lang = $this->_language_2stellen($lang);
					$str_query = "INSERT INTO ncw_tpepdb2_compound_color (compound_id, color, lang, internal_id, last_import) VALUES ('" . $compound_id . "', '" . $text . "', '" . $lang . "',  '" . $internal_id . "', '" . $now . "')";
					
					//echo '
					//' .$str_query. '
					//';
					
					$dbquery = $obj_db->db->prepare($str_query);
					$dbquery->execute();		
					//echo '
					//' . $attribute_id . ' lang=' . $lang . ' text=' . $text; 
				}
        	}
		
			if ($attribute_id == 'TPE_RD_1000_010_REGION') {

				
				foreach($attribute->{'VALUE'} As $region) {

					//var_dump($attribute->{'VALUE'});

					$regin_str = trim($region);

					if ($regin_str == 'TPE-300000000005135') { // Europe, the Middle East and Afrika
						$region_id = 1;
					}
					if ($regin_str == 'TPE-300000000005136') { // Americas
						$region_id = 2;
					}
					if ($regin_str == 'TPE-300000000005137') { // Asia Pacific
						$region_id = 3;
					}
	
					foreach ($region->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $VALUE_TEXT) {
						$text = $VALUE_TEXT;	
						$lang = $VALUE_TEXT->attributes()['lang'];
						$str_query = "INSERT INTO ncw_tpepdb2_compound_region20 (compound_id, region_id, lang, internal_id, description, created, modified) VALUES ('" . $compound_id . "', '" . $region_id . "', '" . $lang . "', '" . $regin_str . "', '" . $text . "', '" . $now . "', '" . $now . "')";
	
						//echo '
						//' .$str_query. '
						//';

						$dbquery = $obj_db->db->prepare($str_query);
						$dbquery->execute();		
					}

					//$str_query = "INSERT INTO ncw_tpepdb2_compound_region (compound_id, region_id, created, modified) VALUES ('" . $compound_id . "', '" . $region_id . "', '" . $now . "', '" . $now . "')";
					//echo '
					//Region:' . $str_query;
					//$dbquery = $obj_db->db->prepare($str_query);
					//$dbquery->execute();
				}
			}

			// Herstellungsland
			if ($attribute_id == 'TPE_RD_1000_015_C_ORIGIN') {
				foreach($attribute->{'VALUE'} As $corigin) {
					//var_dump($attribute->{'VALUE'});
					$corigin_str = trim($corigin);

					if ($corigin_str == 'TPE-300000000006815' || $corigin_str == 'TPE-300000000006870') { // Germany
						$corigin_id = 1;
					}
					if ($corigin_str == 'TPE-300000000006816' || $corigin_str == 'TPE-300000000006871') { // Malaysia
						$corigin_id = 2;
					}
					if ($corigin_str == 'TPE-300000000006817' || $corigin_str == 'TPE-300000000006872') { // USA
						$corigin_id = 3;
					}

					foreach ($corigin->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $VALUE_TEXT) {
						$text = $VALUE_TEXT;	
						$lang = $VALUE_TEXT->attributes()['lang'];
						$str_query = "INSERT INTO ncw_tpepdb2_compound_corigin (compound_id, corigin_id, lang, internal_id, description, created, modified) VALUES ('" . $compound_id . "', '" . $corigin_id . "', '" . $lang . "', '" . $corigin_str . "', '" . $text . "', '" . $now . "', '" . $now . "')";
	
						//echo '
						//' .$str_query. '
						//';

						$dbquery = $obj_db->db->prepare($str_query);
						$dbquery->execute();		
					}
				}
			}

			//TPE_RD_4000_001     Anwendungsbereiche typical applications
          	if ($attribute_id == 'TPE_RD_4000_001') {
				foreach ($attribute->{'VALUE'} As $VALUE_TEXT) {
				$text = $VALUE_TEXT;
				$sort_id = $VALUE_TEXT->attributes()['SORT_ORD'];
					//var_dump($VALUE_TEXT);
					$str_tag_compound[] = $text;
					$str_query = "INSERT INTO ncw_tpepdb2_compound_typicalapplication (compound_id, label_id, sort_id, last_import) VALUES ('" . $compound_id . "', '" . $text . "', '" . $sort_id . "', '" . $now . "')";
					$dbquery = $obj_db->db->prepare($str_query);
					$dbquery->execute();						
				}
          	}
					
			// Recyclinganteil neu hinzu am 18.09.2023
			if ($attribute_id == 'TPE_RD_2000_020_RECYCLING') {
					$str_query = "UPDATE ncw_tpepdb2_compound SET recyclinganteil = '' WHERE id ='" . $compound_id . "'";
					echo $str_query;
					$dbquery = $obj_db->db->prepare($str_query);
					$dbquery->execute();		

				foreach ($attribute->{'VALUE'} As $VALUE_TEXT) {
					$text = $VALUE_TEXT;
					$str_query = "UPDATE ncw_tpepdb2_compound SET recyclinganteil = '". $text ."' WHERE id ='" . $compound_id . "'";
					echo $str_query;
					$dbquery = $obj_db->db->prepare($str_query);
					$dbquery->execute();						
				}
          	}
					
			// Bioanteil neu hinzu am 18.09.2023
			if ($attribute_id == 'TPE_RD_2000_020_BIO') {
				foreach ($attribute->{'VALUE'} As $VALUE_TEXT) {
					$text = $VALUE_TEXT;
					$str_query = "UPDATE ncw_tpepdb2_compound SET bioanteil = '". $text ."' WHERE id ='" . $compound_id . "'";
					$dbquery = $obj_db->db->prepare($str_query);
					$dbquery->execute();						
				}
          	}

			// Polymercode neu hinzu am 10.12.2025
			if ($attribute_id == 'TPE_RD_1000_020_TPECLASS') {
				foreach ($attribute->{'VALUE'}->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $VALUE_TEXT) {
					$polytexttext = $VALUE_TEXT;
					$str_query = "UPDATE ncw_tpepdb2_compound SET polymercode = '". $polytexttext ."' WHERE id ='" . $compound_id . "'";
					$dbquery = $obj_db->db->prepare($str_query);
					$dbquery->execute();
				}
          	}

           // TPE_RD_3000_003_APPROVAL          
          if ($attribute_id == 'TPE_RD_3000_003_APPROVAL') {
            foreach ($attribute->{'VALUE'} As $VALUE_TEXT) {
              $text = $VALUE_TEXT;
              $sort_id = $VALUE_TEXT->attributes()['SORT_ORD'];
							//var_dump($VALUE_TEXT);
							$str_tag_compound[] = $text;
							$str_query = "INSERT INTO ncw_tpepdb2_compound_approval (compound_id, label_id, sort_id, last_import) VALUES ('" . $compound_id . "', '" . $text . "', '" . $sort_id . "', '" . $now . "')";
							$dbquery = $obj_db->db->prepare($str_query);
							$dbquery->execute();						
            }
          }
					
		// ncw_tpepdb2_compound_materialadvantages ??
		//TPE_RD_4000_002                Materialvorteile
          if ($attribute_id == 'TPE_RD_4000_002') {
            foreach ($attribute->{'VALUE'} As $VALUE_TEXT) {
              $text = $VALUE_TEXT;
              $sort_id = $VALUE_TEXT->attributes()['SORT_ORD'];
							//var_dump($VALUE_TEXT);
							$str_tag_compound[] = $text;
							$str_query = "INSERT INTO ncw_tpepdb2_compound_materialadvantages (compound_id, label_id, sort_id, last_import) VALUES ('" . $compound_id . "', '" . $text . "', '" . $sort_id . "', '" . $now . "')";
							$dbquery = $obj_db->db->prepare($str_query);
							$dbquery->execute();

            }
          }
					
			// // ncw_tpepdb2_compound_regulations???
			//TPE_RD_4000_003                Verordnungen
          if ($attribute_id == 'TPE_RD_4000_003') {

						
            foreach ($attribute->{'VALUE'} As $VALUE_TEXT) {
              $text = $VALUE_TEXT;
              $sort_id = $VALUE_TEXT->attributes()['SORT_ORD'];
							//var_dump($VALUE_TEXT);
							$str_tag_compound[] = $text;
							$str_query = "INSERT INTO ncw_tpepdb2_compound_regulations (compound_id, label_id, sort_id, last_import) VALUES ('" . $compound_id . "', '" . $text . "', '" . $sort_id . "', '" . $now . "')";
							//$dbquery = $obj_db->db->prepare($str_query);
							//$dbquery->execute();
            }
          }
					
          // SDS auslesen
          
          if ($attribute_id == 'TPE_RD_3000_001_SDS') {
            foreach ($attribute->{'VALUE'}->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $VALUE_TEXT) {
              $safetytext = $VALUE_TEXT;
              //$lang = $VALUE_TEXT->attributes();
              //echo '
              //' . $attribute_id . ' lang=' . $lang . ' text=' . $text; 
							$str_query = "UPDATE  ncw_tpepdb2_compound set safetydata = '" . $safetytext . "' WHERE id='" . $compound_id . "'";
					//	echo '
					//	'.$str_query;
							
							$dbquery = $obj_db->db->prepare($str_query);
							$dbquery->execute();
							break;
            }
          }
         
        }
      }
      
		$str_tags_compound = implode(';', $str_tag_compound);
		$str_query = "UPDATE ncw_tpepdb2_compound SET tags = '" . $str_tags_compound . "' WHERE id=" . $compound_id;
		$dbquery = $obj_db->db->prepare($str_query);
		$dbquery->execute();
		// VALUES
			
		// Löscht die bishereigen Einträge aus values
		$sql_delete = "DELETE FROM ncw_tpepdb2_compound_values20 WHERE compound_id =" . $compound_id;
		$dbquery = $obj_db->db->prepare($sql_delete);
		$dbquery->execute();
			
      // Hier werden jetzt die eigentlichen Werte wie Härte Dichte usw gelesen
			$controle_103 = false;
			$controle_104 = false;
			$haerte = '';
      foreach ($structure_CLASSIFICATION->{'ATTRIBUTES'} As $attributes) {

          foreach ($attributes->{'ATTRIBUTE'} As $attribute) {
						$norm_id = 0;
						$stammpruefmerkmal_id = 0;
						$sort_id = 0;
						$wert = 0;
						$einheit = '';
						$bezeichnung_id = 0;
						$footer_id = 0;
						
            foreach ($attribute->{'ATTRIBUTE'} As $attribute2) {
	              $intenal_id = $attribute2->attributes()['internal_id'];
							$wert_attribute2 = $attribute2->{'VALUE'}[0];
              //echo '
              //' . $intenal_id . '=>' . $wert_attribute2;
							if ($intenal_id == 'QPMK_NORM' ) {
								$norm_id = $attribute2->{'VALUE'}[0];
							}
							if ($intenal_id == 'QPMK_BEZEICHNUNG' ) {
								$bezeichnung_id = $attribute2->{'VALUE'}[0];
							}
							if ($intenal_id == 'QPMK_FOOTER' ) {
								$footer_id = $attribute2->{'VALUE'}[0];
							}
							
							

							
						}
						

              
						$stammpruefmerkmal_id = $attribute->attributes()['internal_id'];
						$sort_id = $attribute->attributes()['sort_id'];
						$wert = $attribute->{'VALUE'};
						$wert = str_replace('9999', '-', $wert);
						$wert = str_replace('-.0', '-', $wert);
						
						$einheit = $attribute->{'UNITS'}->{'UNIT'}[0];
              
							// Hier wird geprüft ob das Merkmal 103 104 oder 106 ist. In den ersten beiden Fällen wird eine Flag gesetzt, damit der Wert nicht nochmal in die DB eingetragen wird. Das nachträgliche eintragen ist nötig um ShoreA ShorD spalten zu bekommen, bei denen der Wert nicht ausgefüllt ist. Zusätzlich wird für die Sortierung der härtewert mit SA45 oder SD33 in der Compound DB gespeichert
								if (trim($stammpruefmerkmal_id) == '00000103') {
									$controle_103 = true;
									//echo '
									//103';
									if (strlen($haerte) < 1 ) {
										$haerte =  'SA' . $wert;
										$haerte = 'A' . $material_number;
									}
								}
								if (trim($stammpruefmerkmal_id) == '00000104') {
									$controle_104 = true;
									//echo '
									//104';
									//if (strlen($haerte) < 1 ) {
										$haerte =  'SD' . $wert;
										$haerte = 'D' . $material_number;
									//} 
								}
								if (trim($stammpruefmerkmal_id) == '00000106') {
									if (strlen($haerte) < 1 ) {
										$haerte =  'NVL' . $wert;
										$haerte = 'N' . $material_number;
									} 
								}

								if (trim($stammpruefmerkmal_id) == '00000529') {
									if ($wert == 10) {
										$wert = '10 E7 - 10 E9';
									}
								}

						// Update Compound Set Härte für Sortierung
						$sql_insert = "INSERT INTO ncw_tpepdb2_compound_values20 (compound_id, internal_id, norm_id, bezeichnung_id, footer_id, sort_id, value, einheit, last_import) VALUES (" . $compound_id . ", '".$stammpruefmerkmal_id."', '" . $norm_id . "', '" . $bezeichnung_id . "', '" . $footer_id . "' , '".$sort_id."', '".$wert."', '".$einheit."', '" . $now . "')";
						$dbquery = $obj_db->db->prepare($sql_insert);
						$dbquery->execute();
          }

				
      }

	 /// echo 'DH=' . $haerte . ' ';
			// Für die Sortierung der Serienübersicht wird hier bei jedem Compound noch eine Härte gespeichert
			if ($haerte == 'DDW0OCX-LCNT') {
				$haerte = 'DDWAOC';
			}
			if ($compound_id == 8513) {
				$haerte = 'ARR9HP';
			}
			if ($compound_id == 1271) {
				$haerte = 'DTD0LE';
			}
			if ($compound_id == 8814) {
				$haerte = 'DTG0CM';
			}
			if ($compound_id == 10134) {
				$haerte = 'DTD1OF';
			}
			if ($compound_id == 10136) {
				$haerte = 'DTD2OF';
			}
			if ($compound_id == 7525) {
				$haerte = 'DCG0DJ';
			}


			$sql_update = "UPDATE ncw_tpepdb2_compound SET haerte='" . $haerte . "' WHERE id='" . $compound_id . "'";
			$dbquery = $obj_db->db->prepare($sql_update);
			$dbquery->execute();
			
      if ($controle_103 == false) {
						$sql_insert = "INSERT INTO ncw_tpepdb2_compound_values20 (compound_id, internal_id, norm_id, bezeichnung_id, footer_id, sort_id, value, einheit, last_import) VALUES (" . $compound_id . ", '00000103', '001', '001', '0' , '001', '-', 'Shore A', '" . $now . "')";
						$dbquery = $obj_db->db->prepare($sql_insert);
						$dbquery->execute();
			}
      if ($controle_104 == false) {
						$sql_insert = "INSERT INTO ncw_tpepdb2_compound_values20 (compound_id, internal_id, norm_id, bezeichnung_id, footer_id, sort_id, value, einheit, last_import) VALUES (" . $compound_id . ", '00000104', '001', '001', '0' , '001', '-', 'Shore D', '" . $now . "')";
						$dbquery = $obj_db->db->prepare($sql_insert);
						$dbquery->execute();
			}
		// 
		// $structure_PROCESSING
		$str_query = "DELETE FROM ncw_tpepdb2_compound_processings WHERE compound_id='" . $compound_id . "'";
		$dbquery = $obj_db->db->prepare($str_query);
		$dbquery->execute();
			
			
	// Processings durchlaufen
    foreach ($structure_array->{'PROCESSING'} As $structure_PROCESSING) {
		//var_dump($structure_PROCESSING);		
		$sort_id = 0;
		$type = $structure_PROCESSING->attributes()['class'];
		// Die einzelnen Attribute eines PROCESSING durchlaufen
		// Wurde geändert im Nov. 2023 vorher war nur eins möglich
        foreach ($structure_PROCESSING->{'ATTRIBUTE'} As $attribute) {
			$sort_id++;
			$vah_id = $attribute->attributes();
			foreach ($value = $attribute->{'VALUE'} As $values) {
				//var_dump($values->attributes());
				$value = $values->attributes();
				$type_db = '';
				if ($type == 'TPE_RD_2000_001_PROC_INSTR_IM') {
					$type_db = 'processing_im';
				} else if ($type == 'TPE_RD_2000_001_PROC_INSTR_EX') {
					$type_db = 'processing_ex';
				}
				$text = $attribute->{'VALUE'};
				//$sort_id = $VALUE_TEXT->attributes()['SORT_ORD'];
				//var_dump($VALUE_TEXT);
				$tmp_sortid = $sort_id;
				if ($sort_id < 100) {
					$tmp_sortid = '0' . $tmp_sortid;
				}
				if ($sort_id < 10) {
					$tmp_sortid = '0' . $tmp_sortid;
				}
	
				$str_query = "INSERT INTO ncw_tpepdb2_compound_processings (compound_id, label_id, sort_id, type, last_import) VALUES ('" . $compound_id . "', '" . $value . "', '" . $tmp_sortid . "','" . $type_db . "', '" . $now . "')";
				//echo $str_query . '
				//';
				$dbquery = $obj_db->db->prepare($str_query);
				$dbquery->execute();

			}
        }
    }
			
			
      // 
      // $structure_DOCUMENT
	
			// Löschen der bisherigen Einträge			
      $str_query = "
      	 DELETE FROM ncw_tpepdb2_compound_document WHERE 
				 compound_id='" . $compound_id . "'
				";
      $query = $obj_db->db->prepare($str_query);
      $query->execute();
			
			// XML Struktur Dokumente durchsuchen
      foreach ($structure_array->{'DOCUMENTS'} As $structure_DOCUMENTS) {
        foreach ($structure_DOCUMENTS->{'DOCUMENT'} As $document) {
          $type = $document->attributes()['type'];
					$lang = $document->attributes()['lang'];
					$lang = $this->_language_2stellen($lang, $autoenglish = false);
          $value = $document->{'DESCRIPTIONS_SHORT'};
					$text = '';
					$headline = '';
					// verschiebt das Dokument in der Ordnerstruktur in den Ordner
					// assets/tpepdb2/imported_documents
					$this->_relatedDocumentCompoundPDB20($value, $type, $headline, $text, $compound_id, $lang);
        }
      }
      
    }
  
	
			/*
		* Löscht eine Datei aus dem Verzeichnis
		* wenn copy eingestellt ist, wird die Datei in den backup Ordner verschoben
		*/
    protected function _relatedDocumentCompoundPDB20 ($document, $type, $headline, $text, $compound_id, $lang)
    {
			//$document = utf8_decode($document);
			$document = str_replace("\\","____", $document);
			$arr_document = explode("____", $document);

//echo ' DOKUMENT='. $document;
			$document = $arr_document[count($arr_document) -1];
//echo ' DOKUMENT='. $document;
			// Prüfen ob das Dokument schon vorhanden ist
			$obj_db = new Tpepdb2_Label();
			$str_query_get = "SELECT * FROM `ncw_tpepdb2_document` WHERE name='".$document."' AND language='" . $lang . "'";
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();


			
			$now = date('Y-m-d H:i:s');
			// Wenn es den Compound schon gibt
			if (count($arr_get) > 0) {
				$document_id = $arr_get[0]['id'];
			} else {
				// Dokument schreiben
				$str_query = "INSERT INTO `ncw_tpepdb2_document` (name, headline, text, language, created) VALUES ('" . $document . "', '".$headline."', '" . $text . "',  '". $lang ."', '" . $now . "' ) ";
				$query = $obj_db->db->prepare($str_query);
				$query->execute();
				// Id des Dokuments ermitteln
				$str_query_get = "SELECT * FROM `ncw_tpepdb2_document` WHERE name='".$document."' AND language='" . $lang . "'";
				$dbquery = $obj_db->db->prepare($str_query_get);
				$dbquery->execute();
				$arr_get = $dbquery->fetchAll();
				$document_id = $arr_get[0]['id'];
			}
			//echo 'DocumentId' . $document_id;

			// Dokument mit compound verknüpfen prüfen ob der Eintrag schon vorhanden ist
			$str_query_get = "SELECT * FROM `ncw_tpepdb2_compound_document` WHERE compound_id='".$compound_id."' AND document_id='" . $document_id . "'";
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();
			if (count($arr_get) > 0) {
				
			} else {
				// Dokument mit Compound verknüfung schreiben
				$str_query = "INSERT INTO `ncw_tpepdb2_compound_document` (compound_id, document_id, type, created) VALUES ('" . $compound_id . "', '".$document_id."', '" . $type . "', '" . $now . "' ) ";
				$query = $obj_db->db->prepare($str_query);
				$query->execute();
			}
			
			
			$document = str_replace('/usr/sap/TP1/tpe_transfer/TP1/CATMAN/', '', $document);
			//if (true === TpePdbXmlImporter::$copy) {
			// File in aus dem ursprünglichen Verzeichnis löschen und in das Zeilverzeichnis kopieren
			echo ' Root->' . TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document;
			echo ' Dest->' . TpePdbXmlImporter::$DOCUMENTS_DEST_DIR . DS . $document;
				copy(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document, TpePdbXmlImporter::$DOCUMENTS_DEST_DIR . DS . $document);
			//}
			//if (true === TpePdbXmlImporter::$unlink) {
				unlink(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document);
			echo ' Unlink->' . TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document;
					//		rmdir(TpePdbXmlImporter::$DOCUMENTS_DEST_DIR . DS . 'DE' );
			//}

        
    }
	
				/*
		* Löscht eine Datei aus dem Verzeichnis
		* wenn copy eingestellt ist, wird die Datei in den backup Ordner verschoben
		* Wird in der Serie aufgerufen
		*/
    protected function _relatedDocumentSeriePDB20 ($document, $type, $headline, $text, $serie_id, $lang)
    {
			//$document = utf8_decode($document);
			$document = str_replace("\\","____", $document);
			$arr_document = explode("____", $document);


			$document = $arr_document[count($arr_document) -1];

			// Prüfen ob das Dokument schon vorhanden ist
			$obj_db = new Tpepdb2_Label();
			$str_query_get = "SELECT * FROM `ncw_tpepdb2_document` WHERE name='".$document."' AND language='" . $lang . "'";
			//echo $str_query_get;
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();


			
			$now = date('Y-m-d H:i:s');
			// Wenn es den Compound schon gibt
			if (count($arr_get) > 0) {
				$document_id = $arr_get[0]['id'];
				//echo $document_id;
			} else {
				// Dokument schreiben
				$str_query = "INSERT INTO `ncw_tpepdb2_document` (name, headline, text, language, created) VALUES ('" . $document . "', '".$headline."', '" . $text . "',  '". $lang ."', '" . $now . "' ) ";
				$query = $obj_db->db->prepare($str_query);
				$query->execute();
				// Id des Dokuments ermitteln
				$str_query_get = "SELECT * FROM `ncw_tpepdb2_document` WHERE name='".$document."' AND language='" . $lang . "'";
				$dbquery = $obj_db->db->prepare($str_query_get);
				$dbquery->execute();
				$arr_get = $dbquery->fetchAll();
				$document_id = $arr_get[0]['id'];
			}
			//echo 'DocumentId' . $document_id;

			// Dokument mit compound verknüpfen prüfen ob der Eintrag schon vorhanden ist
			$str_query_get = "SELECT * FROM `ncw_tpepdb2_serie_document` WHERE serie_id='".$compound_id."' AND document_id='" . $document_id . "'";
			//echo $str_query_get;
			$dbquery = $obj_db->db->prepare($str_query_get);
			$dbquery->execute();
			$arr_get = $dbquery->fetchAll();
			if (count($arr_get) > 0) {
				
			} else {
				// Dokument mit Compound verknüfung schreiben
				$str_query = "INSERT INTO `ncw_tpepdb2_serie_document` (serie_id, document_id, type, created) VALUES ('" . $serie_id . "', '".$document_id."', '" . $type . "', '" . $now . "' ) ";
				//echo '
				//' . $str_query;
				$query = $obj_db->db->prepare($str_query);
				$query->execute();
			}
			
			//if (true === TpePdbXmlImporter::$copy) {
			// File in aus dem ursprünglichen Verzeichnis löschen und in das Zeilverzeichnis kopieren
			
			//echo TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document;
				copy(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document, TpePdbXmlImporter::$DOCUMENTS_DEST_DIR . DS . $document);
			//}
			//if (true === TpePdbXmlImporter::$unlink) {
				unlink(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document);
					//		rmdir(TpePdbXmlImporter::$DOCUMENTS_DEST_DIR . DS . 'DE' );
			//}

        
    }
	
	
    /**
     * @return int
     */
    protected function _importVerarbeitungshinweise ()
    {
     
			$structure = TpePdbXmlImporter::$VERARBEITUNGSHINWEISE_XML_NODE_STRUCTURE;
			$xml_code = trim(file_get_contents($this->_file));
			$this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);
			$structure_array = $this->_parseXmlElement(explode("/", TpePdbXmlImporter::$VERARBEITUNGSHINWEISE_XML_NODE_STRUCTURE));
			
			// Löscht alle type = processing aus der Label Datenbank
			$obj_db = new Tpepdb2_Label();
			$str_query = "DELETE FROM ncw_tpepdb2_label WHERE type='processing' OR type='processing_im' OR type='processing_ex'";
			$query = $obj_db->db->prepare($str_query);
			$query->execute();
			 $now = date('Y-m-d H:i:s');
      foreach ($structure_array->{'PROCESSING'} As $structure_processing) {
         // echo '
         // ' . $structure_processing->attributes(). ' // Das hier ist z.B. Extrusion oder Injection Molding
         //'; 
				$processing_type = $structure_processing->attributes();
        $text = $structure_processing;
        $id = $structure_processing->{'ITEM'}->attributes();

        foreach ($structure_processing->{'ITEM'} As $structure_processing_item) {
          //echo '
          //' . $structure_processing_item->attributes(). ' // Das hier ist z.B. Nachdruck oder Einspritzdruck
          //'; 
					$atnam = $structure_processing_item->attributes();
          foreach ($structure_processing_item->{'VALUE'} As $structure_processing_item_value) {

						 $vh_id = $structure_processing_item_value->attributes();
							$vh_id = str_replace("'", '′', $vh_id);
						$vh_id = str_replace("&apos;", '′', $vh_id);
							$vh_id = strip_tags($vh_id);
            //$vh_id = str_replace('VAH_', '',$structure_processing_item_value->attributes());
            foreach ($structure_processing_item_value->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $structure_value_text) {
              $text = $structure_value_text;
              $lang = $structure_value_text->attributes();
							
							$text = str_replace('≤', '<=', $text);
							$text = str_replace("'", '′', $text);
							$text = str_replace("&apos;", '′', $text);
							$text = str_replace(".,,,,,,,,,,,,", '.', $text);
							$text = str_replace(".,,,,,,,,,,,", '.', $text);
							$text = str_replace(".,,,,,,,,,,", '.', $text);
							$text = str_replace(".,,", '.', $text);
							$text = str_replace(". ,,", '.', $text);
              //echo '
              //pt= '.$processing_type.' vh_id=' . $vh_id . ' lang=' . $lang . ' text=' . $text; 
							if ($processing_type == 'INJECTION_MOLDING') {
								$str_query = "INSERT INTO ncw_tpepdb2_label (type, attribute, language, translation, created ) VALUES ('processing_im', '" . $vh_id . "', '" . $lang . "', '" . $text . "', '" . $now . "')";
							} else {
								$str_query = "INSERT INTO ncw_tpepdb2_label (type, attribute, language, translation, created ) VALUES ('processing_ex', '" . $vh_id . "', '" . $lang . "', '" . $text . "', '" . $now . "')";
							}
							
							echo '
							' . $str_query;
							
							$query = $obj_db->db->prepare($str_query);
							$query->execute();
            }
          }
					
					
          foreach ($structure_processing_item->{'DESCRIPTIONS_SHORT'} As $structure_processing_item_value) {
						///echo 'DRIN';
						 $vh_id = $structure_processing_item_value->attributes();
						$vh_id = str_replace("'", '′', $vh_id);
						$vh_id = str_replace("&apos;", '′', $vh_id);
            //$vh_id = str_replace('VAH_', '',$structure_processing_item_value->attributes());
            foreach ($structure_processing_item_value->{'DESCRIPTION_SHORT'} As $structure_value_text) {
              $text = $structure_value_text;
              $lang = $structure_value_text->attributes();
							$text = str_replace('≤', '<=', $text);
							$text = str_replace("'", '′', $text);
							$text = str_replace("&apos;", '′', $text);
							$text = str_replace(".,,,,,,,,,,,,", '.', $text);
							$text = str_replace(".,,,,,,,,,,,", '.', $text);
							$text = str_replace(".,,,,,,,,,,", '.', $text);
							$text = str_replace(".,,", '.', $text);
							$text = str_replace(". ,,", '.', $text);
							
							
             // echo '
              //pt= '.$processing_type.' vh_id=' . $vh_id . ' lang=' . $lang . ' text=' . $text; 
							if ($processing_type == 'INJECTION_MOLDING') {
								$str_query = "INSERT INTO ncw_tpepdb2_label (type, attribute, language, translation, created ) VALUES ('processing_im', '" . $atnam . "_DESCRIPTION', '" . $lang . "', '" . $text . "', '" . $now . "')";
							} else {
								$str_query = "INSERT INTO ncw_tpepdb2_label (type, attribute, language, translation, created ) VALUES ('processing_ex', '" . $atnam . "_DESCRIPTION', '" . $lang . "', '" . $text . "', '" . $now . "')";
							}
							
							echo '
							' . $str_query;
							
							$query = $obj_db->db->prepare($str_query);
							$query->execute();
            }
          }
					
        }
      }
    }
  
    /**
     * @return int
     * Stammprüfmerkmale importieren, dazu gehören Footer, Normen und Bezeichnungen
     */
    protected function _importStammpruefmerkmale ()
    {
        $structure = TpePdbXmlImporter::$STAMMPRUEFMERKMALE_XML_NODE_STRUCTURE;
        $xml_code = trim(file_get_contents($this->_file));
        $this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);
        $structure_array = $this->_parseXmlElement(explode("/", TpePdbXmlImporter::$STAMMPRUEFMERKMALE_XML_NODE_STRUCTURE));
       // var_dump($this->_xml_element);
        $now = date('Y-m-d H:i:s');

				
			//$obj = new Tpepdb2_Label();			
			$obj_db = new Tpepdb2_Label();
      $str_query = "
      	 DELETE FROM ncw_tpepdb2_label WHERE 
				 type='QPMK_BEZEICHNUNG'
					OR  type='QPMK_NORM'
					OR  type='QPMK_FOOTER'
				";
			// Löschen der bisherigen Einträge
      $query = $obj_db->db->prepare($str_query);
      $query->execute();
				
       foreach ($structure_array->{'ITEM'} As $structure_value_item) { // Alle ITEMS durchgehen
         $art = $structure_value_item->attributes(); // QMPK_NORM, QMPK_BEZEICHNUNG, QMPK_FOOTER 
				
        foreach ($structure_value_item->{'VALUE'} As $structure_value) { // Alle eigenschaften innerhalb des Blocks ITEM z.B. atnam="QPMK_NORM"
					$arr_shortlong = array();
          $attribute_id = $structure_value->attributes(); // ID der Value


					$cttext = 0;
          foreach ($structure_value->{'VALUE_TEXTS_SHORT'}->{'VALUE_TEXT_SHORT'} As $structure_value_text) { // Alle Kurztexte aller Sprachen durchgehen
            $text = $structure_value_text;
						
            $lang = (string) $structure_value_text->attributes()['lang'][0];
						$text = str_replace("'", '′', $text);
						$text = str_replace("&apos;", '′', $text);
						$text = str_replace("&apos;", '′', $text);
						$text = strip_tags($text);
						
						$arr_shortlong[$lang] = $text;
						$str_query = "INSERT INTO ncw_tpepdb2_label (type, attribute, language, translation, created ) VALUES ('" . $art . "', '" . $attribute_id . "', '" . $lang . "', '" . $text . "', '" . $now . "')";
						if ($art == 'QPMK_NORM') {
							//echo '
							//' . $str_query . ' - ' . $arr_shortlong[$lang] . ' - ' . $lang;
						}
						$query = $obj_db->db->prepare($str_query);
						$query->execute();
						$cttext++;
          }
         $cttext = 0;
          foreach ($structure_value->{'VALUE_TEXTS_LONG'}->{'VALUE_TEXT_LONG'} As $structure_value_text) { // Alle Langtexte aller Sprachen durchgehen
            $text = $structure_value_text;
						if ($art == 'QPMK_FOOTER') {
						//	$text = '';
						}
						//$text = utf8_encode($text);
            $lang = (string) $structure_value_text->attributes()['lang'][0];

						//if ($art == 'QPMK_FOOTER') {
						//	$insert_txt =  $text;
							
						//} else {
							$insert_txt =  $arr_shortlong[$lang] . $text;
						//}
						
					
						
						$insert_txt = str_replace("'", '′', $insert_txt);
						$insert_txt = str_replace("&apos;", '′', $insert_txt);

						$insert_txt = str_replace('– ', '-', $insert_txt);
						$insert_txt = str_replace('–', '-', $insert_txt);
						$insert_txt = str_replace('&ndash; ', '-', $insert_txt);
						$insert_txt = str_replace('&ndash;', '-', $insert_txt);

						$insert_txt = strip_tags($insert_txt);
						$insert_txt = trim($insert_txt);
						
						
						$str_queryS = "SELECT * FROM ncw_tpepdb2_label WHERE attribute='" . $attribute_id . "' AND language='" . $lang . "'";
						$queryS = $obj_db->db->prepare($str_queryS);
						$queryS->execute();
						$arr_get = $queryS->fetchAll();
						if (count($arr_get) > 0) {
							$readId = $arr_get[0]['id'];
							$readText = $arr_get[0]['translation'];
							if (false == strstr($insert_txt, $readText)) {
								$str_query = "UPDATE ncw_tpepdb2_label SET translation='" . $readText . $insert_txt . "' WHERE id='" . $readId . "'";
							} else {
								$str_query = "UPDATE ncw_tpepdb2_label SET translation='" . $insert_txt . "' WHERE id='" . $readId . "'";
							}
						} else {
							$str_query = "INSERT INTO ncw_tpepdb2_label (type, attribute, language, translation, created ) VALUES ('" . $art . "', '" . $attribute_id . "', '" . $lang . "', '" . $insert_txt . "', '" . $now . "')";
						}
						
						//if ($art == 'QPMK_NORM') {
						//	echo '
						//	' . $str_query . ' - ' . $arr_shortlong[$lang] . ' - ' . $lang;
						//}

						
						$query = $obj_db->db->prepare($str_query);
						$query->execute();
						
						$cttext++;
          }
					
					
					
        }
       }
      // echo $structure;
    }
	
	
	 private  function escape($s) {
      return htmlentities(
          $s,
          ENT_QUOTES | ENT_HTML5 | ENT_DISALLOWED | ENT_SUBSTITUTE,
          'UTF-8'
      );
  }
  
    /**
     * @return int
     */
    protected function _importFussnoten () // Veraltet
    {
        $structure = TpePdbXmlImporter::$FUSSNOTEN_XML_NODE_STRUCTURE;
        $xml_code = trim(file_get_contents($this->_file));
        $this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);
        $structure_array = $this->_parseXmlElement(explode("/", TpePdbXmlImporter::$FUSSNOTEN_XML_NODE_STRUCTURE));
//        var_dump($this->_xml_element);
        
        foreach ($structure_array->{'VALUE'} As $structure_value) {
          $fussnoten_id = $structure_value->attributes();
          echo '
          ' . $structure_value->attributes();
          foreach ($structure_value->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $structure_value_text) {
            $text = $structure_value_text;
            $lang = $structure_value_text->attributes();
            //echo '
            //' . $lang . ' => ' . $text; 
          }
        }
      


        echo $structure;
    }
    
    /**
     * @return int
     */
    protected function _importPhrasen ()
    {
			$structure = TpePdbXmlImporter::$PHRASEN_XML_NODE_STRUCTURE;
			$xml_code = trim(file_get_contents($this->_file));
			$this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);
			$structure_array = $this->_parseXmlElement(explode("/", TpePdbXmlImporter::$PHRASEN_XML_NODE_STRUCTURE));
			$now = date('Y-m-d H:i:s');

			$obj = new Tpepdb2_Label();


			$str_query = "
				 DELETE FROM ncw_tpepdb2_label WHERE 

				 type='approval'
					OR  type='typicalapplication'
					OR  type='materialadvantages'
					OR  type='regulations'
			";


			$query = $obj->db->prepare($str_query);
			$query->execute();

      
      foreach ($structure_array->{'PHRASEN'} As $structure_phrasen) {

        $text = $structure_phrasen;
        $type_id = $structure_phrasen->{'ITEM'}->attributes();
         echo '' . $type_id . ' => ' . $text; 
        
        foreach ($structure_phrasen->{'ITEM'}->{'VALUE'} As $structure_phrasen_item) {


					$phrasen_id = $structure_phrasen_item->attributes();
					foreach ($structure_phrasen_item->{'VALUE_TEXTS'}->{'VALUE_TEXT'} As $structure_value_text) {
						$text = $structure_value_text;
						$lang = $structure_value_text->attributes();
						//echo '
						//Phrasen_id=' .$phrasen_id . ' lang=' . $lang . ' text=' . $text; 

						$obj_new = new Tpepdb2_Label();

						if ($type_id == 'TPE_RD_3000_003_APPROVAL') {
								$obj_new->setType('approval');
						} else
						if ($type_id == 'TPE_RD_4000_001') {
								$obj_new->setType('typicalapplication');
						} else 
						if ($type_id == 'TPE_RD_4000_002') {
								$obj_new->setType('materialadvantages');
						} else 
						if ($type_id == 'TPE_RD_4000_003') {
								$obj_new->setType('regulations');
						}



						$obj_new->setAttribute($phrasen_id);
						$obj_new->setLanguage($lang);
						$obj_new->setTranslation($text);
						$obj_new->setCreated($now);
						$obj_new->save();


					 // $str_query = "
					//    INSERT INTO `ncw_tpepdb2_label` (`type`, `attribute`, `language`, `translation`) VALUES ('phrase', '" . $phrasen_id . "', '" . $lang . "', '" . utf8_decode($text) . "');
					 // ";
						//$query_new = $obj_new->db->prepare($str_query);
					 // $query_new->execute();

					}
				}
    	}
  }
    /**
     * @param int $brand_id
     * @return int
     */
    protected function _importSerie_out ($brand_id)
    {
        $serie = new TpePdbXmlSerie(
            $this->_parseXmlElement(
                explode("/", TpePdbXmlImporter::$SERIE_XML_NODE_STRUCTURE)
            ),
            $this->_is_portfolio
        );
        $serie->model->setBrandId($brand_id);
        return $serie->import($this->_import_time);
    }

    /**
     * @param int $brand_id
     * @param int $serie_id
     * @return int
     */
    protected function _importCompound_OUT ($brand_id, $serie_id)
    {
        $compound = new TpePdbXmlCompound(
            $this->_parseXmlElement(
                explode("/", TpePdbXmlImporter::$COMPOUND_XML_NODE_STRUCTURE)
            ),
            $this->_is_portfolio
        );
        $compound->model->setBrandId($brand_id);
        $compound->model->setSerieId($serie_id);
        $compound_id = $compound->import($this->_import_time);
        return $compound_id;
    }

    /**
     * @param string $xml_node_structure
     * @return SimpleXMLElement
     */
    protected function _parseXmlElement ($xml_node_structure)
    {
        $xml_node = $this->_loaded_xml_file;
        $node_count = count($xml_node_structure);
        for ($i = 1; $i < $node_count; ++$i) {
            $node_name = $xml_node_structure[$i];
            $xml_node = $this->_getXmlChildrenNodeByName($xml_node, $node_name);
        }
        return $xml_node;
    }

    /**
     * @param SimpleXMLElement $xml_node
     * @param string $node_name
     * @return SimpleXMLElement
     */
    protected function _getXmlChildrenNodeByName ($xml_node, $node_name)
    {
        $node_name = (string) $node_name;
        foreach ($xml_node->children() as $child) {
            if ($child->getName() == $node_name) {
                return $child;
            }
        }
    }
}
?>
