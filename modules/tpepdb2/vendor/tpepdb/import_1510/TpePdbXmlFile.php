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
     * @param int $import_time
     */
    public function import ($import_time)
    {
        $this->_import_time = (int) $import_time;

        try {
            $xml_code = trim(file_get_contents($this->_file));
            $this->_loaded_xml_file = new SimpleXMLElement($xml_code, null, false);

            $brand_id = $this->_importBrand();
            if (false === $this->_is_portfolio) {
                $serie_id = 0;
            } else {
                $serie_id = $this->_importSerie($brand_id);
            }

            $compound_id = $this->_importCompound($brand_id, $serie_id);

            if (true === TpePdbXmlImporter::$unlink) {
                if (true === TpePdbXmlImporter::$copy) {
                    @copy($this->_file, str_replace("tpepdb2_import/", "tpepdb2_import/backup/", $this->_file));
                }
                @unlink($this->_file);
            }
            if (false === $this->_is_portfolio && true === TpePdbXmlImporter::$unlink) {
                $compound = explode("_", str_replace(TpePdbXmlImporter::$XML_SOURCE_DIR . DS, "", $this->_file));
                if (true === TpePdbXmlImporter::$copy) {
                    @copy(TpePdbXmlImporter::$XML_SOURCE_DIR . DS . "." . $compound[0] . "_" . $compound[1], TpePdbXmlImporter::$XML_SOURCE_DIR . DS . "backup" . DS . "." . $compound[0] . "_" . $compound[1]);
                }
                @unlink(TpePdbXmlImporter::$XML_SOURCE_DIR . DS . "." . $compound[0] . "_" . $compound[1]);
            }
        } catch (Exception $e) {
            $message = "Import: " . date("Y-m-d H:i:s", $import_time) . "\n";
            $message .= "Message: " . $e->getMessage() . " (File: " . $this->_file . ")";
            TpePdbXmlImporter::$FILE_LOG->log($message);
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

    /**
     * @param int $brand_id
     * @return int
     */
    protected function _importSerie ($brand_id)
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
    protected function _importCompound ($brand_id, $serie_id)
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
