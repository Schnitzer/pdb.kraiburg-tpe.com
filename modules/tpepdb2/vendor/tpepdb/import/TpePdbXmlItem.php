<?php

class TpePdbXmlItem {

    /**
     * @var string
     * @static
     */
    public static $STATUS_UPTODATE = "portfolio";

    /**
     * @var string
     * @static
     */
    public static $STATUS_OUTOFDATE = "non_portfolio";

    /**
     * @var string
     */
    protected $_ncw_model_name = "";

    /**
     * @var string
     */
    protected $_ncw_name = "";
    
    /**
     * @var string
     */
    protected $_ncw_name_zh = "";

    /**
     * @var SimpleXMLElement
     */
    protected $_xml_element = null;

    /**
     * @var Ncw_Model
     */
    public $model =  null;

    /**
     * @var string
     */
    protected $_name = "";

    /**
     * @var string
     */
    protected $_language = "";

    /**
     * @var array
     */
    protected $_languages = array();

    /**
     * @var string
     */
    protected $_sds = "";

    /**
     * @var array
     */
    protected $_documents = array();

    /**
     * @var array
     */
    protected $_attributes = array();

    /**
     * @var array
     */
    protected $_regions = array();

    /**
     * @var time
     */
    protected $_import_time = 0;

    /**
     * @var array
     */
    protected $_processing_notes = array();

    /**
     * @var bool
     */
    public $is_portfolio = false;

    /**
     * @param SimpleXMLElement $xml_element
     */
    public function __construct (SimpleXMLElement $xml_element = null, $is_portfolio = false)
    {
        $this->_xml_element = $xml_element;
				
				//echo $xml_element;
        
        
        $this->is_portfolio = $is_portfolio;

        $ncw_name_exploded = explode('_', $this->_ncw_model_name);
        $this->_ncw_name = array_pop($ncw_name_exploded);
        $this->model = new $this->_ncw_model_name();

        $this->_loadName();
        if ($this->_ncw_model_name == "Tpepdb2_Brand") {
          $this->_loadZhName();
        }
        $this->_loadLanguages();
        $this->_loadDescription();
        $this->_loadDocuments();
        $this->_loadAttributes();
        $this->_loadProcessingNote();
    }

    /**
     *
     */
    protected function _loadName ()
    {
        $this->_name = trim((string) $this->_xml_element->DESCRIPTIONS_SHORT[0]->DESCRIPTION_SHORT);
    }

    /**
     *
     */
    protected function _loadLanguages ()
    {

    }

    /**
     *
     */
    protected function _loadDescription ()
    {
        if (true === isset($this->_xml_element->DESCRIPTIONS_LONG)) {
            foreach ($this->_xml_element->DESCRIPTIONS_LONG->DESCRIPTION_LONG as $description_long) {
                if (true === isset($description_long["lang"])
                    && false === empty($description_long["lang"])
                ) {
                    $lang = $this->_getLanguageCode((string) $description_long["lang"]);
                } else {
                    $lang = $this->_language;
                }
                if ((string) $description_long["type"] == "WLBM-0001") {
                    $name = "description";
                } else if ((string) $description_long["type"] == "WLBM-ZCM1") {
                    $name = "text1";
                } else if ((string) $description_long["type"] == "WLBM-ZCM2") {
                    $name = "text2";
                }
                $this->addAttribute($name, (string) $description_long, "text", $lang, null);
            }
        }
    }

