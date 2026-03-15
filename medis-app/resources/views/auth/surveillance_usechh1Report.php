<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>USECHH 1 Employee Details</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';
$pdfMode = !empty($pdfMode);
$esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$backUrl = function_exists('route') ? route('general.report') : 'general_report.php';
$printUrl = function_exists('route') ? route('pdf.usechh1', ['employee_id' => $employeeData->employee_id ?? request()->query('employee_id')]) : 'PDF_USECHH1.php';
$employee = $employeeData ?? (object) [];
$medicalHistory = $medicalHistoryData ?? (object) [];
$currentOccupational = $currentOccupationalData ?? (object) [];
$pastOccupationalRows = isset($pastOccupationalHistoryRows) && is_iterable($pastOccupationalHistoryRows) ? $pastOccupationalHistoryRows : [];
$personalSocialHistory = $personalSocialHistoryData ?? (object) [];
$trainingHistory = $trainingHistoryData ?? (object) [];
$workerName = trim((string) (($employee->employee_firstName ?? '') . ' ' . ($employee->employee_lastName ?? '')));
$identityNo = $employee->employee_NRIC ?? ($employee->employee_passportNo ?? '');
medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => '',
    'pdfMode' => $pdfMode,
]);
?>
<style>
.report-page{width:100%;max-width:none;margin:0;color:#0f172a;font-family:"Poppins","Segoe UI",Tahoma,Geneva,Verdana,sans-serif}
.sheet{padding:24px 26px;border:1px solid #e5e7eb;border-radius:20px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.04)}
.report-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}
.report-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:10px;padding:8px 14px;background:#fff;color:#374151;font-size:14px;font-weight:500;cursor:pointer}
.report-btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}
.sheet-top{position:relative;display:block;text-align:center;margin-bottom:10px}
.center-title{display:block;width:100%;text-align:center;font-size:13px;font-weight:700;line-height:1.55;color:#0f172a}
.right-code{position:absolute;right:0;top:0;font-size:13px;font-weight:700;line-height:1.55;color:#0f172a}
.sheet-title{text-align:center;margin-bottom:18px}.sheet-title .line{font-size:13px;font-weight:700;line-height:1.5}.sheet-title .main{font-size:16px;font-weight:700;line-height:1.5;margin-top:10px}
.info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:18px}.info-card{border:1px solid #e5e7eb;border-radius:16px;padding:14px;background:#fff}.info-card h3{margin:0 0 12px;font-size:1rem}.line-list{display:grid;gap:10px}.line-row{display:grid;grid-template-columns:190px 1fr;gap:12px;align-items:start}.line-label{font-size:13px;font-weight:600;color:#334155}.line-value{font-size:13px;line-height:1.6;color:#0f172a;min-height:20px;padding-bottom:4px;border-bottom:1px solid #e5e7eb}.full{grid-column:1/-1}.table-wrap{border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;background:#fff}.history-table{width:100%;border-collapse:collapse}.history-table th,.history-table td{padding:12px 14px;border-top:1px solid #e5e7eb;vertical-align:top;text-align:left;font-size:13px}.history-table thead th{border-top:none;background:#f8fafc;font-weight:700;color:#334155}.history-table tbody th{background:#f8fafc;font-weight:600;color:#111827;width:260px}.section-block{margin-top:18px}.section-block h3{margin:0 0 12px;font-size:1rem}.toolbar-hide .app-card{padding:0;border:0;background:transparent;box-shadow:none}.toolbar-hide .app-page{padding:18px;background:#f3f6f8}
@media(max-width:860px){.info-grid{grid-template-columns:1fr}.line-row{grid-template-columns:1fr}.history-table,.history-table thead,.history-table tbody,.history-table tr,.history-table th,.history-table td{display:block;width:100%}.history-table thead{display:none}.history-table tbody th{width:100%}}
@media print{body{background:#fff}.app-topbar,.app-sidebar,.report-actions{display:none!important}.app-shell{display:block}.app-main,.app-page,.app-card{display:block;height:auto;overflow:visible;padding:0!important;border:0!important;background:#fff!important}.sheet{padding:0;box-shadow:none;border:0}}
</style>
<div class="report-page toolbar-hide">
    <section class="sheet">
        <div class="sheet-top">
            <span class="center-title">Occupational Safety and Health Act 1994 (Act 514)</span>
            <span class="right-code">USECHH 1</span>
        </div>
        <div class="sheet-title">
            <div class="line">Use and Standard of Exposure of Chemicals Hazardous to Health Regulations 2000</div>
            <div class="main">EMPLOYEE DETAILS</div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>Employee Details</h3>
                <div class="line-list">
                    <div class="line-row"><span class="line-label">Name</span><span class="line-value"><?php echo $esc($workerName); ?></span></div>
                    <div class="line-row"><span class="line-label">NRIC / Passport</span><span class="line-value"><?php echo $esc($identityNo); ?></span></div>
                    <div class="line-row"><span class="line-label">Date of Birth</span><span class="line-value"><?php echo $esc($employee->employee_DOB ?? ''); ?></span></div>
                    <div class="line-row"><span class="line-label">Gender</span><span class="line-value"><?php echo $esc($employee->employee_gender ?? ''); ?></span></div>
                    <div class="line-row full"><span class="line-label">Address</span><span class="line-value"><?php echo $esc($employee->employee_address ?? ''); ?></span></div>
                    <div class="line-row"><span class="line-label">Telephone</span><span class="line-value"><?php echo $esc($employee->employee_telephone ?? ''); ?></span></div>
                    <div class="line-row"><span class="line-label">Email</span><span class="line-value"><?php echo $esc($employee->employee_email ?? ''); ?></span></div>
                </div>
            </div>
            <div class="info-card">
                <h3>Personal Profile</h3>
                <div class="line-list">
                    <div class="line-row"><span class="line-label">Ethnicity</span><span class="line-value"><?php echo $esc($employee->employee_ethnicity ?? ''); ?></span></div>
                    <div class="line-row"><span class="line-label">Citizenship</span><span class="line-value"><?php echo $esc($employee->employee_citizenship ?? ''); ?></span></div>
                    <div class="line-row"><span class="line-label">Marital Status</span><span class="line-value"><?php echo $esc($employee->employee_martialStatus ?? ''); ?></span></div>
                    <div class="line-row"><span class="line-label">No. of Children</span><span class="line-value"><?php echo $esc($employee->no_of_children ?? ''); ?></span></div>
                    <div class="line-row"><span class="line-label">Years Married</span><span class="line-value"><?php echo $esc($employee->years_married ?? ''); ?></span></div>
                </div>
            </div>
        </div>

        <div class="section-block">
            <h3>Medical History</h3>
            <div class="table-wrap">
                <table class="history-table">
                    <tbody>
                        <tr><th>Diagnosed History</th><td><?php echo nl2br($esc($medicalHistory->diagnosed_history ?? '')); ?></td></tr>
                        <tr><th>Medication History</th><td><?php echo nl2br($esc($medicalHistory->medication_history ?? '')); ?></td></tr>
                        <tr><th>Admitted History</th><td><?php echo nl2br($esc($medicalHistory->admitted_history ?? '')); ?></td></tr>
                        <tr><th>Family History</th><td><?php echo nl2br($esc($medicalHistory->family_history ?? '')); ?></td></tr>
                        <tr><th>Other History</th><td><?php echo nl2br($esc($medicalHistory->others_history ?? '')); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-block">
            <h3>Occupational &amp; Company History</h3>
            <div class="table-wrap">
                <table class="history-table">
                    <thead>
                        <tr><th>Record</th><th>Job Title</th><th>Company Name</th><th>Employment Duration</th><th>Chemical Exposure Duration</th><th>Chemical Exposure Incidents</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Current Company</th>
                            <td><?php echo $esc($currentOccupational->job_title ?? ''); ?></td>
                            <td><?php echo $esc($currentOccupational->company_name ?? ''); ?></td>
                            <td><?php echo $esc($currentOccupational->employment_duration ?? ''); ?></td>
                            <td><?php echo $esc($currentOccupational->chemical_exposure_duration ?? ''); ?></td>
                            <td><?php echo nl2br($esc($currentOccupational->chemical_exposure_incidents ?? '')); ?></td>
                        </tr>
                        <?php foreach ($pastOccupationalRows as $index => $row): ?>
                        <tr>
                            <th><?php echo $esc('Past Company ' . ($index + 1)); ?></th>
                            <td><?php echo $esc($row->job_title ?? ''); ?></td>
                            <td><?php echo $esc($row->company_name ?? ''); ?></td>
                            <td><?php echo $esc($row->employment_duration ?? ''); ?></td>
                            <td><?php echo $esc($row->chemical_exposure_duration ?? ''); ?></td>
                            <td><?php echo nl2br($esc($row->chemical_exposure_incidents ?? '')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-block">
            <h3>Personal &amp; Social History</h3>
            <div class="table-wrap">
                <table class="history-table">
                    <tbody>
                        <tr><th>Smoking History</th><td><?php echo $esc($personalSocialHistory->smoking_history ?? ''); ?></td></tr>
                        <tr><th>Years of Smoking</th><td><?php echo $esc($personalSocialHistory->years_of_smoking ?? ''); ?></td></tr>
                        <tr><th>No. of Cigarettes</th><td><?php echo $esc($personalSocialHistory->no_of_cigarettes ?? ''); ?></td></tr>
                        <tr><th>Vaping History</th><td><?php echo $esc($personalSocialHistory->vaping_history ?? ''); ?></td></tr>
                        <tr><th>Years of Vaping</th><td><?php echo $esc($personalSocialHistory->years_of_vaping ?? ''); ?></td></tr>
                        <tr><th>Hobby</th><td><?php echo nl2br($esc($personalSocialHistory->hobby ?? '')); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-block">
            <h3>Training History</h3>
            <div class="table-wrap">
                <table class="history-table">
                    <thead>
                        <tr><th>Training Item</th><th>Answer</th><th>Comments</th></tr>
                    </thead>
                    <tbody>
                        <tr><th>Handling of Chemical</th><td><?php echo $esc($trainingHistory->handling_of_chemical ?? ''); ?></td><td><?php echo nl2br($esc($trainingHistory->chemical_comments ?? '')); ?></td></tr>
                        <tr><th>Sign &amp; Symptoms Knowledge</th><td><?php echo $esc($trainingHistory->sign_symptoms ?? ''); ?></td><td><?php echo nl2br($esc($trainingHistory->sign_comments ?? '')); ?></td></tr>
                        <tr><th>Chemical Poisoning Knowledge</th><td><?php echo $esc($trainingHistory->chemical_poisoning ?? ''); ?></td><td><?php echo nl2br($esc($trainingHistory->poisoning_comments ?? '')); ?></td></tr>
                        <tr><th>Proper PPE Knowledge</th><td><?php echo $esc($trainingHistory->proper_PPE ?? ''); ?></td><td><?php echo nl2br($esc($trainingHistory->proper_comments ?? '')); ?></td></tr>
                        <tr><th>PPE Usage</th><td><?php echo $esc($trainingHistory->PPE_usage ?? ''); ?></td><td><?php echo nl2br($esc($trainingHistory->usage_comments ?? '')); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php if (!$pdfMode): ?>
    <div class="report-actions">
        <a class="report-btn" href="<?php echo $esc($backUrl); ?>">Back</a>
        <a class="report-btn primary" href="<?php echo $esc($printUrl); ?>">Print</a>
    </div>
    <?php endif; ?>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>

