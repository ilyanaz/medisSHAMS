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
$showValue = static function ($value): string {
    $value = trim((string) ($value ?? ''));
    return $value !== '' ? $value : '-';
};
$addressParts = array_values(array_filter([
    trim((string) ($employee->employee_address ?? '')),
    trim((string) ($employee->employee_postcode ?? '')),
    trim((string) ($employee->employee_district ?? '')),
    trim((string) ($employee->employee_state ?? '')),
], static fn ($value) => $value !== ''));
$fullAddress = $addressParts !== [] ? implode(', ', $addressParts) : '-';
$isUsechh1Pdf = $pdfMode;
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
.sheet{padding:16px 18px;background:#fff}
.report-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}
.report-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:10px;padding:8px 14px;background:#fff;color:#374151;font-size:14px;font-weight:500;cursor:pointer}
.report-btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}
.sheet-top{position:relative;display:block;text-align:center;margin-bottom:10px}
.center-title{display:block;width:100%;text-align:center;font-size:12px;font-weight:700;line-height:1.45;color:#0f172a}
.right-code{position:absolute;right:0;top:0;font-size:12px;font-weight:700;line-height:1.45;color:#0f172a}
.sheet-title{text-align:center;margin-bottom:18px}
.sheet-title .line{font-size:12px;font-weight:700;line-height:1.45}
.sheet-title .main{font-size:15px;font-weight:700;line-height:1.45;margin-top:8px}
.document-table{width:100%;border-collapse:collapse;margin-bottom:16px;table-layout:fixed}
.document-table th,.document-table td{border:1px solid #c9d8ea;padding:8px 10px;font-size:11.5px;vertical-align:top;text-align:left;word-wrap:break-word}
.document-table th{font-weight:700;color:#0f172a;background:#fff}
.document-table .label-cell{width:21%;font-weight:700;background:#fff}
.document-table .value-cell{width:29%}
.document-table .wide-label{width:18%}
.document-table .wide-value{width:32%}
.document-table.employee-details th,
.document-table.employee-details td{border:none;padding-left:0;padding-right:16px}
.section-block{margin-top:22px}
.section-heading{display:flex;align-items:center;gap:14px;margin:0 0 14px}
.section-heading::after{content:"";flex:1;height:1px;background:#c9d8ea}
.section-heading span{font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}
.text-block{min-height:28px;line-height:1.6;white-space:pre-wrap}
.subtle{color:#475569}
.occupational-table th,.occupational-table td{text-align:left}
.occupational-table thead th{background:#fff}
.training-table thead th{background:#fff}
.pdf-page-break{page-break-before:always;break-before:page}
.toolbar-hide .app-card{padding:0;border:0;background:transparent;box-shadow:none}
.toolbar-hide .app-page{padding:18px;background:#f3f6f8}
@media print{body{background:#fff}.app-topbar,.app-sidebar,.report-actions{display:none!important}.app-shell{display:block}.app-main,.app-page,.app-card{display:block;height:auto;overflow:visible;padding:0!important;border:0!important;background:#fff!important}.sheet{padding:0;border:0;box-shadow:none}}
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

        <div class="section-block">
            <?php if (!$pdfMode): ?>
            <div class="section-heading"><span>Employee Details</span></div>
            <?php endif; ?>
            <table class="document-table employee-details">
                <tbody>
                    <tr>
                        <td class="label-cell">Name</td>
                        <td class="value-cell"><?php echo $esc($showValue($workerName)); ?></td>
                        <td class="label-cell">NRIC / Passport</td>
                        <td class="value-cell"><?php echo $esc($showValue($identityNo)); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">Date of Birth</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->employee_DOB ?? null)); ?></td>
                        <td class="label-cell">Gender</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->employee_gender ?? null)); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">Telephone</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->employee_telephone ?? null)); ?></td>
                        <td class="label-cell">Email</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->employee_email ?? null)); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">Address</td>
                        <td colspan="3"><?php echo $esc($fullAddress); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">Ethnicity</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->employee_ethnicity ?? null)); ?></td>
                        <td class="label-cell">Citizenship</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->employee_citizenship ?? null)); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">Marital Status</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->employee_martialStatus ?? null)); ?></td>
                        <td class="label-cell">No. of Children</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->no_of_children ?? null)); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">Years Married</td>
                        <td class="value-cell"><?php echo $esc($showValue($employee->years_married ?? null)); ?></td>
                        <td class="label-cell">&nbsp;</td>
                        <td class="value-cell">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section-block">
            <div class="section-heading"><span>Medical History</span></div>
            <table class="document-table">
                <tbody>
                    <tr><td class="label-cell">Diagnosed History</td><td colspan="3" class="text-block"><?php echo nl2br($esc($showValue($medicalHistory->diagnosed_history ?? null))); ?></td></tr>
                    <tr><td class="label-cell">Medication History</td><td colspan="3" class="text-block"><?php echo nl2br($esc($showValue($medicalHistory->medication_history ?? null))); ?></td></tr>
                    <tr><td class="label-cell">Admitted History</td><td colspan="3" class="text-block"><?php echo nl2br($esc($showValue($medicalHistory->admitted_history ?? null))); ?></td></tr>
                    <tr><td class="label-cell">Family History</td><td colspan="3" class="text-block"><?php echo nl2br($esc($showValue($medicalHistory->family_history ?? null))); ?></td></tr>
                    <tr><td class="label-cell">Other History</td><td colspan="3" class="text-block"><?php echo nl2br($esc($showValue($medicalHistory->others_history ?? null))); ?></td></tr>
                </tbody>
            </table>
        </div>

        <div class="section-block">
            <div class="section-heading"><span>Occupational and Company History</span></div>
            <table class="document-table occupational-table">
                <thead>
                    <tr>
                        <th style="width:14%;">Record</th>
                        <th style="width:16%;">Job Title</th>
                        <th style="width:18%;">Company Name</th>
                        <th style="width:16%;">Employment Duration</th>
                        <th style="width:16%;">Chemical Exposure Duration</th>
                        <th style="width:20%;">Chemical Exposure Incidents</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Current Company</strong></td>
                        <td><?php echo $esc($showValue($currentOccupational->job_title ?? null)); ?></td>
                        <td><?php echo $esc($showValue($currentOccupational->company_name ?? null)); ?></td>
                        <td><?php echo $esc($showValue($currentOccupational->employment_duration ?? null)); ?></td>
                        <td><?php echo $esc($showValue($currentOccupational->chemical_exposure_duration ?? null)); ?></td>
                        <td class="text-block"><?php echo nl2br($esc($showValue($currentOccupational->chemical_exposure_incidents ?? null))); ?></td>
                    </tr>
                    <?php foreach ($pastOccupationalRows as $index => $row): ?>
                    <tr>
                        <td><strong><?php echo $esc('Past Company ' . ($index + 1)); ?></strong></td>
                        <td><?php echo $esc($showValue($row->job_title ?? null)); ?></td>
                        <td><?php echo $esc($showValue($row->company_name ?? null)); ?></td>
                        <td><?php echo $esc($showValue($row->employment_duration ?? null)); ?></td>
                        <td><?php echo $esc($showValue($row->chemical_exposure_duration ?? null)); ?></td>
                        <td class="text-block"><?php echo nl2br($esc($showValue($row->chemical_exposure_incidents ?? null))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section-block">
            <div class="section-heading"><span>Personal and Social History</span></div>
            <table class="document-table">
                <tbody>
                    <tr>
                        <td class="label-cell">Smoking History</td>
                        <td class="value-cell"><?php echo $esc($showValue($personalSocialHistory->smoking_history ?? null)); ?></td>
                        <td class="label-cell">Vaping History</td>
                        <td class="value-cell"><?php echo $esc($showValue($personalSocialHistory->vaping_history ?? null)); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">No. of Cigarettes</td>
                        <td class="value-cell"><?php echo $esc($showValue($personalSocialHistory->no_of_cigarettes ?? null)); ?></td>
                        <td class="label-cell">Years of Vaping</td>
                        <td class="value-cell"><?php echo $esc($showValue($personalSocialHistory->years_of_vaping ?? null)); ?></td>
                    </tr>
                    <tr>
                        <td class="label-cell">Years of Smoking</td>
                        <td class="value-cell"><?php echo $esc($showValue($personalSocialHistory->years_of_smoking ?? null)); ?></td>
                        <td class="label-cell">Hobby</td>
                        <td class="value-cell"><?php echo $esc($showValue($personalSocialHistory->hobby ?? null)); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section-block<?php echo $isUsechh1Pdf ? ' pdf-page-break' : ''; ?>">
            <div class="section-heading"><span>Training History</span></div>
            <table class="document-table training-table">
                <thead>
                    <tr>
                        <th style="width:34%;">Training Item</th>
                        <th style="width:16%;"><?php echo $isUsechh1Pdf ? '&nbsp;' : 'Answer'; ?></th>
                        <th style="width:50%;">Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><strong>Handling of Chemical</strong></td><td><?php echo $esc($showValue($trainingHistory->handling_of_chemical ?? null)); ?></td><td class="text-block"><?php echo nl2br($esc($showValue($trainingHistory->chemical_comments ?? null))); ?></td></tr>
                    <tr><td><strong>Sign and Symptoms Knowledge</strong></td><td><?php echo $esc($showValue($trainingHistory->sign_symptoms ?? null)); ?></td><td class="text-block"><?php echo nl2br($esc($showValue($trainingHistory->sign_comments ?? null))); ?></td></tr>
                    <tr><td><strong>Chemical Poisoning Knowledge</strong></td><td><?php echo $esc($showValue($trainingHistory->chemical_poisoning ?? null)); ?></td><td class="text-block"><?php echo nl2br($esc($showValue($trainingHistory->poisoning_comments ?? null))); ?></td></tr>
                    <tr><td><strong>Proper PPE Knowledge</strong></td><td><?php echo $esc($showValue($trainingHistory->proper_PPE ?? null)); ?></td><td class="text-block"><?php echo nl2br($esc($showValue($trainingHistory->proper_comments ?? null))); ?></td></tr>
                    <tr><td><strong>PPE Usage</strong></td><td><?php echo $esc($showValue($trainingHistory->PPE_usage ?? null)); ?></td><td class="text-block"><?php echo nl2br($esc($showValue($trainingHistory->usage_comments ?? null))); ?></td></tr>
                </tbody>
            </table>
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

