<?php


class TpePdbWeb extends Ncw_Object {

    /**
     * @var int
     */
    public $serie_id = 0;

    /**
     * @var int
     */
    public $compound_id = 0;

    /**
     * @var int
     */
    public $region_id = 0;

    /**
     * @var boolean
     */
    public $make_pdf = false;

    /**
     * @var array
     */
    public $regions = array();

    /**
     * @var boolean
     */
    public $searching = false;

    /**
     * @var string
     */
    public $search_type = "";

    /**
     * @var string
     */
    public $search_value = "";

    /**
     * @var array
     */
    public $search_types = array(
        "serie" => "Series search",
        "compound" => "Compound search",
        "text" => "Text search",
        "properties" => "Properties search",
    );

    /**
     * @var int
     */
    public $language_id = 1;

    /**
     * @var string
     */
    public $language = "en";

    /**
     * @var string
     */
    public $pdf_language = "en";

    /**
     * @var int
     */
    public $pdf_language_id = 1;

    /**
     *
     */
    public $market = "";

    /**
     *
     */
    public $application = "";

    /**
     *
     */
    public $advantage = "";

    /**
     *
     */
    public function __construct ($language_code = "en", $language_id = 1)
    {
        $this->language = substr($language_code, 0, 2);
        $this->language_id = (int) $language_id;

        $this->search_type = "serie";
        if (true == TpePdbWeb::rights()) {
            $this->searching = true;
        }

        if ((true === isset($_GET["ls"]) && $_GET["ls"] == "it")) {
            $this->language = "it";
            $this->language_id = 8;
        }
        if (true === isset($_GET["sid"])) {
            $this->serie_id = (int) $_GET["sid"];
        }
        if (true === isset($_GET["cid"])) {
            $this->compound_id = (int) $_GET["cid"];
        }
        if (true === isset($_GET["r"])) {
            $this->searching = true;
            $this->region_id = (int) $_GET["r"];
        }
        if (true === isset($_GET["pdf"])) {
            $this->make_pdf = (boolean) $_GET["pdf"];
        }

        if (true === isset($_GET["st"]) && false === empty($_GET["st"])) {
            $this->search_type = Ncw_Library_Sanitizer::clean($_GET["st"]);
        }
        if (true === isset($_GET["s"])) {
            $this->searching = true;
        }
        if (true === isset($_GET["s"]) && false === empty($_GET["s"])) {
             $this->search_value = Ncw_Library_Sanitizer::clean($_GET["s"]);
        }
        if (true === isset($_GET["ma"])) {
            $this->searching = true;
            $this->market = (int) $_GET["ma"];
        }
        if (true === isset($_GET["ap"])) {
            $this->searching = true;
            $this->application = (int) $_GET["ap"];
        }
        if (true === isset($_GET["ad"])) {
            $this->searching = true;
            $this->advantage = (int) $_GET["ad"];
        }
        if (true === isset($_GET["l"]) && strlen($_GET["l"]) == 2) {
            $this->pdf_language = Ncw_Library_Sanitizer::clean($_GET["l"]);
            $language = new Wcms_Language();
            $language = $language->findBy('shortcut', $this->pdf_language);
            if (false !== $language) {
                $this->pdf_language_id = $language->getId();
            }
        }

        if (true === isset($_GET["sess"]) && true === (boolean) $_GET["sess"]) {
            if (true === Ncw_Components_Session::checkInAll("tpepdb2_last_search")) {
                $last_search = Ncw_Components_Session::readInAll("tpepdb2_last_search");
                foreach ($last_search as $key => $value) {
                    $this->searching = true;
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function javascript ()
    {
        $str_javascript = "<script type=\"text/javascript\" src=\"" . Ncw_Configure::read("Project.url") . "/" . ASSETS . "/tpepdb2/javascript/config.js\"></script>";
        $str_javascript .= "<script type=\"text/javascript\" src=\"" . Ncw_Configure::read("Project.url") . "/" . ASSETS . "/tpepdb2/javascript/main.js\"></script>";
        //$str_javascript .= "<script type=\"text/javascript\" src=\"" . Ncw_Configure::read("Project.url") . "/" . ASSETS . "/tpepdb2/javascript/rotateCellContent.js\"></script>";
        return $str_javascript;
    }

    /**
     * @return string
     */
    public function urlArgs ()
    {
        $args = array();
        if (true === isset($_GET["sid"])) {
            $args[] = "sid=" . (int) $_GET["sid"];
        }
        if (true === isset($_GET["cid"])) {
            $args[] = "cid=" . (int) $_GET["cid"];
        }
        if (true === isset($_GET["r"])) {
            $args[] = "r=" . (int) $_GET["r"];
        }
        if (true === isset($_GET["st"])) {
            $args[] = "st=" . Ncw_Library_Sanitizer::clean($_GET["st"]);
        }
        if (true === isset($_GET["s"])) {
            $args[] = "s=" . Ncw_Library_Sanitizer::clean($_GET["s"]);
        }
        if (true === isset($_GET["ma"])) {
            $args[] = "ma=" . (int) $_GET["ma"];
        }
        if (true === isset($_GET["ap"])) {
            $args[] = "ap=" . (int) $_GET["ap"];
        }
        if (true === isset($_GET["ad"])) {
            $args[] = "ad=" . (int) $_GET["ad"];
        }

        if (count($args) > 0) {
            return "?" . implode("&amp;", $args);
        }
        return "";
    }

    /**
     * @return string
     */
    public function content ()
    {
        if ($this->serie_id > 0
            || $this->compound_id > 0
        ) {
            if ($this->serie_id > 0) {
                $type = "serie";
                $id = $this->serie_id;
            } else if ($this->compound_id > 0) {
                $type = "compound";
                $id = $this->compound_id;
            }

            if (true === $this->make_pdf) {
                $this->_pdf($type, $id);
            } else {
                return $this->_item($type, $id);
            }
        } else {
            return $this->_search();
        }
    }

    /**
     *
     */
    protected function _item ($type, $id)
    {
        $model = $this->_loadModel($type);
        $model->setId($id);
        return $model->template($this->language, $this->language_id);
    }

    /**
     *
     */
    protected function _pdf ($type, $id)
    {
        $model = $this->_loadModel($type);
        $model->setId($id);
        list($pdf_name, $pdf_file) = $model->makePdf($this->pdf_language, $this->pdf_language_id);

        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header("Content-type: application/pdf");
        header("Content-disposition: attachment; filename=\"" . $this->sanitizeFileName($pdf_name) . "\"");
        header('Content-Transfer-Encoding: binary');
        if (false !== isset($_SERVER['HTTP_ACCEPT_ENCODING']) || empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            header('Content-Length: ' . filesize($pdf_file));
        }
        @readfile($pdf_file);

        /*header('Content-Type: application/pdf');
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Disposition: inline; filename="'. $pdf_name .'"');
        if (false !== isset($_SERVER['HTTP_ACCEPT_ENCODING']) || empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            header('Content-Length: ' . filesize($pdf_file));
        }
        @readfile($pdf_file);*/
    }

    public function sanitizeFileName ($str) {
        $str = strip_tags($str);
        $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
        $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
        //$str = strtolower($str);
        $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
        $str = htmlentities($str, ENT_QUOTES, "utf-8");
        $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
        $str = str_replace(' ', '-', $str);
        //$str = rawurlencode($str);
        $str = str_replace('%', '-', $str);
        return $str;
    }

    /**
     *
     */
    protected function _search ()
    {
        $language_id = $this->language_id;
        if ($this->language == "us") {
            $this->language = "en";
        }
        $language = $this->language;
        $this->_getRegions();

        ob_start();
        include_once ASSETS . DS . 'tpepdb2' . DS . 'templates' . DS . 'elements' . DS . 'search_header.phtml';
        $html = ob_get_clean();
        if (true === $this->searching) {
            if ($this->search_type != 'properties' && true == isset($this->search_types[$this->search_type])) {

                Ncw_Components_Session::writeInAll(
                    "tpepdb2_last_search",
                    array(
                        "region_id" => $this->region_id,
                        "search_type" => $this->search_type,
                        "search_value" => $this->search_value,
                    )
                );

                if ($this->search_type == "text") {
                    $serie = $this->_loadModel("Serie");
                    $series = $serie->search($this->search_value, $this->language, $this->region_id, false, $this->language_id, true);

                    $found_series = array();
                    foreach ($series as $found_serie) {
                        $found_series[] = $found_serie['id'];
                    }
                    $series_prop = $serie->searchViaProperties($this->search_value, $this->search_value, $this->search_value, $this->language, $this->region_id, false, $this->language_id, true, $found_series);

                    $series = array_merge($series, $series_prop);

                    $compound = $this->_loadModel("Compound");
                    $compounds = $compound->search($this->search_value, $this->language, $this->region_id, false, $this->language_id, true);
                    ob_start();
                    include_once ASSETS . DS . "tpepdb2" . DS . "templates" . DS . "search_templates" . DS . "text_search.phtml";
                    $html .= ob_get_clean();
                } else {
                    $model = $this->_loadModel($this->search_type);
                    $html .= $model->search($this->search_value, $this->language, $this->region_id, true, $this->language_id);
                }
            } else if ($this->search_type == 'properties' && ($this->market != "" || $this->application != "" || $this->advantage != "")) {

                Ncw_Components_Session::writeInAll(
                    "tpepdb2_last_search",
                    array(
                        "region_id" => $this->region_id,
                        "search_type" => $this->search_type,
                        "market" => $this->market,
                        "application" => $this->application,
                        "advantage" => $this->advantage,
                    )
                );

                $serie = $this->_loadModel("Serie");
                $html .= $serie->searchViaProperties($this->market, $this->application, $this->advantage, $this->language, $this->region_id, true, $this->language_id);
            }
        }
        return $html;
    }

    /**
     *
     */
    protected function _loadModel ($name)
    {
        $model_name = "Tpepdb2_" . ucfirst($name);
        return new $model_name();
    }

    /**
     *
     */
    protected function _getRegions ()
    {
        $region_model = new Tpepdb2_Region();
        $region_model->unbindModel("all");
        $this->regions = $region_model->fetch("all");
    }

    /**
     * hat der User das Recht die Non Portfolio Compounds zu sehen
     * gibt true zurück wenn der user das Recht hat
     * false wenn nicht
     */
    public static function rights ()
    {
        $user = Ncw_Components_Session::readInAll('user');
        $obj_acl = new Ncw_Components_Acl();

        $obj_acl->read($user['id'], '');

        if (false === $obj_acl->check('/tpepdb2/non_portfolio')) {
            return false;
        } else {
            return true;
        }
    }
}
?>
