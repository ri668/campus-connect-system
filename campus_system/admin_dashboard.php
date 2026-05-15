<?php
require 'database.php';
requireAdmin();

$campusList = array_keys(CAMPUS_USERS);
$filterCampus = $_GET['campus'] ?? 'all';

if ($filterCampus !== 'all') {
    $stmt = $pdo->prepare("SELECT * FROM records WHERE deleted_at IS NULL AND campus = ? ORDER BY id DESC");
    $stmt->execute([$filterCampus]);
} else {
    $stmt = $pdo->query("SELECT * FROM records WHERE deleted_at IS NULL ORDER BY id DESC");
}
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total    = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL")->fetchColumn();
$added    = $pdo->query("SELECT COUNT(*) FROM records WHERE added_status='Yes' AND deleted_at IS NULL")->fetchColumn();
$deleted  = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NOT NULL")->fetchColumn();
$campuses = $pdo->query("SELECT COUNT(DISTINCT campus) FROM records WHERE deleted_at IS NULL")->fetchColumn();

$notifs = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$unreadCount = count(array_filter($notifs, fn($n) => !$n['is_read']));

$campusCount = $pdo->query("SELECT campus, COUNT(*) as cnt FROM records WHERE deleted_at IS NULL GROUP BY campus ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="style.css">
<style>
.admin-badge {
    background: var(--gold);
    color: var(--text-dark);
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
}
.campus-count-table { background: #fff; border-radius: var(--radius); overflow: hidden; border: 1px solid var(--border); }
.campus-count-table table { min-width: unset; }
/* Force stats grid to be 4 columns landscape */
.stats-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 16px !important;
}
@media (max-width: 700px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>
</head>
<body>
<div class="app-shell">
    <div class="page-header">
        <div class="header-brand">
            <div class="header-fish"><?= flameIcon(28) ?></div>
            <div>
                <h1>Admin Dashboard</h1>
                <p>All Campus Management System</p>
            </div>
        </div>
        <div class="header-right">
            <span class="admin-badge">⚙ Administrator</span>
            <div class="notif-bell" onclick="toggleNotif()" title="Notifications">
                🔔
                <?php if ($unreadCount > 0): ?>
                <span class="notif-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </div>
            <a href="logout.php" class="logout-btn">Sign Out</a>
        </div>
    </div>

    <nav class="nav-bar">
        <a href="admin_dashboard.php" class="nav-link active"><span>🏠</span> Overview</a>
        <a href="admin_records.php" class="nav-link"><span>📋</span> All Records</a>
        <a href="admin_analytics.php" class="nav-link nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="admin_notify.php" class="nav-link"><span>📣</span> Send Notification</a>
        <a href="recycle_bin.php" class="nav-link nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
    </nav>

    <div class="container">
        <div class="page-wrapper" style="margin-top:24px">
            <h2 style="margin-bottom:18px">Overview</h2>
            <div class="stats-grid">
                <div class="stat-card"><h3><?= $total ?></h3><p>Total Records</p></div>
                <div class="stat-card"><h3><?= $added ?></h3><p>Added (Yes)</p></div>
                <div class="stat-card alert-card"><h3><?= $deleted ?></h3><p>Deleted Records</p></div>
                <div class="stat-card"><h3><?= $campuses ?></h3><p>Active Campuses</p></div>
            </div>

            <h3 style="font-family:'Cinzel',serif;font-size:1rem;color:var(--teal-dark);margin-bottom:12px;padding-bottom:8px;border-bottom:2px solid var(--teal-pale);margin-top:24px">Members per Campus</h3>
            <div class="campus-count-table" style="margin-bottom:0">
                <table>
                    <thead><tr><th>Campus</th><th>Total Records</th></tr></thead>
                    <tbody>
                        <?php foreach($campusCount as $cc): ?>
                        <tr>
                            <td><?= htmlspecialchars($cc['campus'] ?: 'Unspecified') ?></td>
                            <td><strong><?= $cc['cnt'] ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($campusCount)): ?>
                        <tr><td colspan="2" style="text-align:center;padding:20px;color:var(--text-light)">No data yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="overlay" id="overlay" onclick="toggleNotif()"></div>
<div class="notif-panel" id="notifPanel">
    <div class="notif-panel-header">
        <h3>🔔 All Notifications</h3>
        <div style="display:flex;gap:8px;align-items:center">
            <button onclick="markAllRead()" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;padding:4px 10px;border-radius:6px;font-size:0.72rem;cursor:pointer;">✓ Mark All Read</button>
            <button class="notif-close" onclick="toggleNotif()">✕</button>
        </div>
    </div>
    <div class="notif-list" id="notifList">
        <?php if (empty($notifs)): ?>
            <div class="notif-empty">No notifications yet.</div>
        <?php else: ?>
            <?php foreach ($notifs as $n): ?>
            <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>" id="notif-<?= $n['id'] ?>">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div class="notif-sender">
                        <?= $n['sender_role'] === 'admin' ? '🔑 Admin → All' : '🏫 ' . htmlspecialchars($n['sender_campus']) ?>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center">
                        <label class="notif-read-toggle">
                            <input type="checkbox" <?= $n['is_read'] ? 'checked disabled' : '' ?> onchange="markOneRead(<?= $n['id'] ?>, this)">
                            <span><?= $n['is_read'] ? 'Read' : 'Mark read' ?></span>
                        </label>
                        <button onclick="deleteNotif(<?= $n['id'] ?>)" title="Delete" style="background:none;border:1px solid rgba(255,100,100,0.3);cursor:pointer;font-size:0.72rem;color:rgba(255,100,100,0.8);padding:2px 6px;border-radius:4px;">✕</button>
                    </div>
                </div>
                <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                <?php if ($n['note']): ?>
                <div class="notif-note">📝 <?= htmlspecialchars($n['note']) ?></div>
                <?php endif; ?>
                <div class="notif-time"><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleNotif() {
    document.getElementById('notifPanel').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
    fetch('mark_read.php?campus=admin');
    const badge = document.querySelector('.notif-badge');
    if (badge) badge.style.display = 'none';
    document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('unread'));
}
function markAllRead() {
    fetch('mark_read.php?campus=admin');
    document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('unread'));
    document.querySelectorAll('.notif-read-toggle input').forEach(input => {
        input.checked = true;
        input.disabled = true;
        const label = input.parentElement;
        if (label) label.querySelector('span').textContent = 'Read';
    });
    const badge = document.querySelector('.notif-badge');
    if (badge) badge.style.display = 'none';
}
function markOneRead(id, checkbox = null) {
    fetch('mark_read.php?id=' + id + '&campus=admin');
    const item = document.getElementById('notif-' + id);
    if (item) item.classList.remove('unread');
    if (checkbox) {
        checkbox.checked = true;
        checkbox.disabled = true;
        checkbox.nextElementSibling.textContent = 'Read';
    } else {
        const input = item ? item.querySelector('.notif-read-toggle input') : null;
        if (input) {
            input.checked = true;
            input.disabled = true;
            const label = input.parentElement;
            if (label) label.querySelector('span').textContent = 'Read';
        }
    }
}
function deleteNotif(id) {
    if (!confirm('Delete this notification?')) return;
    fetch('delete_notification.php?id=' + id).then(() => {
        const el = document.getElementById('notif-' + id);
        if (el) el.remove();
    });
}
</script>
</body>
</html>
