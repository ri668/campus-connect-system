<?php
require 'database.php';
requireLogin();

$id   = (int)$_GET['id'];
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM records WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) { header("Location: index.php"); exit; }
if (!isAdmin() && $row['campus'] !== currentCampus()) { header("Location: index.php"); exit; }

if (isset($_POST['update'])) {
    $sql = "UPDATE records SET week_no=?,number_no=?,week_timeline=?,invited_during=?,last_name=?,first_name=?,mi=?,course_year=?,campus=?,added_status=?,cell_leader=?,consolidation_process=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['week_no'], $_POST['number_no'], $_POST['week_timeline'],
        $_POST['invited_during'], $_POST['last_name'], $_POST['first_name'],
        $_POST['mi'], $_POST['course_year'],
        $_POST['campus'] ?? $row['campus'],
        $_POST['added_status'], $_POST['cell_leader'], $_POST['consolidation_process'], $id
    ]);
    header("Location: " . ($from === 'admin' ? "admin_records.php" : "index.php"));
    exit;
}

$campus = currentCampus();
$campusLogo = getCampusLogo($campus);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Record</title>
<link rel="stylesheet" href="style.css">
<style>body { background: url('<?= htmlspecialchars(getCampusBackground($campus)) ?>') center/cover no-repeat fixed; }</style>
</head>
<body>
<div class="app-shell">
    <div class="page-header">
        <div class="header-brand">
            <div class="header-fish">
                <?php if (!isAdmin() && $campusLogo): ?>
                <img src="<?= htmlspecialchars($campusLogo) ?>" alt="<?= htmlspecialchars($campus) ?> logo" style="width:40px;height:40px;object-fit:contain;border-radius:50%;background:#fff;padding:2px;" onerror="this.style.display='none'">
                <?php else: ?>
                <?= flameIcon(28) ?>
                <?php endif; ?>
            </div>
            <div>
                <h1>Edit Record</h1>
                <p>Campus Database Management System</p>
            </div>
        </div>
        <div class="header-right">
            <?php if (isAdmin()): ?>
            <span style="background:var(--gold);color:var(--text-dark);padding:4px 12px;border-radius:50px;font-size:0.72rem;font-weight:700;">⚙ ADMINISTRATOR</span>
            <?php else: ?>
            <span class="campus-badge"><?= htmlspecialchars($campus) ?></span>
            <?php endif; ?>
            <a href="logout.php" class="logout-btn">Sign Out</a>
        </div>
    </div>

    <div class="container">
        <div class="page-wrapper">
            <div class="back-nav">
                <a href="<?= $from === 'admin' ? 'admin_records.php' : 'index.php' ?>">← Back</a>
            </div>
            <h1 class="page-title">Edit Record #<?= $id ?></h1>

            <form method="POST" class="record-form">
                <input type="text" name="week_no" placeholder="Week No" value="<?= htmlspecialchars($row['week_no']) ?>">
                <input type="text" name="number_no" placeholder="No." value="<?= htmlspecialchars($row['number_no']) ?>">
                <input type="text" name="week_timeline" placeholder="Week Timeline" value="<?= htmlspecialchars($row['week_timeline']) ?>">
                <input type="text" name="invited_during" placeholder="Invited During" value="<?= htmlspecialchars($row['invited_during']) ?>">
                <input type="text" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($row['last_name']) ?>">
                <input type="text" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($row['first_name']) ?>">
                <input type="text" name="mi" placeholder="Middle Initial" value="<?= htmlspecialchars($row['mi']) ?>">
                <input type="text" name="course_year" placeholder="Course / Year Level" value="<?= htmlspecialchars($row['course_year'] ?? '') ?>">
                <?php if (isAdmin()): ?>
                <input type="text" name="campus" placeholder="Campus" value="<?= htmlspecialchars($row['campus']) ?>">
                <?php endif; ?>
                <input type="text" name="added_status" placeholder="Added? (Yes/No)" value="<?= htmlspecialchars($row['added_status']) ?>">
                <input type="text" name="cell_leader" placeholder="Cell Leader" value="<?= htmlspecialchars($row['cell_leader']) ?>">
                <input type="text" name="consolidation_process" placeholder="Consolidation" value="<?= htmlspecialchars($row['consolidation_process']) ?>">
                <button type="submit" name="update">✏️ Update Record</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
