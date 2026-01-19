<?php

include('geturlcontent.php');

function saveToFile($content, $file = 'start.php')
{
	


// Öffnet die Datei, um den vorhandenen Inhalt zu laden

// Schreibt den Inhalt in die Datei zurück
file_put_contents($file, $content);


}


$webseite_url = 'http://2019.kraiburg-tpe.com';

$code_webseite = getUrlContent( $webseite_url . '/en/product-database');






$code_webseite = str_replace('"/modules/', '"' . $webseite_url . '/modules/', $code_webseite);
$code_webseite = str_replace('"/themes/', '"' . $webseite_url . '/themes/', $code_webseite);
$code_webseite = str_replace('"/sites/', '"' . $webseite_url . '/sites/', $code_webseite);
$code_webseite = str_replace('"/core/', '"' . $webseite_url . '/core/', $code_webseite);
$code_webseite = str_replace('"/libraries/', '"' . $webseite_url . '/libraries/', $code_webseite);

$code_webseite = str_replace("background-image:url('/sites/", "background-image:url('" . $webseite_url . "/sites/", $code_webseite);
$code_webseite = str_replace('href="/', 'href="' . $webseite_url . '/', $code_webseite);

$code_webseite = str_replace('http://2019.kraiburg-tpe.com/en/node/63', 'http://kraiburg-tpe.com/special/curl/en/', $code_webseite);
$code_webseite = str_replace('http://2019.kraiburg-tpe.com/de/node/63', 'http://kraiburg-tpe.com/special/curl/de/', $code_webseite);
$code_webseite = str_replace('http://2019.kraiburg-tpe.com/pl/node/63', 'http://kraiburg-tpe.com/special/curl/pl/', $code_webseite);
$code_webseite = str_replace('http://2019.kraiburg-tpe.com/it/node/63', 'http://kraiburg-tpe.com/special/curl/it/', $code_webseite);
$code_webseite = str_replace('http://2019.kraiburg-tpe.com/fr/node/63', 'http://kraiburg-tpe.com/special/curl/fr/', $code_webseite);
$code_webseite = str_replace('http://2019.kraiburg-tpe.com/es/node/63', 'http://kraiburg-tpe.com/special/curl/es/', $code_webseite);
$code_webseite = str_replace('http://2019.kraiburg-tpe.com/ko/node/63', 'http://kraiburg-tpe.com/special/curl/kr/', $code_webseite);
$code_webseite = str_replace('http://2019.kraiburg-tpe.com/ja/node/63', 'http://kraiburg-tpe.com/special/curl/jp/', $code_webseite);
$code_webseite = str_replace('http://2019.kraiburg-tpe.com/zh-hans/node/63', 'http://kraiburg-tpe.com/special/curl/zh/', $code_webseite);
	
$arr_code_website = explode('<pdbplaceholder>', $code_webseite);

$arr_code_website[1] = str_replace('</pdbplaceholder>', '', $arr_code_website[1]);
$arr_code_website[1] = str_replace('<pre>PDB PLACEHOLDER</pre>', '', $arr_code_website[1]);

saveToFile($arr_code_website[0]);
saveToFile($arr_code_website[1], 'ende.php');



echo 'Done';
?>