<?php

class TpePdbModel extends Ncw_Model {

    /**
     * @param string $search_value
     * @param string $language
     * @param int $region_id
     * @param boolean $with_templates
     * @return string,array
     */
    public function search ($search_value, $language, $region_id = null, $with_template = true, $language_id, $brand = false)
    {
        $search_value = html_entity_decode((string) $search_value);
        $name_lower = strtolower($this->name);
        $region = false;

        $condition_string = $this->name . ".name LIKE '%" . $search_value . "%'";
        if (true === $brand) {
            $condition_string = "(" . $condition_string. " || " . "Brand.name LIKE '%" . $search_value. "%' || " . "Brand.name LIKE '%" . str_replace(" ", "® ", $search_value). "%')";
        }
        $conditions = array(
            $condition_string
        );
        if (true === is_int($region_id) && $region_id > 0) {
            $region = true;
            $conditions[] = "SerieRegion.region_id = " . $region_id;
        }
        if (false == TpePdbWeb::rights()) {
            $conditions[] = $this->name . ".status = 'portfolio'";
        }

        if ($name_lower == "serie") {
            $order = "brand_id";
        } else {
            $order = "serie_id";
        }

        ${$name_lower . "s"} = $this->_readAllOf(
            $this->name,
            $language,
            $conditions,
            $region,
            array($this->name . "." . $order, $this->name . ".name"),
            "50"
        );

        if (true === $with_template) {
            ob_start();
            include_once ASSETS . DS . "tpepdb2" . DS . "templates" . DS . "search_templates" . DS . $name_lower . "_search.phtml";
            return ob_get_clean();
        }

        return ${$name_lower . "s"};
    }

    /**
     * @param string $language
     * @param mixed $template
     * @return string
     */
    public function template ($language, $language_id, $template = "")
    {
        if ($this->getId() > 0) {
            $compound = $serie = $brand = $compounds = $processingnote = $labels = array();

            list($data, $languages) = $this->_readData($this->name, $this->getId(), $language);

            if ($data['name'] == '') {
                return;
            }
			// EDIT: Winni am 15.05 _0 für was ist diese Abfrage gut???
			// Warum hier not_avalible
            if ($data['language'] == '' && $data['status'] == "non_portfolio_O") {
                include_once ASSETS . DS . "tpepdb2" . DS . "templates" . DS . "not_available.phtml";
                return;
            } else if ($data['language'] == '') {
                $language_us = false;
                if ($language == "us") {
                    $language_us = true;
                }
                list($data, $languages, $language) = $this->_getExistingLanguage($this->name);
            }

            if ($data['status'] == 'non_portfolio' && false == TpePdbWeb::rights()) {
                include_once ASSETS . DS . "tpepdb2" . DS . "templates" . DS . "permission_denied.phtml";
                return;
            }

            $processingnotes = array();

            if ($language == 'de' || $language == 'fr' || $language == 'it' || $language == 'en') {
            	$orderlanguage = $language;
            } else {
            	$orderlanguage = 'en';
            }

            switch ($this->name) {
                case "Compound":
                    $compound = $data;
                    $processingnote = new Tpepdb2_Processingnote();
                    $processingnotes_obj = $processingnote->fetch(
                        "all",
                        array(
                            "fields" => array(
                                "Processingnote." . $language . "_type",
                                "Processingnote." . $language . "_name",
                                "Processingnote." . $language . "_text",
                                "Processingnote.en_type"
                            ),
                            "conditions" => array(
                                "Processingnote.compound_id" => $this->getId(),
                            ),
                            "order" => array("Processingnote.en_type ASC", "Processingnote.num ASC")
                        )
                    );
                    if (false !== $processingnotes_obj) {
                        $processingnotes = array();
                        foreach ($processingnotes_obj as $processingnote) {
                            $processingnotes[$processingnote->data[$orderlanguage ."_type"]][$processingnote->data[$language ."_name"]] = $processingnote->data[$language ."_text"];
                        }
                    }

                    list($serie) = $this->_readData("Serie", $compound["serie_id"], $language);
                    list($brand) = $this->_readData("Brand", $compound["brand_id"], $language);
                    break;
                case "Serie":
                    $serie = $data;

                    $compounds = $this->_readAllOf(
                        "Compound",
                        $language,
                        array(
                            "Compound.serie_id" => $this->getId()
                        ),
                        false,
                        array("Compound.name", "CompoundValues.103")
                    );

                    $processingnote = new Tpepdb2_Processingnote();
                    $processingnotes_obj = $processingnote->fetch(
                        "all",
                        array(
                            "fields" => array(
                                "Processingnote." . $language . "_type",
                                "Processingnote." . $language . "_name",
                                "Processingnote." . $language . "_text",
                                "Processingnote.en_type"
                            ),
                            "conditions" => array(
                                "Compound.serie_id" => $this->getId(),
                            ),
                            "order" => array("Processingnote.en_type ASC", "Processingnote.num ASC")
                        )
                    );
                    if (false !== $processingnotes_obj) {
                        $processingnotes = array();
                        foreach ($processingnotes_obj as $processingnote) {
                            $processingnotes[$processingnote->data[$orderlanguage . "_type"]][$processingnote->data[$language ."_name"]] = $processingnote->data[$language ."_text"];
                        }
                    }

                    list($brand) = $this->_readData("Brand", $serie["brand_id"], $language);
                    break;
            }
            unset($data);

            $all_values = array_merge($compound, $serie, $brand);
            $labels = $this->_loadLabels($language, $all_values);
            unset($all_values);

            if (false !== $template) {
                if (true === empty($template)) {
                    $template = "item_templates" . DS . strtolower($this->name);
                }
                ob_start();
                include_once ASSETS . DS . "tpepdb2" . DS . "templates" . DS . $template . ".phtml";
                return ob_get_clean();
            } else {
                return array($compound, $serie, $brand, $processingnotes, $compounds, $labels);
            }
        }
    }

