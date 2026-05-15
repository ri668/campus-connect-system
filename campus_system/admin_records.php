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

$notifs = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$unreadCount = count(array_filter($notifs, fn($n) => !$n['is_read']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — All Records</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">
    <div class="page-header">
        <div class="header-brand">
            <div class="header-fish"><?= flameIcon(28) ?></div>
            <div>
                <h1>Admin — All Records</h1>
                <p>All Campus Management System</p>
            </div>
        </div>
        <div class="header-right">
            <span style="background:var(--gold);color:var(--text-dark);padding:4px 12px;border-radius:50px;font-size:0.72rem;font-weight:700;letter-spacing:1px">⚙ ADMINISTRATOR</span>
            <div class="notif-bell" onclick="toggleNotif()">🔔
                <?php if ($unreadCount > 0): ?>
                <span class="notif-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </div>
            <a href="logout.php" class="logout-btn">Sign Out</a>
        </div>
    </div>

    <nav class="nav-bar">
        <a href="admin_dashboard.php" class="nav-link"><span>🏠</span> Overview</a>
        <a href="admin_records.php" class="nav-link active"><span>📋</span> All Records</a>
        <a href="admin_analytics.php" class="nav-link nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="admin_notify.php" class="nav-link"><span>📣</span> Send Notification</a>
        <a href="recycle_bin.php" class="nav-link nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
    </nav>

    <div class="container">
        <div class="page-wrapper">
            <div class="table-header">
                <div class="header-with-search">
                    <div>
                        <h2>All Campus Records</h2>
                        <p class="table-subtitle">
                            <?= $filterCampus === 'all' ? 'Showing all campuses' : 'Filtered: ' . htmlspecialchars($filterCampus) ?>
                        </p>
                    </div>
                    <div class="search-container">
                        <form method="GET" style="display:flex;align-items:center;gap:8px">
                            <label for="campus" style="font-size:0.82rem;font-weight:700;color:var(--text-mid)">Campus:</label>
                            <select name="campus" id="campus" class="filter-select" onchange="this.form.submit()">
                                <option value="all" <?= $filterCampus === 'all' ? 'selected' : '' ?>>All Campuses</option>
                                <?php foreach ($campusList as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= $filterCampus === $c ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <input type="text" id="searchInput" class="search-bar" placeholder="🔍 Search..." autocomplete="off">
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
                            <th>Course/Year</th>
                            <th>Added?</th>
                            <th>Cell Leader</th>
                            <th>Consolidation</th>
                            <th>Campus</th>
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
                            <td><strong><?= htmlspecialchars($row['campus']) ?></strong></td>
                            <td class="actions-cell">
                                <a href="edit.php?id=<?= $row['id'] ?>&from=admin" class="action-link edit-link">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>&from=admin" class="action-link delete-link" onclick="return confirm('Move to recycle bin?')">Delete</a>
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

const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const rows = document.querySelectorAll('#recordsBody tr');
    let count = 0;
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const show = !q || text.includes(q);
        row.style.display = show ? '' : 'none';
        if (show) count++;
    });
    const countEl = document.getElementById('searchCount');
    countEl.textContent = q ? `${count} result${count !== 1 ? 's' : ''}` : '';
});
</script>
</body>
</html>