    /**
     *
     */
    protected function _loadDocuments ()
    {
        $documents_count = count($this->_xml_element->DOCUMENTS->DOCUMENT);
        for ($i = 0; $i < $documents_count; ++$i) {
            $document_path = (string) $this->_xml_element->DOCUMENTS->DOCUMENT[$i];
            $document_path_exploded = explode("/", str_replace("\\", "/", $document_path));
            $document_name = array_pop($document_path_exploded);

            if (true === isset($this->_xml_element->DOCUMENTS->DOCUMENT[$i]["lang"])
                && false === empty($this->_xml_element->DOCUMENTS->DOCUMENT[$i]["lang"])
            ) {
                $document_lang = $this->_getLanguageCode((string) $this->_xml_element->DOCUMENTS->DOCUMENT[$i]["lang"]);
            } else {
                $document_lang = $this->_language;
            }
            $headline = "";
            if (true === isset($this->_xml_element->DOCUMENTS->DOCUMENT[$i]->DESCRIPTIONS_SHORT)) {
                foreach ($this->_xml_element->DOCUMENTS->DOCUMENT[$i]->DESCRIPTIONS_SHORT->DESCRIPTION_SHORT as $description_short) {
                    if ($this->_getLanguageCode((string) $description_short["lang"]) == $document_lang) {
                        $headline = (string) $description_short;
                    }
                }
            }
            $text = "";
            if (true === isset($this->_xml_element->DOCUMENTS->DOCUMENT[$i]->DESCRIPTIONS_LONG->DESCRIPTION_LONG)) {
                foreach ($this->_xml_element->DOCUMENTS->DOCUMENT[$i]->DESCRIPTIONS_LONG->DESCRIPTION_LONG as $description_long) {
                    if ($this->_getLanguageCode((string) $description_long["lang"]) == $document_lang) {
                        $text = (string) $description_long;
                    }
                }

            }

           $this->_documents[] = array(
                "name" => $document_name,
                "language" => $document_lang,
                "type" => $this->_xml_element->DOCUMENTS->DOCUMENT[$i]["type"],
                "headline" => $headline,
                "text" => $text
            );

            if (false == in_array($document_name, TpePdbXmlImporter::$documents)) {
                TpePdbXmlImporter::$documents[] = $document_name;
            }
        }
    }

    /**
     *
     */
    protected function _loadAttributes ()
    {
        if (true === isset($this->_xml_element->CLASSIFICATION[0])) {
            $xml_element = $this->_xml_element->CLASSIFICATION[0];

            $this->_addAttributes($xml_element->ATTRIBUTE);

            // COMPLEX ATTRIBUTES
            $attributess_count = count($xml_element->ATTRIBUTES);
            for ($i = 0; $i < $attributess_count; ++$i) {
                $this->_addAttributes($xml_element->ATTRIBUTES[$i]->ATTRIBUTE);
            }
        }
    }

