<?php
require 'database.php';
requireLogin();
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT campus FROM records WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) { header("Location: recycle_bin.php"); exit; }
if (!isAdmin() && $row['campus'] !== currentCampus()) {
    header("Location: recycle_bin.php"); exit;
}

$pdo->prepare("UPDATE records SET deleted_at = NULL WHERE id=?")->execute([$id]);
header("Location: recycle_bin.php");
exit;
?>
