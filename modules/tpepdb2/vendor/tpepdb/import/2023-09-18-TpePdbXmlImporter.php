<?php

require_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tpepdb" . DS . "import" . DS . "TpePdbXmlFiles.php";

class TpePdbXmlImporter {

    /**
     * @var string
     * @static
     */
    public static $XML_SOURCE_DIR = "";

    /**
     * @var string
     * @static
     */
    public static $DOCUMENTS_SOURCE_DIR = "";

    /**
     * @var string
     * @static
     */
    public static $DOCUMENTS_DEST_DIR = "";

    /**
     * @var string
     * @static
     */
    public static $BRAND_XML_NODE_STRUCTURE = "CATALOG/NODES/NODE/ITEMS/ITEM";

    /**
     * @var string
     * @static
     */
    public static $SERIE_XML_NODE_STRUCTURE = "CATALOG/NODES/NODE";

    /**
     * @var string
     * @static
     */
    public static $FUSSNOTEN_XML_NODE_STRUCTURE = "CATALOG/NODES/NODE/ITEM";
	
    /**
     * @var string $STAMMPRUEFMERKMALE_XML_NODE_STRUCTURE
     * @static
     */
    public static $STAMMPRUEFMERKMALE_XML_NODE_STRUCTURE = "CATALOG/NODES/NODE";
	
	    /**
     * @var string
     * @static
     */
    public static $PHRASEN_XML_NODE_STRUCTURE = "CATALOG/NODES";
	
		    /**
     * @var string
     * @static
     */
    public static $VERARBEITUNGSHINWEISE_XML_NODE_STRUCTURE = "CATALOG/NODES";
	
    /**
     * @var string
     * @static
     */
    public static $COMPOUND_XML_NODE_STRUCTURE = "CATALOG/NODES/NODE/ITEMS/ITEM";

    /**
     * @var string
     * @static
		 Pfad ur ID also ob der Compound ein Portfolio oder non portfolio ist
     */
    public static $COMPOUND_XML_NODE_STRUCTURE_PORTFOLIONONPORTFOLIO = "CATALOG/HEAD";

	    /**
     * @var string
     * @static
		 Geht bis Node um dort die ID der Serie auslesen zu können
     */
    public static $COMPOUND_XML_NODE_STRUCTURE_SERIES = "CATALOG/NODES";
    /**
     * @var Ncw_Library_Log_Logger_File
     */
    public static $FILE_LOG = null;

    /**
     * @var Ncw_Library_Log_Logger_File
     */
    public static $ROOT_PATH = '';

    /**
     * @var array
     */
    protected $_files = array();

    /**
     * @var TpePdbXmlFiles
     */
    protected $_xml_files = null;

    /**
     * @var boolean
     */
    protected $_import_done = false;

    /**
     * @var boolean
     */
    protected $_portfolio_import = false;

    /**
     * @var int
     */
    protected $_timestamp = 0;

    /**
     * @var array
     * @static
     */
    protected static $_labels = array();

    /**
     * @var array
     * @static
     */
    public static $processing_notes = array();

    /**
     * @var array
     * @static
     */
    public static $documents = array();

    /**
     * @var boolean
     * @static
     */
    public static $unlink = true;

    /**
     * @var boolean
     * @static
     */
    public static $copy = true;

    /**
     * @param string $type
     */
    public function __construct ()
    {
        if (true === isset($_GET['set_root_path']) && $_GET['set_root_path'] == 1) {
            self::$ROOT_PATH = '/usr/www/users/kraibn/pdb/';
        }

        self::$XML_SOURCE_DIR = self::$ROOT_PATH . "tpepdb2_import";
        self::$DOCUMENTS_SOURCE_DIR = self::$ROOT_PATH ."tpepdb2_import";
        self::$DOCUMENTS_DEST_DIR = self::$ROOT_PATH .ASSETS . DS . "tpepdb2" . DS . "imported_documents";

        self::$FILE_LOG = new Ncw_Library_Log_Logger_File("TpePdb2_log.txt");

        $this->_xml_files = new TpePdbXmlFiles();
    }

