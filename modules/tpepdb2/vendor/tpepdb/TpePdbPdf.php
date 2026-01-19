<?php
include_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tcpdf' . DS . 'config' . DS . 'tcpdf_config.php';
include_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tcpdf' . DS . 'tcpdf.php';

class TpePdbPdf extends TCPDF
{
    public $language_id = 1;

    public $font = '';

    public $rotate_cells = array();

    public $header_content = '';

    public $header_content_symbol = '';

    public $header_description_anmerkung = '';

    public $layoutmode = 2021;

    public $header_type = '';

    public $header_lineheight = '20';

    public $header_top = 10;

    public $header_name = '';

    public $header_description = '';

    public $series_header_cols = '';

    public $pdf_mode = '';

    public $compound_status = '';

    public $compoundStatusFooter = '';

    public $language_code_tmp = '';

    public $storageMonth = '12';

    public $footer_content = '';

    public $footer_content_s1 = '';

    public $table_end = false;

    public function setLayoutmode()
    {
        // if ( $_SERVER['REMOTE_ADDR'] == Ncw_Configure::read('developer_internal_ip') ) {
        $this->layoutmode = 2021;
        // }
    }

    public function __construct($author, $title, $subject, $language_id, $language_code = 'en')
    {
        $this->language_id = $language_id;
        $this->setLayoutmode();
        $this->language_code_tmp = $language_code;
        switch ($language_code) {
            case 'zh':
                // $this->font = "msungstdlight";
                // $this->font = "cyberbit";
                if (strstr($_SERVER['HTTP_USER_AGENT'], 'Android')) {
                    $this->font = 'arialuni';
                    // $this->font = "kozgopromedium";
                    // $this->font = "hanamina";
                } else if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                    $this->font = 'kozgopromedium';
                    $this->font = 'arialuni';
                } else {
                    $this->font = 'arialuni';
                }
                break;
            case 'jp':
                if (strstr($_SERVER['HTTP_USER_AGENT'], 'Android')) {
                    $this->font = 'arialuni';
                    // $this->font = "kozgopromedium";
                    // $this->font = "hanamina";
                } else if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                    $this->font = 'kozgopromedium';
                } else {
                    $this->font = 'arialuni';
                }
                break;
            case 'kr':
                if (strstr($_SERVER['HTTP_USER_AGENT'], 'Android')) {
                    $this->font = 'arialuni';
                } else if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                    // $this->font = "hysmyeongjostdmedium";
                    $this->font = 'arialuni';
                } else {
                    $this->font = 'arialuni';
                }
                break;
            case 'pl':
                if (strstr($_SERVER['HTTP_USER_AGENT'], 'Android')) {
                    $this->font = 'TitilliumWeb';
                    // $this->font = "kozgopromedium";
                    // $this->font = "hanamina";
                } else if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                    //    $this->font = "kozgopromedium";
                } else {
                    $this->font = 'TitilliumWeb';
                }
                // $this->font = "arialuni";
                break;
            default:
                if (strstr($_SERVER['HTTP_USER_AGENT'], 'Android') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                    $this->font = '';
                } else {
                    // $this->font = "arialuni";
                    $this->font = '';
                }
                break;
        }

        // $this->addTTFfont("modules/tpepdb2/vendor/tcpdf/fonts/ARIALUNI.ttf", "TrueTypeUnicode", "", 32);
        // $this->addTTFfont("modules/tpepdb2/vendor/tcpdf/fonts/ArialUnicode-Bold.ttf", "TrueTypeUnicode", "", 32);

        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $this->SetCreator('Kraiburg TPE');
        $this->SetAuthor(trim($author));
        $this->SetTitle($title);
        $this->SetSubject('');
        $this->SetKeywords('');

        $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->SetFont($this->font);
        $this->SetMargins(0, 0, 0);

        if ($_SESSION['datasheetmode'] == 'pg') {
            $this->SetMargins(7.5, 16.5, 7.5, true);  // Hier werden Die Abstände des eigentlichen Contents eingestellt Margin beeinflusst auch den Header. Die 20 beeinflusst nur den Start
        } else {
            if ($this->compound_status != 'non_portfolio') {
                if (true == isset($_GET['sid'])) {
                    $this->SetMargins(7.5, 88.5, 7.5, true);  // Hier werden Die Abstände des eigentlichen Contents eingestellt
                } else {
                    $this->SetMargins(7.5, 108.5, 7.5, true);  // Hier werden Die Abstände des eigentlichen Contents eingestellt
                }
            } else {
                $this->SetMargins(7.5, 46.5, 7.5, true);  // Hier werden Die Abstände des eigentlichen Contents eingestellt
            }
        }

        $this->SetHeaderMargin(7.5);
        $this->SetFooterMargin(36);

        $this->SetAutoPageBreak(FALSE, 35);

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
                // 'br' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
            )
        );
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function RotateCellContent($ae)
    {
        if (false === isset($this->rotate_cells['cellxy'])) {
            $this->rotate_cells['cellxy'] = array();
        }
        if (false === isset($this->rotate_cells['page_no'])) {
            $this->rotate_cells['page_no'] = array();
        }
        $this->rotate_cells['cellxy'][] = array($this->GetX(), $this->GetY());

        $this->rotate_cells['page_no'][] = $this->PageNo();
    }

    public function Header()
    {
        $language_id = $this->language_id;
        if ($this->pdf_mode == 'series') {
            if ($this->PageNo() > 1) {
                $this->setAutoPageBreak(false);
            }
        }

        $this->SetFont($this->font);
        $this->writeHTML($this->header_content, true, false, true, false, '');  // Logo Produktinformationen

        if ($this->PageNo() == 2 && $this->pdf_mode != 'series' && $this->layoutmode == 2021 && $_SESSION['datasheetmode'] != 'pg') {  // Datasheet oder Productinfromation
            $this->writeHTMLCell($w = 0, $h = 0, $x = 76, $y = 10, $html = $this->header_type2, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        } else {
            $this->writeHTMLCell($w = 0, $h = 0, $x = 76, $y = 7.6, $html = $this->header_type, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        }

        // if ($_SERVER["REMOTE_ADDR"] =='62.154.179.198') {
        $this->writeHTMLCell($w = 0, $h = 0, $x = 76, $y = 19.6, $html = str_replace(' | ', '', $this->header_name), $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        // } else {
        //    $this->writeHTMLCell($w = 0, $h = 0, $x = 76, $y = 22.6, $html = $this->header_name, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        // }

        $y = 44;
        if ($this->pdf_mode == 'series') {
            $y = 44;
        }
        if ($this->PageNo() < 2) {
            $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, 38.5, $html = $this->header_description_anmerkung, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            if ($this->language_code_tmp == 'es') {
                $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, 108.5, $html = $this->header_content_symbol, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);  // Grauer Kasten unter Logo
                $this->writeHTMLCell($w = 180, $h = 0, $x = 14.8, 110, $html = $this->header_description, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            } else {
                $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, 108.5, $html = $this->header_content_symbol, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);  // Grauer Kasten unter Logo
                $this->writeHTMLCell($w = 180, $h = 0, $x = 14.8, 110, $html = $this->header_description, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }
        } else {
            $this->SetMargins(7.5, 38, 7.5, true);  // Hier wird der Abstand nach oben Seite 2 aller Datenblätter festgelegt
        }
    }

    public function Footer()
    {
        $language_id = $this->language_id;

        $this->SetFont($this->font);
        //
        if ($this->PageNo() < 2 || $_SESSION['datasheetmode'] == 'pg') {
            if (true == isset($_SESSION['allseries2'])) {
                // $pdf->SetXY(0, 95);
                // $this->writeHTML('' . $this->footer_content, true, false, true, false, "");
                $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 290, $html = $this->footer_content, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                // $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 210, $html = $this->footer_content_s1  , $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            } else {
                if ($this->layoutmode == 2021) {
                    if ($this->compound_status == 'non_portfolio' && $this->pdf_mode != 'series') {
                        if ($_SESSION['storageMonth'] == 24) {
                            $html = '<table cellpadding="0" style=""><tr><td style="font-size: 11px;">' . Wcms_ContentboxController::getContenbox('pdb2-datasheet-footer-storage-24m', $language_id) . '</td></tr></table>';
                        } else {
                            $html = '<table cellpadding="0" style=""><tr><td style="font-size: 11px;">' . Wcms_ContentboxController::getContenbox('pdb2-datasheet-footer-storage', $language_id) . '</td></tr></table>';
                        }

                        if ($this->pdf_mode != 'series') {
                            $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 237, $html, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                        } else {
                            $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 237, $html, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                        }
                    }

                    $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 290, $html = $this->footer_content, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                } else {
                    $this->writeHTML('<br /><br />' . $this->footer_content, true, false, true, false, '');
                }

                /*
                 * Compound Lagerhinweise extra für non_portfolio auf Seite 1 des PDF's drucken
                 */
                if ($this->compoundStatusFooter == 'non_portfolio') {
                    $html = '<table cellpadding="0" style=""><tr><td style="font-size: 11px;">' . Wcms_ContentboxController::getContenbox('pdb2-datasheet-footer-storage', $language_id) . '</td></tr></table>';
                    $this->writeHTMLCell($w = 195, $h = 0, $x = 8, $y = 264, $html, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                }
            }
        } else {
            // $this->writeHTMLCell($w = 0, $h = 0, $x = 10, $y = 270, $html =$this->footer_content , $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            if (true == isset($_SESSION['allseries2'])) {
                $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 290, $html = $this->footer_content, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                // $this->writeHTML('' . $this->footer_content, true, false, true, false, "");
            } else {
                // if ($this->layoutmode == 2021) {
                // $this->writeHTML('<br /><br />' . $this->footer_content, true, false, true, false, "");
                $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 290, $html = $this->footer_content, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                if ($this->pdf_mode != 'series') {
                    $lh = 15;
                    if ($this->language_code_tmp == 'jp' || $this->language_code_tmp == 'kr' || $this->language_code_tmp == 'zh') {
                        $lh = 19;
                    }
                    $html = '<table cellpadding="5" style=" border: 1px solid #224d8f;"><tr><td style="font-size: 15px; line-height: ' . $lh . 'px;">' . Wcms_ContentboxController::getContenbox('diclaimer-s', $language_id) . '</td></tr></table>';
                    $this->writeHTMLCell($w = 190, $h = 0, $x = 9.5, $y = 177, $html, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                    $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 237, $html = $this->footer_content_s1, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                }

                // } else {
                //	$this->writeHTML('<br /><br />' . $this->footer_content, true, false, true, false, "");
                // }
            }
            if ($this->PageNo() == 3) {
                $lh = 15;
                if ($this->language_code_tmp == 'jp' || $this->language_code_tmp == 'kr' || $this->language_code_tmp == 'zh') {
                    $lh = 19;
                }
                $html = '<table cellpadding="5" style=" border: 1px solid #224d8f;"><tr><td style="font-size: 15px; line-height: ' . $lh . 'px;">' . Wcms_ContentboxController::getContenbox('diclaimer-s', $language_id) . '</td></tr></table>';
                $this->writeHTMLCell($w = 190, $h = 0, $x = 9.5, $y = 177, $html, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                $this->writeHTMLCell($w = 195, $h = 0, $x = 7.5, $y = 237, $html = $this->footer_content_s1, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }
        }
        if ($this->PageNo() > 2) {
            // Delete page 3
            if (false == isset($_SESSION['allseries'])) {
                if ($this->pdf_mode != 'series') {
                    if ($_SESSION['datasheetmode'] != 'pg') {
                        $this->deletePage(3);
                    }
                }
            } else {
                $this->deletePage(3);
            }
        }
    }
}
?>