    /**
     * @param string $attributes_xml
     */
    protected function _addAttributes ($attributes_xml)
    {
        $attributes_count = count($attributes_xml);
        for ($i = 0; $i < $attributes_count; ++$i) {
            $continue = true;
            $attribute = $attributes_xml[$i];

            $attribute["internal_id"] = str_replace(array("CAT_", "CAT"), "", $attribute["internal_id"]);

            if ($attribute["internal_id"] == "REGION") {
                $this->_regions[] = trim((string) $attribute->VALUE);
                $continue = false;
            }
            switch ($attribute["internal_id"]) {
            case "SRS_SDS":
                $this->_sds = (string) $attribute->VALUE;
                $continue = false;
                break;
            case "QPMK_BEZEICHNUNG":
                $continue = false;
            case "QPMK_NORM":
                $continue = false;
                break;
            case "QPMK_FOOTER":
                $continue = false;
                break;
            default:
                $attribute_name = (string) $attribute["internal_id"];
                if ($attribute_name[0] == "0") {
                    $attribute_name = (int) $attribute_name;
                }
                break;
            }
            if (true === $continue) {
                $attribute_type = (string) $attribute["type"];
                if (false === empty($attribute_name)) {
                    $attribute_value = "";
                    $attribute_name = strtolower($attribute_name);
                    $attribute_num = null;
                    if ($attribute_name == "materialvorteile"
                        || $attribute_name == "markets"
                        || $attribute_name == "anwendungsbereiche"
                    ) {
                        if (true === isset($attribute->VALUE_TEXT)) {
                            $attribute_values = array();
                            foreach ($attribute->VALUE_TEXT as $value_text) {
                                $add = "";
                                if (true === isset($attribute->DESCRIPTIONS_LONG)) {
                                    $desc_lang = (string) $value_text["lang"];
                                    foreach ($attribute->DESCRIPTIONS_LONG->DESCRIPTION_LONG as $desription_long) {
                                        if ((string) $desription_long["lang"] == $desc_lang) {
                                            $add = (string) $desription_long;
                                            break;
                                        }
                                    }
                                }
                                $value_text[0] = $value_text . $add;
                                $attribute_values[] = $value_text;
                            }
                        } else {
                            $attribute_values = array();
                            foreach ($attribute->DESCRIPTIONS_LONG->DESCRIPTION_LONG as $desription_long) {
                                $attribute_values[] = $desription_long;
                            }
                        }
                        $attribute_num = (int) $attribute->VALUE;
                    } else if (true === isset($attribute->VALUE_TEXT)) {
                        $attribute_values = $attribute->VALUE_TEXT;
                    } else {
                        $attribute_values = $attribute->VALUE;
                    }

                    // Attribute
                    foreach ($attribute_values as $attribute_value) {
                        if (true === isset($attribute_value["lang"])
                            && false === empty($attribute_value["lang"])
                        ) {
                            $attribute_lang = $this->_getLanguageCode((string) $attribute_value["lang"]);
                            $this->addAttribute($attribute_name, trim((string) $attribute_value), $attribute_type, $attribute_lang, $attribute_num);
                        } else {
                            foreach ($this->_languages as $language) {
                                $attribute_lang = $this->_getLanguageCode((string) $language);
                                $this->addAttribute($attribute_name, trim((string) $attribute_value), $attribute_type, $attribute_lang, $attribute_num);

                                if ($attributes_xml[$i + 2]["internal_id"] == "QPMK_FOOTER" || $attributes_xml[$i + 3]["internal_id"] == "QPMK_FOOTER") {
                                    if ($attributes_xml[$i + 3]["internal_id"] == "QPMK_FOOTER") {
                                        $desc_long = $attributes_xml[$i + 3];
                                    } else {
                                        $desc_long = $attributes_xml[$i + 2];
                                    }
                                    $descriptions_long_count = count($desc_long->DESCRIPTIONS_LONG->DESCRIPTION_LONG);
                                    for ($z = 0; $z < $descriptions_long_count; ++$z) {
                                        $description_long = $desc_long->DESCRIPTIONS_LONG->DESCRIPTION_LONG[$z];
                                        if (true === isset($description_long["lang"])
                                            && false === empty($description_long["lang"])
                                        ) {
                                            $label_lang = $this->_getLanguageCode((string) $description_long["lang"]);
                                            TpePdbXmlImporter::addLabel(strtolower($this->_ncw_name), $attribute_name . "_note", (string) $description_long, $label_lang);
                                        }
                                    }
                                }
                            }
                            
							$this->_addLabel($attributes_xml, $attribute_name, $i, "NORM", "norm");

							$this->_addLabel($attributes_xml, $attribute_name, $i, "BEZEICHNUNG", "name");
    	                }
                    }

                    // UNIT
                    if (true === isset($attribute->UNITS)) {
                        foreach ($attribute->UNITS->UNIT as $unit) {
                            $lang = $this->_getLanguageCode((string) $unit["lang"]);
                            $this->addAttribute($attribute_name . "_unit", (string) $unit, "char", $lang, $attribute_num);
                        }
                    }

                    if ($attribute_name != "materialvorteile"
                        && $attribute_name != "markets"
                        && $attribute_name != "anwendungsbereiche"
                    ) {
                        // LABELS
                        if (true === isset($attribute->DESCRIPTIONS_SHORT)) {
                            $descriptions_short_count = count($attribute->DESCRIPTIONS_SHORT->DESCRIPTION_SHORT);
                            for ($z = 0; $z < $descriptions_short_count; ++$z) {
                                $description_short = $attribute->DESCRIPTIONS_SHORT->DESCRIPTION_SHORT[$z];
                                if (true === isset($description_short["lang"])
                                    && false === empty($description_short["lang"])
                                ) {
                                    $label_lang = $this->_getLanguageCode((string) $description_short["lang"]);
                                    TpePdbXmlImporter::addLabel(strtolower($this->_ncw_name), $attribute_name, (string) $description_short, $label_lang);
                                }
                            }
                            /*$descriptions_long_count = count($attribute->DESCRIPTIONS_LONG->DESCRIPTION_LONG);
                            for ($z = 0; $z < $descriptions_long_count; ++$z) {
                                $description_long = $attribute->DESCRIPTIONS_LONG->DESCRIPTION_LONG[$z];
                                if (true === isset($description_long["lang"])
                                    && false === empty($description_long["lang"])
                                ) {
                                    $label_lang = $this->_getLanguageCode((string) $description_long["lang"]);
                                    TpePdbXmlImporter::addLabel(strtolower($this->_ncw_name), $attribute_name . "_note", (string) $description_long, $label_lang);
                                }
                            }*/
                        }
                    }
                }
            }
        }
    }

