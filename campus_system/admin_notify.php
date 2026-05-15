<?php
require 'database.php';
requireAdmin();

$success = false;
$campusList = array_keys(CAMPUS_USERS);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $note    = trim($_POST['note'] ?? '');
    $targets = $_POST['targets'] ?? [];

    if ($message && !empty($targets)) {
        if (in_array('all', $targets)) {
            $target = 'all';
        } else {
            $target = implode('|', $targets);
        }

        $stmt = $pdo->prepare("INSERT INTO notifications (sender_campus, sender_role, message, note, target) VALUES (?, 'admin', ?, ?, ?)");
        $stmt->execute(['ADMIN', $message, $note, $target]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Send Notification</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">
    <div class="page-header">
        <div class="header-brand">
            <div class="header-fish"><?= flameIcon(28) ?></div>
            <div>
                <h1>Send Notification</h1>
                <p>Admin — Broadcast to Campuses</p>
            </div>
        </div>
        <div class="header-right">
            <span style="background:var(--gold);color:var(--text-dark);padding:4px 12px;border-radius:50px;font-size:0.72rem;font-weight:700;">⚙ ADMINISTRATOR</span>
            <a href="logout.php" class="logout-btn">Sign Out</a>
        </div>
    </div>

    <nav class="nav-bar">
        <a href="admin_dashboard.php" class="nav-link"><span>🏠</span> Overview</a>
        <a href="admin_records.php" class="nav-link"><span>📋</span> All Records</a>
        <a href="admin_analytics.php" class="nav-link nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="admin_notify.php" class="nav-link active"><span>📣</span> Send Notification</a>
        <a href="recycle_bin.php" class="nav-link nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
    </nav>

    <div class="container">
        <div class="page-wrapper">
            <div class="back-nav"><a href="admin_dashboard.php">← Back to Dashboard</a></div>
            <h1 class="page-title">📣 Send Notification to Campuses</h1>

            <?php if ($success): ?>
            <div style="background:rgba(26,122,74,0.12);border:1.5px solid var(--green);color:var(--green);border-radius:var(--radius-sm);padding:14px 18px;margin-bottom:20px;font-weight:600;">
                ✅ Notification sent successfully!
            </div>
            <?php endif; ?>

            <div class="notif-form-box">
                <h3>Compose Notification</h3>
                <form method="POST">
                    <div style="margin-bottom:12px">
                        <label style="display:block;font-weight:700;font-size:0.82rem;color:var(--text-mid);margin-bottom:6px;letter-spacing:1px;text-transform:uppercase">Message *</label>
                        <textarea name="message" placeholder="Type your notification message here..." required></textarea>
                    </div>

                    <div style="margin-bottom:16px">
                        <label style="display:block;font-weight:700;font-size:0.82rem;color:var(--text-mid);margin-bottom:6px;letter-spacing:1px;text-transform:uppercase">Admin Note (optional)</label>
                        <textarea name="note" placeholder="Additional note or instructions..."></textarea>
                    </div>

                    <div style="margin-bottom:16px">
                        <label style="display:block;font-weight:700;font-size:0.82rem;color:var(--text-mid);margin-bottom:10px;letter-spacing:1px;text-transform:uppercase">Send To:</label>
                        <div style="margin-bottom:10px">
                            <label style="display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:0.85rem;cursor:pointer;color:var(--teal-dark)">
                                <input type="checkbox" name="targets[]" value="all" id="checkAll" onchange="toggleAll(this)" style="accent-color:var(--teal);width:16px;height:16px">
                                ✅ Select All Campuses
                            </label>
                        </div>
                        <div class="campus-checkboxes" id="campusCheckboxes">
                            <?php foreach ($campusList as $c): ?>
                            <label>
                                <input type="checkbox" name="targets[]" value="<?= htmlspecialchars($c) ?>" class="campus-cb">
                                <?= htmlspecialchars($c) ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="button" style="width:100%;justify-content:center;padding:14px;font-size:0.95rem;">
                        📣 Send Notification
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAll(cb) {
    document.querySelectorAll('.campus-cb').forEach(c => c.checked = cb.checked);
}
document.querySelectorAll('.campus-cb').forEach(cb => {
    cb.addEventListener('change', () => {
        const all = document.querySelectorAll('.campus-cb');
        const checked = document.querySelectorAll('.campus-cb:checked');
        document.getElementById('checkAll').checked = all.length === checked.length;
    });
});
</script>
</body>
</html>
