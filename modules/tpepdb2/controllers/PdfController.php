<?php
/**
 * PdfController class.
 *
 * @package netzcraftwerk
 */
class Tpepdb2_PdfController extends Tpepdb2_ModuleController
{
    /**
     * @var array
     */
    public $acl_publics = array(
      "index",
      "safetydata",
	  "allseries",
	  "allseriesgenerate"
    );

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
		// Hascode prüfen ob Zeit abgelaufen ist
		$u = Ncw_Library_Sanitizer::escape($_GET['u']);
		if (strlen($u) > 20) {
			$hascodeOk = $this->_checkHashCode($u);
			if ($hascodeOk != true) {
				echo 'Der Link ist leider abgelaufen.<br />We are sorry The used link is not active anymore.';
				exit;
			}
		}
		$this->view = false;
		include_once "modules/tpepdb2/vendor/tpepdb/TpePdbWeb.php";
		$language_code = $language_id = 0;
		$_GET['pdf'] = 1;
		$_SESSION['datasheetmode'] = $_GET['datasheetmode'];
		// dieser Parameter wird von den PDF Teilen Links übergeben 
		unset($_SESSION['u']);
		if (true == isset($this->params['url']['u'])) {
			$_SESSION['u'] = Ncw_Library_Sanitizer::escape($this->params['url']['u']);
		}
		if ('true' == Ncw_Library_Sanitizer::escape($this->params['url']['notunset'])) {
			$_SESSION['allseries2'] = 'true';
		} else {
			unset($_SESSION['allseries2']);
		}
		$tpepdb_web = new TpePdbWeb($language_code, $language_id);
		$tpepdb_web->content();
    }

	
    /**
     * allseriesAction generiert alle Reihen PDF#s der gewählten Sprache
     *
     */
    public function allseriesAction ()
    {
        $this->view = false;
			
			$start = time();
			// $language = $this->params["url"]["l"];
			$language = Ncw_Library_Sanitizer::escape($this->params["url"]["l"]);

			$region = 0;
			$_SESSION['allseries2'] = 'true';

			$str_search = "
				SELECT
				id, name
				FROM ncw_tpepdb2_serie As series
				
				WHERE pdb20 = 1
				
				ORDER by name
			";
			
			//return $str_search;

			$db = Ncw_Database::getInstance();
			$sth = $db->prepare( $str_search);
			$sth->execute();
			$results = $sth->fetchAll();
			
			foreach ($results As $seriestmp) {
				echo '<iframe src="https://pdb.kraiburg-tpe.com/tpepdb/pdf?sid='.$seriestmp['id'].'&l='.$language.'&datasheetmode=datasheet&notunset=true" style="width: 10px; height: 10px; border: 0; background: #000;"></iframe>';
				//sleep(3);
				//break;
			}


			echo '<script>
			$("iframe").css("width: 33px;");
			</script>';
		}
	
		/*
		* Zusammenfügen der Einzelnen PDF Dateien und generieren der 1. Seite 
		+ Inhaltsverzeichnis
		*/
    public function allseriesjoinAction ()
    {
        $this->view = false;
        include_once "modules/tpepdb2/vendor/tpepdb/TpePdbWeb.php";
				$arr_dateien = $_SESSION['katalog_datien'];


        $safetydata = false;
        $language = Ncw_Library_Sanitizer::escape($this->params["url"]["l"]);
			
			
				
			
        $region = 0;
			  $_SESSION['allseries2'] = 'true';
			
				$str_search = "
					SELECT 

					series.id, 
					series.name,
					brand.name brandname,
					brand.name_zh brandnamezh

					FROM ncw_tpepdb2_serie As series
					
					INNER JOIN ncw_tpepdb2_brand As brand
					ON series.brand_id = brand.id
					
					WHERE series.pdb20 = 1
					
					
					ORDER by brand_id, name


				";
				$db = Ncw_Database::getInstance();
				$sth = $db->prepare( $str_search);
				$sth->execute();
				$results = $sth->fetchAll();
			
			
				$arr_serien = array();
				$counter = 3;
				$str_counter = 0;
				$arr_dateien = array();
				$arr_serien_left = array();
				$arr_serien_right = array();
				$count_all = count($results);
			
			
				$verzeichnis =  ASSETS . DS . "tpepdb2" . DS ."pdfs" . DS . "katalog" . DS . $language;
				
				foreach ($results As $seriestmp) {
					
					
					$name_datei = str_replace('/', '-', $seriestmp['name']);
					$name_datei = 'kat'. $name_datei . '.pdf';
					$arr_dateien[] = $name_datei;
					
					
					$str_counter += $this->count_pdf_pages($verzeichnis . DS . $name_datei);
					
					$counter_anker = $str_counter;
					
					
					$brandname = $seriestmp['brandname'];
					if ($language == 'zh') {
						$brandname = $seriestmp['brandnamezh'];
					}
					$str_inhalt_eine_zeile = '<table border="0" cellpadding="2" width="300"><tr>
					<td width="45">
						<a href="#'.$counter_anker.'" style="color:black; text-decoration: none; text-align: right;">' . $str_counter . '</a>
					</td>
					<td> - ' . $seriestmp['name'] . '</td>
					<td style="color: #ccc; width: 108px;"> - ' . $brandname .'</td>
					</tr></table>';
					
					if (($counter/3) *2  <= $count_all +1) {
						$arr_serien_left[] = $str_inhalt_eine_zeile;
					} else {
						$arr_serien_right[] = $str_inhalt_eine_zeile;
					}
					//$arr_serien[] = $str_inhalt_eine_zeile;
					//$arr_serien[] =  $counter . ' - ' . $seriestmp['name'];
					$counter += 3;
				}
				$str_inhaltsverzeichnis_left = implode('<br />', $arr_serien_left);
				$str_inhaltsverzeichnis_right = implode('<br />', $arr_serien_right);


					include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tcpdf" . DS . "config" . DS . "tcpdf_config.php";
					include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tcpdf" . DS . "tcpdf.php";
					include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "FPDI" . DS . "fpdi.php";

          $pdf = new FPDI();
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
					$pdf->SetMargins(0, 0, 0);
					$pdf->setAutoPageBreak(false);
			
					$img_startseite = ASSETS . DS . "tpepdb2" . DS ."pdfs" . DS . "katalog" . DS . 'images' . DS . 'deckblatt.jpg';
					$pdf->AddPage();
					$pdf->Image($img_startseite, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=true);
					$pdf->SetXY(40, 95);
						//$pdf->Write(8, ' - ' . $count . ' - ');
					if ($language == 'zh' || $language == 'kr' || $language == 'jp') {
						$pdf->setFont("arialuni");
					}
			
					// STARTSEITE
					$language_id = $this->_languagestrtoid($language); 
					$str_startseite = strtoupper(html_entity_decode(Wcms_ContentboxController::getContenbox('serienportfolio', $language_id))) . '<br />KRAIBURG TPE';
					$pdf->SetTextColor(3,78,144); // Blau
					if ($language_id == 5) {
						$pdf->SetFontSize(30);
					} else {
						$pdf->SetFontSize(35);
					}
					
					$pdf->writeHTMLCell(0, 25, '', '', $str_startseite, '', 0, 0, false, 'L', false);
			


	
					// INHALTSVERZEICHNIS
					$pdf->AddPage();
					$pdf->SetXY(27, 27);
					$pdf->SetFontSize(13.5);
					if ($language == 'zh' || $language == 'kr' || $language == 'jp') {
						$pdf->SetFontSize(12.5);
					}

					$str_inhaltsverzeichnis_ueb = '<div style="font-size: 30px;">'. html_entity_decode(Wcms_ContentboxController::getContenbox('inhaltsverzeichnis', $language_id)) . '</div>';
					$pdf->setFont("TitilliumWeb");
					$pdf->writeHTMLCell(0, 0, '', '', $str_inhaltsverzeichnis_ueb, '', 0, 0, false, 'L', true);
					
					$str_inhaltsverzeichnis = '
					<table border="0" width="500">
						<tr>
							<td>' . $str_inhaltsverzeichnis_left . '</td>
							<td>' . $str_inhaltsverzeichnis_right . '</td>
						</tr>
					</table>';
			
					$pdf->SetXY(15, 70);
					$pdf->SetFontSize(11.5);
					if ($language == 'zh') {
						$pdf->SetFont('arialuni');
					}
					$pdf->writeHTMLCell(0, 0, '', '', $str_inhaltsverzeichnis, '', 0, 0, false, 'L', false);
					//$pdf->SetXY(120, 50);
					//$pdf->writeHTMLCell(0, 0, '', '', $str_inhaltsverzeichnis_right, '', 0, 0, false, 'L', false);
					$count = 2;
			
			
					$flag = false;
					$real_count = 3;
					foreach ($arr_dateien AS $file) {
							// get the page count
							$pageCount = $pdf->setSourceFile($verzeichnis . DS . $file);
							// iterate through all pages
							for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
								// import a page
								$templateId = $pdf->importPage($pageNo);
								// get the size of the imported page
								$size = $pdf->getTemplateSize($templateId);

								// create a page (landscape or portrait depending on the imported page size)
								if ($size['w'] > $size['h']) {
										$pdf->AddPage('L', array($size['w'], $size['h']));
								} else {
										$pdf->AddPage('P', array($size['w'], $size['h']));
								}
								$pdf->SetXY(100, 285);
									//$pdf->Write(8, ' - ' . $count . ' - ');
								$seiten_count = $count + 1;
										$str_seitenzahl =  '- ' .  $seiten_count . ' -' ;
								//$pdf->SetFillColor(3,78,144); // Blau
								$pdf->SetFillColor(255,255,255); // Blau
								//$pdf->SetTextColor(255,255,255);
								$pdf->SetTextColor(0,0,0);
								$pdf->SetFontSize(8);
								//$pdf->writeHTMLCell(0, 0, '', '', $str_seitenzahl, '', 0, 1, false, 'R', false);
								$pdf->Cell($w, $h=7, $str_seitenzahl, $border=0, $ln=0, $align='', $fill=1, $link='', $stretch=0, $ignore_min_height=false, $calign='R', $valign='M');
							//	$pdf->writeHTML($str_seitenzahl, true, false, true, false, '');
									// use the imported page
								$count++;
								$real_count++;
									$pdf->useTemplate($templateId);
								
								if ($real_count == 3106 || $real_count == 3106) {
									
									$pdf->deletePage($real_count);
									$count--;
								}
								
							}
					}
			
 
            
            $pdf->Output("katalog.pdf");
        
    }
	
				// Make a function for convenience 
		public function count_pdf_pages($pdfname) {
			$pdftext = file_get_contents($pdfname);
			$num = preg_match_all("/\/Page\W/", $pdftext, $dummy);

			return $num;
		}
		
	
		protected function _languagestrtoid($language_str)
		{
			//if ($language_str > 0) {
				if ($language_str == 'en') {
					return '1';
				}
				if ($language_str == 'de') {
					return '2';
				}
				if ($language_str == 'fr') {
					return '5';
				}
				if ($language_str == 'it') {
					return '8';
				}
				if ($language_str == 'pl') {
					return '11';
				}
				if ($language_str == 'pt') {
					return '9';
				}

				if ($language_str == 'es') {
					return '4';
				}
				if ($language_str == 'jp') {
					return '7';
				}
				if ($language_str == 'zh') {
					return '3';
				}
				if ($language_str == 'kr') {
					return '10';
				}
			//}

			return 'eng';
		}
	
    /**
     * Sicherheitdatenblätter 
     * Festlegen der Position der Eindrucke
     */
    public function safetydataAction ()
    {
        $this->view = false;

        if (false === isset($this->params["url"]["s"])
            && false === isset($this->params["url"]["l"])
            && false === isset($this->params["url"]["t"])
            && false === isset($this->params["url"]["r"])
        ) {
            return;
        }

        $safetydata =$this->params["url"]["s"];
        $language = $this->params["url"]["l"];
        $region = 0;
        if (true == isset($this->params["url"]["r"])) {
            $region = (int) $this->params["url"]["r"];
        }
        $text = Ncw_Library_Sanitizer::clean($this->params["url"]["t"]);
		
		$text = str_replace('_', '-', $text);
		$text = str_replace('&#45;', '-', $text);
		
        $safetydata = str_replace('/', '', $safetydata);
		$safetydata = str_replace('HTC8881/', 'HTC8881-', $safetydata);
		$safetydata = str_replace('HTF8675-', 'HTF8675', $safetydata);
		$safetydata = str_replace('FOR-TEC E', 'For-Tec E', $safetydata);
		$safetydata = str_replace('HTC9486/18', 'HTC948618', $safetydata);
		$safetydata = str_replace('S7 HTC948618+19', 'S7 HTC94861819', $safetydata);
		$safetydata = str_replace('S21 max. 100 ppm MAH', 'S21 max100ppmMAH', $safetydata);
		$safetydata = str_replace('S22 max. 10 ppm MAH', 'S22 max10ppmMAH', $safetydata);
        $safetydata = str_replace("S23 STC8319/", 'S23 STC8319', $safetydata);
		$safetydata = str_replace("S25 STC8319/", 'S23 STC8319', $safetydata);		
		$safetydata = str_replace("S25 HTC8848/158", 'S25', $safetydata);
      	$safetydata = str_replace("S25%20HTC8848/158", 'S25', $safetydata);
		$safetydata = str_replace("HTC8848158", '', $safetydata);

		if (true == strstr($safetydata, 'S24' ) ) {
			$safetydata = trim($safetydata);
		}
		if (true == strstr($safetydata, 'S25' ) ) {
			$safetydata = trim($safetydata);
		}

        switch ($region) {
            case 1:
                if ($language != "de" && $language != "it" && $language != "pl" && $language != "fr" && $language != "es") {
                    $language = "en";
                }
                break;
            case 2:
                $language = "us";
                break;
            case 3:
                if ($language != "zh" && $language != "kr" && $language != "jp") {
                    $language = "asia";
                }
            break;
        }

        $path = ASSETS . DS . "tpepdb2" . DS ."safetydata" . DS . $safetydata . "_" . $language . ".pdf";

        if ($language == "us" && false === is_file($path)) {
            $path = str_replace("us", "en", $path);
        }

        if ($language == "zh" && false === is_file($path)) {
            $path = str_replace("zh", "asia", $path);
        }

        if (false === is_file($path)) {
            $path = ASSETS . DS . "tpepdb2" . DS ."safetydata" . DS . $safetydata . "_en.pdf";
        }

        if (true === is_file($path)) {
            include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tcpdf" . DS . "config" . DS . "tcpdf_config.php";
            include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "tcpdf" . DS . "tcpdf.php";
            include_once MODULES . DS . "tpepdb2" . DS . "vendor" . DS . "FPDI" . DS . "fpdi.php";

            $pdf = new FPDI();

            $page_count = $pdf->setSourceFile($path);
            for ($page_no = 1; $page_no <= $page_count; ++$page_no) {
                 $tpl_idx = $pdf->ImportPage($page_no);
                 $s = $pdf->getTemplateSize($tpl_idx);
                 $pdf->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h']));
                 $pdf->useTemplate($tpl_idx);

                 if ($page_no == 1) {

					$pdf->SetFont('TitilliumWeb', '', 9);
                    $pdf->SetTextColor(0, 0, 0);
                    $x = 81.5;
					$y = 33;

					$str_product_code = 'Product code: ';

                    if ($language == "fr") {
                        $x = 77;
                    }
                    if ($language == "us") {
                        $x = 77;
                    }
                    if ($language == "kr") {
                        $x = 84;
                    }

                    if ($safetydata == "S1 THERMOLAST") {
                        $x = 72.8;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.7;
							$y = 86.5;
						}
						if ($language == "kr") {
							$x = 86.3;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.8;
							$y = 86.5;
						}
                    }
                    if ($safetydata == "S1 THERMOLAST K") {
                        $x = 72.8;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.7;
							$y = 86.5;
						}
						if ($language == "kr") {
							$x = 86.3;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.8;
							$y = 86.5;
						}
                    }
                    if ($safetydata == "S51 THERMOLAST V") {
                        $x = 72.8;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.7;
							$y = 86.5;
						}
						if ($language == "kr") {
							$x = 86.3;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.8;
							$y = 86.5;
						}
                    }
                    if ($safetydata == "S52 THERMOLAST A") {
                        $x = 72.8;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.7;
							$y = 86.5;
						}
						if ($language == "kr") {
							$x = 86.3;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.8;
							$y = 86.5;
						}
                    }
                    if ($safetydata == "S53 THERMOLAST S") {
                        $x = 72.8;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.7;
							$y = 86.5;
						}
						if ($language == "kr") {
							$x = 86.3;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.8;
							$y = 86.5;
						}
                    }
                    if ($safetydata == "S54 THERMOLAST H") {
                        $x = 72.8;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.7;
							$y = 86.5;
						}
						if ($language == "kr") {
							$x = 86.3;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.8;
							$y = 86.5;
						}
                    }
                    if ($safetydata == "S55 THERMOLAST R") {
                        $x = 72.8;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.7;
							$y = 86.5;
						}
						if ($language == "kr") {
							$x = 86.3;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.8;
							$y = 86.5;
						}
                    }
                    if ($safetydata == "S56 THERMOLAST DW") {
                        $x = 72.8;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.7;
							$y = 86.5;
						}
						if ($language == "kr") {
							$x = 86.3;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.8;
							$y = 86.5;
						}
                    }
					
                    if ($safetydata == "S2 COPEC") {
                        $x = 72.6;
						$y = 86.5;
						$str_product_code = '';
						if ($language == "asia") {
							$y = 86.5;
							$x = 81;
						}
						if ($language == "zh") {
							$y = 86.5;
							$x = 85.5;
						}
						if ($language == "kr") {
							$y = 86.5;
							$x = 86.2;
						}
						if ($language == "jp") {
							$y = 86.5;
							$x = 84.5;
						}
                    }
                    if ($safetydata == "S3 For-Tec E") {
                        $x = 72.8;
						$y = 85.4;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.4;
						}
						if ($language == "zh") {
							$x = 85.5;
							$y = 86.4;
						}
						if ($language == "kr") {
							$x = 86.2;
							$y = 86.4;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.4;
						}
                    }
                    if ($safetydata == "S4 HIPEX") {
                        $x = 72.7;
						$y = 86.4;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.4;
						}
						if ($language == "zh") {
							$x = 85.5;
							$y = 86.4;
						}
						if ($language == "kr") {
							$x = 86.2;
							$y = 86.4;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.4;
						}
                    }

                    if ($safetydata == "S13 THERMOLAST M") {
                        $x = 76.6;
						$y = 85.5;
						$str_product_code = '';
						if ($language == "asia") {
							$x = 81;
							$y = 86.5;
						}
						if ($language == "zh") {
							$x = 85.8;
						}
						if ($language == "kr") {
							$x = 84.7;
							$y = 86.5;
						}
						if ($language == "jp") {
							$x = 84.5;
							$y = 86.5;
						}
						if ($language == "us") {
							$x = 72.7;
						}
                    }

                    if ($safetydata == "S5 HTF867569") {
                        $x = 55; 
                    }
                    if ($safetydata == "S7") {
                        $x = -55; 
						$y = 1000;
                    }
                    if ($safetydata == "S8 max 250ppm DCOIT") {
                        $x = 55;
                    }
                    
                    if ($safetydata == "S9 max15000ppm DuftoelOperett") {
                        $x = 55;
                    }
                    if ($safetydata == "S10 TC7EAZ") {
                        $x = 60;
                    }
                    if ($safetydata == "S12 max 2490ppm DCOIT") {
                        $x = 60;
                    }
                    if ($safetydata == "S11 low density 0,6-0,9") {
                        $x = 60;
                    }

									 
                    if ($safetydata == "S17 HTC8881-01" || $safetydata == "S17 HTC888101") {
                        $x = 65;
                    }
									 
                    if ($safetydata == "S18 3760") {
                        $x = 55;
                    }
                    if ($safetydata == "S19 7955") {
                        $x = 55;
                    }
                    if ($safetydata == "S20 8959") {
                        $x = 55;
                    }
									 
                    if ($safetydata == "S21 max100ppmMAH") {
                        $x = 81.4;
						$y = 90;
						if ($language == "kr" || $language == "jp" || $language == "zh" || $language == "asia" || $language == "us" ) {
								$x = 51.4;
								$y = 20;
						}
						if ($language == "fr" || $language == "es" || $language == "de" ) {
								$x = 85.6;
						}
						$str_product_code = '';
                    }
                    if ($safetydata == "S22 max10ppmMAH") {
                        $x = 81.4;
						$y = 90;
						if ($language == "kr" || $language == "jp" || $language == "zh" || $language == "asia" || $language == "us" ) {
								$x = 51.4;
								$y = 20;
						}
						if ($language == "fr" || $language == "es" || $language == "de" ) {
								$x = 85.6;
						}
						$str_product_code = '';
                    }
									 
                    if ($safetydata == "S23 STC8319136") {
                        $x = 81.4;
						$y = 90;
						if ($language == "kr" || $language == "jp" || $language == "zh" || $language == "asia" || $language == "us" ) {
								$x = 51.4;
								$y = 20;
						}
						if ($language == "fr" || $language == "es" || $language == "de" ) {
								$x = 85.6;
						}
						$str_product_code = '';
                    }
									 
                    if ($safetydata == "S25") {
                        $x = 81.4;
						$y = 90;
						if ($language == "kr" || $language == "jp" || $language == "zh" || $language == "asia" || $language == "us" ) {
							$x = 81.4;
							$y = 90;
						}
						if ($language == "fr" || $language == "es" || $language == "de" ) {
								$x = 85.6;
						}
						$str_product_code = '';
                    }
									 
                    if ($safetydata == "S27") {
						$x = 81.4;
						$y = 90;
						if ($language == "kr" || $language == "jp" || $language == "zh" || $language == "asia" || $language == "us" ) {
							$x = 81.4;
							$y = 90;
						}
						if ($language == "fr" || $language == "es" || $language == "de" ) {
							$x = 85.6;
						}
						$str_product_code = '';
                    }
									 
                    if ($safetydata == "S00 SAP") {
                        $x = 81.4;
						$y = 90;
						if ($language == "kr" || $language == "jp" || $language == "zh" || $language == "asia" || $language == "us" ) {
							$x = 81.4;
							$y = 90;
						}
						if ($language == "fr" || $language == "es" || $language == "de" ) {
							$x = 85.6;
						}
						$str_product_code = '';
                    }
                    if ($safetydata == "S99 SAP EHS") {
                        $x = 97.0;
						$y = 58;
						$str_product_code = '';
                    }
									 
                    if ($safetydata != "S5 HTF867569" && $safetydata != 'S7' && $safetydata != 'S7 HTC94861819' && $safetydata  != 'S99 SAP EHS') {
                        $pdf->SetXY($y, $x);
						$pdf->SetFont('arialuni');
                        $pdf->Write(0, $str_product_code . $text);
                    }
					
                   // if ($safetydata != "S5 HTF867569" && $safetydata != 'S7' && $safetydata != 'S7 HTC94861819') {
                   //     $pdf->SetXY($y, $x);
					//	$pdf->SetFont('arialuni');
                  //      $pdf->Write(0, $str_product_code . $text);
                  //  }
                }
            }
            
            if ($safetydata == "S11 low density 0,6-0,9") {
				$safetydata = str_replace(',', '', $safetydata);
				$safetydata = str_replace(' ', '', $safetydata);
            }
	
			// Titel der SDS jetzt immer SDS da so gewünscht am 23.10.2020
            $safetydata = 'SDS';
            $pdf->Output($safetydata . "_" . $language . "_" . $text .".pdf");
        }
    }

	/*
	* Prüft ob der Hascode noch gültig ist
	*/
	private function _checkHashCode($hashCode)
	{
		return true;
		$str_search = "
		SELECT 
		
		*

		FROM ncw_tpepdb2_sharefile

		WHERE hashCode = '" . $hashCode . "'

		";
		
		$db = Ncw_Database::getInstance();
		$sth = $db->prepare( $str_search);
		$sth->execute();
		$results = $sth->fetchAll();

		$startTime = $results[0]['startzeit'];
		$laufzeit = $results[0]['laufzeit'];
		$nowtime = time();

		if ($startTime > $nowtime) {
			return false;
		}
		if ($startTime + $laufzeit < $nowtime) {
			return false;
		}

		return true;
	}
}
?>
