<?php
// Nepal Premium Store - MySQL + Google Sheets configuration
$host = 'localhost';
$db   = 'nepal_premium_store';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// GOOGLE SHEETS SETTINGS
// Paste the deployed Google Apps Script Web App URL here.
const GOOGLE_SHEET_WEBAPP_URL = 'https://script.google.com/macros/s/AKfycbysRb3_aQWy6_vOtaxbRmGm4HR3TwMLpbVr_mM7TIa9svAHEf_vuFqdmwSk42b_38Ph8w/exec';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed. Check config.php and make sure MySQL is running.<br><small>" . htmlspecialchars($e->getMessage()) . "</small>");
}
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function money($value) { return 'NPR ' . number_format((float)$value, 2); }
function pretty_date($date) { if (!$date) return ''; return date('d M Y', strtotime($date)); }
function next_invoice_no(PDO $pdo) {
    $year = date('Y');
    $stmt = $pdo->query("SELECT invoice_no FROM invoices ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetchColumn(); $n = 1;
    if ($last && preg_match('/(\d{4})$/', $last, $m)) $n = ((int)$m[1]) + 1;
    return 'NPS-' . $year . '-' . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
}
