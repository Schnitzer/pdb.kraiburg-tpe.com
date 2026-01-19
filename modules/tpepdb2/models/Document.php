<?php

class Tpepdb2_Document extends Ncw_Model {

    public $has_many = array(
        'CompoundDocument',
        'SerieDocument',
        'BrandDocument',
    );
}
?>
