<?php

include('../geturlcontent.php');


$langshortcode = 'fr';
include('../cleanpdb.php');


$webseite_url = 'http://2019.kraiburg-tpe.com';


$add_to_url = '?search=true&ls=' . $langshortcode;
$i = 0;
foreach($_GET as $key=>$value)
{
    //if ($i > 0) {
      $add_to_url .= '&';
    //}
    $add_to_url .= $key."=".$value;
}

$code_pdb = getUrlContent('http://pdb.kraiburg-tpe.com' . $add_to_url);
$code_pdb = cleanPdb($code_pdb, $langshortcode);
$code_pdb = str_replace('/sites/', $webseite_url . '/sites/', $code_pdb);
$code_pdb = str_replace('<script src="http://www.kraiburg-tpe.com/pdb/assets/wcms/js/plugins/lightbox/js/lightbox.min.js"></script>', '', $code_pdb);



include('../start.php');
$code_webseite =  $code_pdb;

echo $code_webseite;
include('../ende.php');
?>