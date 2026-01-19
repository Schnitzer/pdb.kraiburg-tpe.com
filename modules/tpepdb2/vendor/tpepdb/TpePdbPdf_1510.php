<?php
include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tcpdf" . DS . "config" . DS . "tcpdf_config.php";
include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tcpdf" . DS . "tcpdf.php";

class TpePdbPdf extends TCPDF {

    /**
     *
     */
    public $language_id = 1;

    /**
     *
     */
    public $font = "";

    /**
     *
     */
    public $rotate_cells = array();

    /**
     *
     */
    public $header_content = '';

    /**
     *
     */
    public $footer_content = '';

    /**
     *
     */
    public $table_end = false;

    /**
     *
     */
    public function __construct ($author, $title, $subject, $language_code = "en", $language_id)
    {
        $this->language_id = $language_id;

        switch ($language_code) {
            case "zh":
                //$this->font = "msungstdlight";
                //$this->font = "cyberbit";
                if (strstr($_SERVER['HTTP_USER_AGENT'],'Android')){
                    $this->font = "arialuni";
                    //$this->font = "kozgopromedium";
                    //$this->font = "hanamina";
                } else if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad')) {
                    $this->font = "kozgopromedium";
                } else {
                    $this->font = "arialuni";
                }
                break;
            case "jp":
                if (strstr($_SERVER['HTTP_USER_AGENT'],'Android')){
                    $this->font = "arialuni";
                    //$this->font = "kozgopromedium";
                    //$this->font = "hanamina";
                } else if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad')) {
                    $this->font = "kozgopromedium";
                } else {
                    $this->font = "arialuni";
                }
                break;
            case "kr":
                if (strstr($_SERVER['HTTP_USER_AGENT'],'Android')){
                    $this->font = "arialuni";
                }else if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad')) {
                    $this->font = "hysmyeongjostdmedium";
                } else {
                    $this->font = "arialuni";
                }
                //$this->font = "undotum";
                break;
            default:
                if (strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad')){
                    $this->font = "";
                } else {
                    //$this->font = "arialuni";
					$this->font = "";
                }

                break;
        }

        //$this->addTTFfont("modules/tpepdb2/vendor/tcpdf/fonts/ARIALUNI.ttf", "TrueTypeUnicode", "", 32);
        //$this->addTTFfont("modules/tpepdb2/vendor/tcpdf/fonts/ArialUnicode-Bold.ttf", "TrueTypeUnicode", "", 32);

        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);

        $this->SetCreator("Microsoft© Word 2010");
        $this->SetAuthor(trim($author));
        $this->SetTitle($title);
        $this->SetSubject("");
        $this->SetKeywords("");

        $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, "", PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, "", PDF_FONT_SIZE_DATA));

        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->SetFont($this->font);

        $this->SetMargins(0, 0, 0);
        $this->SetMargins(10, 38, 10, true);

        $this->SetHeaderMargin(5);
        $this->SetFooterMargin(36);

        $this->SetAutoPageBreak(TRUE, 40);

        $this->setListIndentWidth(4);
        $this->setHtmlVSpace(
            array(
                'ul' => array(0 => array('h' => 1, 'n' => 1), 1 => array('h' => '', 'n' => 0)),
                'div' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                'span' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                'p' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                'b' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                'table' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                'tr' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                'td' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                'th' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                'sup' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
                //'br' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
            )
        );

        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    /**
     *
     */
    public function RotateCellContent ($ae) {
        if (false === isset($this->rotate_cells['cellxy'])) {
            $this->rotate_cells['cellxy'] = array();
        }
        if (false === isset($this->rotate_cells['page_no'])) {
            $this->rotate_cells['page_no'] = array();
        }
        $this->rotate_cells['cellxy'][] = array($this->GetX(), $this->GetY());
        $this->rotate_cells['page_no'][] = $this->PageNo();
    }

    /**
     *
     */
    public function Header ()
    {
        $language_id = $this->language_id;

        $this->SetFont($this->font);
        $this->writeHTML($this->header_content, true, false, true, false, "");
    }

    /**
     *
     */
    public function Footer ()
    {
        $language_id = $this->language_id;

        $this->SetFont($this->font);
        $this->writeHTML($this->footer_content, true, false, true, false, "");

        $this->Image(ASSETS . DS . 'tpepdb2' . DS . 'templates' . DS . 'images' . DS . 'tpe_slogan.gif', 10, 289, 35.325, 0, "GIF");
    }
}
?>
