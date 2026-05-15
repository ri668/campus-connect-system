<?php
require 'database.php';
requireAdmin();

if (isset($_GET['export']) && $_GET['export'] === '1') {
    $stmt = $pdo->query("SELECT * FROM records WHERE deleted_at IS NULL ORDER BY campus, id");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="all_campus_export_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    if (!empty($records)) {
        fputcsv($out, array_keys($records[0]));
        foreach ($records as $r) fputcsv($out, $r);
    }
    fclose($out);
    exit;
}

$total   = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL")->fetchColumn();
$added   = $pdo->query("SELECT COUNT(*) FROM records WHERE added_status='Yes' AND deleted_at IS NULL")->fetchColumn();
$deleted = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NOT NULL")->fetchColumn();

// Calculate improvement metrics
$currentWeek = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$lastWeek = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$weeklyImprovement = $lastWeek > 0 ? round((($currentWeek - $lastWeek) / $lastWeek) * 100, 2) : 0;

$currentMonth = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();
$lastMonth = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL AND MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))")->fetchColumn();
$monthlyImprovement = $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2) : 0;

$currentYear = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();
$lastYear = $pdo->query("SELECT COUNT(*) FROM records WHERE deleted_at IS NULL AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 YEAR))")->fetchColumn();
$yearlyImprovement = $lastYear > 0 ? round((($currentYear - $lastYear) / $lastYear) * 100, 2) : 0;

$byCampus = $pdo->query("SELECT campus, COUNT(*) AS total FROM records WHERE deleted_at IS NULL GROUP BY campus ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
$byStatus = $pdo->query("SELECT added_status AS status, COUNT(*) AS total FROM records WHERE deleted_at IS NULL GROUP BY added_status ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
$byLeader = $pdo->query("SELECT cell_leader, COUNT(*) AS total FROM records WHERE deleted_at IS NULL GROUP BY cell_leader ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
$byConsolidation = $pdo->query("SELECT consolidation_process AS consolidation, COUNT(*) AS total FROM records WHERE deleted_at IS NULL GROUP BY consolidation_process ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);

$campusLabels = json_encode(array_column($byCampus, 'campus'));
$campusValues = json_encode(array_column($byCampus, 'total'));
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
<title>Admin Analytics</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-shell">
    <div class="page-header">
        <div class="header-brand">
            <div class="header-fish"><?= flameIcon(28) ?></div>
            <div>
                <h1>Analytics Report</h1>
                <p>All Campus Overview</p>
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
        <a href="admin_analytics.php" class="nav-link active nav-link-tertiary"><span>📈</span> Analytics</a>
        <a href="admin_notify.php" class="nav-link"><span>📣</span> Send Notification</a>
        <a href="recycle_bin.php" class="nav-link nav-link-secondary"><span>🗑️</span> Recycle Bin</a>
    </nav>

    <div class="container">
        <div class="page-wrapper">
            <div class="analytics-container">
                <h1>Analytics Overview — All Campuses</h1>

                <div class="stats-grid">
                    <div class="stat-card"><h3><?= $total ?></h3><p>Total Records</p></div>
                    <div class="stat-card"><h3><?= $added ?></h3><p>Added (Yes)</p></div>
                    <div class="stat-card alert-card"><h3><?= $deleted ?></h3><p>Deleted</p></div>
                </div>

                <div class="stats-grid" style="margin-top:16px;border-top:2px solid var(--border);padding-top:16px;">
                    <div class="stat-card" style="border-left:4px solid var(--teal-light)"><h3><?= $weeklyImprovement ?>%</h3><p>Weekly Improvement</p></div>
                    <div class="stat-card" style="border-left:4px solid var(--teal)"><h3><?= $monthlyImprovement ?>%</h3><p>Monthly Improvement</p></div>
                    <div class="stat-card" style="border-left:4px solid var(--teal-dark)"><h3><?= $yearlyImprovement ?>%</h3><p>Yearly Improvement</p></div>
                </div>

                <div class="analytics-section">
                    <h3>Export Data</h3>
                    <a class="button" href="admin_analytics.php?export=1">⬇ Export All to CSV</a>
                </div>

                <div class="analytics-section">
                    <h3>Records by Campus</h3>
                    <div class="chart-card"><div class="chart-wrap"><canvas id="campusChart"></canvas></div></div>
                    <div id="campusStats" class="stats-table"></div>
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
const colors = ['#1e73be','#d4650a','#c0392b','#1a7a4a','#243460','#c2185b','#6b7280','#2e7d32','#8e44ad','#e67e22','#16a085','#8e44ad'];

function renderPieChart(id, labels, data) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    new Chart(canvas, {
        type: 'pie',
        data: {
            labels,
            datasets: [{ data, backgroundColor: labels.map((_, i) => colors[i % colors.length]), borderColor: '#fff', borderWidth: 2 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 14 } },
                tooltip: { callbacks: { label: ctx => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                    return `${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw/total*100)}%)`;
                }}}
            }
        }
    });
}

function renderTable(id, headers, rows) {
    const el = document.getElementById(id);
    if (!el) return;
    let html = '<table><thead><tr>' + headers.map(h => `<th>${h}</th>`).join('') + '</tr></thead><tbody>';
    rows.forEach(r => { html += '<tr>' + r.map(c => `<td>${c}</td>`).join('') + '</tr>'; });
    el.innerHTML = html + '</tbody></table>';
}

const cL = <?= $campusLabels ?>, cV = <?= $campusValues ?>;
const sL = <?= $statusLabels ?>, sV = <?= $statusValues ?>;
const lL = <?= $leaderLabels ?>, lV = <?= $leaderValues ?>;
const conL = <?= $consolidationLabels ?>, conV = <?= $consolidationValues ?>;

renderPieChart('campusChart', cL, cV);
renderTable('campusStats', ['Campus','Count'], cL.map((l,i) => [l||'Unspecified', cV[i]]));

renderPieChart('statusChart', sL, sV);
renderTable('statusStats', ['Status','Count'], sL.map((l,i) => [l||'Unspecified', sV[i]]));

renderPieChart('leaderChart', lL, lV);
renderTable('leaderStats', ['Cell Leader','Count'], lL.map((l,i) => [l||'Unspecified', lV[i]]));

renderPieChart('consolidationChart', conL, conV);
renderTable('consolidationStats', ['Consolidation','Count'], conL.map((l,i) => [l||'Unspecified', conV[i]]));
</script>
</body>
</html>
