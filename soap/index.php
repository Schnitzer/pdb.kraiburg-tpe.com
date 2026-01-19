<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Functions

function getRegions ($language_id)
{
    $language_id = (int) $language_id;

    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/getRegions?l=" . $language_id;
	// $datei = "http://www.kraiburg-tpe.com/admin/tpepdb2/soap/getMarkets?l=1&r=2";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);


    return $content;
    //return file_get_contents('http://www.kraiburg-tpe.com/admin/tpepdb2/soap/getRegions?l=' . $language_id);
}

function getMarkets ($language_id, $region_id, $just_count = false)
{
    $language_id = (int) $language_id;
	$region_id = (int) $region_id;

    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/getMarkets" 
        . "?l=" . $language_id 
        . "&r=" . $region_id
        . "&just_count=" . $just_count
        ;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);


    return $content;
}

function getApplications ($language_id, $region_id, $market_id, $just_count = false)
{
    $language_id = (int) $language_id;
	$region_id = (int) $region_id;

    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/getApplications"
        . "?l=" . $language_id 
        . "&r=" . $region_id
		. "&ma=" . $market_id
		. "&just_count=" . $just_count
		;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);


    return $content;
}

function getAdvantages ($language_id, $region_id, $application_id, $just_count = false)
{
    $language_id = (int) $language_id;
	$region_id = (int) $region_id;
    $application_id = (int) $application_id;

    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/getAdvantages"
        . "?l=" . $language_id 
        . "&r=" . $region_id
		. "&ap=" . $application_id
		. "&just_count=" . $just_count
		;
        
        
        
        //$datei = "http://www.kraiburg-tpe.com/admin/tpepdb2/soap/getAdvantages?r=1&l=".$language_id."&ap=38";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);

//$content = $datei;
    return $content;
}

/**
 * Gibt eine Liste gefiltertere Serien zurück 
 */
function getSeriesList ($language_id, $region_id, $markets_id, $application_id, $materialadvantages_id, $just_count = false)
{
    $language_id = (int) $language_id;
	$region_id = (int) $region_id;

    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/getSeriesList"
        . "?l=" . $language_id 
        . "&r=" . $region_id
		. "&ma=" . $markets_id
		. "&anwendungsbereiche_id=" . $application_id
		. "&mv=" . $materialadvantages_id
		. "&just_count=" . $just_count
		;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);

    return $content;
}

/*
 * Wenn eine Serie gefunden wurde, wird nach Klick dieses hier aufgerufen
 * Gibt details zur Serie zurück. Incl. Link zurm Datenblatt und
 * Liste aller entahlenen Compounds  
 */
function getSeriesDetails($language_id, $series_id)
{
    $language_id = (int) $language_id;
    $region_id = (int) $region_id;

    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/getSeriesDetails"
        . "?l=" . $language_id 
        . '&sid=' . $series_id
        ;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);

    return $content;
}

/*
 * Suche nach Text, Serien und Compoundname
 */
function textSearch($sid, $language_id, $region_id, $str_text, $just_count = false, $debug = false)
{
    $language_id = (int) $language_id;
 		$region_id = (int) $region_id;

    
    
    if (false == empty($sid)) {
        session_id($sid);
    }
    session_start();
    
    $username = '';
    $password = '';
    if (true == isset($_SESSION['ncwu'])) {
        $username = $_SESSION['ncwu'];
        $password = $_SESSION['ncwf'];
        //return $_SESSION['ncwu'].$_SESSION['ncwf'];
    }
    
		
    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/textSearch"
        . "?username=" . $username
        . "&password=" . $password
        . "&l=" . $language_id 
        . "&r=" . $region_id
        . '&str_text=' . $str_text
        . '&debug=' . $debug
        . "&just_count=" . $just_count
        ;
return $datei;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);

    return $content;
}

/*
 * Prüft username und Passwort 
 */
function checkLogin($username, $password)
{

    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/checkLogin"
        . "?username=" . $username
        . "&password=" . $password 
        ;


    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);

    return $content;
}

/*
 * Prüft username und Passwort 
 */
function checkLogin2($username, $password)
{

    $datei = "http://www.kraiburg-tpe.com/pdb/admin/tpepdb2/soap/checkLogin"
        . "?username=" . $username
        . "&password=" . $password 
        ;


    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);

    session_start();
    $return = array();
    $return['sid'] = session_id();
    if ($content == 'true') {
        $_SESSION['ncwu'] = $username;
        $_SESSION['ncwf'] = $password;

        return $return['sid'];
    }

    return 'false';
}


/*
 * Prüft username und Passwort 
 */
function checkLogin23($username, $password, $session_id)
{
	if (false == empty($session_id)) {
	    session_id($session_id);
	}
    session_start();
    $return = array();
    $return['sid'] = session_id();
    
    if (true == isset($_SESSION['ncwtest'])) {
        $return['content'] = $_SESSION['ncwtest'] . ' alt';
    } else {
        $_SESSION['ncwtest'] = 'SaveMe';
        $return['content'] = $_SESSION['ncwtest'] . ' neu';
    }
    
    return json_encode($return);
}

// Start Server
$server = new SOAPServer(
    NULL,
    array(
        'uri' => 'http://www.kraiburg-tpe.com/pdb/soap/'
    )
);


// Add functions
$server->addFunction('getRegions');
$server->addFunction('getMarkets');
$server->addFunction('getApplications');
$server->addFunction('getAdvantages');
$server->addFunction('getSeriesList');
$server->addFunction('getSeriesDetails');
$server->addFunction('getMaterialSearch');
$server->addFunction('textSearch');
$server->addFunction('checkLogin');
$server->addFunction('checkLogin2');

$server->handle();

exit();

?>