	protected function _addLabel ($attributes_xml, $attribute_name, $i, $name, $label_name) 
	{
		for ($x = 1; $x < 4; ++$x) {
      		if (false !== strpos($attributes_xml[$i + $x]["internal_id"], $name)) {
        		$label = $attribute_name . "_" . str_replace(array("QPMK_", "DIN_"), "", (string) $attributes_xml[$i + $x]["internal_id"]);

                $descriptions = array();
                if (true === isset($attributes_xml[$i + $x]->DESCRIPTIONS_LONG)) {
                    foreach ($attributes_xml[$i + $x]->DESCRIPTIONS_LONG->DESCRIPTION_LONG as $description) {
                        $descriptions[$this->_getLanguageCode((string) $description["lang"])] = trim($description);
                    }
                }

                foreach ($attributes_xml[$i + $x]->VALUE_TEXT as $text) {
                    $value = $text;
                    if (true === isset($value["lang"])
                        && false === empty($value["lang"])
                    ) {
                        $lang = $this->_getLanguageCode((string) $value["lang"]);
                        
                        if (true == isset($descriptions[$lang])) {
                            $value = trim($value) . $descriptions[$lang];
                        }
                        TpePdbXmlImporter::addLabel(strtolower($this->_ncw_name), $attribute_name , trim($value), $lang);
                        TpePdbXmlImporter::addLabel(strtolower($this->_ncw_name), $attribute_name . "_" . $label_name, trim($value), $lang);
                    }
                }
            	//$count = count($attributes_xml[$i + $x]->VALUE_TEXT);
           		//for ($z = 0; $z < $count; ++$z) {

            	//}
        	}
        }
	}


    /**
     * @param string $attribute_name
     * @param string $attribute_value
     * @param string $attribute_type
     * @param strin $attribute_lang
     * @param int $attribute_num
     */
    public function addAttribute ($attribute_name, $attribute_value, $attribute_type = "char", $attribute_lang = "", $attribute_num = null)
    {
        if (false === isset($this->_attributes[$attribute_lang])) {
            $this->_attributes[$attribute_lang] = array();
        }
        if (true === isset($this->_attributes[$attribute_lang][strtolower($attribute_name)])) {
            if (true === is_array($this->_attributes[$attribute_lang][strtolower($attribute_name)]["value"])) {
                $this->_attributes[$attribute_lang][strtolower($attribute_name)]["value"][(int) $attribute_num] = str_replace(array("<P>", "</P>"), "", (string) $attribute_value);
            }
        } else {
            if ($attribute_num !== null && $attribute_num > 0) {
                $attribute_value = array(
                    (int) $attribute_num => str_replace(array("<P>", "</P>"), "", (string) $attribute_value)
                );
            }
            $this->_attributes[$attribute_lang][strtolower($attribute_name)] = array(
                "value" => $attribute_value,
                "type" => $attribute_type,
                "num" => (int) $attribute_num,
                "language" => $attribute_lang
            );
        }
    }

    /**
     *
     */
    protected function _loadProcessingNote ()
    {
        if (true === isset($this->_xml_element->PROCESSING[0])) {
            $count = 0;
            foreach ($this->_xml_element->PROCESSING as $processing_note) {
                if (false === isset($processing_note->DESCRIPTIONS_SHORT)) {
                    return;
                }

                if (false === isset($this->_processing_notes[$count])) {
                    $this->_processing_notes[$count] = array();
                }

                $names = array();
                foreach ($processing_note->DESCRIPTIONS_SHORT->DESCRIPTION_SHORT as $description) {
                    $lang = $this->_getLanguageCode((string) $description["lang"]);
                    $names[$lang] = (string) $description;
                }

                $attributes_count = count($processing_note->ATTRIBUTE);
                for ($i = 0; $i < $attributes_count; ++$i) {
                    $attribute = $processing_note->ATTRIBUTE[$i];
                    $attribute_name = strtolower((string) $attribute["internal_id"]);
                    if (true === isset($attribute->DESCRIPTIONS_SHORT)) {
                        foreach ($attribute->DESCRIPTIONS_SHORT->DESCRIPTION_SHORT as $description) {
                            $lang = $this->_getLanguageCode((string) $description["lang"]);
                            /*if (false === isset($this->_processing_notes[$count][$attribute_name])) {
                                $this->_processing_notes[$count][$attribute_name] = array();
                                $this->_processing_notes[$count][$attribute_name][$lang] = array();
                            }*/
                            $this->_processing_notes[$count][$attribute_name][$lang]["type"] = (string) $names[$lang];
                            $this->_processing_notes[$count][$attribute_name][$lang]["name"] = trim((string) $description);
                            //$this->_processing_notes[$count][$names[$lang]][$attribute_name][$lang]["num"] = $i;
                        }
                    }
                    if (true === isset($attribute->DESCRIPTIONS_LONG)) {
                        foreach ($attribute->DESCRIPTIONS_LONG->DESCRIPTION_LONG as $description) {
                            $lang = $this->_getLanguageCode((string) $description["lang"]);
                            $this->_processing_notes[$count][$attribute_name][$lang]["text"] = trim((string) $description);
                        }
                    }
                }
                ++$count;
            }
        }
    }

