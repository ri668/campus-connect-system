<?php
require 'database.php';
requireLogin();
if (isAdmin()) { header("Location: admin_analytics.php"); exit; }

$campus = currentCampus();
$campusLogo = getCampusLogo($campus);

if (isset($_GET['export']) && $_GET['export'] === '1') {
    $stmt = $pdo->prepare("SELECT * FROM records WHERE deleted_at IS NULL AND campus = ? ORDER BY id");
    $stmt->execute([$campus]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . preg_replace('/\s+/', '_', $campus) . '_export_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    if (!empty($records)) {
        fputcsv($out, array_keys($records[0]));
        foreach ($records as $r) fputcsv($out, $r);
    }
    fclose($out);
    exit;
}

$total = $pdo->prepare("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL AND campus=?");
$total->execute([$campus]); $total = $total->fetchColumn();

$added = $pdo->prepare("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL AND campus=? AND added_status='Yes'");
$added->execute([$campus]); $added = $added->fetchColumn();

$byStatus = $pdo->prepare("SELECT added_status AS status, COUNT(*) AS total FROM records WHERE deleted_at IS NULL AND campus=? GROUP BY added_status ORDER BY total DESC");
$byStatus->execute([$campus]); $byStatus = $byStatus->fetchAll(PDO::FETCH_ASSOC);

$byLeader = $pdo->prepare("SELECT cell_leader, COUNT(*) AS total FROM records WHERE deleted_at IS NULL AND campus=? GROUP BY cell_leader ORDER BY total DESC");
$byLeader->execute([$campus]); $byLeader = $byLeader->fetchAll(PDO::FETCH_ASSOC);

$byConsolidation = $pdo->prepare("SELECT consolidation_process AS consolidation, COUNT(*) AS total FROM records WHERE deleted_at IS NULL AND campus=? GROUP BY consolidation_process ORDER BY total DESC");
$byConsolidation->execute([$campus]); $byConsolidation = $byConsolidation->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = json_encode(array_column($byStatus, 'status'));
$statusValues = json_encode(array_column($byStatus, 'total'));
$leaderLabels = json_encode(array_column($byLeader, 'cell_leader'));
$leaderValues = json_encode(array_column($byLeader, 'total'));
$consolidationLabels = json_encode(array_column($byConsolidation, 'consolidation'));
$consolidationValues = json_encode(array_column($byConsolidation, 'total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics — <?= htmlspecialchars($campus) ?></title>
<link rel="stylesheet" href="style.css">
<style>body { background: url('<?= htmlspecialchars(getCampusBackground($campus)) ?>') center/cover no-repeat fixed; }</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
                <h1>Analytics</h1>
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
        <a href="analytics.php" class="nav-link active nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="send_notification.php" class="nav-link"><span>📣</span> Notify Admin</a>
    </nav>

    <div class="container">
        <div class="page-wrapper">
            <div class="back-nav"><a href="index.php">← Back to Dashboard</a></div>
            <div class="analytics-container">
                <h1>Analytics — <?= htmlspecialchars($campus) ?></h1>

                <div class="stats-grid">
                    <div class="stat-card"><h3><?= $total ?></h3><p>Total Records</p></div>
                    <div class="stat-card"><h3><?= $added ?></h3><p>Added (Yes)</p></div>
                </div>

                <div class="analytics-section">
                    <h3>Export Data</h3>
                    <a class="button" href="analytics.php?export=1">⬇ Export to CSV</a>
                </div>

                <div class="analytics-section">
                    <h3>Records by Added Status</h3>
                    <div class="chart-card"><div class="chart-wrap"><canvas id="statusChart"></canvas></div></div>
                    <div id="statusStats" class="stats-table"></div>
                </div>

                <div class="analytics-section">
                    <h3>Records by Cell Leader</h3>
                    <div class="chart-card"><div class="chart-wrap"><canvas id="leaderChart"></canvas></div></div>
                    <div id="leaderStats" class="stats-table"></div>
                </div>

                <div class="analytics-section">
                    <h3>Records by Consolidation</h3>
                    <div class="chart-card"><div class="chart-wrap"><canvas id="consolidationChart"></canvas></div></div>
                    <div id="consolidationStats" class="stats-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const colors = ['#1e73be','#d4650a','#c0392b','#1a7a4a','#243460','#c2185b','#6b7280','#2e7d32'];
function renderPieChart(id, labels, data) {
    const c = document.getElementById(id); if (!c) return;
    new Chart(c, { type:'pie', data:{ labels, datasets:[{ data, backgroundColor: labels.map((_,i)=>colors[i%colors.length]), borderColor:'#fff', borderWidth:2 }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{position:'bottom'}, tooltip:{callbacks:{label:ctx=>{ const t=ctx.dataset.data.reduce((a,b)=>a+b,0)||1; return `${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw/t*100)}%)`; }}} } } });
}
function renderTable(id, headers, rows) {
    const el = document.getElementById(id); if (!el) return;
    let html = '<table><thead><tr>' + headers.map(h=>`<th>${h}</th>`).join('') + '</tr></thead><tbody>';
    rows.forEach(r => { html += '<tr>' + r.map(c=>`<td>${c}</td>`).join('') + '</tr>'; });
    el.innerHTML = html + '</tbody></table>';
}
const sL=<?= $statusLabels ?>, sV=<?= $statusValues ?>;
const lL=<?= $leaderLabels ?>, lV=<?= $leaderValues ?>;
const cL=<?= $consolidationLabels ?>, cV=<?= $consolidationValues ?>;

renderPieChart('statusChart', sL, sV);
renderTable('statusStats', ['Status','Count'], sL.map((l,i)=>[l||'Unspecified', sV[i]]));
renderPieChart('leaderChart', lL, lV);
renderTable('leaderStats', ['Cell Leader','Count'], lL.map((l,i)=>[l||'Unspecified', lV[i]]));
renderPieChart('consolidationChart', cL, cV);
renderTable('consolidationStats', ['Consolidation','Count'], cL.map((l,i)=>[l||'Unspecified', cV[i]]));
</script>
</body>
</html>
