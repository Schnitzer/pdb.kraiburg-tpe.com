<?php
/**
 * ImportController class.
 *
 * @package netzcraftwerk
 */
class Tpepdb2_ImportController extends Tpepdb2_ModuleController
{

    /**
     * The page title
     *
     * @var string
     */
    public $page_title = "TPE PDB :: IMPORT";

    /**
     * No model used
     *
     * @var boolean
     */
    public $has_model = false;

    /**
     * @var array
     */
    public $acl_publics = array(
        "index",
        "remove",
        "reset",
				"deletePDB20"
    );

    /**
     * @var array
     */
    public $auth_ip_adresses = array(
        "127.0.0.1"
    );

    /**
     *
     */
    public function beforeFilter ()
    {
        parent::beforeFilter();
        require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'import' . DS . 'TpePdbXmlImporter.php';
    }


	
     /**
     * Entfernt ein Compound
     *
     */
    public function removeAction ()
    {
        $this->view = false;

        $str_key = '';
        $str_compound = '';

        if (true == isset($_GET["key"])) {
            $str_key = Ncw_Library_Sanitizer::clean($_GET["key"]);
        }
        if (true == isset($_GET["cn"])) {
            $str_compound = Ncw_Library_Sanitizer::clean($_GET["cn"]);
        }
        if ($str_key == 'hhhs8889usdhfdjs98ikkkd9012jjd') {
            $obj_compound = new Tpepdb2_Compound();
            $found_compound = $obj_compound->fetch(
                'first',
                array(
                    'conditions' => array(
                        'name' => $str_compound,
                        'status' => 'non_portfolio'
                    )
                )
            );
            if (false !== $found_compound) {
                $obj_compound_delete = new Tpepdb2_Compound();
                $obj_compound_delete->setId($found_compound->getId());
                if($obj_compound_delete->delete()) {
                    print  $str_compound . ' removed';
                }
            }
        }
    }

    /**
     * Index
     *
     */
    public function indexAction ()
    {
        $this->view = false;

            $importer = new TpePdbXmlImporter();
            $importer->readXmlFiles();
            $importer->import();
            print 1;

    }

    /**
     *
     */
    public function resetAction () {
        $this->view = false;
        $db = Ncw_Database::getInstance();
        $result = $db->query("SHOW TABLES LIKE 'ncw_tpepdb2_%'");
        foreach ($result->fetchAll() as $row) {
            if ($row[0] != "ncw_tpepdb2_region") {
                $db->query("TRUNCATE " . $row[0]);
            }
        }
    }
}
?>
