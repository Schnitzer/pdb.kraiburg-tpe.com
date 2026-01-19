<?php
function cleanPdb($str, $langshortcode = 'en')
{
	
	$str = str_replace('http://www.kraiburg-tpe.com/pdb/'.$langshortcode.'/products/product-database', 'http://kraiburg-tpe.com/special/curl/' . $langshortcode, $str);
	
  $arr = explode('<!--TRENNER-->', $str);
  $arr1 = explode('<!--TRENNER-->', $arr[1]);
  
  $db_url = "http://pdb.kraiburg-tpe.com/";
  
  $str = $arr1[0];
  

  $str = '
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
	
$strs .= '<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>

<script src=' . $db_url . 'assets/tpepdb2/javascript/config.js?time=' . date('mdH') . '" type="text/javascript"></script>
<script src="' . $db_url . 'assets/tpepdb2/javascript/main.js?time=' . date('mdH') . '" type="text/javascript"></script>';
	
	$str = str_replace($o, $n, $str);

  
  return $str;
}

?>