    /**
     * @return array
     */
    protected function _getExistingLanguage ($model_name, $en = true)
    {
        $this->unbindModel('all');
        $this->bindModel(
            array(
                'has_one' => array(
                    $model_name . "Values"
                )
            )
        );

        $conditions = array($model_name . '.id' => $this->getId());
        if (true === $en) {
            $conditions[$model_name . "Values.language"] = "en";
        }

        $found = $this->fetch(
            'first',
            array(
                'fields' => array(
                    $model_name . "Values.language"
                ),
                'conditions' => $conditions,
                'order' => array($model_name . "Values.language")
            )
        );
        if (false !== $found) {
            $language = $found->{$model_name . "Values"}->getLanguage();

            $return = $this->_readData($this->name, $this->getId(), $language);
            $return[] = $language;
            return $return;
        } else {
            if (true === $en) {
                return $this->_getExistingLanguage($model_name, false);
            }
            return null;
        }
    }

    /**
     * @param string $model_name
     * @param int $id
     * @param string $language
     * @return array
     */
    protected function _readData ($model_name, $id, $language)
    {
        $class_name = "Tpepdb2_" . $model_name;
        $model = new $class_name();
        $model->setId($id);
        $model->unbindModel("all");
        if ($model_name != "Brand") {
            $model->bindModel(
                array(
                    "has_many" => array(
                        $model_name . "Region"
                    )
                )
            );
        }
        $model->read();
        $data = $model->data();

        $languages = array();
        if ($model_name != "Brand") {
            list($values, $languages) = $this->_readValues($model_name, $id, $language);
            foreach ($values as $key => $value) {
                $data[$key] = $value;
            }

            foreach ($this->_readAdditionalTables($model_name, $id, $language) as $key => $values) {
                $data[$key] = $values;
            }
            $data["id"] = $id;

            $data["regions"] = array();
            if ($data["status"] == "portfolio") {
                foreach ($model->{$model_name . "Region"} as $region) {
                    $data["regions"][] = $region->data();
                }
            }


            $data["documents"] = $this->_readDocuments($model_name, $id, $language);
        }

        return array($data, $languages);
    }

    /**
     * @param string $model_name
     * @param int $id
     * @param string $language
     * @return array
     */
    protected function _readValues ($model_name, $id, $language)
    {
        $class_name = "Tpepdb2_" . $model_name . "Values";
        $values_model = new $class_name();

        $values_en = $values_model->fetch(
            "first",
            array(
                "conditions" => array(
                    $model_name . "Values." . strtolower($model_name) . "_id" => $id,
                    $model_name . "Values.language" => "en"
                )
            )
        );

        $values = $values_model->fetch(
            "first",
            array(
                "conditions" => array(
                    $model_name . "Values." . strtolower($model_name) . "_id" => $id,
                    $model_name . "Values.language" => $language
                )
            )
        );
        if (false !== $values) {
            foreach ($values->data() as $key => $value) {
                if (strstr($key, "unit") && true === empty($value)) {
                    $values->data[$key] = $values_en->data[$key];
                }
            }
            $values_model->data($values->data());
        }

        $languages = array();
        $results = $values_model->db->query(
            "SELECT l.language, w_l.name FROM " . $values_model->db_table_name . " AS l"
            . " LEFT JOIN ncw_wcms_language AS w_l ON l.language=w_l.shortcut"
            . " WHERE l." . strtolower($model_name) . "_id = " . $id
        );
        if (false !== $results) {
            foreach ($results->fetchAll() as $result) {
                $languages[$result['name']] = $result["language"];
            }
        }

        return array($values_model->data(), $languages);
    }

