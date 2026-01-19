<?php

include('geturlcontent.php');




function cleanPdb($str)
{
	
	$str = str_replace('http://www.kraiburg-tpe.com/pdb/en/products/product-database', 'http://kraiburg-tpe.com/special/curl', $str);

	
  $arr = explode('<!--TRENNER-->', $str);
  $arr1 = explode('<!--TRENNER-->', $arr[1]);
  
  $db_url = "https://pdb.kraiburg-tpe.com/";
  
  $str = $arr1[0];
  
	

	
	
  $str = '
	
	<!--<link rel="stylesheet" href="' . $db_url . 'pdb/assets/wcms/css/font-awesome.min.css?p27ht0" media="all" />-->


<!--<link rel="stylesheet" href="' . $db_url . 'assets/wcms/css/kraiburg.css?p27ht0" media="all" />-->
<link rel="stylesheet" href="' . $db_url . 'assets/wcms/css/tpe.css?time=1234" media="all" />
<link rel="stylesheet" href="' . $db_url . 'assets/wcms/css/tpepdb2.css?time=1234" media="all" />
' . $str;
  
  $str .= '<!--<script src="' . $db_url . '/core/assets/vendor/domready/ready.min.js?v=1.0.8"></script>-->
         <script src="' . $db_url . 'assets/wcms/javascript/jquery-3.3.1.min.js?v=3.2.1"></script>
         <script src="' . $db_url . 'assets/tpepdb2/javascript/config.js?time=101609" type="text/javascript"></script>
	       <script src="' . $db_url . 'assets/tpepdb2/javascript/main.js?time=101609" type="text/javascript"></script>' 
        ;
	$o = 'class="layout-content"';
	$n = 'class="layout-content" style="background-image:url(http://2019.kraiburg-tpe.com/sites/default/files/assets/pattern/blue.png"';
	
$str .= '<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>

<script src=' . $db_url . 'assets/tpepdb2/javascript/config.js?time=' . date('mdH') . '" type="text/javascript"></script>
<script src="' . $db_url . 'assets/tpepdb2/javascript/main.js?time=' . date('mdH') . '" type="text/javascript"></script>';
	
	$str = str_replace($o, $n, $str);

  
  return $str;
}

$webseite_url = 'https://www.kraiburg-tpe.com';
/*
$code_webseite = getUrlContent( $webseite_url . '/en/product-database');



$code_webseite = str_replace('"/modules/', '"' . $webseite_url . '/modules/', $code_webseite);
$code_webseite = str_replace('"/themes/', '"' . $webseite_url . '/themes/', $code_webseite);
$code_webseite = str_replace('"/sites/', '"' . $webseite_url . '/sites/', $code_webseite);
$code_webseite = str_replace('"/core/', '"' . $webseite_url . '/core/', $code_webseite);
$code_webseite = str_replace('"/libraries/', '"' . $webseite_url . '/libraries/', $code_webseite);

$code_webseite = str_replace("background-image:url('/sites/", "background-image:url('" . $webseite_url . "/sites/", $code_webseite);
$code_webseite = str_replace('href="/', 'href="' . $webseite_url . '/', $code_webseite);




$arr_code_website = explode('<pdbplaceholder>', $code_webseite);

$arr_code_website[1] = str_replace('</pdbplaceholder>', '', $arr_code_website[1]);
$arr_code_website[1] = str_replace('<pre>PDB PLACEHOLDER</pre>', '', $arr_code_website[1]);
*/
//saveToFile($arr_code_website[0]);
//saveToFile($arr_code_website[1], 'ende.php');

$add_to_url = '?search=true';
$i = 0;
foreach($_GET as $key=>$value)
{
    //if ($i > 0) {
      $add_to_url .= '&';
    //}
    $add_to_url .= $key."=".$value;
}

$code_pdb = getUrlContent('https://pdb.kraiburg-tpe.com' . $add_to_url);
$code_pdb = cleanPdb($code_pdb);
$code_pdb = str_replace('/sites/', $webseite_url . '/sites/', $code_pdb);
$code_pdb = str_replace('<script src="http://www.kraiburg-tpe.com/pdb/assets/wcms/js/plugins/lightbox/js/lightbox.min.js"></script>', '', $code_pdb);


//$code_webseite = $arr_code_website[0] . $code_pdb . $arr_code_website[1];

include('start.php');
$code_webseite =  $code_pdb;

echo $code_webseite;
include('ende.php');
?>