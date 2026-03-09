<?php

/**
 * Encoding Diagnostic Script
 * Run this on both DDEV and Live server to compare results.
 * DELETE THIS FILE AFTER DIAGNOSIS!
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

// Load framework minimally
require_once __DIR__ . '/ncw/Exception.php';
require_once __DIR__ . '/ncw/Object.php';
require_once __DIR__ . '/ncw/Configure.php';
require_once __DIR__ . '/ncw/config/paths.php';
require_once __DIR__ . '/config/core.php';
require_once __DIR__ . '/ncw/basics.php';
require_once __DIR__ . '/ncw/Database.php';
require_once __DIR__ . '/config/database.php';

echo "=== ENCODING DIAGNOSTIC ===\n\n";

// 1. PHP Configuration
echo "--- PHP Configuration ---\n";
echo 'PHP Version: ' . phpversion() . "\n";
echo 'mb_internal_encoding: ' . mb_internal_encoding() . "\n";
echo 'default_charset: ' . ini_get('default_charset') . "\n";
echo 'mbstring.internal_encoding: ' . ini_get('mbstring.internal_encoding') . "\n";
echo 'mbstring.func_overload: ' . ini_get('mbstring.func_overload') . "\n";

// Check PCRE Unicode support
$pcre_unicode = @preg_split('//u', '테스트', -1, PREG_SPLIT_NO_EMPTY);
echo 'PCRE Unicode support: ' . ($pcre_unicode !== false ? 'YES (count=' . count($pcre_unicode) . ')' : 'NO') . "\n";
echo 'PCRE version: ' . PCRE_VERSION . "\n";

// Check mbstring
echo 'mb_convert_encoding available: ' . (function_exists('mb_convert_encoding') ? 'YES' : 'NO') . "\n";

// Check gzip/zlib
echo 'gzcompress available: ' . (function_exists('gzcompress') ? 'YES' : 'NO') . "\n";
echo 'gzuncompress available: ' . (function_exists('gzuncompress') ? 'YES' : 'NO') . "\n";

echo "\n--- App Configuration ---\n";
echo 'App.encodingdb: ' . Ncw_Configure::read('App.encodingdb') . "\n";
echo 'App.encoding: ' . Ncw_Configure::read('App.encoding') . "\n";

// 2. Database Connection
echo "\n--- Database Connection ---\n";
$db = Ncw_Database::getInstance();

// Check connection charset settings
$charset_vars = $db->query("SHOW VARIABLES LIKE 'character_set%'");
foreach ($charset_vars->fetchAll() as $row) {
    echo $row['Variable_name'] . ': ' . $row['Value'] . "\n";
}

echo "\n--- Collation Variables ---\n";
$collation_vars = $db->query("SHOW VARIABLES LIKE 'collation%'");
foreach ($collation_vars->fetchAll() as $row) {
    echo $row['Variable_name'] . ': ' . $row['Value'] . "\n";
}

// 3. Table charset for relevant tables
echo "\n--- Table Charsets ---\n";
$tables = [
    'ncw_tpepdb2_compounddescription',
    'ncw_tpepdb2_serie_values',
    'ncw_tpepdb2_compound_values',
    'ncw_wcms_contentbox_language',
    'ncw_tpepdb2_label'
];

foreach ($tables as $table) {
    $result = $db->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA = DATABASE()");
    $row = $result->fetch();
    if ($row) {
        echo "$table: " . $row['TABLE_COLLATION'] . "\n";
    } else {
        echo "$table: NOT FOUND\n";
    }
}

// 4. Column charsets for key columns
echo "\n--- Column Charsets (compounddescription) ---\n";
$cols = $db->query("SELECT COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = 'ncw_tpepdb2_compounddescription' AND TABLE_SCHEMA = DATABASE() AND CHARACTER_SET_NAME IS NOT NULL");
foreach ($cols->fetchAll() as $row) {
    echo '  ' . $row['COLUMN_NAME'] . ': charset=' . $row['CHARACTER_SET_NAME'] . ', collation=' . $row['COLLATION_NAME'] . "\n";
}

echo "\n--- Column Charsets (serie_values) ---\n";
$cols = $db->query("SELECT COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = 'ncw_tpepdb2_serie_values' AND TABLE_SCHEMA = DATABASE() AND CHARACTER_SET_NAME IS NOT NULL");
foreach ($cols->fetchAll() as $row) {
    echo '  ' . $row['COLUMN_NAME'] . ': charset=' . $row['CHARACTER_SET_NAME'] . ', collation=' . $row['COLLATION_NAME'] . "\n";
}

echo "\n--- Column Charsets (contentbox_language) ---\n";
$cols = $db->query("SELECT COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = 'ncw_wcms_contentbox_language' AND TABLE_SCHEMA = DATABASE() AND CHARACTER_SET_NAME IS NOT NULL");
foreach ($cols->fetchAll() as $row) {
    echo '  ' . $row['COLUMN_NAME'] . ': charset=' . $row['CHARACTER_SET_NAME'] . ', collation=' . $row['COLLATION_NAME'] . "\n";
}

// 5. Actual data test - compound 1414 Korean description
echo "\n--- Compound 1414 Korean Description ---\n";
$sth = $db->prepare("SELECT description, HEX(description) as hex_desc, LENGTH(description) as byte_len, CHAR_LENGTH(description) as char_len FROM ncw_tpepdb2_compounddescription WHERE compound_id = 1414 AND lang = 'kor'");
$sth->execute();
$rows = $sth->fetchAll();
if (count($rows) > 0) {
    foreach ($rows as $row) {
        echo '  description: ' . $row['description'] . "\n";
        echo '  HEX (first 120 chars): ' . substr($row['hex_desc'], 0, 120) . "\n";
        echo '  byte_length: ' . $row['byte_len'] . "\n";
        echo '  char_length: ' . $row['char_len'] . "\n";

        // Check if the string is valid UTF-8
        $is_utf8 = mb_check_encoding($row['description'], 'UTF-8');
        echo '  valid UTF-8: ' . ($is_utf8 ? 'YES' : 'NO') . "\n";

        // Check for double encoding
        $decoded = @mb_convert_encoding($row['description'], 'UTF-8', 'UTF-8');
        echo '  after re-decode: ' . $decoded . "\n";
    }
} else {
    echo "  NO DATA FOUND for compound_id=1414, lang='kor'\n";

    // Try to find what languages exist
    $sth2 = $db->prepare('SELECT lang, description FROM ncw_tpepdb2_compounddescription WHERE compound_id = 1414');
    $sth2->execute();
    $rows2 = $sth2->fetchAll();
    echo "  Available languages for compound 1414:\n";
    foreach ($rows2 as $row2) {
        echo '    lang=' . $row2['lang'] . ': ' . substr($row2['description'], 0, 50) . "\n";
    }
}

// 6. Test with BINARY to bypass charset conversion
echo "\n--- Compound 1414 Korean (BINARY bypass) ---\n";
$sth = $db->prepare("SELECT CAST(description AS BINARY) as raw_desc FROM ncw_tpepdb2_compounddescription WHERE compound_id = 1414 AND lang = 'kor'");
$sth->execute();
$rows = $sth->fetchAll();
if (count($rows) > 0) {
    foreach ($rows as $row) {
        echo '  raw (binary): ' . $row['raw_desc'] . "\n";
        echo '  HEX (first 120): ' . substr(bin2hex($row['raw_desc']), 0, 120) . "\n";
        $is_utf8 = mb_check_encoding($row['raw_desc'], 'UTF-8');
        echo '  valid UTF-8 (binary): ' . ($is_utf8 ? 'YES' : 'NO') . "\n";
    }
}

// 7. Compare with contentbox (which works correctly)
echo "\n--- Contentbox Test (should work) ---\n";
// Korean language_id = 10, try different table names
$contentbox_tables = ['ncw_wcms_contentbox_language', 'ncw_wcms_contentboxlanguage'];
foreach ($contentbox_tables as $cb_table) {
    try {
        $sth = $db->prepare("SELECT cl.body, HEX(cl.body) as hex_body FROM ncw_wcms_contentbox c INNER JOIN $cb_table cl ON c.id = cl.contentbox_id WHERE c.filename = 'pdb---datasheet---processing-method' AND cl.language_id = 10");
        $sth->execute();
        $rows = $sth->fetchAll();
        if (count($rows) > 0) {
            echo "  (table: $cb_table)\n";
            foreach ($rows as $row) {
                echo '  body: ' . $row['body'] . "\n";
                echo '  HEX (first 60): ' . substr($row['hex_body'], 0, 60) . "\n";
                $is_utf8 = mb_check_encoding($row['body'], 'UTF-8');
                echo '  valid UTF-8: ' . ($is_utf8 ? 'YES' : 'NO') . "\n";
            }
        }
        break;  // Found the right table
    } catch (Exception $e) {
        echo "  (table $cb_table not found, trying next)\n";
    }
}

// 8. Test serie_values for compound 1414
echo "\n--- Serie Values for Compound 1414 ---\n";
$sth = $db->prepare('SELECT c.serie_id FROM ncw_tpepdb2_compound c WHERE c.id = 1414');
$sth->execute();
$serie_row = $sth->fetch();
if ($serie_row) {
    $serie_id = $serie_row['serie_id'];
    echo "  serie_id: $serie_id\n";

    $sth2 = $db->prepare("SELECT description, text1, HEX(description) as hex_desc, HEX(text1) as hex_text1 FROM ncw_tpepdb2_serie_values WHERE serie_id = $serie_id AND language = 'kr'");
    $sth2->execute();
    $rows = $sth2->fetchAll();
    foreach ($rows as $row) {
        echo '  description: ' . ($row['description'] ?? 'NULL') . "\n";
        echo '  text1: ' . substr($row['text1'] ?? 'NULL', 0, 100) . "\n";
        echo '  HEX desc (first 60): ' . substr($row['hex_desc'] ?? '', 0, 60) . "\n";
        echo '  HEX text1 (first 60): ' . substr($row['hex_text1'] ?? '', 0, 60) . "\n";
    }
}

// 9. Font file check
echo "\n--- Font Files ---\n";
$font_dir = ROOT . DS . MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tcpdf' . DS . 'fonts' . DS;
$fonts = ['nanumgothic.php', 'nanumgothic.z', 'nanumgothic.ctg.z', 'NanumGothic.ttf',
    'kozgopromedium.php', 'stsongstdlight.php', 'cid0kr.php', 'cid0jp.php', 'cid0cs.php'];
foreach ($fonts as $font) {
    $path = $font_dir . $font;
    if (file_exists($path)) {
        echo "  $font: " . filesize($path) . ' bytes, readable=' . (is_readable($path) ? 'YES' : 'NO') . "\n";
    } else {
        echo "  $font: NOT FOUND\n";
    }
}

// 10. Test nanumgothic.ctg.z decompression
echo "\n--- Font CTG Decompression Test ---\n";
$ctg_file = $font_dir . 'nanumgothic.ctg.z';
if (file_exists($ctg_file)) {
    $ctg_data = file_get_contents($ctg_file);
    echo '  ctg.z size: ' . strlen($ctg_data) . " bytes\n";
    $decompressed = @gzuncompress($ctg_data);
    if ($decompressed !== false) {
        echo '  Decompressed size: ' . strlen($decompressed) . " bytes\n";
        // Check if Korean character U+C640 (와) is in the map
        $ctg_array = json_decode($decompressed, true);
        if (is_array($ctg_array)) {
            echo '  CTG entries: ' . count($ctg_array) . "\n";
            // Check for some Korean characters
            $test_chars = [0xC640, 0xC811, 0xCC29, 0xB418, 0xB294, 0xC5B4, 0xD50C];  // 와접착되는어플
            foreach ($test_chars as $code) {
                echo '  U+' . dechex($code) . ': ' . (isset($ctg_array[$code]) ? 'found (glyph=' . $ctg_array[$code] . ')' : 'NOT FOUND') . "\n";
            }
        } else {
            echo '  CTG format: not JSON, raw hex first 100: ' . substr(bin2hex($decompressed), 0, 100) . "\n";
        }
    } else {
        echo "  DECOMPRESSION FAILED!\n";
    }
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "Please run this on both DDEV and Live server and compare output.\n";
echo "DELETE THIS FILE AFTER USE!\n";
