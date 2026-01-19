<?php

require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlFile.php';

class TpePdbXmlFiles implements Iterator {

    /**
     * @var array
     */
    public $xml_files = array();

    /**
     * @var int
     */
    protected $_iterator_position = 0;

    /**
     * @param TpePdbXmlFile $xml_file
     */
    public function Add (TpePdbXmlFile $xml_file)
    {
        $this->xml_files[] = $xml_file;
    }

    /**
     * @param int $position
     * @return TpePdbXmlFile
     */
    public function getXmlFile ($position)
    {
        $position = (int) $position;
        if (true === isset($this->xml_files[$position])) {
            return $this->xml_files[$position];
        }
        return null;
    }

    /**
     * @return int
     */
    public function getCount ()
    {
        return count($this->xml_files);
    }

    public function rewind ()
    {
        $this->_iterator_position = 0;
    }

    public function current ()
    {
        return $this->xml_files[$this->_iterator_position];
    }

    public function key ()
    {
        return $this->_iterator_position;
    }

    public function next ()
    {
        ++$this->_iterator_position;
    }

    public function valid ()
    {
        return isset($this->xml_files[$this->_iterator_position]);
    }
}
?>
