<?php
require 'database.php';
requireLogin();

$campus = currentCampus();
$campusLogo = getCampusLogo($campus);

if (isAdmin()) {
    $stmt = $pdo->query("SELECT * FROM records WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM records WHERE deleted_at IS NOT NULL AND campus = ? ORDER BY deleted_at DESC");
    $stmt->execute([$campus]);
}
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
$backUrl = isAdmin() ? 'admin_dashboard.php' : 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recycle Bin</title>
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
                <h1>Recycle Bin</h1>
                <p>Soft-deleted Records</p>
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

    <?php if (isAdmin()): ?>
    <nav class="nav-bar">
        <a href="admin_dashboard.php" class="nav-link"><span>🏠</span> Overview</a>
        <a href="admin_records.php" class="nav-link"><span>📋</span> All Records</a>
        <a href="admin_analytics.php" class="nav-link nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="admin_notify.php" class="nav-link"><span>📣</span> Send Notification</a>
        <a href="recycle_bin.php" class="nav-link active nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
    </nav>
    <?php else: ?>
    <nav class="nav-bar">
        <a href="index.php" class="nav-link"><span>🏠</span> Dashboard</a>
        <a href="add.php" class="nav-link nav-link-primary"><span>➕</span> Add Record</a>
        <a href="recycle_bin.php" class="nav-link active nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
        <a href="analytics.php" class="nav-link nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="send_notification.php" class="nav-link"><span>📣</span> Notify Admin</a>
    </nav>
    <?php endif; ?>

    <div class="container">
        <div class="page-wrapper">
            <div class="back-nav"><a href="<?= $backUrl ?>">← Back to Dashboard</a></div>
            <h1 class="page-title">🗑️ Recycle Bin</h1>
            <p style="color:var(--text-mid);margin-bottom:20px;font-size:0.88rem">Records listed here have been soft-deleted. You can restore them at any time.</p>

            <?php if (empty($records)): ?>
            <div style="text-align:center;padding:60px 24px;background:rgba(255,255,255,0.5);border-radius:var(--radius);border:1.5px dashed var(--border-bright);color:var(--text-mid)">
                <div style="font-size:3rem;margin-bottom:12px">🗑️</div>
                <p style="font-size:1.05rem;font-weight:600">The recycle bin is empty.</p>
                <p style="font-size:0.9rem;margin-top:6px">Deleted records will appear here.</p>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Campus</th>
                            <th>Cell Leader</th>
                            <th>Deleted At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($records as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['campus']) ?></td>
                            <td><?= htmlspecialchars($row['cell_leader']) ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($row['deleted_at'])) ?></td>
                            <td>
                                <a class="restore-btn" href="restore.php?id=<?= $row['id'] ?>">↩ Restore</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