    /**
     * @param int $import_time
     * @return int
     */
    public function import ($import_time)
    {
        $this->_import_time = (int) $import_time;

        $this->model->create();

        list($item_id, $last_import) = $this->_checkIfItemExists();
        if ($item_id !== null) {
            $this->model->setId($item_id);
        }

        if ($last_import == $this->_import_time) {
            return $item_id;
        }

        $this->_alterDatabase($this->model);

        $this->model->setName($this->_name);
        if ($this->_ncw_model_name == "Tpepdb2_Brand") {
          $this->model->setNameZh($this->_name_zh);
        }
        if ($this->_ncw_name == "Compound") {
            $this->model->setSafetydata($this->_sds);
        }
        $this->model->setLastImport(date("Y-m-d H:i:s", $this->_import_time));
        if (true === $this->is_portfolio) {
            $this->model->setStatus(TpePdbXmlItem::$STATUS_UPTODATE);
        } else {
            $this->model->setStatus(TpePdbXmlItem::$STATUS_OUTOFDATE);
        }
        $this->model->save(false);
        $item_id = $this->model->getId();

        $additional_tables_truncated = array();
        foreach ($this->_attributes as $attributes_lang => $lang_attributes) {
            $item_values = $this->_getItemValuesObject($attributes_lang);
            foreach ($lang_attributes as $attribute_key => $attribute) {
                if (true === is_array($attribute["value"])) {
                    $db_table_name1 = $this->model->db->getConfig("prefix") . "tpepdb2" . "_" . strtolower($attribute_key);
                    $db_table_name2 = $this->model->db->getConfig("prefix") . "tpepdb2" . "_" . strtolower($this->_ncw_name) . "_" . strtolower($attribute_key);
                    $column_name1 = strtolower($this->_ncw_name) . "_id";
                    $column_name2 = strtolower($attribute_key) . "_id";

                    foreach ($attribute["value"] as $num => $value) {
                        $value = addslashes($value);
                        $sql = "SELECT `id` FROM " . $db_table_name1 . " WHERE num=:num LIMIT 1";
                        $sth = $this->model->db->prepare($sql);
                        $sth->bindValue(":num", $num);
                        $sth->execute();
                        $result = $sth->fetch();
                        $related_id = 0;
                        if (true === empty($result["id"])) {
                            $sql = "INSERT INTO `" . $db_table_name1 . "` "
                                . "(`" . $attribute["language"] . "`, `num`, `last_import`) VALUES ";
                            $sql .= "('"
                                . $value . "', '"
                                . $num . "', '"
                                . date("Y-m-d H:i:s", $this->_import_time)
                                . "')";
                            $this->model->db->exec($sql);
                            $related_id = $this->model->db->lastInsertId();
                        } else {
                            $related_id = $result["id"];
                            $sql = "UPDATE `" . $db_table_name1 . "` SET `" . $attribute["language"] . "` = '" . $value . "', `last_import`='" . date("Y-m-d H:i:s", $this->_import_time) . "' WHERE id=" . $related_id;
                            $this->model->db->exec($sql);
                        }
                        if ($related_id > 0) {
                            $sql = "INSERT INTO `" . $db_table_name2 . "` "
                            . "(`" . $column_name1 . "`, `" . $column_name2 . "`, `last_import`)
                            VALUES ('" . $this->model->getId() . "', '". $related_id . "', '" . date("Y-m-d H:i:s", $this->_import_time) . "')
                            ON DUPLICATE KEY UPDATE `last_import`='" . date("Y-m-d H:i:s", $this->_import_time) . "';";

                            $this->model->db->exec($sql);
                        }
                    }
                } else {
                    if ($item_values !== null) {
                        $item_values->data[$attribute_key] = $attribute["value"];
                    }
                }
            }

            if ($item_values !== null) {
                $item_values->{"set" . $this->_ncw_name . "Id"}($item_id);
                $item_values->save(false);
                unset($item_values);
            }
        }

        $this->_importDocuments();

        $this->_addToRegions();

        $this->_addProcessingNote();

        unset($this->_processing_notes, $this->_attributes, $this->_documents);

        return $this->model->getId();
    }

