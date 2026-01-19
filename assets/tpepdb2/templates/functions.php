<?php

function td_styles ($font_size_table="12px", $width="35%") {
	return 'height: 30px; border-bottom: 1px solid #ccc;width:' . $width . '; font-size:' . $font_size_table .';';
}

$GLOBALS['is_pdf'] = $is_pdf;

function td_value ($value, $font_size_table="12px", $is_bold=false, $color="#4B4B4D", $padding_left="18px", $padding_top="9px;") {
	$bold = "";
	if (true == $is_bold) {
		 $bold = "font-weight: bold;";
	}
	$html = '<table style="width: 100%;" cellpadding="0" cellspacing="0">';

	if (true == $GLOBALS['is_pdf']) {
		$html .= '<tr><td colspan="0" style="height: ' . $padding_top . 'px;line-height:2px;font-size:2px;">&nbsp;</td></tr>';
	}

	if (false != $padding_left) {
		$html .= '<tr><td style="width:' . $padding_left . ';">&nbsp;</td><td style="font-size:' . $font_size_table .';color:' . $color . ';' . $bold . '">' . $value . '</td></tr></table>';
	} else {
		$html .= '<tr><td style="width: 100%; font-size:' . $font_size_table .';color:' . $color . ';' . $bold . '">' . $value . '</td></tr></table>';
	}

	return $html;
}

function resizeImage ($filename, $max_width, $max_height) {
    $filename = str_replace(Ncw_Configure::read('Project.url') . "/", "", $filename);
    list($orig_width, $orig_height) = getimagesize($filename);

    $width = $orig_width;
    $height = $orig_height;

    # Höher
    if ($height > $max_height) {
        $width = ($max_height / $height) * $width;
        $height = $max_height;
    }

    # Breiter
    if ($width > $max_width) {
        $height = ($max_width / $width) * $height;
        $width = $max_width;
    }

    $image_p = imagecreatetruecolor($width, $height);

    $image = imagecreatefromjpeg($filename);

    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

    return $image_p;
}

function remove_bold($string)
{
	if (strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone')  || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') ){ 
		$string = str_replace('<b>', '', $string);
		$string = str_replace('</b>', '', $string);
		
		$string = str_replace('<strong>', '', $string);
		$string = str_replace('</strong>', '', $string);
		
	}
	
	//$string .= ' ' . $_SERVER['HTTP_USER_AGENT'];
	
	return $string;
}
?>
