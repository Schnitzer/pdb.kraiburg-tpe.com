<?php

require_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tpepdb" . DS . "TpePdbModel.php";

class Tpepdb2_Brand extends TpePdbModel {

    public $has_many = array(
        "Serie",
        "BrandValues",
    );
}
?>