    /**
     * @return void
     */
    public function readXmlFiles ()
    {
        $this->_readFiles();
        list($this->_timestamp, $type) = $this->_getOldestTimestamp();

			
				$tmp_files_del = array();
				$tmp_files_serie = array();
				$tmp_files_compound = array();
				$tmp_files_rest = array();
				$tmp_files_phrasen = array();
			  $tmp_all = array();
				foreach ($this->_files as $file) {

                    if (true == strstr($file, 'BRAND_')) {
                        $this->_unlinkDocumentPDB20($file);
                    }

					if (true == strstr(strtolower($file), 'serie') && false == strstr(strtolower($file), '.serie') && false == strstr(strtolower($file), 'delete') && true == strstr(strtolower($file), '.xml')) {
						$tmp_files_serie[] = $file;
					} else
					if ( (true == strstr(strtolower($file), 'verarbeitungshinweise') || true == strstr(strtolower($file), 'phrasen') || true == strstr(strtolower($file), 'stammpruefmerkmale') )  && true == strstr(strtolower($file), '.xml')) {
						$tmp_files_phrasen[] = $file;
					} else
					if (true == strstr(strtolower($file), 'compound') && false == strstr(strtolower($file), '.compound') && false == strstr(strtolower($file), 'delete') && true == strstr(strtolower($file), '.xml')) {
						$tmp_files_compound[] = $file;
					} else
					if (true == strstr(strtolower($file), 'serie') && false == strstr(strtolower($file), '.serie') && true == strstr(strtolower($file), 'delete') && true == strstr(strtolower($file), '.xml')) {
						$tmp_files_del[] = $file;
					} else {
						$tmp_files_rest[] = $file;
					}
				}
			
				$tmp_all = $tmp_files_phrasen + $tmp_files_del + $tmp_files_serie + $tmp_files_compound + $tmp_files_rest;
			
			
				foreach ($tmp_all as $file) {
					//echo '					
//' . $file;
				}
			
				//exit;
			
        foreach ($tmp_all as $file) { // Alle vorhanden Files durchsuchen
						//$file = strtolower($file);
					
	
					
            $file_exploded = explode(".", $file);
						$filetype = $file_exploded[count($file_exploded) -1];
            $file_name_exploded = explode("_", $file_exploded[0]);
						if ($file_name_exploded[0] == 'DELETE') {
							//echo 'delete';
							// löscht Compound aus der DB
							echo 'Delete';
							if ($file_name_exploded[1] == 'COMPOUND') {
								$this->deletePDB20Compound($file_name_exploded[count($file_name_exploded) - 1] );								
							}
							if ($file_name_exploded[1] == 'SERIE') {
								$this->deletePDB20Serie($file_name_exploded[count($file_name_exploded) - 1] );								
							}

							// löscht Compound aus import Verzeichnis
							$this->_unlinkDocumentPDB20($file);
							//break;
						}

					
					if ( true == strstr(strtolower($file), '.compound_' ) || true == strstr(strtolower($file), '.verarbeitungshinweise' ) || true == strstr(strtolower($file), '.stammpruefmerkmale' ) || true == strstr(strtolower($file), '.phrasen' ) || true == strstr(strtolower($file), '.serie') || true == strstr(strtolower($file), '.brand') || true == strstr(strtolower($file), '.brand') ) {
						//echo '.Deleted';
						$this->_unlinkDocumentPDB20($file, true);
						//unlink($file);
					}
					if ($file == 'stammpruefmerkmale.xml' || $file == 'Stammpruefmerkmale.xml') { // Import der Fußnoten XML
						if (file_exists(self::$XML_SOURCE_DIR . DS . $file)) {
							$obj_xml = new TpePdbXmlFile(self::$XML_SOURCE_DIR . DS . $file, '1122343434');
							$obj_xml->importPDB20('stammpruefmerkmale'); // Das passiert in der Datei TpePdbXmlFile.php
							$this->_unlinkDocumentPDB20($file); 
						} else {
								exit('Konnte '.self::$XML_SOURCE_DIR . DS . $file.' nicht öffnen.');
						}
					}
					
					// PHRASEN
					if ($file == 'phrasen.xml' || $file == 'Phrasen.xml') { 	//	approval typicalapplication materialadvantages regulations'
						if (file_exists(self::$XML_SOURCE_DIR . DS . $file)) {
							$obj_xml = new TpePdbXmlFile(self::$XML_SOURCE_DIR . DS . $file, '1122343434');
							$obj_xml->importPDB20('phrasen');
							$this->_unlinkDocumentPDB20($file);
						} else {
								exit('Konnte '.self::$XML_SOURCE_DIR . DS . $file.' nicht öffnen.');
						}
					}
					
					// VERARBEITUNGSHINWEISE
					if ($file == 'verarbeitungshinweise.xml' || $file == 'Verarbeitungshinweise.xml') {
						if (file_exists(self::$XML_SOURCE_DIR . DS . $file)) {
							$obj_xml = new TpePdbXmlFile(self::$XML_SOURCE_DIR . DS . $file, '1122343434');
							$obj_xml->importPDB20('verarbeitungshinweise');
							$this->_unlinkDocumentPDB20($file);
						} else {
								exit('Konnte '.self::$XML_SOURCE_DIR . DS . $file.' nicht öffnen.');
						}
					}
					// SERIE
					if (true == strstr(strtolower($file), 'serie_') && false == strstr(strtolower($file), '.serie_') && $filetype == 'xml' && false == strstr(strtolower($file), 'delete')) {
						if (file_exists(self::$XML_SOURCE_DIR . DS . $file)) {
							$obj_xml = new TpePdbXmlFile(self::$XML_SOURCE_DIR . DS . $file, '1122343434');
							$obj_xml->importPDB20('serie');
							$this->_unlinkDocumentPDB20($file);
						} else {
								exit('Konnte '.self::$XML_SOURCE_DIR . DS . $file.' nicht öffnen.');
						}
					}
					
					// COMPOUND
					if (true == strstr(strtolower($file), 'compound_') && false == strstr(strtolower($file), '.compound_') && $filetype == 'xml' && false == strstr(strtolower($file), 'delete')) {
						if (file_exists(self::$XML_SOURCE_DIR . DS . $file)) {
							$obj_xml = new TpePdbXmlFile(self::$XML_SOURCE_DIR . DS . $file, '1122343434');
							$obj_xml->importPDB20('compound');
							
							//echo 'Compound' .self::$XML_SOURCE_DIR . DS . $file;
							
							$this->_unlinkDocumentPDB20($file);
						} else {
								exit('Konnte '.self::$XML_SOURCE_DIR . DS . $file.' nicht öffnen.');
						}
					}
					

					/*
             if ($type !== "PORTFOLIO" && $type !== "NONPORTFOL"
                && array_pop($file_exploded) == "xml"
                && true === isset($file_name_exploded[1])
                ) {
                if (true === is_file(self::$XML_SOURCE_DIR . DS . "." . $file_name_exploded[0] . "_" . $file_name_exploded[1])) {
                    $this->_xml_files->Add(new TpePdbXmlFile(self::$XML_SOURCE_DIR . DS . $file, $this->_timestamp));
                }
            } else if (array_pop($file_exploded) == "xml"
                && true === isset($file_name_exploded[1])
                && $file_name_exploded[1] == $this->_timestamp
                ) {
                if (true === is_file(self::$XML_SOURCE_DIR . DS . "." . $file_name_exploded[0] . "_" . $file_name_exploded[1])) {
                    if ($file_name_exploded[0] == "PORTFOLIO") {
                        $this->_portfolio_import = true;
                    }
                    $this->_xml_files->Add(new TpePdbXmlFile(self::$XML_SOURCE_DIR . DS . $file, $this->_timestamp));
                }
            }*/
        }
    }

