<?php
require 'database.php';
requireLogin();
if (isAdmin()) { header("Location: admin_dashboard.php"); exit; }

$campus  = currentCampus();
$campusLogo = getCampusLogo($campus);
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($message) {
        $stmt = $pdo->prepare("INSERT INTO notifications (sender_campus, sender_role, message, note, target) VALUES (?, 'user', ?, '', 'admin')");
        $stmt->execute([$campus, $message]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notify Admin</title>
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
                <h1>Notify Admin</h1>
                <p><?= htmlspecialchars($campus) ?></p>
            </div>
        </div>
        <div class="header-right">
            <span class="campus-badge"><?= htmlspecialchars($campus) ?></span>
            <a href="logout.php" class="logout-btn">Sign Out</a>
        </div>
    </div>
    <nav class="nav-bar">
        <a href="index.php" class="nav-link"><span>🏠</span> Dashboard</a>
        <a href="add.php" class="nav-link nav-link-primary"><span>➕</span> Add Record</a>
        <a href="recycle_bin.php" class="nav-link nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
        <a href="analytics.php" class="nav-link nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="send_notification.php" class="nav-link active"><span>📣</span> Notify Admin</a>
    </nav>

    <div class="container">
        <div class="page-wrapper">
            <div class="back-nav"><a href="index.php">← Back to Dashboard</a></div>
            <h1 class="page-title">📣 Send Message to Admin</h1>

            <?php if ($success): ?>
            <div style="background:rgba(26,122,74,0.12);border:1.5px solid var(--green);color:var(--green);border-radius:var(--radius-sm);padding:14px 18px;margin-bottom:20px;font-weight:600;">
                ✅ Message sent to Admin successfully!
            </div>
            <?php endif; ?>

            <div class="notif-form-box">
                <h3>Compose Message</h3>
                <form method="POST">
                    <div style="margin-bottom:12px">
                        <label style="display:block;font-weight:700;font-size:0.82rem;color:var(--text-mid);margin-bottom:6px;letter-spacing:1px;text-transform:uppercase">
                            Message *
                        </label>
                        <textarea name="message" placeholder="Type your message or report to the admin here..." required style="width:100%;padding:12px 14px;border:1.5px solid var(--border-bright);border-radius:var(--radius-sm);font-family:'Raleway',sans-serif;font-size:0.88rem;resize:vertical;min-height:120px;outline:none;color:var(--text-dark);"></textarea>
                    </div>
                    <button type="submit" class="button" style="width:100%;justify-content:center;padding:14px;font-size:0.95rem;">
                        📣 Send to Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
