<?php

class Tpepdb2_Region extends Ncw_Model {

    public $has_many = array(
        "CompoundRegion",
        "SerieRegion",
    );
}
?>
