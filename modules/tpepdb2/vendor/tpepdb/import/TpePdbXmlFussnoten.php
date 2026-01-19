<?php

require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlItem.php';

class TpePdbXmlFussnoten extends TpePdbXmlItem {

    /**
     * @var string
     */
    protected $_ncw_model_name = "Tpepdb2_Brand";

    /**
     * Hier wird aus der XML Struktur der Name des Brands herausgesucht
     */
    protected function _loadName ()
    {
    	
    	//var_dump($this->_xml_element);
        //if (true === $this->is_portfolio) {
        	  var_dump($this->_xml_element);
            //$this->_name = trim((string) $this->_xml_element);
            // Dieses wurde nach dem Umbau auf mehrere Brandsprachen notwendig
            $this->_name = trim($this->_xml_element->{'BRAND'}[4]);
        //} else {
        //    $this->_name = trim((string) $this->_xml_element->DESCRIPTIONS_SHORT[0]->DESCRIPTION_SHORT[3]);
        //}
    }
    
        /**
     *
     */

}
?>
