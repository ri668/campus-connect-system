<?php
session_start();

$host     = "localhost";
$dbname   = "finals_lab1";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}

$pdo->exec("
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_campus VARCHAR(100),
    sender_role VARCHAR(20) DEFAULT 'user',
    message TEXT,
    note TEXT,
    target VARCHAR(200) DEFAULT 'all',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
");

// Add course_year column if not exists
try { $pdo->exec("ALTER TABLE records ADD COLUMN course_year VARCHAR(100) DEFAULT '' AFTER mi"); } catch (PDOException $e) {}

define('CAMPUS_USERS', [
    'UNIVERSITY OF BAGUIO'             => 'UBConnect',
    'SAINT LOUIS UNIVERSITY'           => 'SLUConnect',
    'UNIVERSITY OF PHILIPPINES'        => 'UPConnect',
    'BAGUIO CENTRAL UNIVERSITY'        => 'BCUConnect',
    'PINES CITY COLLEGES'              => 'PCCConnect',
    'PINES CITY NATIONAL HIGH SCHOOL'  => 'PCNHSConnect',
    'GUISAD VALLEY'                    => 'GVNHSonnect',
    'BENGUET NATIONAL HIGH SCHOOL'     => 'BNHSConnect',
    'EASTER COLLEGES'                  => 'ECConnect',
    'ACATECH AVIATION COLLEGE'         => 'AACConnect',
    'UNIVERSITY OF CORDILLERAS'        => 'UCConnect',
    'QUEZON HILL NATIONAL HIGH SCHOOL' => 'QHNHSConnect',
    'BENGUET STATE UNIVERSITY'         => 'BSUConnect',
    'OTHER CAMPUS'                     => 'OCConnect',
]);

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Admin@2025');

define('CAMPUS_LOGOS', [
    'UNIVERSITY OF BAGUIO'             => 'https://upload.wikimedia.org/wikipedia/en/thumb/0/0a/University_of_Baguio_seal.png/120px-University_of_Baguio_seal.png',
    'SAINT LOUIS UNIVERSITY'           => 'https://upload.wikimedia.org/wikipedia/en/6/6e/Saint_Louis_University_%28Philippines%29_seal.png',
    'UNIVERSITY OF PHILIPPINES'        => 'https://upload.wikimedia.org/wikipedia/en/thumb/e/e5/University_of_the_Philippines_seal.svg/120px-University_of_the_Philippines_seal.svg.png',
    'BAGUIO CENTRAL UNIVERSITY'        => 'https://upload.wikimedia.org/wikipedia/en/7/7a/Baguio_Central_University_Seal.png',
    'PINES CITY COLLEGES'             => 'https://upload.wikimedia.org/wikipedia/en/2/22/Pines_City_Colleges_seal.png',
    'PINES CITY NATIONAL HIGH SCHOOL'  => '',
    'GUISAD VALLEY'                    => '',
    'BENGUET NATIONAL HIGH SCHOOL'     => '',
    'EASTER COLLEGES'                  => 'https://upload.wikimedia.org/wikipedia/en/4/4e/Easter_College_logo.png',
    'ACATECH AVIATION COLLEGE'         => '',
    'UNIVERSITY OF CORDILLERAS'        => 'https://upload.wikimedia.org/wikipedia/en/c/c5/University_of_the_Cordilleras_seal.png',
    'QUEZON HILL NATIONAL HIGH SCHOOL' => '',
    'BENGUET STATE UNIVERSITY'         => 'https://upload.wikimedia.org/wikipedia/en/6/67/Benguet_State_University_seal.png',
    'OTHER CAMPUS'                     => '',
]);

function requireLogin() {
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        header("Location: login.php"); exit;
    }
}
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') { header("Location: index.php"); exit; }
}
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function currentCampus() {
    return $_SESSION['campus'] ?? '';
}
function getCampusLogo($campus) {
    return getCampusIcon($campus);
}
function flameIcon($size = 28) {
    return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M40 6 C36 16 28 18 30 32 C24 24 20 28 23 38 C17 30 19 40 25 46 C20 46 16 50 21 56 C27 64 38 68 50 62 C62 56 66 44 60 34 C55 24 49 28 47 18 C45 10 42 8 40 6Z" fill="rgba(201,168,76,0.95)"/>
        <path d="M36 34 C31 30 25 33 27 39 C29 44 35 42 37 46 C39 50 37 54 39 57 C41 59 46 56 45 51 C44 46 40 44 40 39 C40 34 37 34 36 34Z" fill="rgba(14,69,71,0.9)"/>
        <ellipse cx="52" cy="54" rx="11" ry="6" fill="none" stroke="rgba(201,168,76,0.95)" stroke-width="2.5"/>
        <path d="M63 54 L72 47 L72 61 Z" fill="rgba(201,168,76,0.95)"/>
        <line x1="48" y1="51" x2="46" y2="57" stroke="rgba(201,168,76,0.95)" stroke-width="2" stroke-linecap="round"/>
        <circle cx="46" cy="52" r="1.5" fill="rgba(201,168,76,0.95)"/>
    </svg>';
}

require_once __DIR__ . '/background.php';
?>
