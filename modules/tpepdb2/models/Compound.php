<?php

require_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tpepdb" . DS . "TpePdbModel.php";

class Tpepdb2_Compound extends TpePdbModel {

    public $has_many = array(
        "CompoundDocument",
        "CompoundValues",
        //"CompoundRegion"
    );
    
    public $has_one = array(
        "Processingnote",
    );
    
    public $belongs_to = array(
        "Serie",
        "Brand",
    );
}
?>