    /**
     * @param string $language
     * @return Ncw_Model
     */
    protected function _getItemValuesObject ($language)
    {
        $language = (string) $language;
        $ncw_name_lower = strtolower($this->_ncw_name);
        $class_name = "Tpepdb2_" . $this->_ncw_name . "Values";
        $file_name = MODULES . DS . "tpepdb2" . DS . "models" . DS . $this->_ncw_name . "Values" . ".php";
        if (true === file_exists(TpePdbXmlImporter::$ROOT_PATH . $file_name)) {
            $item_values = new $class_name();
            if ($this->model->getId() > 0) {
                $item_values->unbindModel("all");
                $found_item_values = $item_values->fetch(
                    "first",
                    array(
                        "fields" => array($this->_ncw_name . "Values.id"),
                        "conditions" => array(
                            $ncw_name_lower . "_id" => $this->model->getId(),
                            "language" => $language
                        )
                    )
                );
                if (false !== $found_item_values) {
                    $item_values->setId($found_item_values->getId());
                }
            }
            $item_values->setLanguage($language);
        } else {
            $item_values = null;
        }
        return $item_values;
    }


    /**
     * @return array
     */
    protected function _checkIfItemExists ()
    {
        $this->model->unbindModel("all");
        $found_item = $this->model->findBy(
            "name",
            $this->_name,
            array(
                "fields" => array(
                    $this->_ncw_name . ".id",
                    $this->_ncw_name . ".last_import"
                )
            )
        );
        if (false !== $found_item) {
            return array($found_item->getId(), strtotime($found_item->getLastImport()));
        }
        return array(null, null);
    }

    /**
     * param int $item_id
     */
    protected function _importDocuments ()
    {
        $rel_item_model_name = (string) $this->_ncw_model_name . "Document";
        $rel_item_name_exploded = explode('_', $rel_item_model_name);
        $rel_item_name = array_pop($rel_item_name_exploded);
        unset($rel_item_name_exploded);

        if (false === file_exists(TpePdbXmlImporter::$ROOT_PATH . MODULES . DS . "tpepdb2" . DS . "models" . DS . $rel_item_name . ".php")) {
            return null;
        }

        $item_document_model = new $rel_item_model_name();
        $found_item_documents = $item_document_model->fetch(
            "all",
            array(
                "fields" => array(
                    "Document.name",
                    "Document.language",
                    $rel_item_name . ".id",
                ),
                "conditions" => array(
                    $rel_item_name . "." . strtolower(trim($this->_ncw_name)) . "_id" => $this->model->getId(),
                    $rel_item_name . ".id" . " IS NOT NULL"
                )
            )
        );
        $existing_item_documents = array();
        foreach ($found_item_documents as $item_document) {
            $existing_item_documents[$item_document->Document->getLanguage()][$item_document->Document->getName()] = $item_document->getId();
        }
        unset($found_item_documents);

        foreach ($this->_documents as $document) {
            if (true === isset($existing_item_documents[$document["language"]][$document["name"]])) {
                $existing_item_documents[$document["language"]][$document["name"]] = "used";
                continue;
            }
            $document_id = $this->_checkIfDocumentExists($document);
            if ($document_id === null) {
                $document_id = $this->_addNewDocument($document);
                if ($document_id === null) {
                    continue;
                }
            }
            $item_document_model->create();
            $item_document_model->{"set" . $this->_ncw_name . "Id"}($this->model->getId());
            $item_document_model->setDocumentId($document_id);
            $item_document_model->setType($document["type"]);
            $item_document_model->save();
        }

        foreach ($existing_item_documents as $lang => $lang_documents) {
            foreach ($lang_documents as $item_document_id) {
                if ($document !== "used") {
                    $item_document_model->setId($item_document_id);
                    $item_document_model->delete();
                }
            }
        }
    }

    /**
     * @param array $document_file
     * @return int
     */
    protected function _addNewDocument ($document)
    {
			$document["name"] = trim($document["name"]);
			$document["language"] = trim($document["name"]);
        if (true === file_exists(TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . trim($document["name"]))) {
            $src_dir = TpePdbXmlImporter::$DOCUMENTS_DEST_DIR . DS . $document["language"];
            if (false === is_dir($src_dir)) {
                mkdir($src_dir);
            }
            $document_path = TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . trim($document["name"]);
            copy(
                $document_path,
                $src_dir . DS . trim($document["name"])
            );
            chmod($document_path, 0777);

            $document_model = new Tpepdb2_Document();
            $document_model->setName($document["name"]);
            $document_model->setHeadline($document["headline"]);
            $document_model->setText($document["text"]);
            $document_model->setLanguage($document["language"]);
            $document_model->save();

            return $document_model->getId();
        } else {
            $message = "";
            $message .= "Import: " . date("Y-m-d H:i:s", $this->_import_time) . "\t";
            $message .= "Message: File " . TpePdbXmlImporter::$DOCUMENTS_SOURCE_DIR . DS . $document["name"] . " does not exist";
            TpePdbXmlImporter::$FILE_LOG->log($message);

            return null;
        }
    }

