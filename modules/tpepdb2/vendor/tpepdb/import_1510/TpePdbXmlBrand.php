<?php

require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlItem.php';

class TpePdbXmlBrand extends TpePdbXmlItem {

    /**
     * @var string
     */
    protected $_ncw_model_name = "Tpepdb2_Brand";

    /**
     *
     */
    protected function _loadName ()
    {
        if (true === $this->is_portfolio) {
            $this->_name = trim((string) $this->_xml_element);
        } else {
            $this->_name = trim((string) $this->_xml_element->DESCRIPTIONS_SHORT[0]->DESCRIPTION_SHORT);
        }
    }
}
?>
