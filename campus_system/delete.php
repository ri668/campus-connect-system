<?php
require 'database.php';
requireLogin();

$id   = (int)$_GET['id'];
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT campus FROM records WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) { header("Location: index.php"); exit; }
if (!isAdmin() && $row['campus'] !== currentCampus()) {
    header("Location: index.php"); exit;
}

$pdo->prepare("UPDATE records SET deleted_at = NOW() WHERE id=?")->execute([$id]);

header("Location: " . ($from === 'admin' ? "admin_records.php" : "index.php"));
exit;
?>
