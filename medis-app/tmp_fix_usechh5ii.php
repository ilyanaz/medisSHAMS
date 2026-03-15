<?php
$path = 'C:\xampp\htdocs\medisSHAMS\medis-app\routes\web.php';
$c = file_get_contents($path);

$needle = <<<'TXT'
                    $surveillanceRows[] = [
                        'module' => 'surveillance', 'filter' => 'usechh 5ii', 'employee_name' => $employeeName !== '' ? $employeeName : 'Not set', 'company' => $companyName !== '' ? $companyName : '-', 'chemical_name' => $chemicalName !== '' ? $chemicalName : '-', 'status' => 'N/A', 'status_key' => 'na', 'date_examined' => $dateExamined, 'href' => route('surveillance.report.abnormal', $query), 'pdf_href' => route('pdf.usechh5ii', $query),
                    ];
TXT;
$c = str_replace($needle, '', $c);

$oldEnd = <<<'TXT'
                }
                $data['surveillanceReportRows'] = $surveillanceRows;
TXT;
$newEnd = <<<'TXT'
                }
                $usechh5iiGroups = $declarations
                    ->filter(static fn ($record) => strcasecmp((string) ($record->fitness_result ?? ''), 'Not Fit') === 0)
                    ->groupBy(static function ($record) {
                        return implode('|', [
                            (string) ($record->company_id ?? 0),
                            trim((string) ($record->chemicals ?? '')),
                            (string) ($record->examination_date ?: ($record->doctor_date ?: ($record->employee_date ?: ''))),
                        ]);
                    });
                foreach ($usechh5iiGroups as $group) {
                    $record = $group->first();
                    $companyName = (string) ($record->company_name ?? '-');
                    $chemicalName = (string) ($record->chemicals ?? '-');
                    $dateExamined = $record->examination_date ?: ($record->doctor_date ?: ($record->employee_date ?: date('Y-m-d')));
                    $query = [
                        'declaration_id' => $record->declaration_id,
                        'company_id' => $record->company_id,
                        'surveillance_id' => $record->surveillance_id,
                        'chemical_name' => $chemicalName,
                        'date_examined' => $dateExamined,
                    ];
                    $surveillanceRows[] = [
                        'module' => 'surveillance', 'filter' => 'usechh 5ii', 'employee_name' => $companyName !== '' ? $companyName : 'Not set', 'company' => $companyName !== '' ? $companyName : '-', 'chemical_name' => $chemicalName !== '' ? $chemicalName : '-', 'status' => 'N/A', 'status_key' => 'na', 'date_examined' => $dateExamined, 'href' => route('surveillance.report.abnormal', $query), 'pdf_href' => route('pdf.usechh5ii', $query),
                    ];
                }
                $data['surveillanceReportRows'] = $surveillanceRows;
TXT;
$c = str_replace($oldEnd, $newEnd, $c);

if (strpos($c, "if (4view === 'auth.surveillance_abnormalReport')") === false) {
    $oldRemoval = <<<'TXT'
                if ($view === 'auth.surveillance_removalReport') {
                    $employeeId = (int) ($data['selectedEmployee']->employee_id ?? 0);
                    $surveillanceId = (int) ($data['surveillanceId'] ?? 0);
                    $data['fitnessReportRow'] = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('fitness_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
                    $data['existingRemoval'] = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('removal_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
                }
TXT;
    $newRemoval = <<<'TXT'
                if ($view === 'auth.surveillance_removalReport') {
                    $employeeId = (int) ($data['selectedEmployee']->employee_id ?? 0);
                    $surveillanceId = (int) ($data['surveillanceId'] ?? 0);
                    $data['fitnessReportRow'] = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('fitness_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
                    $data['existingRemoval'] = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('removal_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
                }
                if ($view === 'auth.surveillance_abnormalReport') {
                    $companyId = (int) (request()->query('company_id') ?? ($data['selectedCompany']->company_id ?? 0));
                    $chemicalLabel = trim((string) (request()->query('chemical_name') ?? ($data['chemicalInfo']->chemicals ?? '')));
                    $cutoffDate = request()->query('date_examined') ?? ($data['chemicalInfo']->examination_date ?? null);
                    $data['companyLabel'] = trim((string) ($data['selectedCompany']->company_name ?? ''));
                    $data['chemicalLabel'] = $chemicalLabel;
                    $data['dateExamined'] = $cutoffDate;
                    $data['printParams'] = array_filter([
                        'declaration_id' => request()->query('declaration_id'),
                        'company_id' => $companyId,
                        'surveillance_id' => request()->query('surveillance_id'),
                        'chemical_name' => $chemicalLabel,
                        'date_examined' => $cutoffDate,
                    ], static fn ($value) => !empty($value));

                    $abnormalRows = collect();
                    if ($companyId > 0 && $chemicalLabel !== '' && !empty($cutoffDate)) {
                        $abnormalRows = DB::table('declaration as d')
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
                            ->whereDate('ci.examination_date', $cutoffDate)
                            ->where('ms.conclusion_fitness', 'Not Fit')
                            ->orderBy('e.employee_firstName')
                            ->orderBy('e.employee_lastName')
                            ->get([
                                'e.employee_firstName', 'e.employee_lastName', 'e.employee_NRIC', 'e.employee_passportNo', 'e.employee_sex',
                                'ci.examination_type', 'ci.company_designation',
                                'ms.history_of_health', 'ms.clinical_findings as ms_clinical_findings', 'ms.CF_work_related', 'ms.TO_work_related', 'ms.BM_work_related', 'ms.conclusion_fitness',
                                'cf.result_clinical_findings',
                                't.blood_count', 't.renal_function', 't.liver_function', 't.chest_xray', 't.spirometry_FEV1', 't.spirometry_FVC', 't.spirometry_FEV_FVC',
                                'bm.biological_exposure', 'bm.baseline_results', 'bm.baseline_annual',
                                'r.MRPdate_start', 'r.need_treatment', 'r.need_refer', 'r.need_notification',
                            ]);
                    }

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
                        if (!empty($row->need_treatment)) { $parts[] = 'Treatment'; }
                        if (!empty($row->need_refer)) { $parts[] = 'Referral'; }
                        if (!empty($row->need_notification)) { $parts[] = 'Notification'; }
                        return $parts === [] ? 'N/A' : implode(', ', $parts);
                    };
                    $data['abnormalRows'] = $abnormalRows->map(static function ($row) use ($formatTargetOrgan, $formatWorkRelated, $formatBei, $formatRecommendation) {
                        $employeeName = trim((string) (($row->employee_firstName ?? '') . ' ' . ($row->employee_lastName ?? '')));
                        $identity = trim((string) (($row->employee_NRIC ?? '') !== '' ? ($row->employee_NRIC ?? '') : ($row->employee_passportNo ?? '')));
                        $clinical = trim((string) (($row->ms_clinical_findings ?? $row->result_clinical_findings ?? '')));
                        $history = trim((string) ($row->history_of_health ?? ''));
                        return [
                            'employee_name' => $employeeName !== '' ? $employeeName : 'N/A',
                            'identity_no' => $identity !== '' ? $identity : 'N/A',
                            'sex' => trim((string) ($row->employee_sex ?? '')) ?: 'N/A',
                            'designation' => trim((string) ($row->company_designation ?? '')) ?: 'N/A',
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
TXT;
    $c = str_replace($oldRemoval, $newRemoval, $c);
}

file_put_contents($path, $c);