    /**
     * @param int $id
     * @param string $language
     * @return array
     */
    protected function _readDocuments ($model_name, $id, $language)
    {
        $model_name_lower = strtolower($model_name);
        if ($model_name_lower == "brand") {
            return null;
        }
        $documents = array();
        $class_name = "Tpepdb2_" . $model_name . "Document";
        $model = new $class_name();
        $found_documents = $model->fetch(
            "all",
            array(
                "fields" => array(
                    "Document.id",
                    "Document.name",
                    "Document.headline",
                    "Document.text",
                    "Document.language",
                     $model_name . "Document.type",
                ),
                "conditions" => array(
                    $model_name . "Document." . $model_name_lower . "_id" => $id,
                    "(Document.language = '" . $language . "' || Document.language = '')"
                ),
                "order" => $model_name . "Document.id"
            )
        );
        if (false !== $found_documents) {
            foreach ($found_documents as $document) {
                $path = Ncw_Configure::read('Project.url') . DS . ASSETS . DS . "tpepdb2" . DS . "imported_documents";
                if ($document->Document->getLanguage() != "") {
                    $path .=  DS . $document->Document->getLanguage();
                }
                $path .= DS . $document->Document->getName();
                $documents[] = array(
                    "id" => $document->Document->getId(),
                    "name" => $document->Document->getName(),
                    "headline" => $document->Document->getHeadline(),
                    "text" => $document->Document->getText(),
                    "language" => $document->Document->getLanguage(),
                    "type" => $document->getType(),
                    "path" => $path
                );
            }
        }
        return $documents;
    }

    /**
     * @param int $id
     * @param string $language
     * @return array
     */
    protected function _readAdditionalTables ($model_name, $id, $language)
    {
        $vars = $db_tables = array();
        $model_name = strtolower($model_name);
        $db_table_prefix = $this->db->getConfig("prefix") . "tpepdb2_";

        $additionals = array("anwendungsbereiche", "markets", "materialvorteile");
        foreach ($additionals as $additional) {
            $sql = "SELECT t1." . $language . " FROM " . $db_table_prefix . "" . $additional . " AS t1"
            . " INNER JOIN " . $db_table_prefix . "serie_" . $additional . " AS t2 ON t2." . $additional. "_id = t1.id"
            . " WHERE serie_id = " . $id . ""
            . " ORDER BY t1." . $language . "";
            $result = $this->db->query($sql);

            if (false !== $result) {
                foreach ($result->fetchAll() as $row) {
                    $vars[$additional][] = $row[$language];
                }
            }
        }

        return $vars;
    }

    /**
     * @param string $language
     * @param array $values
     * @return string
     */
    protected function _loadLabels ($language, $values)
    {
        $label = new Tpepdb2_Label();
        $found_labels = $label->fetch(
            "all",
            array(
                "fields" => array(
                    "Label.type",
                    "Label.attribute",
                    "Label.translation",
                ),
                "conditions" => array(
                    "Label.language" => $language
                )
            )
        );

        $labels = array();
        foreach ($values as $key => $value) {
            if (false === isset($labels[$key])) {
                $labels["compound"][$key] = "";
            }
        }
        foreach ($found_labels as $label) {
            $labels[$label->getType()][$label->getAttribute()] = $label->getTranslation();
        }

        return $labels;
    }

