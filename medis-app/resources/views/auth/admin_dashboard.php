<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'Admin',
    'active' => 'dashboard',
]);
$companyTotal = (int) ($companyTotal ?? 0);
$employeeTotal = (int) ($employeeTotal ?? 0);
$clinicTotal = (int) ($clinicTotal ?? 0);
$doctorTotal = (int) ($doctorTotal ?? 0);
$recentCompanies = $recentCompanies ?? collect();
?>
<style>
.admin-page{display:grid;gap:18px}.hero{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}.hero h1{margin:0;font-size:2rem}.hero p{margin:6px 0 0;color:#6b7280;max-width:760px}.hero-actions{display:flex;gap:10px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;font-size:.92rem}.btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.stat{border:1px solid #e5e7eb;border-radius:18px;padding:18px;background:#fff}.stat span{display:block;color:#6b7280;font-size:.85rem}.stat strong{display:block;margin-top:10px;font-size:2rem;color:#111827}.main-grid{display:grid;grid-template-columns:1.3fr .9fr;gap:16px}.panel{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:18px}.panel h2{margin:0 0 14px;font-size:1.1rem}.quick-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.quick-card{border:1px solid #e5e7eb;border-radius:18px;padding:16px;text-decoration:none;color:#111827;background:#fafafa}.quick-card strong{display:block;font-size:1rem}.quick-card span{display:block;margin-top:6px;color:#6b7280;font-size:.9rem}.table-wrap{border:1px solid #e5e7eb;border-radius:22px;background:#fff;overflow:hidden}.table-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:18px;border-bottom:1px solid #edf0f2}.table-head h2{margin:0;font-size:1.1rem}.table{width:100%;border-collapse:collapse}.table th,.table td{padding:14px 18px;text-align:left;border-bottom:1px solid #edf0f2}.table th{font-size:.8rem;color:#6b7280;text-transform:uppercase;letter-spacing:.04em}.badge{display:inline-flex;padding:5px 10px;border-radius:999px;background:#e8f5ec;color:#166534;font-size:.75rem;font-weight:700}.empty{padding:18px;color:#6b7280}.mini-list{display:grid;gap:12px}.mini-item{display:flex;justify-content:space-between;gap:10px;padding-bottom:12px;border-bottom:1px solid #edf0f2}.mini-item:last-child{padding-bottom:0;border-bottom:none}.mini-item span{display:block;color:#6b7280;font-size:.88rem}@media (max-width:1100px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.main-grid{grid-template-columns:1fr}}@media (max-width:720px){.stats{grid-template-columns:1fr}.quick-grid{grid-template-columns:1fr}}
</style>
<div class="admin-page">
    <section class="hero">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Review the core master data for clinic, company, employee, and doctor records from one place.</p>
        </div>
        <div class="hero-actions">
            <a class="btn primary" href="<?php echo $esc(route('admin.company')); ?>">Manage Companies</a>
            <a class="btn" href="<?php echo $esc(route('admin.clinic')); ?>">Clinic Setup</a>
        </div>
    </section>

    <section class="stats">
        <article class="stat"><span>Total Companies</span><strong><?php echo $esc(number_format($companyTotal)); ?></strong></article>
        <article class="stat"><span>Total Employees</span><strong><?php echo $esc(number_format($employeeTotal)); ?></strong></article>
        <article class="stat"><span>Total Clinics</span><strong><?php echo $esc(number_format($clinicTotal)); ?></strong></article>
        <article class="stat"><span>Total Doctors</span><strong><?php echo $esc(number_format($doctorTotal)); ?></strong></article>
    </section>

    <section class="main-grid">
        <article class="panel">
            <h2>Quick Access</h2>
            <div class="quick-grid">
                <a class="quick-card" href="<?php echo $esc(route('admin.company')); ?>"><strong>Company Master</strong><span>Review and manage registered company records.</span></a>
                <a class="quick-card" href="<?php echo $esc(route('admin.employee')); ?>"><strong>Employee Master</strong><span>Check employee records synced for surveillance and audiometry.</span></a>
                <a class="quick-card" href="<?php echo $esc(route('admin.clinic')); ?>"><strong>Clinic Master</strong><span>View clinic name, contact details, and login information.</span></a>
                <a class="quick-card" href="<?php echo $esc(route('admin.settings')); ?>"><strong>Admin Settings</strong><span>Open common account, document, and system shortcuts.</span></a>
            </div>
        </article>

        <article class="panel">
            <h2>Admin Notes</h2>
            <div class="mini-list">
                <div class="mini-item"><div><strong>Company records</strong><span>Use this area to review employer contact details and MYKPP registration.</span></div><span class="badge">Ready</span></div>
                <div class="mini-item"><div><strong>Employee records</strong><span>Validate NRIC, passport, and communication fields before medical flows.</span></div><span class="badge">Ready</span></div>
                <div class="mini-item"><div><strong>Clinic configuration</strong><span>Keep email, header image, signature, and logo information up to date.</span></div><span class="badge">Active</span></div>
            </div>
        </article>
    </section>

    <section class="table-wrap">
        <div class="table-head">
            <h2>Recent Company Records</h2>
            <a class="btn" href="<?php echo $esc(route('admin.company')); ?>">Open Full List</a>
        </div>
        <?php if (count($recentCompanies) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>Registration</th>
                        <th>Telephone</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentCompanies as $company): ?>
                        <tr>
                            <td><?php echo $esc($company->company_id ?? '-'); ?></td>
                            <td><?php echo $esc($company->company_name ?? '-'); ?></td>
                            <td><?php echo $esc($company->mykpp_registration_no ?? '-'); ?></td>
                            <td><?php echo $esc($company->company_telephone ?? '-'); ?></td>
                            <td><?php echo $esc($company->company_email ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty">No company data found yet.</div>
        <?php endif; ?>
    </section>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>