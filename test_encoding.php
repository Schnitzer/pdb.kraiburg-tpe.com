<?php
error_reporting(E_ALL);
require_once __DIR__ . '/ncw/Exception.php';
require_once __DIR__ . '/ncw/Object.php';
require_once __DIR__ . '/ncw/Configure.php';
require_once __DIR__ . '/ncw/config/paths.php';
require_once __DIR__ . '/config/core.php';
require_once __DIR__ . '/ncw/basics.php';
require_once __DIR__ . '/ncw/Database.php';
require_once __DIR__ . '/config/database.php';

$db = Ncw_Database::getInstance();

// Check text1 with utf8mb4 connection
$sth = $db->prepare("SELECT text1, HEX(text1) as h FROM ncw_tpepdb2_serie_values WHERE serie_id=81 AND language='kr'");
$sth->execute();
$r = $sth->fetch();
echo 'utf8mb4 text1: ' . trim($r['text1']) . "\n";
echo 'utf8mb4 HEX: ' . $r['h'] . "\n";
echo 'valid UTF-8: ' . (mb_check_encoding($r['text1'], 'UTF-8') ? 'YES' : 'NO') . "\n\n";

// Check with latin1 connection
$db->exec("SET NAMES 'latin1'");
$sth2 = $db->prepare("SELECT text1, HEX(text1) as h FROM ncw_tpepdb2_serie_values WHERE serie_id=81 AND language='kr'");
$sth2->execute();
$r2 = $sth2->fetch();
echo 'latin1 text1: ' . trim($r2['text1']) . "\n";
echo 'latin1 HEX: ' . $r2['h'] . "\n";
echo 'HEX same: ' . ($r['h'] === $r2['h'] ? 'YES' : 'NO') . "\n";