    /**
     * @param array $document
     * @return int
     */
    protected function _checkIfDocumentExists ($document)
    {
			$document["name"] = trim($document["name"]);
			$document["language"] = trim($document["name"]);
        $document_model = new Tpepdb2_Document();
        $document_model->unbindModel("all");
        $document = $document_model->fetch(
            "first",
            array(
                "fields" => array("Document.id"),
                "conditions" => array(
                    "Document.name" => trim($document["name"]),
                    "Document.language" => trim($document["language"]),
                )
            )
        );
        if (false !== $document) {
            return $document->getId();
        }
        return null;
    }

    /**
     *
     */
    protected function _addToRegions ()
    {
        $db_table_name = $this->model->db_table_name . "_region";
        $db = $this->model->db;
        $name = strtolower($this->model->name);

        foreach ($this->_regions as $region) {
            $region_id = 0;
            switch ($region) {
                case "E":
                    $region_id = 1;
                    break;
                case "U":
                    $region_id = 2;
                    break;
                case "A":
                    $region_id = 3;
                    break;
            }

            $sql = "INSERT INTO " . $db_table_name . " (`" . $name . "_id`, `region_id`) VALUES (:id, :region_id)";
            $sth = $db->prepare($sql);
            $sth->bindValue("id", $this->model->getId());
            $sth->bindValue("region_id", $region_id);
            $sth->execute();
        }
    }

    /**
     *
     */
    protected function _addProcessingNote ()
    {
        $db = $this->model->db;

        // delete all processing notes belonging to the compound
        $compound_id = $this->model->getId();
        $sql = "DELETE FROM `ncw_tpepdb2_processingnote` WHERE `compound_id` = '" . $compound_id . "';";
        $db->exec($sql);

        $processing_note_obj = new Tpepdb2_Processingnote();
        foreach ($this->_processing_notes as $count => $processing_note) {
            $num = 0;
            foreach ($processing_note as $key => $values) {
                /*$note = $processing_note_obj->fetch(
                    "first",
                    array(
                        "fields" => array(
                            "Processingnote.id"
                        ),
                        "conditions" => array(
                            "Processingnote.compound_id" => $this->model->getId(),
                            "Processingnote.internal_name" => $key
                        )
                    )
                );
                if ($note !== false) {
                    $processing_note_obj->data["id"] = $note->getId();
                }*/

                /*if (true === isset(TpePdbXmlImporter::$processing_notes[$this->model->getId() . "_" . $key])) {
                    $processing_note_obj->data["id"] = TpePdbXmlImporter::$processing_notes[$this->model->getId() . "_" . $key];
                }*/

                $processing_note_obj->data["internal_name"] = $key;
                $processing_note_obj->data["num"] = $num++;
                foreach ($values as $lang => $value) {
                    $processing_note_obj->data[$lang . "_type"] = $value["type"];
                    $processing_note_obj->data[$lang . "_name"] = $value["name"];
                    $processing_note_obj->data[$lang . "_text"] = $value["text"];
                }
                //$processing_note_obj->setLastImport(date("Y-m-d H:i:s", $this->_import_time));
                $processing_note_obj->setCompoundId($compound_id);
                $processing_note_obj->save(false);
                $processing_note_obj->create();
            }
        }
    }

    /**
     * @param Ncw_Model $model
     */
    protected function _alterDatabase ()
    {
        $this->_createAttributeColumns($this->model->db_table_name . "_values", $this->_attributes);
        $this->_createTables();
    }

    /**
     * @param string $db_table_name
     * @param array $attributes
     */
    protected function _createAttributeColumns ($db_table_name, $attributes)
    {
        $db_table_name = (string) $db_table_name;

        $fields = array();
        $result = $this->model->db->query("SHOW COLUMNS FROM " . $db_table_name);
        if ($result !== false) {
            foreach ($result->fetchAll() as $row) {
                $fields[] = $row["Field"];
            }

            $new_columns = array();
            foreach ($attributes as $attribute_lang => $lang_attributes) {
                foreach ($lang_attributes as $attribute_name => $attribute) {
                    if (true === is_array($attribute["value"])) {
                        continue;
                    }
                    if (false === in_array($attribute_name, $fields)) {
                        $new_columns[] = "ADD `" . $attribute_name . "` " . $this->_getSQLAttributeType($attribute["type"], $attribute_name);
                    }
                }
            }
            $new_columns = array_unique($new_columns);
            if (count($new_columns) > 0) {
                $this->model->db->exec("ALTER TABLE `" . $db_table_name . "` " . implode(", ", $new_columns) . ";");
            }
        }
    }

