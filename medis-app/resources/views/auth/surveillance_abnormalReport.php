<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Surveillance Abnormal Report</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';
$pdfMode = !empty($pdfMode);
$esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rows = $abnormalRows ?? [];
$companyId = (int) (($selectedCompany->company_id ?? 0) ?: ($_GET['company_id'] ?? 0));
$companyLabel = $companyLabel ?? trim((string) ($selectedCompany->company_name ?? ''));
$chemicalLabel = $chemicalLabel ?? trim((string) (($chemicalName ?? '') ?: ($_GET['chemical_name'] ?? '')));
$dateExamined = $dateExamined ?? ($_GET['date_examined'] ?? null);
$printParams = $printParams ?? array_filter([
    'declaration_id' => ($declarationId ?? ($_GET['declaration_id'] ?? null)),
    'company_id' => $companyId,
    'surveillance_id' => ($surveillanceId ?? ($_GET['surveillance_id'] ?? null)),
    'chemical_name' => ($chemicalLabel ?: null),
    'date_examined' => ($dateExamined ?: null),
], static fn ($value) => !empty($value));

if ((empty($rows) || $companyLabel === '') && class_exists('\\Illuminate\\Support\\Facades\\DB')) {
    $db = \Illuminate\Support\Facades\DB::class;
    if ($companyId > 0 && $companyLabel === '') {
        $companyRow = \Illuminate\Support\Facades\DB::table('company')->where('company_id', $companyId)->first();
        $companyLabel = trim((string) ($companyRow->company_name ?? ''));
    }
    if ($companyId > 0 && $chemicalLabel !== '' && !empty($dateExamined)) {
        $rawRows = \Illuminate\Support\Facades\DB::table('declaration as d')
            ->join('employee as e', 'd.employee_id', '=', 'e.employee_id')
            ->leftJoin('chemical_information as ci', function ($join) {
                $join->on('d.employee_id', '=', 'ci.employee_id')
                    ->on('d.surveillance_id', '=', 'ci.surveillance_id');
            })
            ->leftJoin('ms_findings as ms', function ($join) {
                $join->on('d.employee_id', '=', 'ms.employee_id')
                    ->on('d.surveillance_id', '=', 'ms.surveillance_id');
            })
            ->leftJoin('clinical_findings as cf', function ($join) {
                $join->on('d.employee_id', '=', 'cf.employee_id')
                    ->on('d.surveillance_id', '=', 'cf.surveillance_id');
            })
            ->leftJoin('target_organ as t', function ($join) {
                $join->on('d.employee_id', '=', 't.employee_id')
                    ->on('d.surveillance_id', '=', 't.surveillance_id');
            })
            ->leftJoin('biological_monitoring as bm', function ($join) {
                $join->on('d.employee_id', '=', 'bm.employee_id')
                    ->on('d.surveillance_id', '=', 'bm.surveillance_id');
            })
            ->leftJoin('recommendation as r', function ($join) {
                $join->on('d.employee_id', '=', 'r.employee_id')
                    ->on('d.surveillance_id', '=', 'r.surveillance_id');
            })
            ->where('d.company_id', $companyId)
            ->where('ci.chemicals', $chemicalLabel)
            ->whereDate('ci.examination_date', $dateExamined)
            ->where('ms.conclusion_fitness', 'Not Fit')
            ->orderBy('e.employee_firstName')
            ->orderBy('e.employee_lastName')
            ->get([
                'e.employee_firstName', 'e.employee_lastName', 'e.employee_NRIC', 'e.employee_passportNo', 'e.employee_gender',
                'ci.examination_type', DB::raw('(SELECT job_title FROM occupational_history WHERE occupational_history.employee_id = d.employee_id ORDER BY occupHistory_id ASC LIMIT 1) as job_title'),
                'ms.history_of_health', 'ms.clinical_findings as ms_clinical_findings', 'ms.CF_work_related', 'ms.TO_work_related', 'ms.BM_work_related', 'ms.conclusion_fitness',
                'cf.result_clinical_findings',
                't.blood_count', 't.renal_function', 't.liver_function', 't.chest_xray', 't.spirometry_FEV1', 't.spirometry_FVC', 't.spirometry_FEV_FVC',
                'bm.biological_exposure', 'bm.baseline_results', 'bm.baseline_annual',
                'r.MRPdate_start', 'r.recommencation_type', 'r.notes',
            ]);

        $formatTargetOrgan = static function ($row): string {
            $parts = [];
            if (!empty($row->blood_count)) { $parts[] = 'Full blood count'; }
            if (!empty($row->renal_function)) { $parts[] = 'Renal Profile'; }
            if (!empty($row->liver_function)) { $parts[] = 'Liver Profile'; }
            if (!empty($row->chest_xray)) { $parts[] = 'Chest X-ray'; }
            if ($row->spirometry_FEV1 !== null || $row->spirometry_FVC !== null || $row->spirometry_FEV_FVC !== null) { $parts[] = 'Spirometry'; }
            return $parts === [] ? 'N/A' : implode(', ', $parts);
        };
        $formatWorkRelated = static function ($row): string {
            $flags = [trim((string) ($row->CF_work_related ?? '')), trim((string) ($row->TO_work_related ?? '')), trim((string) ($row->BM_work_related ?? ''))];
            $answered = array_values(array_filter($flags, static fn ($v) => $v !== ''));
            if (in_array('Yes', $answered, true)) { return 'Yes'; }
            if ($answered !== [] && count(array_filter($answered, static fn ($v) => $v === 'No')) === count($answered)) { return 'No'; }
            return 'N/A';
        };
        $formatBei = static function ($row): string {
            foreach ([$row->baseline_annual ?? null, $row->baseline_results ?? null, $row->biological_exposure ?? null] as $value) {
                $value = trim((string) ($value ?? ''));
                if ($value !== '') { return $value; }
            }
            return 'N/A';
        };
        $formatRecommendation = static function ($row): string {
            $parts = [];
            if (!empty($row->MRPdate_start)) { $parts[] = 'MRP'; }
            if (!empty($row->recommencation_type)) { $parts[] = trim((string) $row->recommencation_type); }
            if (!empty($row->notes)) { $parts[] = trim((string) $row->notes); }
            return $parts === [] ? 'N/A' : implode(', ', array_values(array_unique($parts)));
        };

        $rows = $rawRows->map(static function ($row) use ($formatTargetOrgan, $formatWorkRelated, $formatBei, $formatRecommendation) {
            $employeeName = trim((string) (($row->employee_firstName ?? '') . ' ' . ($row->employee_lastName ?? '')));
            $identity = trim((string) (($row->employee_NRIC ?? '') !== '' ? ($row->employee_NRIC ?? '') : ($row->employee_passportNo ?? '')));
            $clinical = trim((string) (($row->ms_clinical_findings ?? $row->result_clinical_findings ?? '')));
            $history = trim((string) ($row->history_of_health ?? ''));
            return [
                'employee_name' => $employeeName !== '' ? $employeeName : 'N/A',
                'identity_no' => $identity !== '' ? $identity : 'N/A',
                'sex' => trim((string) ($row->employee_gender ?? '')) ?: 'N/A',
                'designation' => trim((string) ($row->job_title ?? '')) ?: 'N/A',
                'assessment_type' => trim((string) ($row->examination_type ?? '')) ?: 'N/A',
                'history_effect' => $history !== '' ? $history : 'N/A',
                'clinical_findings' => $clinical !== '' ? $clinical : 'N/A',
                'target_organ' => $formatTargetOrgan($row),
                'bm_determinant' => $formatBei($row),
                'work_relatedness' => $formatWorkRelated($row),
                'recommendation' => $formatRecommendation($row),
                'conclusion' => trim((string) ($row->conclusion_fitness ?? '')) ?: 'N/A',
            ];
        })->values()->all();
    }
}
$resolvedUser = class_exists('\\Illuminate\\Support\\Facades\\Auth') ? \Illuminate\Support\Facades\Auth::user() : null;
$footerFirst = trim((string) (($resolvedUser->first_name ?? $resolvedUser->firstName ?? '') ?: ''));
$footerLast = trim((string) (($resolvedUser->last_name ?? $resolvedUser->lastName ?? '') ?: ''));
$footerName = trim($footerFirst . ' ' . $footerLast);
if ($footerName === '') {
    $footerName = trim((string) (($resolvedUser->name ?? $username ?? 'User') ?: 'User'));
}
$generatedFooter = 'Generated by ' . $footerName . ' at ' . date('D, d M Y h:i A');
medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => 'report',
    'pdfMode' => $pdfMode,
]);
?>
<style>
.report-page{width:100%;max-width:none;margin:0;background:#fff;color:#0f172a;font-family:"Poppins","Segoe UI",Tahoma,Geneva,Verdana,sans-serif}
.sheet{padding:18px 18px 22px;border:1px solid #e5e7eb;border-radius:20px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.04);width:100%}
.sheet-top{position:relative;display:block;margin-bottom:8px;text-align:center}
.sheet-top .center-title{display:block;width:100%;text-align:center;font-size:13px;font-weight:700;line-height:1.55;color:#0f172a}
.sheet-top .right-code{position:absolute;right:0;top:0;display:block;text-align:right;font-size:13px;font-weight:700;line-height:1.55;color:#0f172a}
.sheet-title{text-align:center;margin-bottom:12px}
.sheet-title .line{font-size:13px;font-weight:700;line-height:1.55;color:#0f172a}
.sheet-title .main{font-size:16px;font-weight:700;line-height:1.5;margin-top:8px;color:#0f172a}
.meta{display:grid;gap:8px;margin:14px 0 16px;font-size:14px}
.meta-row{display:flex;align-items:flex-end;gap:8px}
.meta-label{min-width:160px;font-weight:600;color:#0f172a}
.meta-line{flex:1;min-height:18px;padding:0;color:#334155}
.summary-table{width:100%;border-collapse:collapse;table-layout:fixed}
.summary-table th,.summary-table td{border:1px solid #cbd5e1;padding:5px 4px;vertical-align:top;font-size:10px;line-height:1.15;word-break:break-word}
.summary-table th{background:#eef7f0;font-weight:700;text-align:center;color:#14321f}
.summary-table td{background:#fff;color:#334155}
.toolbar-hide .app-card{padding:0;border:0;background:transparent;box-shadow:none}.toolbar-hide .app-page{padding:18px;background:#f3f6f8}
.report-actions{display:flex;justify-content:flex-end;gap:10px;margin-bottom:12px}
.report-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:10px;padding:8px 12px;background:#fff;color:#374151;font-size:14px;cursor:pointer}
.report-btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}
.pdf-footer{margin-top:14px;padding-top:8px;border-top:1px solid #e5e7eb;text-align:right;font-size:11px;color:#64748b}
.pdf-mode .sheet{padding:0;border:0;border-radius:0;box-shadow:none}.pdf-mode .app-page{padding:0!important;background:#fff}.pdf-mode .report-page{padding:0}.pdf-mode .report-actions{display:none!important}.pdf-mode .pdf-footer{position:fixed;left:0;right:0;bottom:0;margin:0;padding-top:6px;background:#fff;border-top:1px solid #e5e7eb}
.col-no{width:3%}.col-name{width:10%}.col-passport{width:8%}.col-sex{width:4%}.col-job{width:9%}.col-type{width:8%}.col-history{width:9%}.col-clinical{width:7%}.col-target{width:9%}.col-bm{width:9%}.col-work{width:7%}.col-action{width:10%}.col-conclusion{width:7%}.pdf-mode .summary-table th,.pdf-mode .summary-table td{padding:3px 2px;font-size:8.4px;line-height:1.08}
@page{size:A4 landscape;margin:10mm 8mm 12mm 8mm}@media print{body{background:#fff}.app-topbar,.app-sidebar,.report-actions{display:none!important}.app-shell{display:block}.app-main,.app-page,.app-card{display:block;height:auto;overflow:visible;padding:0!important;border:0!important;background:#fff!important}.report-page{max-width:none}.sheet{padding:0}}
@media(max-width:1200px){.summary-table{min-width:1080px}.report-page{overflow:auto;width:100%}}
</style>
<div class="report-page toolbar-hide<?php echo $pdfMode ? ' pdf-mode' : ''; ?>">
    <div class="sheet">
        <?php if (!$pdfMode): ?>
        <div class="report-actions">
            <a class="report-btn" href="<?php echo $esc(function_exists('route') ? route('general.report') : 'general_report.php'); ?>">Back</a>
            <button class="report-btn primary" type="button" onclick="window.open('<?php echo $esc(route('pdf.usechh5ii', $printParams)); ?>', '_blank')">Print</button>
        </div>
        <?php endif; ?>

        <div class="sheet-top">
            <span class="center-title">Occupational Safety and Health Act 1994 (Act 514)</span>
            <span class="right-code">USECHH 5ii</span>
        </div>

        <div class="sheet-title">
            <div class="line">Use and Standard of Exposure of Chemical Hazardous to Health Regulations 2000</div>
            <div class="main">DETAILS OF WORKERS WITH ABNORMAL EXAMINATION RESULTS</div>
        </div>

        <div class="meta">
            <div class="meta-row">
                <span class="meta-label">Name of Company: </span>
                <span class="meta-line"><?php echo $esc($companyLabel !== '' ? $companyLabel : 'N/A'); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Name of Chemical: </span>
                <span class="meta-line"><?php echo $esc($chemicalLabel !== '' ? $chemicalLabel : 'N/A'); ?></span>
            </div>
        </div>

        <table class="summary-table">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-name">Employee's name</th>
                    <th class="col-passport">NRIC/Passport</th>
                    <th class="col-sex">Sex</th>
                    <th class="col-job">Job category/Designation</th>
                    <th class="col-type">Type of Assessment</th>
                    <th class="col-history">History of health effect due to CHTH exposure</th>
                    <th class="col-clinical">Clinical findings</th>
                    <th class="col-target">Target organ function test (specify organ)</th>
                    <th class="col-bm">BM determinant</th>
                    <th class="col-work">Work relatedness</th>
                    <th class="col-action">Recommendation/action taken</th>
                    <th class="col-conclusion">Conclusion of MS Findings (Fit/Not Fit)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $index => $row): ?>
                    <tr>
                        <td><?php echo $esc($index + 1); ?></td>
                        <td><?php echo $esc($row['employee_name'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['identity_no'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['sex'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['designation'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['assessment_type'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['history_effect'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['clinical_findings'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['target_organ'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['bm_determinant'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['work_relatedness'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['recommendation'] ?? 'N/A'); ?></td>
                        <td><?php echo $esc($row['conclusion'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>1</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>N/A</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pdf-footer"><?php echo $esc($generatedFooter); ?></div>
    </div>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>