    /**
     *
     */
    protected function _readFiles ()
    {
        $import_folder = self::$XML_SOURCE_DIR;
        if ($handle = opendir($import_folder)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != ".DS_Store") {
                    $this->_files[] = $file;
                }
            }
            closedir($handle);
        }
    }

    /**
     * @return int timestamp
     */
    protected function _getOldestTimestamp ()
    {
        $timestamps = array();

        foreach ($this->_files as $file) {
            $file_exploded = explode("_", $file);
            if ($file[0] == ".") {
                $timestamp = (int) $file_exploded[1];
                if (false == in_array($timestamp, $timestamps)) {
                    $timestamps[] = array($timestamp, str_replace(".", "", $file_exploded[0]));
                }
            }
        }
        sort($timestamps);
        if (true === isset($timestamps[0])) {
            return $timestamps[0];
        } else {
            return null;
        }
    }

    /**
     *
     */
    public function import ()
    {
    	set_time_limit(0);
        $import_time = time();



        for ($i = 0; count($this->_xml_files->xml_files); $i++) {
					
	        $xml_file = array_pop($this->_xml_files->xml_files);
	        $xml_file->import($import_time);
	        unset($xml_file);
	        $this->_import_done = true;
        } 



        if (true === $this->_import_done) {
            self::importLabels($this->_portfolio_import);
            $this->_deleteUnusedDocuments(array("Serie", "Compound"));
					  
        }

        if (true === $this->_portfolio_import) {
            $this->_deleteOldPortfolio($import_time, "Compound");
            $this->_deleteOldPortfolio($import_time, "Serie");

            $this->_deleteOldRelatedItems($import_time, "anwendungsbereiche");
            $this->_deleteOldRelatedItems($import_time, "markets");
            $this->_deleteOldRelatedItems($import_time, "materialvorteile");

            /*$db = Ncw_Database::getInstance();
            $sql = "DELETE FROM `ncw_tpepdb2_processingnote` WHERE `last_import` < '" . date("Y-m-d H:i:s", $import_time) . "';";
            $db->exec($sql);*/
        }

        if (true === is_file(self::$XML_SOURCE_DIR . DS . ".PORTFOLIO_" . $this->_timestamp) && true === self::$unlink) {
            if (true === self::$copy) {
                copy(self::$XML_SOURCE_DIR . DS . ".PORTFOLIO_" . $this->_timestamp, self::$XML_SOURCE_DIR . DS . "backup" . DS . ".PORTFOLIO_" . $this->_timestamp);
            }
            unlink(self::$XML_SOURCE_DIR . DS . ".PORTFOLIO_" . $this->_timestamp);
        }
        if (true === is_file(self::$XML_SOURCE_DIR . DS . ".NONPORTFOL_" . $this->_timestamp) && true === self::$unlink) {
            if (true === self::$copy) {
                copy(self::$XML_SOURCE_DIR . DS . ".NONPORTFOL_" . $this->_timestamp, self::$XML_SOURCE_DIR . DS . "backup" . DS . ".NONPORTFOL_" . $this->_timestamp);
            }
            unlink(self::$XML_SOURCE_DIR . DS . ".NONPORTFOL_" . $this->_timestamp);
        }

        if (true === $this->_import_done) {
            $this->_unlinkDocuments();
            //$this->_deleteCreatedPdfs("compound");
            //$this->_deleteCreatedPdfs("serie");

            $this->_removeModels();
        }
    }

    /**
     * @param string $label_type
     * @param string $label_name
     * @param string $label_value
     * @param string $label_lang
     */
    public static function addLabel ($label_type, $label_attribute, $label_translation, $label_lang)
    {
        if (false === isset(self::$_labels[$label_type])) {
            self::$_labels[$label_type]= array();
        }
        if (false === isset(self::$_labels[$label_type][$label_attribute])) {
            self::$_labels[$label_type][$label_attribute]= array();
        }
        if (false == in_array($label_translation, array("DIN Norm", "QPMK_NORM"))) {
            $label_translation = str_replace(array("CAT_", "CAT"), "", $label_translation);
            self::$_labels[$label_type][$label_attribute][$label_lang] = $label_translation;
        }
    }

    /**
     *
     */
    protected static function importLabels ($portfolio_import)
    {
        $label = new Tpepdb2_Label();

        $found_comp_labels = $label->fetch(
            "all",
            array(
                "fields" => array(
                    "Label.id",
                    "Label.attribute",
                ),
                "conditions" => array(
                    "Label.type" => "compound",
                )
            )
        );
        $note_labels = array();
        foreach ($found_comp_labels as $found_comp_label) {
            if (true == strstr($found_comp_label->getAttribute(), "_note")) {
                $note_labels[] = $found_comp_label;
            }
        }

        $upd_note_labels = array();
        foreach (self::$_labels as $label_type => $type_labels) {
            $label->setType($label_type);
            foreach ($type_labels as $label_attribute => $attribute_labels) {
                $label->setAttribute($label_attribute);

                if (true == strstr($label_attribute, "_note")) {
                    $upd_note_labels[] = $label_attribute;
                }

                foreach ($attribute_labels as $label_lang => $translation) {

                    $found_label = $label->fetch(
                        "first",
                        array(
                            "fields" => array(
                                "Label.id",
                            ),
                            "conditions" => array(
                                "Label.type" => $label_type,
                                "Label.attribute" => $label_attribute,
                                "Label.language" => $label_lang,
                            )
                        )
                    );
                    if (false !== $found_label) {
                        $label->setId($found_label->getId());
                    } else {
                        $label->create();
                    }
                    $label->setTranslation($translation);
                    $label->setLanguage($label_lang);
                    $label->save();
                }
            }
        }

        if (true === $portfolio_import) {
            foreach ($note_labels as $note_label) {
                if (false == in_array($note_label->getAttribute(), $upd_note_labels)) {
                    $label->setId($note_label->getId());
                    $label->delete();
                }
            }
        }
    }

		/*
		* löscht ein Compound anhand des namens
		*/
		public function deletePDB20Compound($compoundname)
		{
			$this->view = false;
			echo 'DELETE: ' . $compoundname;
			$compoundname = str_replace('+', '/',$compoundname);
			//$compoundname = Ncw_Library_Sanitizer::clean($compoundname);
				echo 'DELETE: ' . $compoundname;
			$obj_compound = new Tpepdb2_Compound();
			$found_compound = $obj_compound->fetch(
					'first',
					array(
							'conditions' => array(
									'name' => $compoundname
							)
					)
			);
			if (false !== $found_compound) {
				
					$obj_compound_delete = new Tpepdb2_Compound();
					$compound_id = $found_compound->getId();
					//echo 'Mester' . $compound_id;
					//$obj_compound_delete->setId($compound_id);
				
					$str_query = "DELETE FROM ncw_tpepdb2_compound WHERE id='" . $compound_id . "'";
					echo '
					' . $str_query . '
					';
				
					$dbquery = $obj_compound->db->prepare($str_query);
					$dbquery->execute();
				
					//if($obj_compound_delete->delete()) {
							print  $compoundname . ' removed';
					//}
				
				$obj_db = new Tpepdb2_Label();
				
					// Löschen der zugeordneten Einträge
					$str_query = "DELETE FROM ncw_tpepdb2_compound_typicalapplication WHERE compound_id='" . $compound_id . "'";
					$dbquery = $obj_compound->db->prepare($str_query);
					$dbquery->execute();

					$str_query = "DELETE FROM ncw_tpepdb2_compound_materialadvantages WHERE compound_id='" . $compound_id . "'";
					$dbquery = $obj_compound->db->prepare($str_query);
					$dbquery->execute();
				
					$str_query = "DELETE FROM ncw_tpepdb2_compound_approval WHERE compound_id='" . $compound_id . "'";
					$dbquery = $obj_compound->db->prepare($str_query);
					$dbquery->execute();
					
					$str_query = "DELETE FROM ncw_tpepdb2_compound_regulations WHERE compound_id='" . $compound_id . "'";
					$dbquery = $obj_compound->db->prepare($str_query);
					$dbquery->execute();
				
			}
			

			
		}
	
	
			/*
		* löscht eine Serie anhand des namens
		*/
		public function deletePDB20Serie($compoundname)
		{
			$this->view = false;
			echo 'DELETE: ' . $compoundname;
			
			$compoundname = str_replace('---', '/', $compoundname);
			$compoundname = Ncw_Library_Sanitizer::clean($compoundname);
			
			$obj_compound = new Tpepdb2_Serie();
			$found_compound = $obj_compound->fetch(
					'first',
					array(
							'conditions' => array(
									'name' => $compoundname
							)
					)
			);
			if (false !== $found_compound) {
				
					$obj_compound_delete = new Tpepdb2_Serie();
					$serie_id = $found_compound->getId();
					//echo 'Mester' . $compound_id;
					//$obj_compound_delete->setId($compound_id);
				
					$str_query = "DELETE FROM ncw_tpepdb2_serie WHERE id='" . $serie_id . "'";
					echo '
					' . $str_query . '
					';
				
					$dbquery = $obj_compound->db->prepare($str_query);
					$dbquery->execute();
				
					//if($obj_compound_delete->delete()) {
							print  $compoundname . ' removed';
					//}
				
				$obj_db = new Tpepdb2_Label();
				
					// Löschen der zugeordneten Einträge
					$str_query = "DELETE FROM ncw_tpepdb2_serie_values WHERE serie_id='" . $serie_id . "'";
					$dbquery = $obj_compound->db->prepare($str_query);
					$dbquery->execute();


				
			}
			

			
		}
	
		/*
		* Löscht eine Datei aus dem Verzeichnis
		* wenn copy eingestellt ist, wird die Datei in den backup Ordner verschoben
		*/
    protected function _unlinkDocumentPDB20 ($document, $nocopy = false)
    {
        
				//echo $document;
				if ($nocopy == false) {
					if (true === TpePdbXmlImporter::$copy) {
							copy(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document, TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . "backup" . DS . $document);
					}
				}

				if (true === TpePdbXmlImporter::$unlink) {
						unlink(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document);
				}
        
    }
	

	
    /**
     *
     */
    protected function _unlinkDocuments ()
    {
        foreach (self::$documents as $document) {
					
            if (true === TpePdbXmlImporter::$copy) {
                copy(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document, TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . "backup" . DS . $document);
            }
            if (true === TpePdbXmlImporter::$unlink) {
                unlink(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document);
            }
        }
    }

    /**
     * @param int $import_time
     * @param string $model_name
     */
    protected function _deleteOldPortfolio ($import_time, $model_class_name)
    {
        $import_time = (int) $import_time;
        $model_class_name = "Tpepdb2_" . (string) $model_class_name;

        $model_name_exploded = explode("_", $model_class_name);
        $model_name = array_pop($model_name_exploded);

        $model = new $model_class_name();
        $old_items = $model->fetch(
            "all",
            array(
                "fields" => array(
                    $model_name . ".id",
                ),
                "conditions" => array(
                    $model_name . ".last_import <" => date("Y-m-d H:i:s", $import_time),
                    $model_name . ".status" => "portfolio"
                )
            )
        );
        foreach ($old_items as $old_item) {
            $model->setId($old_item->getId());
            $model->delete();
        }
    }

    /**
     * @param int $import_time
     * @param string $name
     */
    protected function _deleteOldRelatedItems ($import_time, $name)
    {
        $db = Ncw_Database::getInstance();
        $db_table_name = $db->getConfig("prefix") . "tpepdb2_" . strtolower($name);
        $column_name = strtolower($name) . "_id";

        $sql = "DELETE FROM `" . $db_table_name . "` WHERE `last_import` < '" . date("Y-m-d H:i:s", $import_time) . "';";
        $db->exec($sql);

        $db_table_name = $db->getConfig("prefix") . "tpepdb2_serie_" . strtolower($name);
        $sql = "DELETE FROM `" . $db_table_name . "` WHERE `last_import` < '" . date("Y-m-d H:i:s", $import_time) . "';";
        $db->exec($sql);
    }

    /**
     * @param array $model_names
     */
    protected function _deleteUnusedDocuments ($model_names)
    {
        $db = Ncw_Database::getInstance();

        $sql = "";
        foreach ($model_names as $model_name) {
            $model_name = strtolower((string) $model_name);
            $sql .= "LEFT JOIN `ncw_tpepdb2_" . $model_name . "_document` AS `" . $model_name . "`
            ON `Document`.`id`=`" . $model_name . "`.`document_id` ";
        }
        $sql .= "WHERE ";

        $sql_where = array();
        foreach ($model_names as $model_name) {
            $model_name = strtolower((string) $model_name);
            $sql_where[] = "`" . $model_name . "`.`id` IS NULL";
        }
        $sql .= implode("&&", $sql_where);

        $result = $db->query("SELECT `Document`.`name`, `Document`.`language` FROM `ncw_tpepdb2_document` AS `Document`" . $sql);
        foreach ($result->fetchAll() as $row) {
            $file_name = TpePdbXmlImporter::$DOCUMENTS_DEST_DIR;
            if ($row["language"] != "") {
                $file_name .=  DS . $row["language"];
            }
            $file_name .= DS . $row["name"];
            if (true === file_exists($file_name)) {
                unlink($file_name);
            }
        }

        $result = $db->query("DELETE `Document` FROM `ncw_tpepdb2_document` AS `Document`" . $sql);

        // empty tmp folder
        $tmp_folder = self::$ROOT_PATH . ASSETS . DS . 'tpepdb2' . DS . 'imported_documents' . DS . 'tmp';
        $items = glob($tmp_folder . DS . '*');
        foreach($items as $item) {
            unlink($item);
        }
    }

    /**
     *
     */
    protected function _deleteCreatedPdfs ($name = "compound") {
        $path = self::$ROOT_PATH . ASSETS . DS . "tpepdb2" . DS . "pdfs" . DS . $name;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if (false == in_array($file, array(".", ".."))) {
                    $this->_deleteDirectory($path . DS . $file);
                }
            }
            closedir($handle);
        }
    }

    /**
     *
     */
    protected function _deleteDirectory ($dirPath) {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object !="..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        $this->_deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }

    /**
     *
     */
    protected function _removeModels ()
    {
        $tmp_folder = self::$ROOT_PATH . TMP . DS . "cache" . DS . "models";
        $items = glob($tmp_folder . DS . 'Tpepdb2_*');
        foreach($items as $item) {
            unlink($item);
        }
    }
}
?>
