<?php
$str = 'PA와 접착되는 어플리케이션';

// Test 1: Kommt der String korrekt an?
echo 'HEX: ' . bin2hex($str) . "\n";

// Test 2: Wie viele Zeichen erkennt preg_split?
$chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
echo 'preg_split Zeichenanzahl: ' . count($chars) . " (erwartet: 20)\n";

// Test 3: Was liefert mb_convert_encoding für einen Korean-Char?
$sample = '와';
$ucs = mb_convert_encoding($sample, 'UCS-4BE', 'UTF-8');
list(, $cp) = unpack('N', $ucs);
echo "Codepoint von '와': " . $cp . " (erwartet: 50752)\n";

// Test 4: mbstring Konfiguration
echo 'mb_internal_encoding: ' . mb_internal_encoding() . "\n";
echo 'mbstring.encoding_translation: ' . ini_get('mbstring.encoding_translation') . "\n";
echo 'default_charset: ' . ini_get('default_charset') . "\n";
