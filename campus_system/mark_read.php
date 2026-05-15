<?php
require 'database.php';
requireLogin();
$campus = $_GET['campus'] ?? currentCampus();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id=?")->execute([$id]);
} elseif ($campus === 'admin') {
    $pdo->exec("UPDATE notifications SET is_read = 1");
} else {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE target = 'all' OR target LIKE ?")->execute(["%$campus%"]);
}
echo 'ok';
?>
