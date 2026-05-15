<?php
require 'database.php';
requireLogin();
if (isAdmin()) { header("Location: admin_dashboard.php"); exit; }

$campus = currentCampus();
$campusLogo = getCampusLogo($campus);

if (isset($_POST['submit'])) {
    $sql = "INSERT INTO records (week_no, number_no, week_timeline, invited_during, last_name, first_name, mi, course_year, campus, added_status, cell_leader, consolidation_process)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['week_no'], $_POST['number_no'], $_POST['week_timeline'],
        $_POST['invited_during'], $_POST['last_name'], $_POST['first_name'],
        $_POST['mi'], $_POST['course_year'], $campus,
        $_POST['added_status'], $_POST['cell_leader'], $_POST['consolidation_process']
    ]);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add New Record</title>
<link rel="stylesheet" href="style.css">
<style>body { background: url('<?= htmlspecialchars(getCampusBackground($campus)) ?>') center/cover no-repeat fixed; }</style>
</head>
<body>
<div class="app-shell">
    <div class="page-header">
        <div class="header-brand">
            <div class="header-fish">
                <?php if ($campusLogo): ?>
                <img src="<?= htmlspecialchars($campusLogo) ?>" alt="<?= htmlspecialchars($campus) ?> logo" style="width:40px;height:40px;object-fit:contain;border-radius:50%;background:#fff;padding:2px;" onerror="this.style.display='none'">
                <?php else: ?>
                <?= flameIcon(28) ?>
                <?php endif; ?>
            </div>
            <div>
                <h1>Campus Database Management</h1>
                <p>Record Management &amp; Analytics System</p>
            </div>
        </div>
        <div class="header-right">
            <span class="campus-badge"><?= htmlspecialchars($campus) ?></span>
            <a href="logout.php" class="logout-btn">Sign Out</a>
        </div>
    </div>
    <nav class="nav-bar">
        <a href="index.php" class="nav-link"><span>🏠</span> Dashboard</a>
        <a href="add.php" class="nav-link active nav-link-primary"><span>➕</span> Add Record</a>
        <a href="recycle_bin.php" class="nav-link nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
        <a href="analytics.php" class="nav-link nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="send_notification.php" class="nav-link"><span>📣</span> Notify Admin</a>
    </nav>

    <div class="container">
        <div class="page-wrapper">
            <div class="back-nav"><a href="index.php">← Back to Dashboard</a></div>
            <h1 class="page-title">Add New Record</h1>
            <p style="color:var(--text-mid);margin-bottom:20px;font-size:0.88rem">Campus: <strong><?= htmlspecialchars($campus) ?></strong></p>

            <form method="POST" class="record-form">
                <input type="text" name="week_no" placeholder="Week No" required>
                <input type="text" name="number_no" placeholder="No." required>
                <input type="text" name="week_timeline" placeholder="Week Timeline" required>
                <input type="text" name="invited_during" placeholder="Invited During" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="mi" placeholder="Middle Initial">
                <input type="text" name="course_year" placeholder="Course / Year Level">
                <input type="text" name="added_status" placeholder="Added? (Yes/No)">
                <input type="text" name="cell_leader" placeholder="Cell Leader">
                <input type="text" name="consolidation_process" placeholder="Consolidation Process">
                <button type="submit" name="submit">💾 Save Record</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
