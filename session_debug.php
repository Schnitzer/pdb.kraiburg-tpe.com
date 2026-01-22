<?php
// Session Debug Script für Testserver
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session Diagnose</h1>";

echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h2>2. Session Konfiguration</h2>";
echo "session.save_handler: " . ini_get('session.save_handler') . "<br>";
echo "session.save_path: " . ini_get('session.save_path') . "<br>";
echo "session.name: " . ini_get('session.name') . "<br>";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "<br>";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "<br>";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "<br>";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "<br>";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "<br>";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "<br>";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "<br>";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "<br>";

echo "<h2>3. Session Save Path Prüfung</h2>";
$save_path = ini_get('session.save_path');
echo "Save Path existiert: " . (is_dir($save_path) ? 'JA' : 'NEIN') . "<br>";
if (is_dir($save_path)) {
    echo "Save Path beschreibbar: " . (is_writable($save_path) ? 'JA' : 'NEIN') . "<br>";
    echo "Save Path Berechtigungen: " . substr(sprintf('%o', fileperms($save_path)), -4) . "<br>";
    echo "Save Path Owner: " . posix_getpwuid(fileowner($save_path))['name'] . "<br>";
}
echo "PHP läuft als User: " . posix_getpwuid(posix_geteuid())['name'] . "<br>";

echo "<h2>4. Custom Session Path (Framework)</h2>";
$custom_path = __DIR__ . '/tmp/sessions';
echo "Custom Path: $custom_path<br>";
echo "Custom Path existiert: " . (is_dir($custom_path) ? 'JA' : 'NEIN') . "<br>";
if (is_dir($custom_path)) {
    echo "Custom Path beschreibbar: " . (is_writable($custom_path) ? 'JA' : 'NEIN') . "<br>";
    echo "Custom Path Berechtigungen: " . substr(sprintf('%o', fileperms($custom_path)), -4) . "<br>";
    echo "Anzahl Session-Dateien: " . count(glob($custom_path . '/sess_*')) . "<br>";
}

echo "<h2>5. Session Test</h2>";
// Test mit Standard-Pfad
echo "Starte Session mit Standard-Pfad...<br>";
session_start();
$_SESSION['test'] = 'standard_path';
echo "Session ID (Standard): " . session_id() . "<br>";
echo "Session Daten: " . print_r($_SESSION, true) . "<br>";
session_write_close();

// Test mit Custom-Pfad
echo "<br>Starte Session mit Custom-Pfad...<br>";
if (is_dir($custom_path) && is_writable($custom_path)) {
    session_save_path($custom_path);
    session_name('NCWSESSID');
    session_start();
    $_SESSION['test'] = 'custom_path';
    echo "Session ID (Custom): " . session_id() . "<br>";
    echo "Session Daten: " . print_r($_SESSION, true) . "<br>";
    echo "Session File: " . $custom_path . '/sess_' . session_id() . "<br>";
    echo "Session File existiert: " . (file_exists($custom_path . '/sess_' . session_id()) ? 'JA' : 'NEIN') . "<br>";
    session_write_close();
} else {
    echo "FEHLER: Custom Path nicht beschreibbar!<br>";
}

echo "<h2>6. Cookie Test</h2>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'nicht gesetzt') . "<br>";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'nicht gesetzt') . "<br>";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'nicht gesetzt') . "<br>";
echo "REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'nicht gesetzt') . "<br>";
echo "<br>Gesendete Cookies:<br>";
echo "<pre>" . print_r($_COOKIE, true) . "</pre>";

echo "<h2>7. Empfehlung</h2>";
if (!is_dir($custom_path)) {
    echo "<strong style='color:red;'>FEHLER: tmp/sessions Verzeichnis existiert nicht!</strong><br>";
    echo "Führe aus: mkdir -p " . $custom_path . "<br>";
}
if (is_dir($custom_path) && !is_writable($custom_path)) {
    echo "<strong style='color:red;'>FEHLER: tmp/sessions Verzeichnis nicht beschreibbar!</strong><br>";
    echo "Führe aus: chmod 777 " . $custom_path . "<br>";
    echo "Oder besser: chown www-data:www-data " . $custom_path . " && chmod 755 " . $custom_path . "<br>";
}
