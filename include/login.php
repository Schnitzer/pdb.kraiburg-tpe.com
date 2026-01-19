<?php
session_start();
include('geturlcontent.php');


$langshortcode = 'en';

include('cleanpdb.php');

$webseite_url = 'http://2019.kraiburg-tpe.com';

$this_url = 'pdb.kraiburg-tpe.com/include/login.php';
$this_url = $_SERVER['PHP_SELF'];

$add_to_url = '&search=true&ls=' . $langshortcode;

//var_dump($_SESSION);

$i = 0;
foreach($_GET as $key=>$value)
{
    //if ($i > 0) {
      $add_to_url .= '&';
    //}
    $add_to_url .= $key."=".$value;
}
foreach($_POST as $key=>$value)
{
    //if ($i > 0) {
      $add_to_url .= '&';
    //}
    $add_to_url .= $key."=".$value;
}
$str_getUrlContent = 'http://pdb.kraiburg-tpe.com/index.php?url=en/products/loginktpepdb-215' . $add_to_url;
//echo $str_getUrlContent;
$code_pdb = getUrlContent($str_getUrlContent);
$code_pdb = cleanPdb($code_pdb, $langshortcode);
$code_pdb = str_replace('/sites/', $webseite_url . '/sites/', $code_pdb);

//var_dump($_POST);

$code_pdb = str_replace('http://my.formlink.de', $this_url, $code_pdb);

$code_pdb = str_replace('<script src="http://www.kraiburg-tpe.com/pdb/assets/wcms/js/plugins/lightbox/js/lightbox.min.js"></script>', '', $code_pdb);



include('start.php');
$code_webseite =  $code_pdb;

echo $code_webseite;
include('ende.php');
?>