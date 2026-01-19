<?php

require_once "nusoap/lib/nusoap.php";

// start client
$client = new nusoap_client("http://www.kraiburg-tpe.com/pdb/soap/");
#$client = new nusoap_client("http://localhost/www.kraiburg-tpe.com/soap/");


// alle Regionen
//$result = $client->call("getRegions", array("language_id" => 1));

// Märkte einer Region
//$result = $client->call("getMarkets", array("language_id" => 3, "region_id" => 3, "count" => false));

// Anwendungen eines Marktes einer Region
//$result = $client->call("getApplications", array("language_id" => 3, "region_id" => 1, "market_id" => 16, "count" => false));

// Materialvorteile einer Application
//$result = $client->call("getAdvantages", array("username" => "testuser", "password" => 'ncwwsol1', "language_id" => 2, "region_id" => 1, "application_id" => 39, "count" => false));

// Liste der gefilterten Serien
// market_id, application_id materialadvantages_id sind optional
// Gibt id, name, Beschreibung (kurz) und Beschreibung zurück
//$result = $client->call("getSeriesList", array("language_id" =>2, "region_id" => 1, "market_id" => 0, "application_id" => 28, "materialadvantages_id" => 0, "count" => false));
//$result = $client->call("getSeriesList", array("username" => "testuser", "password" => "ncwwsol1", "language_id" =>2, "region_id" => 3, "market_id" => 0, "application_id" => 0, "materialadvantages_id" => 0, "count" => false));


// Detailansicht einer Serie
//$result = $client->call("getSeriesDetails", array("language_id" => 2, "series_id" => 20));

// Username und Passwort prüfen
//$result = $client->call("checkLogin", array("username" => 'testuser', "password" => 'ncwwsol1'));

//$result = $client->call("checkLogin2", array("username" => 'testuser', "password" => 'ncwwsol1'));

//$result = $client->call("textSearch", array("sid" => "testuser", "language_id" =>2, "region_id" => 1, "str_text" => urlencode("dw/cs"), "count" => 0, "debug" => 2));
//$result = $client->call("textSearch", array("sid" => "7318d7696bb972ffcc0475f10b6a8f7e", "language_id" => 2, "region_id" => 1, "str_text" => urlencode("TF5AAC-SLP"), "count" => 0, "debug" => 1));
$result = $client->call("textSearch", array("sid" => "7318d7696bb972ffcc0475f10b6a8f7e3", "language_id" =>2, "region_id" => 1, "str_text" => urlencode("tm5med"), "count" => 0, "debug" => 3));
//$result = $client->call("textSearch", array("sid" => "7318d7696bb972ffcc0475f10b6a8f7e", "language_id" =>2, "region_id" => 0, "str_text" => urlencode("TF5EFC"), "count" => 0, "debug" => 4));
//$result = $client->call("textSearch", array("sid" => "7318d7696bb972ffcc0475f10b6a8f7e", "language_id" => 2, "region_id" => 1, "str_text" => urlencode("Tür- und Fensterdichtungen"), "count" => falsw, "debug" => 0));
//$result = $client->call("textSearch", array("sid" => "7318d7696bb972ffcc0475f10b6a8f7e", "language_id" => 2, "region_id" => 0, "str_text" => urlencode("Tür- und Fensterdichtungen"), "count" => falsw, "debug" => 0));

//$result = $client->call("textSearch", array("username" => "testuser", "password" => "ncwwsol1", "language_id" => 1, "region_id" => 1, "str_text" => urlencode("Telecommunication"), "count" => 1, "debug" => false));

//$result = $client->call("checkLogin2", array("username" => 'testuser', "password" => 'ncwwsol1'));

/*
session_start();

$sid = "";
if (true === isset($_SESSION['PDB_SID'])) {
	$sid = $_SESSION['PDB_SID'];
}

$result = $client->call("checkLogin2", array("username" => 'testuser', "password" => 'ncwwsol1', 'session_id' => $sid));
$result = json_decode($result);
$sid = $result->sid;
if (false === isset($_SESSION['PDB_SID']) && false === empty($sid)) {
	$_SESSION['PDB_SID'] = $sid;
}

*/

var_dump($result);
?>