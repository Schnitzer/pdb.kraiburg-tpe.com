<?php

require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlItem.php';

class TpePdbXmlSerie extends TpePdbXmlItem {

    /**
     * @var string
     */
    protected $_ncw_model_name = "Tpepdb2_Serie";

    /**
     *
     */
    protected function _loadLanguages ()
    {
        if (true === isset($this->_xml_element->DESCRIPTIONS_SHORT->DESCRIPTION_SHORT[0])) {
            foreach ($this->_xml_element->DESCRIPTIONS_SHORT->DESCRIPTION_SHORT as $description) {
                $language = (string) $description["lang"];
                if (false === in_array($language, $this->_languages)) {
                    $this->_languages[] = $language;
                }
            }
        }
    }
}
?>
