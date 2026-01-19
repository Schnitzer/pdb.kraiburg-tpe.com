<?php

include('geturlcontent.php');

function saveToFile($content, $file = 'start.php')
{
	
$content = cleanstring($content); 

// Öffnet die Datei, um den vorhandenen Inhalt zu laden

// Schreibt den Inhalt in die Datei zurück
file_put_contents($file, $content);


}

function cleanstring ($str)
{
	
	$str = str_replace('http://', 'https://', $str);
	
	return $str;
}


$webseite_url = 'https://www.kraiburg-tpe.com';


$country = 'de';

//echo $webseite_url . '/' . $country . '/product-database';

//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/en/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/de/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/pl/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/it/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/es/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/fr/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/es/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/ko/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/ja/node/63');
//$code_webseite = getUrlContent('https://www.kraiburg-tpe.com/zh-hans/node/63');

//$code_webseite = getUrlContent( $webseite_url . '/' . $country . '/product-database');

$subdomian = 'pdb';
	$pdb_url = 'https://' . $subdomian . '.kraiburg-tpe.com';

$arr_languages = array(
	'de' => 'https://www.kraiburg-tpe.com/de/node/63',
	'en' => 'https://www.kraiburg-tpe.com/en/product-database',
	'pl' => 'https://www.kraiburg-tpe.com/pl/node/63',
	'it' => 'https://www.kraiburg-tpe.com/it/node/63',
	'fr' => 'https://www.kraiburg-tpe.com/fr/node/63',
	'es' => 'https://www.kraiburg-tpe.com/es/node/63',
	'kr' => 'https://www.kraiburg-tpe.com/ko/node/63',
	'jp' => 'https://www.kraiburg-tpe.com/ja/node/63',
	'zh' => 'https://www.kraiburg-tpe.com/zh-hans/node/63'
	
);

foreach ($arr_languages As $key => $item)  {
	$code_webseite = getUrlContent($item);
	
	// Hier wird der chat entfernt
	//$arr_code_webseite = explode('<script async type="text/javascript" src="https://userlike-cdn-widgets.s3-eu-west-1.amazonaws.com/', $code_webseite);
	//$arr_code_webseite2 = explode('.js"></script>', $arr_code_webseite[1]);
	//$code_webseite = $arr_code_webseite[0] . $arr_code_webseite2[1];
	//$code_webseite = str_replace('<script async type="text/javascript" src="https://userlike-cdn-widgets.s3-eu-west-1.amazonaws.com/b6b08bc6f9fbaeac1f232bfa4f8ba58c629adb2ec32902f742efdf63d1552ec2.js"></script>', '', $code_webseite);
	
	$code_webseite = str_replace('"www.', 'www.', $code_webseite);
	$code_webseite = str_replace('"/modules/', '"' . $webseite_url . '/modules/', $code_webseite);
	$code_webseite = str_replace('"/themes/', '"' . $webseite_url . '/themes/', $code_webseite);
	$code_webseite = str_replace('"/sites/', '"' . $webseite_url . '/sites/', $code_webseite);
	$code_webseite = str_replace('"/core/', '"' . $webseite_url . '/core/', $code_webseite);
	$code_webseite = str_replace('"/libraries/', '"' . $webseite_url . '/libraries/', $code_webseite);

	$code_webseite = str_replace("background-image:url('/sites/", "background-image:url('" . $webseite_url . "/sites/", $code_webseite);
	$code_webseite = str_replace('href="/', 'href="' . $webseite_url . '/', $code_webseite);

	
	// Hier wird nach Aufruf der PDB Webseite der Parameter searchheader eingefügt um auf der Suchseite zu landen (Sessions werden gelöscht in der TpePdbWeb.php)
	$code_webseite = str_replace('https://pdb.kraiburg-tpe.com/?ls=', 'https://'.$subdomian.'.kraiburg-tpe.com/?searchheader=true&ls=', $code_webseite);
	
	//$code_webseite = str_replace('http://', 'https://', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/en/node/63', $pdb_url. '/?ls=en', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/en/product-database', $pdb_url. '/?ls=en" target="_self', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/de/node/63', $pdb_url. '/?ls=de&sess=1" target="_self', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/pl/node/63', $pdb_url. '/?ls=pl" target="_self', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/it/node/63', $pdb_url. '/?ls=it" target="_self', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/fr/node/63', $pdb_url. '/?ls=fr" target="_self', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/es/node/63', $pdb_url. '/?ls=es" target="_self', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/ko/node/63', $pdb_url. '/?ls=kr" target="_self', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/ja/node/63', $pdb_url. '/?ls=jp" target="_self', $code_webseite);
	$code_webseite = str_replace('https://www.kraiburg-tpe.com/zh-hans/node/63', $pdb_url. '/?ls=zh" target="_self', $code_webseite);

	$code_webseite = str_replace('class="ui-link"', '', $code_webseite);

	
	$arr_code_website = explode('<pdbplaceholder>', $code_webseite);

	$arr_code_website[1] = str_replace('</pdbplaceholder>', '', $arr_code_website[1]);
	$arr_code_website[1] = str_replace('<pre>PDB PLACEHOLDER</pre>', '', $arr_code_website[1]);

	$arr_code_website[0] = str_replace('www.', 'www.', $arr_code_website[0]);
	$arr_code_website[1] = str_replace('www.', 'www.', $arr_code_website[1]);
	
	$arr_code_website[0] = str_replace('https://'.$subdomian.'.kraiburg-tpe.com/themes/schnitzraum/fonts/', $getDomain . '/themes/schnitzraum/fonts/', $arr_code_website[0]);
	$arr_code_website[0] = str_replace('https://'.$subdomian.'.kraiburg-tpe.com/themes/schnitzraum/', $getDomain . '/themes/schnitzraum/', $arr_code_website[0]);
	
	
	// PDB  Files schreiben DB jetzt online
	saveToFile($arr_code_website[0], '../../include/' . $key . '/start.php');
	saveToFile($arr_code_website[1], '../../include/'. $key. '/ende.php');
	
	// PDB 2 Files schreiben
	saveToFile(str_replace('pdb.', $subdomian. '.',$arr_code_website[0]), '/public_html//pdb/include/' . $key . '/start.php');
	saveToFile(str_replace('pdb.', $subdomian. '.',$arr_code_website[1]), '/public_html/pdb/include/'. $key. '/ende.php');
	echo $key .' done ' . date('Y-m-d H:i:s') . '<br />';
}






?>