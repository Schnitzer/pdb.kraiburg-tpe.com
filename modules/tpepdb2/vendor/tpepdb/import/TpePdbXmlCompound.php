<?php

require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlItem.php';

class TpePdbXmlCompound extends TpePdbXmlItem {

    /**
     * @var string
     */
    protected $_ncw_model_name = "Tpepdb2_Compound";

    /**
     *
     */
    protected function _loadName ()
    {
        $this->_name = trim((string) $this->_xml_element->MATERIAL_NUMBER);
    }

    /**
     *
     */
    protected function _loadLanguages ()
    {
        if (true === $this->is_portfolio) {
        	  //var_dump($this->_xml_element->BRAND);
        	  
        	  //echo '<br><br>';
            if (true === isset($this->_xml_element->BRAND[0])) {
                foreach ($this->_xml_element->BRAND as $brand) {
                    $language = (string) $brand["lang"];
                    if (false === in_array($language, $this->_languages)) {
                        $this->_languages[] = $language;
                    }
                }
            }
        } else {
        		// Dieser Umbau war nötig nachdem DESCRIPTION_SHORT aus den XML Dateien entfernt wurde
        		// Wird benötigt um überhaupt Werte in der compound_values zu erhalten
            if (true === isset($this->_xml_element->CLASSIFICATION[0])) {
                foreach ($this->_xml_element->CLASSIFICATION[0]->ATTRIBUTES as $attribute) {
                	if (true === isset($attribute->ATTRIBUTE)) {
                		foreach($attribute->ATTRIBUTE[1] as $description) {
                      $language = (string) $description["lang"];
                      if (strlen(trim($language)) > 0) {
	                      if (false === in_array($language, $this->_languages)) {
	                          $this->_languages[] = $language;
	                      }
                      }
                		}
                	}
                	/*
                    if (true === isset($attribute->ATTRIBUTE->DESCRIPTIONS_SHORT)) {
                        foreach ($attribute->ATTRIBUTE->DESCRIPTIONS_SHORT->DESCRIPTION_SHORT as $description) {
                            $language = (string) $description["lang"];
                            if (false === in_array($language, $this->_languages)) {
                                $this->_languages[] = $language;
                            }
                        }
                        break;
                    }
                    */
                }
            }
        }
    }
}
?>
