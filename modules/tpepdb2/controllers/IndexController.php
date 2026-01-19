<?php
/**
 * IndexController class.
 *
 * @package netzcraftwerk
 */
class Tpepdb2_IndexController extends Tpepdb2_ModuleController
{

    /**
     * The page title
     *
     * @var string
     */
    public $page_title = "TPE PDB";

    /**
     * No model used
     *
     * @var boolean
     */
    public $has_model = false;

    /**
     * Index
     *
     */
    public function indexAction ()
    {
        //$this->acl->addAco("/tpepdb2", "TPE PDB 2");
        //$this->acl->addAco("/tpepdb2/non_portfolio", "TPE PDB 2 - Test Reports");
    }
}
?>
