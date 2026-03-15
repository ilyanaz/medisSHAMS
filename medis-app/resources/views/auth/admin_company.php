<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Company</title>
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
$companies = $companies ?? collect();
$companyTotal = (int) ($companyTotal ?? count($companies));
?>
<style>
.page{display:grid;gap:18px}.page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}.page-head h1{margin:0;font-size:1.9rem}.page-head p{margin:6px 0 0;color:#6b7280}.actions{display:flex;gap:10px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;font-size:.92rem}.btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}.summary{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px}.summary strong{font-size:2rem;color:#111827}.summary span{display:block;color:#6b7280}.table-wrap{border:1px solid #e5e7eb;border-radius:22px;background:#fff;overflow:hidden}.table-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:18px;border-bottom:1px solid #edf0f2}.table-head input{border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;min-width:280px}.table{width:100%;border-collapse:collapse}.table th,.table td{padding:14px 18px;text-align:left;border-bottom:1px solid #edf0f2;vertical-align:top}.table th{font-size:.8rem;color:#6b7280;text-transform:uppercase;letter-spacing:.04em}.muted{color:#6b7280;font-size:.9rem}.empty{padding:18px;color:#6b7280}.two-line strong{display:block}.two-line span{display:block;color:#6b7280;font-size:.9rem}.table-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:16px 18px;color:#6b7280;font-size:.84rem}
</style>
<div class="page">
    <section class="page-head">
        <div>
            <h1>Admin Company</h1>
            <p>Master list of companies based on the <code>company</code> table.</p>
        </div>
        <div class="actions">
            <a class="btn primary" href="<?php echo $esc(route('surveillance.company.new')); ?>">+ Add Company</a>
            <a class="btn" href="<?php echo $esc(route('admin.dashboard')); ?>">Back to Dashboard</a>
        </div>
    </section>

    <section class="summary">
        <span>Total Company Records</span>
        <strong><?php echo $esc(number_format($companyTotal)); ?></strong>
    </section>

    <section class="table-wrap">
        <div class="table-head">
            <strong>Company Directory</strong>
            <input type="text" placeholder="Search company">
        </div>
        <?php if (count($companies) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Workers</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?php echo $esc($company->company_id ?? '-'); ?></td>
                            <td class="two-line"><strong><?php echo $esc($company->company_name ?? '-'); ?></strong><span><?php echo $esc($company->mykpp_registration_no ?? 'No registration no'); ?></span></td>
                            <td><?php echo $esc(trim((string) (($company->company_address ?? '-') . ', ' . ($company->company_postcode ?? '-') . ' ' . ($company->company_district ?? '-') . ', ' . ($company->company_state ?? '-')))); ?></td>
                            <td class="two-line"><strong><?php echo $esc($company->company_telephone ?? '-'); ?></strong><span><?php echo $esc($company->company_email ?? '-'); ?></span></td>
                            <td><?php echo $esc(number_format((int) ($company->total_workers ?? 0))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table><div class="table-foot"><span class="pager">Showing 1-2 of 4,220 records</span></div>
        <?php else: ?>
            <div class="empty">No company records found.</div>
        <?php endif; ?>
    </section>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>