<?php
require 'database.php';
header('Content-Type: application/json');

$query  = isset($_GET['q']) ? trim($_GET['q']) : '';
$campus = isset($_GET['campus']) ? trim($_GET['campus']) : '';

if (empty($campus) && isset($_SESSION) && isset($_SESSION['campus'])) {
    $campus = $_SESSION['campus'];
}

$searchTerm = "%$query%";

if (isAdmin()) {
    if (empty($query)) {
        $stmt = $pdo->query("SELECT * FROM records WHERE deleted_at IS NULL ORDER BY id DESC");
    } else {
        $sql = "SELECT * FROM records WHERE deleted_at IS NULL AND (
            id LIKE ? OR week_no LIKE ? OR number_no LIKE ? OR week_timeline LIKE ? OR
            invited_during LIKE ? OR last_name LIKE ? OR first_name LIKE ? OR mi LIKE ? OR
            course_year LIKE ? OR campus LIKE ? OR added_status LIKE ? OR cell_leader LIKE ? OR consolidation_process LIKE ?
        ) ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_fill(0, 13, $searchTerm));
    }
} else {
    if (empty($query)) {
        $stmt = $pdo->prepare("SELECT * FROM records WHERE deleted_at IS NULL AND campus = ? ORDER BY id DESC");
        $stmt->execute([$campus]);
    } else {
        $sql = "SELECT * FROM records WHERE deleted_at IS NULL AND campus = ? AND (
            id LIKE ? OR week_no LIKE ? OR number_no LIKE ? OR week_timeline LIKE ? OR
            invited_during LIKE ? OR last_name LIKE ? OR first_name LIKE ? OR mi LIKE ? OR
            course_year LIKE ? OR campus LIKE ? OR added_status LIKE ? OR cell_leader LIKE ? OR consolidation_process LIKE ?
        ) ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge([$campus], array_fill(0, 13, $searchTerm)));
    }
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