    /**
     *
     */
    protected function _createTables ()
    {
        $db = $this->model->db;
        $db_table_prefix = $db->getConfig("prefix") . "tpepdb2";

        /*$tables = array();
        $result = $db->query("SHOW TABLES LIKE '" . $db_table_prefix ."%'");
        foreach ($result->fetchAll() as $row) {
            $tables[] = $row[0];
        }*/
        $fields = array();
        $ncw_name = strtolower($this->_ncw_name);
        foreach ($this->_attributes as $attribute_lang => $lang_attributes) {
            foreach ($lang_attributes as $attribute_name => $attribute) {
                if (true === is_array($attribute["value"])) {
                    $db_table_name = $db_table_prefix . "_" . strtolower($attribute_name);

                    if (false === isset($fields[$attribute_name])) {
                        $fields[$attribute_name] = array();
                        $result = $this->model->db->query("SHOW COLUMNS FROM " . $db_table_name);
                        if ($result !== false) {
                            foreach ($result->fetchAll() as $row) {
                                $fields[$attribute_name][] = $row["Field"];
                            }
                        }
                    }

                    if (false === in_array($attribute["language"], $fields[$attribute_name])) {
                        $this->model->db->exec("ALTER TABLE `" . $db_table_name . "` ADD `" . $attribute["language"] . "` VARCHAR(255);");
                    }

                    /*if (false === in_array($db_table_name, $tables)) {
                        $sql = "CREATE TABLE IF NOT EXISTS `" . $db_table_name . "` (
                          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                          `value` " . $this->_getSQLAttributeType($attribute["type"]) . " NOT NULL,
                          `num` tinyint(3) unsigned NOT NULL,
                          `language` varchar(4) NOT NULL,
                          `created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
                          `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00',
                          PRIMARY KEY (`id`),
                          UNIQUE (`value`, `num`, `language`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
                        ";
                        $db->exec($sql);
                        $db_table_name = $db_table_prefix . "_" . $ncw_name . "_" . strtolower($attribute_name);
                        $sql = "CREATE TABLE IF NOT EXISTS `" . $db_table_name . "` (
                          `" . strtolower($attribute_name) . "_id` int(10) unsigned NOT NULL,
                          `" . $ncw_name . "_id` int(10) unsigned NOT NULL,
                          PRIMARY KEY (`" . strtolower($attribute_name) . "_id`, `" . $ncw_name . "_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
                        ";
                        $db->exec($sql);
                    }*/

                    /*$attributes = array();
                    foreach ($attribute["value"] as $attribute_value) {
                        $attributes["value"] = array(
                            "value" => $attribute_value,
                            "type" => $attribute["type"],
                        );
                    }
                    $this->_createAttributeColumns($db, $db_table_name, $attributes);*/
                }
            }
        }
    }

    /**
     * @param string $language
     */
    protected function _getLanguageCode ($language)
    {
        switch ($language) {
            case "ger":
                return "de";
            case "eng":
                return "en";
            case "ita":
                return "it";
            case "spa":
                return "es";
            case "fre":
                return "fr";
            case "kor":
                return "kr";
            case "jpn":
                return "jp";
            case "chi":
                return "zh";
            case "pol":
                return "pl";
            default:
                return $language;
        }
    }

    /**
     * @param string $attribute_type
     */
    protected function _getSQLAttributeType ($attribute_type, $attribute_name)
    {
        switch ($attribute_type) {
            case "char":
                $varchar_size = "50";
                $parts = explode('_', $attribute_name);
                if (true === isset($parts[1])) {
                    if ($parts[1] == "unit") {
                        $varchar_size = "20";
                    } else if ($parts[1] == "norm") {
                        $varchar_size = "150";
                    }
                }
                return "VARCHAR(" . $varchar_size . ")";
                break;
            case "num":
                return "FLOAT";
                break;
            case "text":
                return "TEXT";
                break;
            default:
                return "VARCHAR(255)";
                break;
        }
    }
}
?>
