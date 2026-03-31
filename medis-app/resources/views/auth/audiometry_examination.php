
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audiometry Examination</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';

$esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$stepBack = function_exists('route') ? route('audiometry.list') : 'audiometry_list.php';
$saveExamUrl = function_exists('route') ? route('audiometry.examination.save') : 'audiometry_examination.php';
$steps = [
    ['label' => 'Company'],
    ['label' => 'Employee'],
    ['label' => 'Questionnaire'],
    ['label' => 'Audiometry List'],
    ['label' => 'Examination', 'active' => true],
    ['label' => 'Report'],
];
$sectionStatuses = $sectionStatuses ?? [];
$sectionItems = [
    ['key' => 'information', 'label' => 'Audiometry Information'],
    ['key' => 'baseline', 'label' => 'Baseline & Annual Audiograph'],
    ['key' => 'summary', 'label' => 'Summary Audiograph'],
    ['key' => 'comments', 'label' => 'Audiograph Test Comments'],
];
$selectedEmployee = $selectedEmployee ?? null;
$selectedCompany = $selectedCompany ?? null;
$currentOccupationalHistory = $currentOccupationalHistory ?? null;
$audiometryTest = $audiometryTest ?? null;
$pastMedical = $pastMedical ?? null;
$baselineAudiograph = $baselineAudiograph ?? null;
$annualAudiograph = $annualAudiograph ?? null;
$audioComments = $audioComments ?? null;
$summaryRightRows = $summaryRightRows ?? [];
$summaryLeftRows = $summaryLeftRows ?? [];
$calculatedAverages = $calculatedAverages ?? [];
$doctor = $doctor ?? null;
$audiometryId = $audiometryId ?? null;

$employeeName = trim((string) (($selectedEmployee->employee_firstName ?? '') . ' ' . ($selectedEmployee->employee_lastName ?? '')));
$employeeIdentity = $selectedEmployee->employee_NRIC ?? ($selectedEmployee->employee_passportNo ?? '');
$employeeAge = !empty($selectedEmployee->employee_DOB) ? (string) now()->diffInYears(\Carbon\Carbon::parse($selectedEmployee->employee_DOB)) : '';
$doctorName = trim((string) (($doctor->doctor_firstName ?? '') . ' ' . ($doctor->doctor_lastName ?? '')));

$freqs = [
    '250' => '250',
    '500' => '500',
    '1000' => '1K',
    '2000' => '2K',
    '3000' => '3K',
    '4000' => '4K',
    '6000' => '6K',
    '8000' => '8K',
];

$fieldSuffix = static function (string $hz): string {
    return match ($hz) {
        '1000' => '1k',
        '2000' => '2k',
        '3000' => '3k',
        '4000' => '4k',
        '6000' => '6k',
        '8000' => '8k',
        default => $hz,
    };
};

$rowColors = ['#f59e0b', '#fb923c', '#22c55e', '#3b82f6', '#6366f1', '#8b5cf6'];
$getValue = static function (string $name, $default = '') {
    return old($name, $default);
};
$getEarColor = static function (string $side): string {
    return strtoupper($side) === 'RIGHT' ? '#c85a11' : '#1f4f8c';
};
$chartRows = static function (array $rows): array {
    $points = [];
    $xMap = ['500' => 4, '1000' => 20, '2000' => 36, '3000' => 52, '4000' => 68, '6000' => 84, '8000' => 98];
    foreach ($rows as $row) {
        $series = [];
        foreach ($xMap as $hz => $x) {
            $value = $row['values'][$hz] ?? '';
            if ($value === '' || ! is_numeric($value)) {
                continue;
            }
            $y = ((float) $value + 20) / 140 * 100;
            $series[] = ['x' => $x, 'y' => max(0, min(100, $y))];
        }
        $points[] = $series;
    }
    return $points;
};
$summaryRightChart = $chartRows($summaryRightRows);
$summaryLeftChart = $chartRows($summaryLeftRows);

$yesNoFromInt = static function ($value): string {
    if ($value === null || $value === '') {
        return '';
    }
    return (int) $value === 1 ? 'Yes' : 'No';
};

medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => 'audiometry',
]);
?>
<style>
.app-page{padding:10px 12px 12px}
.flow{display:grid;grid-template-rows:auto minmax(0,1fr);gap:28px;height:calc(100vh - 130px);min-height:0}
.stepper{border:0;border-radius:0;background:transparent;padding:0}.stepper h3{display:none}
.step-list{position:relative;display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:6px;align-items:start;padding-bottom:6px}
.step-list::before{content:"";position:absolute;left:20px;right:20px;top:19px;height:2px;background:#d7dee8;z-index:0}
.step-link{position:relative;z-index:1;display:grid;justify-items:center;gap:8px;padding:0 4px;border-radius:14px;color:#374151;text-align:center;cursor:default}.step-link.active{color:#14321f;font-weight:700}
.step-index{width:38px;height:38px;border-radius:999px;border:1px solid #9ca3af;background:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700}.step-link.active .step-index{background:#389B5B;border-color:#389B5B;color:#fff}
.step-label{font-size:.82rem;line-height:1.25;max-width:96px}
.exam-shell{display:grid;grid-template-columns:220px minmax(0,1fr);gap:8px;min-height:0}
.side{border:0;border-radius:0;background:transparent;padding:0;position:sticky;top:0;height:100%;max-height:100%;overflow:auto}
.status-list{display:grid;gap:8px;position:relative}.status-list::before{content:"";position:absolute;left:18px;top:10px;bottom:10px;width:2px;background:#d9e2dd}
.status-item{position:relative;display:flex;align-items:center;gap:10px;color:#334155;cursor:pointer;background:transparent;border:0;padding:2px 0;text-align:left;width:100%;font:inherit}
.status-icon{width:34px;height:34px;min-width:34px;min-height:34px;border-radius:999px;border:2px solid #d1d5db;background:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;line-height:1;position:relative;z-index:1;flex-shrink:0}
.status-icon.ok{background:#dcfce7;border-color:#389B5B;color:#166534}.status-icon.bad{background:#fee2e2;border-color:#ef4444;color:#b91c1c}
.status-text{display:grid;gap:2px;min-width:0;align-content:center}.status-text strong{font-size:.9rem;line-height:1.18;min-height:2.1em;display:flex;align-items:center}.status-text span{font-size:.76rem;color:#64748b;line-height:1.15}
.main{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:12px;min-height:0;overflow:auto}
.top h2{margin:0 0 12px;font-size:1.8rem}
.notice{margin-top:14px;padding:12px 14px;border-radius:12px}.ok-msg{border:1px solid #bbf7d0;background:#f0fdf4;color:#166534}.err-msg{border:1px solid #fecaca;background:#fef2f2;color:#991b1b}
.stack{display:grid;gap:16px}.card{display:none;padding:0;border:0;background:transparent}.card.is-active{display:block}
.status-item.is-current .status-text strong{color:#166534}.status-item.is-current .status-icon{box-shadow:0 0 0 4px rgba(56,155,91,.12)}
.card-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;flex-wrap:wrap;margin-bottom:10px}.card-head h3{margin:0;font-size:1.12rem;line-height:1.35}
.badge{display:inline-flex;align-items:center;border-radius:999px;padding:7px 12px;font-size:.82rem;font-weight:700}.badge.done{background:#dcfce7;color:#166534}.badge.missing{background:#fee2e2;color:#b91c1c}
.info-layout{display:grid;grid-template-columns:1fr;gap:16px}
.info-panel,.audiograph-card,.summary-card,.comment-card{border:1px solid #dfe5ea;border-radius:18px;background:#fff;overflow:hidden}
.info-head,.audiograph-head,.summary-head,.comment-head{padding:10px 14px;background:#eef7f0;border-bottom:1px solid #dfe5ea;font-size:.95rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}.audiograph-head,.summary-head,.comment-head{text-align:center}
.info-table,.audio-table,.summary-table,.comment-grid{width:100%;border-collapse:collapse}
.info-table th,.info-table td,.audio-table th,.audio-table td,.summary-table th,.summary-table td,.comment-grid th,.comment-grid td{border:1px solid #dfe5ea;padding:9px 10px;vertical-align:middle}
.info-table th,.comment-grid th{background:#f8fafc;font-size:.84rem;font-weight:700;text-align:left}.info-table td{font-size:.88rem}
.info-table input,.info-table select,.comment-grid input,.comment-grid select,.comment-grid textarea{width:100%;border:1px solid #d1d5db;border-radius:10px;padding:8px 10px;font:inherit;background:#fff}.comment-grid textarea{min-height:88px;resize:vertical}
.info-static{font-weight:600;color:#0f172a}.audiograph-grid{display:grid;grid-template-columns:1fr;gap:18px}.summary-split,.comment-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.date-row{display:grid;grid-template-columns:120px 1fr;align-items:center;border-bottom:1px solid #e5e7eb}.date-row span{padding:8px 12px;font-size:.86rem;font-weight:700;background:#f8fafc}.date-row strong{padding:8px 12px;font-size:.9rem}
.audio-table th{background:#f8fafc;font-weight:700;text-align:center}.audio-table .freq-head{background:#f8fafc}.audio-table .ear-label{background:#fff;color:#0f172a;font-weight:700;text-align:left;padding-left:14px;width:120px}.audio-table .section-band{background:#f8fafc;color:#0f172a;font-weight:700;font-size:.9rem;letter-spacing:.04em}.audio-table input{width:100%;border:0;background:transparent;font:inherit;text-align:center;padding:4px 0}
.audiograph-tools{display:flex;justify-content:flex-end;padding:12px 14px;border-top:1px solid #dfe5ea;background:#fcfdfd}
.graph-btn{display:inline-flex;align-items:center;gap:8px;border:1px solid #d1d5db;border-radius:12px;padding:9px 14px;background:#fff;color:#374151;cursor:pointer;font:inherit}
.graph-modal{position:fixed;inset:0;background:rgba(15,23,42,.42);display:none;align-items:center;justify-content:center;z-index:80;padding:24px}
.graph-modal.is-open{display:flex}
.graph-dialog{width:min(920px,100%);max-height:90vh;overflow:auto;border-radius:22px;border:1px solid #dfe5ea;background:#fff;box-shadow:0 22px 60px rgba(15,23,42,.18)}
.graph-dialog-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid #e5e7eb}
.graph-dialog-head h3{margin:0;font-size:1.08rem}
.graph-close{border:1px solid #d1d5db;background:#fff;border-radius:12px;width:40px;height:40px;font-size:1.2rem;cursor:pointer}
.graph-dialog-body{padding:18px}
.graph-controls{display:grid;grid-template-columns:repeat(2,minmax(0,220px));gap:14px;margin-bottom:16px}
.graph-control{display:grid;gap:6px}
.graph-control label{font-size:.82rem;font-weight:700;color:#475569}
.graph-control select{width:100%;border:1px solid #d1d5db;border-radius:10px;padding:9px 10px;background:#fff;font:inherit}
.chart-surface{height:420px;border:1px solid #d6dee7;border-radius:14px;background:#fff;overflow:hidden;padding:10px}.chart-surface .hz-row,.chart-surface .db-col{display:none}.chart-surface svg{width:100%;height:100%;display:block}.chart-legend{display:grid;grid-template-columns:repeat(2,max-content);justify-content:center;column-gap:36px;row-gap:10px;margin-top:14px;font-size:.82rem}.legend-item{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:700;color:#475569}.legend-preview{width:74px;height:20px;display:inline-flex;align-items:center;justify-content:center}.legend-preview svg{width:100%;height:100%;display:block;overflow:visible}
.summary-table th{background:#f8fafc;font-weight:700;text-align:center}.summary-table .date-head{text-align:left;background:#fff7ed}.tone-chip{font-weight:700}.readonly-box{display:block;width:100%;padding:9px 10px;border:1px solid #d1d5db;border-radius:10px;background:#f8fafc;font-weight:600}
.actions{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:20px}.btn,.next{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;cursor:pointer}.next{background:#389B5B;border-color:#389B5B;color:#fff}
@media(max-width:1400px){.audiograph-grid,.summary-split,.comment-layout{grid-template-columns:1fr}}@media(max-width:1200px){.exam-shell{grid-template-columns:1fr}}@media(max-width:1100px){.stepper{padding:14px}.step-list{grid-template-columns:repeat(3,minmax(0,1fr))}.step-label{max-width:none}}
</style>
<div class="flow">
    <aside class="stepper">
        <div class="step-list">
            <?php foreach ($steps as $index => $step): ?>
                <div class="step-link<?php echo !empty($step['active']) ? ' active' : ''; ?>">
                    <span class="step-index"><?php echo $index + 1; ?></span>
                    <span class="step-label"><?php echo $esc($step['label']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>

    <div class="exam-shell">
        <aside class="side">
            <div class="status-list">
                <?php foreach ($sectionItems as $index => $item): $done = !empty($sectionStatuses[$item['key']]); ?>
                    <button type="button" class="status-item<?php echo $index === 0 ? ' is-current' : ''; ?>" data-audio-section="<?php echo $index; ?>">
                        <span class="status-icon <?php echo $done ? 'ok' : 'bad'; ?>"><?php echo $done ? '&#10003;' : '!'; ?></span>
                        <span class="status-text">
                            <strong><?php echo $esc($item['label']); ?></strong>
                            <span><?php echo $done ? 'Completed' : 'Incomplete'; ?></span>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </aside>

        <section class="main">
            <div class="top">
                <h2>Audiometry Examination</h2>
            </div>
            <?php if (session('status')): ?><div class="notice ok-msg"><?php echo $esc(session('status')); ?></div><?php endif; ?>
            <?php if (isset($errors) && method_exists($errors, 'any') && $errors->any()): ?><div class="notice err-msg"><?php echo $esc($errors->first()); ?></div><?php endif; ?>

            <form method="POST" action="<?php echo $esc($saveExamUrl); ?>" id="audiometryExamForm">
                <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
                <input type="hidden" name="employee_id" value="<?php echo $esc($selectedEmployee->employee_id ?? ''); ?>">
                <input type="hidden" name="company_id" value="<?php echo $esc($selectedCompany->company_id ?? ''); ?>">
                <input type="hidden" name="audiometry_id" value="<?php echo $esc($audiometryId ?? ''); ?>">

                <div class="stack">
                    <section class="card is-active" data-section-index="0">
                        <div class="card-head">
                            <div><h3>Audiometry Information</h3></div>
                            <span class="badge missing">Incomplete</span>
                        </div>

                        <div class="info-layout">
                            <section class="info-panel">
                                <div class="info-head">Employee Information</div>
                                <table class="info-table">
                                    <tr><th>Name</th><td><span class="info-static"><?php echo $esc($employeeName !== '' ? $employeeName : '-'); ?></span></td></tr>
                                    <tr><th>I/C No.</th><td><span class="info-static"><?php echo $esc($employeeIdentity !== '' ? $employeeIdentity : '-'); ?></span></td></tr>
                                    <tr><th>Company</th><td><span class="info-static"><?php echo $esc($selectedCompany->company_name ?? '-'); ?></span></td></tr>
                                    <tr><th>Job</th><td><span class="info-static"><?php echo $esc($currentOccupationalHistory->job_title ?? '-'); ?></span></td></tr>
                                    <tr><th>Contact No.</th><td><span class="info-static"><?php echo $esc($selectedEmployee->employee_telephone ?? '-'); ?></span></td></tr>
                                    <tr><th>Sex</th><td><span class="info-static"><?php echo $esc($selectedEmployee->employee_gender ?? '-'); ?></span></td></tr>
                                    <tr><th>Age</th><td><span class="info-static"><?php echo $esc($employeeAge !== '' ? $employeeAge : '-'); ?></span></td></tr>
                                </table>
                            </section>
                            <section class="info-panel">
                                <div class="info-head">Test Information</div>
                                <table class="info-table">
                                    <tr><th>Type</th><td><select name="type_audiogram"><option value="">Select</option><option value="Baseline" <?php echo $getValue('type_audiogram', $pastMedical->type_audiogram ?? '') === 'Baseline' ? 'selected' : ''; ?>>Baseline</option><option value="Annual" <?php echo $getValue('type_audiogram', $pastMedical->type_audiogram ?? '') === 'Annual' ? 'selected' : ''; ?>>Annual</option></select></td></tr>
                                    <tr><th>Total Years Working</th><td><input type="number" min="0" name="total_years_working" value="<?php echo $esc($getValue('total_years_working', $audiometryTest->total_years_working ?? '')); ?>"></td></tr>
                                    <tr><th>Duration of Employment</th><td><input type="number" min="0" name="noYears_working" value="<?php echo $esc($getValue('noYears_working', $audiometryTest->noYears_working ?? '')); ?>"></td></tr>
                                    <tr><th>Audiometer</th><td><input type="number" min="0" name="audiometer" value="<?php echo $esc($getValue('audiometer', $audiometryTest->audiometer ?? '')); ?>"></td></tr>
                                    <tr><th>Calibration Date</th><td><input type="date" name="calibration_date" value="<?php echo $esc($getValue('calibration_date', $audiometryTest->calibration_date ?? '')); ?>"></td></tr>
                                    <tr><th>Date of Audiometry Test</th><td><input type="date" name="audioTest_date" value="<?php echo $esc($getValue('audioTest_date', $audiometryTest->audioTest_date ?? date('Y-m-d'))); ?>"></td></tr>
                                    <tr><th>SEG</th><td><input type="number" min="0" name="seg" value="<?php echo $esc($getValue('seg', $pastMedical->seg ?? '')); ?>"></td></tr>
                                    <tr><th>dB (LEX)</th><td><input type="number" step="0.01" name="exposure_lex" value="<?php echo $esc($getValue('exposure_lex', $pastMedical->exposure_lex ?? '')); ?>"></td></tr>
                                </table>
                            </section>

                            <section class="info-panel">
                                <div class="info-head">Past Medical History</div>
                                <table class="info-table">
                                    <tr><th>Ear Infections</th><td><select name="ear_infections"><option value="">Select</option><option value="Yes" <?php echo $getValue('ear_infections', $yesNoFromInt($pastMedical->ear_infections ?? null)) === 'Yes' ? 'selected' : ''; ?>>Yes</option><option value="No" <?php echo $getValue('ear_infections', $yesNoFromInt($pastMedical->ear_infections ?? null)) === 'No' ? 'selected' : ''; ?>>No</option></select></td></tr>
                                    <tr><th>Head Injury</th><td><select name="head_injury"><option value="">Select</option><option value="Yes" <?php echo $getValue('head_injury', $yesNoFromInt($pastMedical->head_injury ?? null)) === 'Yes' ? 'selected' : ''; ?>>Yes</option><option value="No" <?php echo $getValue('head_injury', $yesNoFromInt($pastMedical->head_injury ?? null)) === 'No' ? 'selected' : ''; ?>>No</option></select></td></tr>
                                    <tr><th>Ototoxic Drugs</th><td><select name="ototoxic_drugs"><option value="">Select</option><option value="Yes" <?php echo $getValue('ototoxic_drugs', $yesNoFromInt($pastMedical->ototoxic_drugs ?? null)) === 'Yes' ? 'selected' : ''; ?>>Yes</option><option value="No" <?php echo $getValue('ototoxic_drugs', $yesNoFromInt($pastMedical->ototoxic_drugs ?? null)) === 'No' ? 'selected' : ''; ?>>No</option></select></td></tr>
                                    <tr><th>Previous Ear Surgery</th><td><select name="prev_earSurgery"><option value="">Select</option><option value="Yes" <?php echo $getValue('prev_earSurgery', $yesNoFromInt($pastMedical->prev_earSurgery ?? null)) === 'Yes' ? 'selected' : ''; ?>>Yes</option><option value="No" <?php echo $getValue('prev_earSurgery', $yesNoFromInt($pastMedical->prev_earSurgery ?? null)) === 'No' ? 'selected' : ''; ?>>No</option></select></td></tr>
                                    <tr><th>Previous Noise Exposure</th><td><select name="pre_noiseExposure"><option value="">Select</option><option value="Yes" <?php echo $getValue('pre_noiseExposure', $pastMedical->pre_noiseExposure ?? '') === 'Yes' ? 'selected' : ''; ?>>Yes</option><option value="No" <?php echo $getValue('pre_noiseExposure', $pastMedical->pre_noiseExposure ?? '') === 'No' ? 'selected' : ''; ?>>No</option></select></td></tr>
                                    <tr><th>Significant Hobbies</th><td><select name="significant_hobbies"><option value="">Select</option><option value="Yes" <?php echo $getValue('significant_hobbies', $pastMedical->significant_hobbies ?? '') === 'Yes' ? 'selected' : ''; ?>>Yes</option><option value="No" <?php echo $getValue('significant_hobbies', $pastMedical->significant_hobbies ?? '') === 'No' ? 'selected' : ''; ?>>No</option></select></td></tr>
                                    <tr><th>Otoscopy</th><td><select name="otoscopy"><option value="">Select</option><option value="Normal" <?php echo $getValue('otoscopy', ($pastMedical && $pastMedical->otoscopy !== null) ? ((int) $pastMedical->otoscopy === 1 ? 'Normal' : 'Abnormal') : '') === 'Normal' ? 'selected' : ''; ?>>Normal</option><option value="Abnormal" <?php echo $getValue('otoscopy', ($pastMedical && $pastMedical->otoscopy !== null) ? ((int) $pastMedical->otoscopy === 1 ? 'Normal' : 'Abnormal') : '') === 'Abnormal' ? 'selected' : ''; ?>>Abnormal</option></select></td></tr>
                                    <tr><th>Rinne Test</th><td><div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px"><input type="text" name="audio_rinneRight" placeholder="Right" value="<?php echo $esc($getValue('audio_rinneRight', $pastMedical->audio_rinneRight ?? '')); ?>"><input type="text" name="audio_rinneLeft" placeholder="Left" value="<?php echo $esc($getValue('audio_rinneLeft', $pastMedical->audio_rinneLeft ?? '')); ?>"></div></td></tr>
                                    <tr><th>Weber Test</th><td><select name="audio_weber"><option value="">Select</option><option value="Center" <?php echo $getValue('audio_weber', $pastMedical->audio_weber ?? '') === 'Center' ? 'selected' : ''; ?>>Center</option><option value="Right" <?php echo $getValue('audio_weber', $pastMedical->audio_weber ?? '') === 'Right' ? 'selected' : ''; ?>>Right</option><option value="Left" <?php echo $getValue('audio_weber', $pastMedical->audio_weber ?? '') === 'Left' ? 'selected' : ''; ?>>Left</option></select></td></tr>
                                </table>
                            </section>
                        </div>
                    </section>

                    <section class="card" data-section-index="1">
                        <div class="card-head">
                            <div><h3>Baseline &amp; Annual Audiograph</h3></div>
                            <span class="badge missing">Incomplete</span>
                        </div>

                        <div class="audiograph-grid">
                            <?php foreach (['baseline' => ['title' => 'Baseline Audiograph', 'row' => $baselineAudiograph], 'annual' => ['title' => 'Annual Audiograph', 'row' => $annualAudiograph]] as $prefix => $meta): ?>
                                <section class="audiograph-card">
                                    <div class="audiograph-head"><?php echo $esc($meta['title']); ?></div>
                                    <div class="date-row"><span>Date</span><strong><?php echo $esc($getValue('audioTest_date', $audiometryTest->audioTest_date ?? date('Y-m-d'))); ?></strong></div>
                                    <table class="audio-table">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <?php foreach ($freqs as $hz => $label): ?><th class="freq-head"><?php echo $esc($label); ?></th><?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (['right' => 'RIGHT', 'left' => 'LEFT'] as $sideKey => $sideLabel): ?>
                                                <tr>
                                                    <td class="ear-label"><?php echo $esc($sideLabel); ?></td>
                                                    <?php foreach ($freqs as $hz => $label): ?>
                                                        <?php $suffix = $fieldSuffix($hz); $column = ($sideKey === 'right' ? 'R_' : 'L_') . $suffix; $fieldName = $prefix . '_' . $sideKey . '_' . $hz; ?>
                                                        <td><input type="number" name="<?php echo $esc($fieldName); ?>" value="<?php echo $esc($getValue($fieldName, $meta['row']->{$column} ?? '')); ?>"></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div class="audiograph-head section-band">Diagnostic Bone Conduction</div>
                                    <table class="audio-table">
                                        <tbody>
                                            <?php foreach (['right' => 'RIGHT', 'left' => 'LEFT'] as $sideKey => $sideLabel): ?>
                                                <tr>
                                                    <td class="ear-label"><?php echo $esc($sideLabel); ?></td>
                                                    <?php foreach ($freqs as $hz => $label): ?>
                                                        <?php $suffix = $fieldSuffix($hz); $column = 'bone_' . ($sideKey === 'right' ? 'R' : 'L') . $suffix; $fieldName = $prefix . '_bone_' . $sideKey . '_' . $hz; ?>
                                                        <td><input type="number" name="<?php echo $esc($fieldName); ?>" value="<?php echo $esc($getValue($fieldName, $meta['row']->{$column} ?? '')); ?>"></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div class="audiograph-tools">
                                        <button type="button" class="graph-btn" data-open-graph="<?php echo $esc($prefix); ?>">Plot Graph</button>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <section class="card" data-section-index="2">
                        <div class="card-head">
                            <div><h3>Summary Audiograph</h3></div>
                            <span class="badge <?php echo !empty($sectionStatuses['summary']) ? 'done' : 'missing'; ?>"><?php echo !empty($sectionStatuses['summary']) ? 'Completed' : 'Incomplete'; ?></span>
                        </div>

                        <div class="summary-split">
                            <?php foreach (['Right Audiograph' => [$summaryRightRows, $summaryRightChart], 'Left Audiograph' => [$summaryLeftRows, $summaryLeftChart]] as $title => $bundle): ?>
                                <?php [$rows, $chartSeries] = $bundle; ?>
                                <section class="summary-card">
                                    <div class="summary-head"><?php echo $esc($title); ?></div>
                                    <table class="summary-table">
                                        <thead>
                                            <tr>
                                                <th class="date-head">Date</th>
                                                <?php foreach (array_slice($freqs, 1, null, true) as $hz => $label): ?><th><?php echo $esc($label); ?></th><?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($rows !== []): ?>
                                                <?php foreach ($rows as $index => $row): ?>
                                                    <tr>
                                                        <td class="date-head"><span class="tone-chip" style="color:<?php echo $esc($rowColors[$index % count($rowColors)]); ?>"><?php echo $esc($row['tone']); ?></span><br><?php echo $esc($row['date']); ?></td>
                                                        <?php foreach (array_slice($row['values'], 1, null, true) as $value): ?><td><?php echo $esc($value !== '' ? $value : '-'); ?></td><?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="8">No summary records yet.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="chart-box">
                                        <div class="chart-surface">
                                            <div class="hz-row"><span>500</span><span>1K</span><span>2K</span><span>3K</span><span>4K</span><span>6K</span><span>8K</span></div>
                                            <div class="db-col"><span>-20</span><span>-10</span><span>0</span><span>10</span><span>20</span><span>30</span><span>40</span><span>50</span><span>60</span><span>80</span><span>100</span></div>
                                            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                                                <?php foreach ($chartSeries as $seriesIndex => $series): ?>
                                                    <?php if (count($series) > 1): ?><polyline fill="none" stroke="<?php echo $esc($rowColors[$seriesIndex % count($rowColors)]); ?>" stroke-width="1.8" points="<?php echo $esc(implode(' ', array_map(static fn($point) => $point['x'] . ',' . $point['y'], $series))); ?>"></polyline><?php endif; ?>
                                                    <?php foreach ($series as $point): ?><circle cx="<?php echo $esc($point['x']); ?>" cy="<?php echo $esc($point['y']); ?>" r="1.8" fill="#fff" stroke="<?php echo $esc($rowColors[$seriesIndex % count($rowColors)]); ?>" stroke-width="1.6"></circle><?php endforeach; ?>
                                                <?php endforeach; ?>
                                            </svg>
                                        </div>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="card" data-section-index="3">
                        <div class="card-head">
                            <div><h3>Audiograph Test Comments</h3></div>
                            <span class="badge missing">Incomplete</span>
                        </div>

                        <div class="comment-layout">
                            <section class="comment-card">
                                <div class="comment-head">Comments</div>
                                <table class="comment-grid">
                                    <tr><th>Standard Threshold Shift</th><td><div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px"><label>Right Ear<span class="readonly-box"><?php echo $esc($calculatedAverages['STS_right'] ?? 'No'); ?></span></label><label>Left Ear<span class="readonly-box"><?php echo $esc($calculatedAverages['STS_left'] ?? 'No'); ?></span></label></div></td></tr>
                                    <tr><th>Average 2K, 3K, 4K</th><td><div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px"><label>Right Ear<input type="number" step="0.01" name="average1_right" value="<?php echo $esc($getValue('average1_right', $audioComments->average1_right ?? ($calculatedAverages['average1_right'] ?? 0))); ?>" readonly></label><label>Left Ear<input type="number" step="0.01" name="average1_left" value="<?php echo $esc($getValue('average1_left', $audioComments->average1_left ?? ($calculatedAverages['average1_left'] ?? 0))); ?>" readonly></label></div></td></tr>
                                    <tr><th>Average 0.5, 1K, 2K, 3K</th><td><div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px"><label>Right Ear<input type="number" step="0.01" name="average2_right" value="<?php echo $esc($getValue('average2_right', $audioComments->average2_right ?? ($calculatedAverages['average2_right'] ?? 0))); ?>" readonly></label><label>Left Ear<input type="number" step="0.01" name="average2_left" value="<?php echo $esc($getValue('average2_left', $audioComments->average2_left ?? ($calculatedAverages['average2_left'] ?? 0))); ?>" readonly></label></div></td></tr>
                                    <tr><th>Standard Analysis</th><td><textarea name="standard_analysis"><?php echo $esc($getValue('standard_analysis', $audioComments->standard_analysis ?? '')); ?></textarea></td></tr>
                                    <tr><th>Recommendation</th><td><textarea name="audio_recommendation"><?php echo $esc($getValue('audio_recommendation', $audioComments->audio_recommendation ?? '')); ?></textarea></td></tr>
                                </table>
                            </section>

                            <section class="comment-card">
                                <div class="comment-head">Sign Off</div>
                                <table class="comment-grid">
                                    <tr><th>Done by</th><td><span class="readonly-box"><?php echo $esc($doctorName !== '' ? $doctorName : '-'); ?></span></td></tr>
                                    <tr><th>Date</th><td><span class="readonly-box"><?php echo $esc($getValue('audioTest_date', $audiometryTest->audioTest_date ?? date('Y-m-d'))); ?></span></td></tr>
                                    <tr><th>Reviewed by</th><td><span class="readonly-box"><?php echo $esc($doctorName !== '' ? $doctorName : '-'); ?></span></td></tr>
                                    <tr><th>Remark</th><td><textarea name="remarks"><?php echo $esc($getValue('remarks', $audioComments->remarks ?? '')); ?></textarea></td></tr>
                                </table>
                            </section>
                        </div>
                    </section>
                </div>

                <div class="actions">
                    <div id="examWizardText">Section 1 of 4</div>
                    <div>
                        <a class="btn" id="examPrevLink" href="<?php echo $esc($stepBack); ?>">Back</a>
                        <button class="btn" id="examPrevBtn" type="button" style="display:none;">Previous Section</button>
                        <button class="next" id="examNextBtn" type="button">Next Section</button>
                        <button class="next" id="examSaveBtn" type="submit" style="display:none;">Save &amp; Next</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>
<div class="graph-modal" id="audiographGraphModal" aria-hidden="true">
    <div class="graph-dialog">
        <div class="graph-dialog-head">
            <h3 id="audiographGraphTitle">Audiograph Plot</h3>
            <button type="button" class="graph-close" id="closeAudiographGraph">&times;</button>
        </div>
        <div class="graph-dialog-body">
            <div class="graph-controls">
                <div class="graph-control">
                    <label for="graphAirMode">Air Conduction Symbol</label>
                    <select id="graphAirMode">
                        <option value="baseline">Baseline</option>
                        <option value="unmasked">Unmasked</option>
                        <option value="masked">Masked</option>
                    </select>
                </div>
                <div class="graph-control">
                    <label for="graphBoneMode">Bone Conduction Symbol</label>
                    <select id="graphBoneMode">
                        <option value="unmasked">Unmasked</option>
                        <option value="masked">Masked</option>
                    </select>
                </div>
            </div>
            <div class="chart-surface">
                <svg viewBox="0 0 760 520" preserveAspectRatio="xMidYMid meet" id="audiographGraphSvg"></svg>
            </div>
            <div class="chart-legend">
                <span class="legend-item"><span class="legend-preview" id="legendAirRight"></span><span id="legendAirRightText">RIGHT AIR</span></span>
                <span class="legend-item"><span class="legend-preview" id="legendAirLeft"></span><span id="legendAirLeftText">LEFT AIR</span></span>
                <span class="legend-item"><span class="legend-preview" id="legendBoneRight"></span><span id="legendBoneRightText">RIGHT BONE</span></span>
                <span class="legend-item"><span class="legend-preview" id="legendBoneLeft"></span><span id="legendBoneLeftText">LEFT BONE</span></span>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
var cards=Array.prototype.slice.call(document.querySelectorAll('.card[data-section-index]'));
var navItems=Array.prototype.slice.call(document.querySelectorAll('.status-item[data-audio-section]'));
var prevBtn=document.getElementById('examPrevBtn');
var nextBtn=document.getElementById('examNextBtn');
var saveBtn=document.getElementById('examSaveBtn');
var prevLink=document.getElementById('examPrevLink');
var text=document.getElementById('examWizardText');
var form=document.getElementById('audiometryExamForm');
var graphModal=document.getElementById('audiographGraphModal');
var graphSvg=document.getElementById('audiographGraphSvg');
var graphTitle=document.getElementById('audiographGraphTitle');
var closeGraphBtn=document.getElementById('closeAudiographGraph');
var openGraphButtons=Array.prototype.slice.call(document.querySelectorAll('[data-open-graph]'));
var graphAirMode=document.getElementById('graphAirMode');
var graphBoneMode=document.getElementById('graphBoneMode');
var legendAirRight=document.getElementById('legendAirRight');
var legendAirLeft=document.getElementById('legendAirLeft');
var legendBoneRight=document.getElementById('legendBoneRight');
var legendBoneLeft=document.getElementById('legendBoneLeft');
var legendBoneRightText=document.getElementById('legendBoneRightText');
var legendBoneLeftText=document.getElementById('legendBoneLeftText');
var graphState={
    baseline:{air:'baseline',bone:'unmasked'},
    annual:{air:'baseline',bone:'unmasked'}
};
var activeGraphPrefix='baseline';
var current=0;
if(!cards.length||!form){return;}
function sync(){cards.forEach(function(card,index){card.classList.toggle('is-active',index===current);});navItems.forEach(function(item,index){item.classList.toggle('is-current',index===current);});if(prevBtn){prevBtn.style.display=current>0?'inline-flex':'none';}if(prevLink){prevLink.style.display=current===0?'inline-flex':'none';}if(nextBtn){nextBtn.style.display=current<cards.length-1?'inline-flex':'none';}if(saveBtn){saveBtn.style.display=current===cards.length-1?'inline-flex':'none';}if(text){text.textContent='Section '+(current+1)+' of '+cards.length;}}
function setSectionStatus(index, done){var item=navItems[index];var card=cards[index];if(item){var icon=item.querySelector('.status-icon');var label=item.querySelector('.status-text span');if(icon){icon.classList.remove('ok','bad');icon.classList.add(done?'ok':'bad');icon.innerHTML=done?'&#10003;':'!';}if(label){label.textContent=done?'Completed':'Incomplete';}}if(card){var badge=card.querySelector('.badge');if(badge){badge.classList.remove('done','missing');badge.classList.add(done?'done':'missing');badge.textContent=done?'Completed':'Incomplete';}}}
function fieldValue(selector){var element=form.querySelector(selector);if(!element){return '';}return (element.value||'').trim();}
function informationComplete(){return fieldValue('select[name="type_audiogram"]')!==''&&fieldValue('input[name="total_years_working"]')!==''&&fieldValue('input[name="noYears_working"]')!==''&&fieldValue('input[name="audiometer"]')!==''&&fieldValue('input[name="calibration_date"]')!==''&&fieldValue('input[name="audioTest_date"]')!==''&&fieldValue('input[name="seg"]')!==''&&fieldValue('input[name="exposure_lex"]')!==''&&fieldValue('select[name="ear_infections"]')!==''&&fieldValue('select[name="head_injury"]')!==''&&fieldValue('select[name="ototoxic_drugs"]')!==''&&fieldValue('select[name="prev_earSurgery"]')!==''&&fieldValue('select[name="pre_noiseExposure"]')!==''&&fieldValue('select[name="significant_hobbies"]')!==''&&fieldValue('select[name="otoscopy"]')!==''&&fieldValue('input[name="audio_rinneRight"]')!==''&&fieldValue('input[name="audio_rinneLeft"]')!==''&&fieldValue('select[name="audio_weber"]')!=='';}
function audiographFilled(prefix){return ['250','500','1000','2000','3000','4000','6000','8000'].every(function(freq){return fieldValue('input[name="'+prefix+'_right_'+freq+'"]')!==''&&fieldValue('input[name="'+prefix+'_left_'+freq+'"]')!==''&&fieldValue('input[name="'+prefix+'_bone_right_'+freq+'"]')!==''&&fieldValue('input[name="'+prefix+'_bone_left_'+freq+'"]')!=='';});}
function baselineComplete(){return audiographFilled('baseline')&&audiographFilled('annual');}
function summaryComplete(){return baselineComplete();}
function commentsComplete(){return fieldValue('textarea[name="standard_analysis"]')!==''&&fieldValue('textarea[name="audio_recommendation"]')!==''&&fieldValue('textarea[name="remarks"]')!=='';}
function refreshVisibleStatuses(){setSectionStatus(0,informationComplete());setSectionStatus(1,baselineComplete());setSectionStatus(2,summaryComplete());setSectionStatus(3,commentsComplete());}
var frequencyOrder=['250','500','1000','2000','3000','4000','6000','8000'];
var frequencyLabels={'250':'250','500':'500','1000':'1K','2000':'2K','3000':'3K','4000':'4K','6000':'6K','8000':'8K'};
var dbLevels=[-20,-10,0,10,20,30,40,50,60,70,80,90,100];
var svgNs='http://www.w3.org/2000/svg';
function svgNode(name, attrs){
    var node=document.createElementNS(svgNs,name);
    Object.keys(attrs||{}).forEach(function(key){ node.setAttribute(key, attrs[key]); });
    return node;
}
function plotX(index){
    var left=84;
    var right=720;
    return left + ((right-left) / (frequencyOrder.length - 1)) * index;
}
function plotY(value){
    var top=54;
    var bottom=474;
    var clamped=Math.max(-20, Math.min(100, parseFloat(value)));
    return top + ((clamped + 20) / 120) * (bottom-top);
}
function buildSeries(prefix, group, color, markerType, dashed){
    var points=[];
    frequencyOrder.forEach(function(freq, index){
        var value=fieldValue('input[name="'+prefix+'_'+group+'_'+freq+'"]');
        if(value===''||isNaN(value)){return;}
        points.push({x:plotX(index), y:plotY(value), raw:parseFloat(value)});
    });
    return {color:color, markerType:markerType, dashed:!!dashed, points:points};
}
function drawMarker(parent, point, color, markerType){
    if(markerType==='air-right'){
        parent.appendChild(svgNode('circle',{cx:point.x, cy:point.y, r:10, fill:'#fff', stroke:color, 'stroke-width':3}));
        return;
    }
    if(markerType==='air-right-baseline'){
        parent.appendChild(svgNode('circle',{cx:point.x, cy:point.y, r:10, fill:'#fff', stroke:color, 'stroke-width':3}));
        return;
    }
    if(markerType==='air-right-masked'){
        parent.appendChild(svgNode('circle',{cx:point.x, cy:point.y, r:9, fill:color, stroke:color, 'stroke-width':2.5}));
        return;
    }
    if(markerType==='air-left'){
        parent.appendChild(svgNode('line',{x1:point.x-8, y1:point.y-8, x2:point.x+8, y2:point.y+8, stroke:color, 'stroke-width':3}));
        parent.appendChild(svgNode('line',{x1:point.x-8, y1:point.y+8, x2:point.x+8, y2:point.y-8, stroke:color, 'stroke-width':3}));
        return;
    }
    if(markerType==='air-left-masked'){
        parent.appendChild(svgNode('polygon',{points:[(point.x-8)+','+(point.y-9),(point.x+8)+','+(point.y-9),point.x+','+point.y,(point.x+8)+','+(point.y+9),(point.x-8)+','+(point.y+9),point.x+','+point.y].join(' '), fill:color, stroke:color, 'stroke-width':2.5}));
        return;
    }
    if(markerType==='air-left-baseline'){
        parent.appendChild(svgNode('line',{x1:point.x-8, y1:point.y-8, x2:point.x+8, y2:point.y+8, stroke:color, 'stroke-width':3}));
        parent.appendChild(svgNode('line',{x1:point.x-8, y1:point.y+8, x2:point.x+8, y2:point.y-8, stroke:color, 'stroke-width':3}));
        return;
    }
    if(markerType==='bone-right-baseline'){
        parent.appendChild(svgNode('polygon',{points:[point.x+','+(point.y-10),(point.x+10)+','+point.y,point.x+','+(point.y+10),(point.x-10)+','+point.y].join(' '), fill:'#fff', stroke:color, 'stroke-width':3}));
        return;
    }
    if(markerType==='bone-left-baseline'){
        parent.appendChild(svgNode('polygon',{points:[point.x+','+(point.y-10),(point.x+10)+','+(point.y+10),(point.x-10)+','+(point.y+10)].join(' '), fill:'#fff', stroke:color, 'stroke-width':3}));
        return;
    }
    if(markerType==='bone-right-masked'){
        var bracketRight=svgNode('text',{x:point.x,y:point.y+7,'text-anchor':'middle','font-size':'30','font-weight':'800',fill:color});
        bracketRight.textContent='[';
        parent.appendChild(bracketRight);
        return;
    }
    if(markerType==='bone-left-masked'){
        var bracketLeft=svgNode('text',{x:point.x,y:point.y+7,'text-anchor':'middle','font-size':'30','font-weight':'800',fill:color});
        bracketLeft.textContent=']';
        parent.appendChild(bracketLeft);
        return;
    }
    var textNode=svgNode('text',{x:point.x,y:point.y+5,'text-anchor':'middle','font-size':'28','font-weight':'800',fill:color});
    textNode.textContent=markerType==='bone-right-annual' ? '<' : '>';
    parent.appendChild(textNode);
}
function drawSeries(parent, series){
    if(series.points.length>1){
        var polyAttrs={fill:'none', stroke:series.color, 'stroke-width':3, points:series.points.map(function(point){return point.x+','+point.y;}).join(' ')};
        if(series.dashed){polyAttrs['stroke-dasharray']='5 7';}
        parent.appendChild(svgNode('polyline', polyAttrs));
    }
    series.points.forEach(function(point){drawMarker(parent, point, series.color, series.markerType);});
}
function renderLegendSymbol(target, color, markerType, dashed){
    if(!target){return;}
    target.innerHTML='';
    var svg=svgNode('svg',{viewBox:'0 0 90 24','aria-hidden':'true'});
    var lineAttrs={x1:'10',y1:'12',x2:'80',y2:'12',stroke:color,'stroke-width':'2.4','stroke-linecap':'round'};
    if(dashed){lineAttrs['stroke-dasharray']='4 5';}
    svg.appendChild(svgNode('line', lineAttrs));
    drawMarker(svg, {x:45, y:12}, color, markerType);
    target.appendChild(svg);
}
function currentAirTypes(prefix){
    var mode=(graphState[prefix]&&graphState[prefix].air) || 'baseline';
    if(mode==='masked'){
        return {right:'air-right-masked',left:'air-left-masked'};
    }
    if(mode==='baseline'){
        return {right:'air-right-baseline',left:'air-left-baseline'};
    }
    return {right:'air-right',left:'air-left'};
}
function currentBoneTypes(prefix){
    var mode=(graphState[prefix]&&graphState[prefix].bone) || 'unmasked';
    if(mode==='masked'){
        return {right:'bone-right-masked',left:'bone-left-masked',dashed:false};
    }
    if(prefix==='baseline'){
        return {right:'bone-right-annual',left:'bone-left-annual',dashed:true};
    }
    return {right:'bone-right-annual',left:'bone-left-annual',dashed:true};
}
function renderGraph(prefix){
    if(!graphSvg){return;}
    activeGraphPrefix=prefix;
    if(graphTitle){graphTitle.textContent=(prefix==='baseline'?'Baseline':'Annual')+' Audiograph Plot';}
    graphSvg.innerHTML='';
    var width=760;
    var height=520;
    var plotLeft=84;
    var plotRight=720;
    var plotTop=54;
    var plotBottom=474;
    var axisColor='#cbd5e1';
    var labelColor='#475569';

    graphSvg.appendChild(svgNode('rect',{x:'1',y:'1',width:String(width-2),height:String(height-2),rx:'14',fill:'#fff',stroke:'#d6dee7'}));

    dbLevels.forEach(function(level){
        var y=plotY(level);
        graphSvg.appendChild(svgNode('line',{x1:String(plotLeft),y1:String(y),x2:String(plotRight),y2:String(y),stroke:axisColor,'stroke-width':'1'}));
        var dbText=svgNode('text',{x:String(plotLeft-18),y:String(y+5),'text-anchor':'end','font-size':'16','font-weight':'700',fill:labelColor});
        dbText.textContent=String(level);
        graphSvg.appendChild(dbText);
    });
    frequencyOrder.forEach(function(freq, index){
        var x=plotX(index);
        graphSvg.appendChild(svgNode('line',{x1:String(x),y1:String(plotTop),x2:String(x),y2:String(plotBottom),stroke:axisColor,'stroke-width':'1'}));
        var freqText=svgNode('text',{x:String(x),y:'30','text-anchor':'middle','font-size':'16','font-weight':'700',fill:labelColor});
        freqText.textContent=frequencyLabels[freq];
        graphSvg.appendChild(freqText);
    });

    graphSvg.appendChild(svgNode('line',{x1:String(plotLeft),y1:String(plotTop),x2:String(plotLeft),y2:String(plotBottom),stroke:'#94a3b8','stroke-width':'1.5'}));
    graphSvg.appendChild(svgNode('line',{x1:String(plotLeft),y1:String(plotBottom),x2:String(plotRight),y2:String(plotBottom),stroke:'#94a3b8','stroke-width':'1.5'}));

    var airTypes=currentAirTypes(prefix);
    var boneTypes=currentBoneTypes(prefix);
    var seriesList=[
        buildSeries(prefix,'right',graphState[prefix].air==='masked' ? '#dc2626' : (graphState[prefix].air==='baseline' ? '#dc2626' : '#7f1d1d'),airTypes.right,false),
        buildSeries(prefix,'left',graphState[prefix].air==='masked' ? '#2563eb' : (graphState[prefix].air==='baseline' ? '#2563eb' : '#111827'),airTypes.left,false),
        buildSeries(prefix,'bone_right','#7f1d1d',boneTypes.right,boneTypes.dashed),
        buildSeries(prefix,'bone_left','#1f2937',boneTypes.left,boneTypes.dashed)
    ];
    seriesList.forEach(function(series){drawSeries(graphSvg, series);});

    renderLegendSymbol(legendAirRight, graphState[prefix].air==='masked' ? '#dc2626' : (graphState[prefix].air==='baseline' ? '#dc2626' : '#7f1d1d'), airTypes.right, false);
    renderLegendSymbol(legendAirLeft, graphState[prefix].air==='masked' ? '#2563eb' : (graphState[prefix].air==='baseline' ? '#2563eb' : '#111827'), airTypes.left, false);
    renderLegendSymbol(legendBoneRight, '#7f1d1d', boneTypes.right, boneTypes.dashed);
    renderLegendSymbol(legendBoneLeft, '#1f2937', boneTypes.left, boneTypes.dashed);
    if(graphAirMode){graphAirMode.value=(graphState[prefix]&&graphState[prefix].air) || 'baseline';}
    if(graphBoneMode){graphBoneMode.value=(graphState[prefix]&&graphState[prefix].bone) || 'unmasked';}
    if(legendBoneRightText){legendBoneRightText.textContent='RIGHT BONE';}
    if(legendBoneLeftText){legendBoneLeftText.textContent='LEFT BONE';}
}
function openGraph(prefix){
    renderGraph(prefix);
    openGraphButtons.forEach(function(button){
        button.classList.toggle('is-open-source', button.getAttribute('data-open-graph')===prefix);
    });
    if(graphModal){
        graphModal.classList.add('is-open');
        graphModal.setAttribute('aria-hidden','false');
    }
}
function closeGraph(){
    openGraphButtons.forEach(function(button){button.classList.remove('is-open-source');});
    if(graphModal){
        graphModal.classList.remove('is-open');
        graphModal.setAttribute('aria-hidden','true');
    }
}
navItems.forEach(function(item,index){item.addEventListener('click',function(){current=index;sync();});});
if(prevBtn){prevBtn.addEventListener('click',function(){if(current>0){current-=1;sync();}});} 
if(nextBtn){nextBtn.addEventListener('click',function(){if(current<cards.length-1){current+=1;sync();}});} 
openGraphButtons.forEach(function(button){button.addEventListener('click',function(){openGraph(button.getAttribute('data-open-graph'));});});
if(closeGraphBtn){closeGraphBtn.addEventListener('click',closeGraph);}
if(graphModal){graphModal.addEventListener('click',function(event){if(event.target===graphModal){closeGraph();}});}
if(graphAirMode){graphAirMode.addEventListener('change',function(){graphState[activeGraphPrefix].air=this.value;renderGraph(activeGraphPrefix);});}
if(graphBoneMode){graphBoneMode.addEventListener('change',function(){graphState[activeGraphPrefix].bone=this.value;renderGraph(activeGraphPrefix);});}
form.addEventListener('input',refreshVisibleStatuses);form.addEventListener('change',refreshVisibleStatuses);
form.addEventListener('input',function(event){if(graphModal&&graphModal.classList.contains('is-open')){var activeGraph=document.querySelector('[data-open-graph].is-open-source');if(activeGraph){renderGraph(activeGraph.getAttribute('data-open-graph'));}}});
form.addEventListener('submit',function(event){if(!(informationComplete()&&baselineComplete()&&commentsComplete())){event.preventDefault();alert('Please complete all required audiometry sections before saving.');}});
refreshVisibleStatuses();sync();
})();
</script>
<?php medis_render_navigation_end(); ?>
</body>
</html>



