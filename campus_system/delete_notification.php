<?php
require 'database.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare("DELETE FROM notifications WHERE id=?")->execute([$id]);
}
echo 'ok';
?>
