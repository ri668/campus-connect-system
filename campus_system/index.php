<?php
require 'database.php';
requireLogin();
if (isAdmin()) { header("Location: admin_dashboard.php"); exit; }

$campus = currentCampus();
$campusLogo = getCampusLogo($campus);

$stmt = $pdo->prepare("SELECT * FROM records WHERE deleted_at IS NULL AND campus = ? ORDER BY id DESC");
$stmt->execute([$campus]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nStmt = $pdo->prepare("
    SELECT * FROM notifications
    WHERE (target = 'all' OR target LIKE ?)
    ORDER BY created_at DESC LIMIT 30
");
$nStmt->execute(["%$campus%"]);
$notifications = $nStmt->fetchAll(PDO::FETCH_ASSOC);

$unread = array_filter($notifications, fn($n) => !$n['is_read']);
$unreadCount = count($unread);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Campus Dashboard — <?= htmlspecialchars($campus) ?></title>
<link rel="stylesheet" href="style.css">
<style>body { background: url('<?= htmlspecialchars(getCampusBackground($campus)) ?>') center/cover no-repeat fixed; }</style>
</head>
<body>
<div class="app-shell">
    <div class="page-header">
        <div class="header-brand">
            <div class="header-fish">
                <?php if ($campusLogo): ?>
                <img src="<?= htmlspecialchars($campusLogo) ?>" alt="<?= htmlspecialchars($campus) ?> logo" style="width:40px;height:40px;object-fit:contain;border-radius:50%;background:#fff;padding:2px;" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <span style="display:none"><?= flameIcon(28) ?></span>
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
        <a href="index.php" class="nav-link nav-link-primary active"><span>🏠</span> Dashboard</a>
        <a href="add.php" class="nav-link nav-link-primary"><span>➕</span> Add Record</a>
        <a href="recycle_bin.php" class="nav-link nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
        <a href="analytics.php" class="nav-link nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="send_notification.php" class="nav-link"><span>📣</span> Notify Admin</a>
    </nav>

    <div class="container">
        <div class="page-wrapper">
            <div class="table-header">
                <div class="header-with-search">
                    <div>
                        <h2>My Records</h2>
                        <p class="table-subtitle">Active records for <?= htmlspecialchars($campus) ?></p>
                    </div>
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-bar" placeholder="🔍 Search by any field..." autocomplete="off">
                        <span id="searchCount" class="search-count"></span>
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Week No</th>
                            <th>No.</th>
                            <th>Week Timeline</th>
                            <th>Invited During</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>MI</th>
                            <th>Course / Year Level</th>
                            <th>Added?</th>
                            <th>Cell Leader</th>
                            <th>Consolidation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="recordsBody">
                        <?php foreach($records as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['week_no']) ?></td>
                            <td><?= htmlspecialchars($row['number_no']) ?></td>
                            <td><?= htmlspecialchars($row['week_timeline']) ?></td>
                            <td><?= htmlspecialchars($row['invited_during']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['mi']) ?></td>
                            <td><?= htmlspecialchars($row['course_year'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['added_status']) ?></td>
                            <td><?= htmlspecialchars($row['cell_leader']) ?></td>
                            <td><?= htmlspecialchars($row['consolidation_process']) ?></td>
                            <td class="actions-cell">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="action-link edit-link">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="action-link delete-link" onclick="return confirm('Move to recycle bin?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="overlay" id="overlay" onclick="toggleNotif()"></div>

<div class="notif-panel" id="notifPanel">
    <div class="notif-panel-header">
        <h3>🔔 Notifications</h3>
        <div style="display:flex;gap:8px;align-items:center">
            <button onclick="markAllRead()" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;padding:4px 10px;border-radius:6px;font-size:0.72rem;cursor:pointer;">✓ Mark All Read</button>
            <button class="notif-close" onclick="toggleNotif()">✕</button>
        </div>
    </div>
    <div class="notif-list" id="notifList">
        <?php if (empty($notifications)): ?>
            <div class="notif-empty">No notifications yet.</div>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
            <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>" id="notif-<?= $n['id'] ?>">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div class="notif-sender">
                        <?= $n['sender_role'] === 'admin' ? '🔑 Admin' : '🏫 ' . htmlspecialchars($n['sender_campus']) ?>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center">
                        <label class="notif-read-toggle">
                            <input type="checkbox" <?= $n['is_read'] ? 'checked disabled' : '' ?> onchange="markOneRead(<?= $n['id'] ?>, this)">
                            <span><?= $n['is_read'] ? 'Read' : 'Mark read' ?></span>
                        </label>
                        <button onclick="deleteNotif(<?= $n['id'] ?>)" title="Delete" style="background:none;border:none;cursor:pointer;font-size:0.75rem;color:rgba(255,100,100,0.8);padding:2px 6px;border-radius:4px;border:1px solid rgba(255,100,100,0.3)">✕</button>
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
    fetch('mark_read.php?campus=<?= urlencode($campus) ?>');
    const badge = document.querySelector('.notif-badge');
    if (badge) badge.style.display = 'none';
    document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('unread'));
}
function markAllRead() {
    fetch('mark_read.php?campus=<?= urlencode($campus) ?>');
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
    fetch('mark_read.php?id=' + id + '&campus=<?= urlencode($campus) ?>');
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

const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', function() {
    const q = this.value.trim();
    fetch('search.php?q=' + encodeURIComponent(q) + '&campus=<?= urlencode($campus) ?>')
        .then(r => r.json())
        .then(data => {
            const body = document.getElementById('recordsBody');
            body.innerHTML = '';
            if (!data.length) {
                body.innerHTML = '<tr><td colspan="13" style="text-align:center;padding:20px;color:#7fa8aa">No records found</td></tr>';
                return;
            }
            data.forEach(row => {
                body.innerHTML += `<tr>
                    <td>${row.id}</td><td>${row.week_no}</td><td>${row.number_no}</td>
                    <td>${row.week_timeline}</td><td>${row.invited_during}</td>
                    <td>${row.last_name}</td><td>${row.first_name}</td><td>${row.mi}</td>
                    <td>${row.course_year||''}</td><td>${row.added_status}</td>
                    <td>${row.cell_leader}</td><td>${row.consolidation_process}</td>
                    <td class="actions-cell">
                        <a href="edit.php?id=${row.id}" class="action-link edit-link">Edit</a>
                        <a href="delete.php?id=${row.id}" class="action-link delete-link" onclick="return confirm('Move to recycle bin?')">Delete</a>
                    </td></tr>`;
            });
        });
});
</script>
</body>
</html>
