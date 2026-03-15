<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => 'dashboard',
    'pageSubtitle' => 'Your performance summary this week',
]);
$surveillanceUrl = function_exists('route') ? route('surveillance.company') : '#';
?>
<style>
.dashboard-grid{display:grid;gap:18px}.hero{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}.hero h2{margin:0;font-size:2rem}.hero p{margin:6px 0 0;color:#6b7280}.hero-actions{display:flex;gap:10px;flex-wrap:wrap}.hero-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;font-size:.92rem}.hero-btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.stat{border:1px solid #e5e7eb;border-radius:18px;padding:18px;background:#fff}.stat span{display:block;color:#6b7280;font-size:.85rem}.stat strong{display:block;margin-top:8px;font-size:2rem}.main-grid{display:grid;grid-template-columns:1.5fr .9fr;gap:16px}.panel{border:1px solid #e5e7eb;border-radius:20px;padding:18px;background:#fff}.panel h3{margin:0 0 14px;font-size:1.05rem}.chart{height:280px;border-radius:18px;background:linear-gradient(180deg,#fafafa,#f3f4f6);position:relative;overflow:hidden}.chart svg{position:absolute;inset:0;width:100%;height:100%}.list{display:grid;gap:12px}.list-item{display:flex;justify-content:space-between;gap:12px;padding-bottom:12px;border-bottom:1px solid #edf0f2}.list-item:last-child{border-bottom:none;padding-bottom:0}.tag{padding:4px 9px;border-radius:999px;font-size:.75rem;font-weight:600}.ok{background:#dcfce7;color:#166534}.warn{background:#fef3c7;color:#92400e}.table-wrap{border:1px solid #e5e7eb;border-radius:20px;background:#fff;overflow:auto}.table-head{display:flex;justify-content:space-between;align-items:center;padding:18px;border-bottom:1px solid #edf0f2;gap:12px;flex-wrap:wrap}.table-head h3{margin:0}.table-head input{border:1px solid #d1d5db;border-radius:10px;padding:10px 12px;min-width:260px}.table{width:100%;border-collapse:collapse}.table th,.table td{padding:14px 18px;text-align:left;border-bottom:1px solid #edf0f2;font-size:.92rem}.table th{color:#6b7280;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em}.bar{height:8px;border-radius:999px;background:#e5e7eb;overflow:hidden}.bar span{display:block;height:100%;border-radius:999px;background:#389B5B}@media (max-width:1100px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.main-grid{grid-template-columns:1fr}}@media (max-width:700px){.stats{grid-template-columns:1fr}}
</style>
<div class="dashboard-grid">
    <section class="hero">
        <div>
            <h2>Reporting overview</h2>
            <p>Monitor surveillance activity, pending examinations, and clinic workload in one place.</p>
        </div>
        <div class="hero-actions">
            <a class="hero-btn primary" href="<?php echo $esc($surveillanceUrl); ?>">Open Surveillance</a>
            <a class="hero-btn" href="#">Print Report</a>
        </div>
    </section>

    <section class="stats">
        <article class="stat"><span>Total Companies</span><strong>120</strong></article>
        <article class="stat"><span>Total Employees</span><strong>2,430</strong></article>
        <article class="stat"><span>Examinations Today</span><strong>48</strong></article>
        <article class="stat"><span>Pending Review</span><strong>11</strong></article>
    </section>

    <section class="main-grid">
        <article class="panel">
            <h3>Monthly Surveillance Trend</h3>
            <div class="chart">
                <svg viewBox="0 0 800 280" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0 220 C80 170 120 120 180 160 S290 250 360 180 S500 70 590 120 S720 210 800 110" fill="none" stroke="#111827" stroke-width="4" stroke-linecap="round"></path>
                    <path d="M0 228 C80 190 120 150 180 180 S290 235 360 205 S500 120 590 150 S720 195 800 150" fill="none" stroke="#c7cdd4" stroke-width="3" stroke-dasharray="4 6"></path>
                </svg>
            </div>
        </article>
        <article class="panel">
            <h3>Current Tasks</h3>
            <div class="list">
                <div class="list-item"><div><strong>Alpha Engineering</strong><div>Periodic medical surveillance</div></div><span class="tag ok">Ready</span></div>
                <div class="list-item"><div><strong>Gamma Plantations</strong><div>Employee declaration pending</div></div><span class="tag warn">Pending</span></div>
                <div class="list-item"><div><strong>Delta Marine</strong><div>Doctor review required</div></div><span class="tag warn">Review</span></div>
            </div>
        </article>
    </section>

    <section class="table-wrap">
        <div class="table-head">
            <h3>Recently Active Cases</h3>
            <input type="text" placeholder="Search cases">
        </div>
        <table class="table">
            <thead><tr><th>Employee</th><th>Company</th><th>Status</th><th>Progress</th></tr></thead>
            <tbody>
                <tr><td>Nur Aisyah</td><td>Alpha Engineering</td><td><span class="tag ok">Enrolled</span></td><td><div class="bar"><span style="width:70%"></span></div></td></tr>
                <tr><td>Daniel Lim</td><td>Gamma Plantations</td><td><span class="tag ok">Enrolled</span></td><td><div class="bar"><span style="width:60%"></span></div></td></tr>
                <tr><td>Siti Mariam</td><td>Delta Marine</td><td><span class="tag warn">Pending</span></td><td><div class="bar"><span style="width:30%"></span></div></td></tr>
            </tbody>
        </table>
    </section>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>