    /**
     * @param string $model_name
     * @param string $language
     * @param array $given_conditions
     * @param boolean $region
     * @return array
     */
    protected function _readAllOf ($model_name, $language, $given_conditions = array(), $region = false, $order = array(), $limit = null)
    {
        $model_name = (string) $model_name;
        $language = (string) $language;
        $data = array();

        $class_name = "Tpepdb2_" . $model_name;
        $model = new $class_name();
        $model->unbindModel("all");

        if (false === $this->_checkIfLanguageExists($model_name, $language)) {
            $language = "en";
        }

        $belongs_to = array();
        $has_one = array($model_name . "Values");

        if ($model_name == "Compound") {
            $belongs_to[] = "Serie";
        }
        $belongs_to["Brand"] = array(
            "join_condition" => $model_name.".brand_id=Brand.id"
        );
        $model->bindModel(
            array(
                "belongs_to" => $belongs_to,
                "has_one" => $has_one,
            )
        );
        $has_one = array();
        if (true === $region) {
            $has_one["SerieRegion"] = array("join_condition" => "Serie.id=SerieRegion.serie_id");
        }
        /*if ($model_name === "Compound") {
            $has_one["SerieValues"] = array("join_condition" => "Serie.id=SerieValues.serie_id");
            $given_conditions[] = "(SerieValues.language = '". $language . "'"
                . " || SerieValues.language IS NULL)";
        }*/
        $model->bindModel(
            array(
                "has_one" => $has_one,
            )
        );

        // such conditions
        $conditions = array_merge(
            $given_conditions,
            array(
                "(" . $model_name . "Values.language = '". $language . "'"
                . " || " . $model_name . "Values.language IS NULL)"
            )
        );

        $found_items = $model->fetch(
            "all",
            array(
                "conditions" => $conditions,
                'order' => $order,
                'limit' => $limit
            )
        );

        $values_en = null;
        if (false !== $found_items) {
            foreach ($found_items as $item) {
                if ($model_name == "Compound") {
                    $serie = $item->{"Serie"}->data();
                    /*foreach ($item->{"Serie"}->data() as $key => $value) {
                        $serie[(string) $key.""] = $value;
                    }
                    foreach ($item->{"SerieValues"}->data() as $key => $value) {
                        $serie[(string) $key.""] = $value;
                    }*/
                } else {
                    $serie = array();
                }
                $brand = $item->{"Brand"}->data();

                $data_array = array();
                foreach ($item->{$model_name . "Values"}->data() as $key => $value) {
                    if (strstr($key, "unit") && true === empty($value)) {
                        if ($values_en == null) {
                            $compound_values = new Tpepdb2_CompoundValues();
                            $values_en = $compound_values->fetch(
                                "first",
                                array(
                                    "conditions" => array(
                                        "CompoundValues." . "compound_id" => $item->getId(),
                                        "CompoundValues.language" => "en"
                                    )
                                )
                            );
                        }
                        if (false !== $values_en && $values_en != null) {
                            if (true === isset($values_en->data[$key])) {
                                $item->{$model_name . "Values"}->data[$key] = $value = $values_en->data[$key];
                            }
                        }
                    }

                    $data_array[(string) $key.""] = $value;
                }
                foreach ($item->data() as $key => $value) {
                    $data_array[(string) $key.""] = $value;
                }
                $data_array["serie"] = $serie;
                $data_array["brand"] = $brand;
                $data[] = $data_array;
            }
        }

        return $data;
    }

    /**
     * @param string $language
     * @return array
     */
    public function makePdf ($language, $language_id)
    {
        $pdf_created = (boolean) $this->readField("pdf_created");

        include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tpepdb" . DS . "TpePdbPdf.php";

        $name = $this->readField("name");
        $model_name = strtolower($this->name);
        list($compound, $serie, $brand, $processingnotes, $compounds, $labels) = $this->template($language, $language_id, false);

        $pdf_name = str_replace(array(" ", "/"), "-", $name);
        $dir = ASSETS . DS . "tpepdb2" . DS . "pdfs" . DS . $model_name . DS . $language;

        $author = "Kraiburg TPE";
        if (${$model_name}['status'] == 'portfolio') {
            $subject = Wcms_ContentboxController::getContenbox('pdb---datasheet---datasheet', $language_id);
        } else {
            $subject = str_replace('-', '_', Wcms_ContentboxController::getContenbox('pdb---datasheet---test-report', $language_id));
        }
        $pdf_name =  $subject . '_' . $pdf_name;
        if ($model_name == "serie") {
            $pdf_name .= "_" . Wcms_ContentboxController::getContenbox('pdb---series', $language_id);
            $subject = $author . " " . Wcms_ContentboxController::getContenbox('pdb---series', $language_id) . " " . $subject;
        } else {
            $subject = $author . " " . Wcms_ContentboxController::getContenbox('pdb---compound', $language_id) . " " . $subject;
        }
        $pdf_title = $pdf_name;
        $pdf_name .= '.pdf';

        $pdf_file = $dir . DS . $pdf_name;

        //if (false === $pdf_created || false === is_file($pdf_file)) {
            $pdf = new TpePdbPdf($author, str_replace("_", " ", $pdf_title), $subject, $language, $language_id);

            ob_start();
            include_once ASSETS . DS . "tpepdb2" . DS . "templates" . DS . "pdf_templates" . DS . $model_name . "_pdf.phtml";
            ob_get_clean();

            if (false == is_dir($dir)) {
                mkdir($dir);
            }
            $pdf->Output($pdf_file, "F");
            //$this->setPdfCreated(true);
            //$this->saveField("pdf_created");
        //}

        return array($pdf_name, $pdf_file);
    }

    /**
     * @param string $type
     * @param string $language
     */
    protected function _checkIfLanguageExists ($type, $language)
    {
        $db = Ncw_Database::getInstance();

        $sth = $db->prepare(
            "SELECT count(1) AS count
            FROM `ncw_tpepdb2_" . strtolower($type) . "_values` AS v
            WHERE v.language=:langauge
            LIMIT 1
            "
        );
        $sth->bindValue(":langauge", $language);
        $sth->execute();
        $result = $sth->fetch();

        if (true === isset($result['count']) && $result['count'] > 0) {
            return true;
        } else {
            return false;
        }
    }

}
?>
