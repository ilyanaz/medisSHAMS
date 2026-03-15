<?php

use App\Http\Controllers\Auth\LoginController;
use App\Models\Clinic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

$resolveLogoUrl = static function (?string $logo): ?string {
    if (! $logo) {
        return null;
    }

    if (filter_var($logo, FILTER_VALIDATE_URL)) {
        return $logo;
    }

    $normalized = ltrim($logo, '/');

    if (file_exists(public_path($normalized))) {
        return asset($normalized);
    }

    if (file_exists(storage_path('app/public/'.$normalized))) {
        return asset('storage/'.$normalized);
    }

    return asset($normalized);
};

$resolveDoctorProfile = static function () {
    $user = Auth::user();
    $username = $user->username ?? null;

    if ($username) {
        $doctor = DB::table('doctor')->where('doctor_username', $username)->first();
        if ($doctor) {
            return $doctor;
        }
    }

    return DB::table('doctor')->orderBy('doctor_id')->first();
};

$ensureDoctorProfile = static function () use ($resolveDoctorProfile) {
    $doctor = $resolveDoctorProfile();
    if ($doctor) {
        return $doctor;
    }

    $user = Auth::user();
    $baseUsername = $user->username ?? 'system_doctor';
    $username = $baseUsername;
    $suffix = 1;

    while (DB::table('doctor')->where('doctor_username', $username)->exists()) {
        $username = $baseUsername.'_'.$suffix;
        $suffix++;
    }

    $doctorId = DB::table('doctor')->insertGetId([
        'doctor_firstName' => 'System',
        'doctor_lastName' => 'Doctor',
        'doctor_username' => $username,
        'doctor_password' => Hash::make('password'),
        'doctor_sign' => '',
        'doctor_picture' => '',
        'doctor_email' => $user->username ?? null,
    ]);

    return DB::table('doctor')->where('doctor_id', $doctorId)->first();
};

$resolveSettingsUser = static function () {
    $authUser = Auth::user();

    if (! $authUser) {
        return null;
    }

    return DB::table('users')->where('user_id', $authUser->user_id)->first();
};

$resolveViewData = static function () use ($resolveLogoUrl, $resolveDoctorProfile, $resolveSettingsUser): array {
    $clinic = class_exists(Clinic::class) ? Clinic::query()->first() : null;
    $doctor = $resolveDoctorProfile();
    $user = $resolveSettingsUser();
    $username = $user->username ?? $doctor->doctor_username ?? 'User';

    return [
        'clinicName' => $clinic?->clinic_name ?: 'Medis SHAMS',
        'clinicLogoUrl' => !empty($clinic?->clinic_logo) ? $resolveLogoUrl($clinic?->clinic_logo) : (!empty($clinic?->clinic_header) ? 'data:image/png;base64,'.base64_encode($clinic->clinic_header) : null),
        'username' => $username,
        'companies' => DB::table('company')->orderBy('company_name')->get(),
        'doctorProfile' => $doctor,
        'settingsData' => $user,
    ];
};

$storeUserAsset = static function (Request $request, string $field, string $column, string $folder, string $statusMessage) use ($resolveSettingsUser) {
    $user = $resolveSettingsUser();
    if (! $user) {
        return redirect()->route('login')->withErrors(['auth' => 'Please log in first.']);
    }

    $validated = $request->validate([
        $field => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
    ]);

    $file = $validated[$field];
    $targetDir = public_path('uploads/'.$folder);
    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $oldPath = $user->{$column} ?? null;
    if ($oldPath) {
        $oldFullPath = public_path(ltrim($oldPath, '/'));
        if (is_file($oldFullPath)) {
            @unlink($oldFullPath);
        }
    }

    $filename = $user->username.'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
    $file->move($targetDir, $filename);
    $relativePath = 'uploads/'.$folder.'/'.$filename;

    DB::table('users')->where('user_id', $user->user_id)->update([$column => $relativePath]);

    return redirect()->back()->with('status', $statusMessage);
};

$decodeSignatureDataUrl = static function (?string $value): ?string {
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    if (preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/', $value, $matches)) {
        $decoded = base64_decode(str_replace(' ', '+', $matches[2]), true);
        return $decoded !== false ? $decoded : null;
    }

    return $value;
};

$storeUserBase64Asset = static function (Request $request, string $field, string $column, string $folder, string $statusMessage) use ($resolveSettingsUser, $decodeSignatureDataUrl) {
    $user = $resolveSettingsUser();
    if (! $user) {
        return redirect()->route('login')->withErrors(['auth' => 'Please log in first.']);
    }

    $validated = $request->validate([
        $field => ['required', 'string'],
    ]);

    $binary = $decodeSignatureDataUrl($validated[$field] ?? null);
    if (! $binary) {
        return redirect()->back()->withErrors([$field => 'Invalid signature data.'])->withInput();
    }

    $targetDir = public_path('uploads/'.$folder);
    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $oldPath = $user->{$column} ?? null;
    if ($oldPath) {
        $oldFullPath = public_path(ltrim($oldPath, '/'));
        if (is_file($oldFullPath)) {
            @unlink($oldFullPath);
        }
    }

    $filename = $user->username.'_'.Str::random(8).'.png';
    $relativePath = 'uploads/'.$folder.'/'.$filename;
    file_put_contents(public_path($relativePath), $binary);

    DB::table('users')->where('user_id', $user->user_id)->update([$column => $relativePath]);
    DB::table('doctor')->where('doctor_username', $user->username)->update(['doctor_sign' => $binary]);

    return redirect()->back()->with('status', $statusMessage);
};

$deleteUserAsset = static function (string $column, string $statusMessage) use ($resolveSettingsUser) {
    $user = $resolveSettingsUser();
    if (! $user) {
        return redirect()->route('login')->withErrors(['auth' => 'Please log in first.']);
    }

    $oldPath = $user->{$column} ?? null;
    if ($oldPath) {
        $oldFullPath = public_path(ltrim($oldPath, '/'));
        if (is_file($oldFullPath)) {
            @unlink($oldFullPath);
        }
    }

    DB::table('users')->where('user_id', $user->user_id)->update([$column => null]);

    return redirect()->back()->with('status', $statusMessage);
};
$combinePhone = static function (?string $code, ?string $number): ?string {
    $code = trim((string) $code);
    $number = trim((string) $number);

    if ($code === '' && $number === '') {
        return null;
    }

    return trim($code.' '.$number);
};

$syncLatestCompany = static function (array $rows): ?int {
    $latest = null;

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $companyName = trim((string) ($row['company_name'] ?? ''));
        if ($companyName === '') {
            continue;
        }

        $latest = [
            'company_name' => $companyName,
            'job_title' => trim((string) ($row['job_title'] ?? '')),
            'employment_duration' => trim((string) ($row['employment_duration'] ?? '')),
            'chemical_exposure_duration' => trim((string) ($row['chemical_exposure_duration'] ?? '')),
            'chemical_exposure_incidents' => trim((string) ($row['chemical_exposure_incidents'] ?? '')),
        ];
    }

    if (! $latest) {
        return null;
    }

    $existing = DB::table('company')->where('company_name', $latest['company_name'])->orderByDesc('company_id')->first();
    if ($existing) {
        return (int) $existing->company_id;
    }

    return (int) DB::table('company')->insertGetId([
        'company_name' => $latest['company_name'],
        'mykpp_registration_no' => null,
        'company_address' => null,
        'company_postcode' => null,
        'company_district' => null,
        'company_state' => null,
        'company_telephone' => null,
        'company_email' => null,
        'company_fax' => null,
        'total_workers' => 0,
    ]);
};
$refreshCompanyWorkerTotals = static function (): void {
    $currentCompanyCounts = DB::table('occupational_history as oh')
        ->join(DB::raw('(SELECT employee_id, MIN(occupHistory_id) AS first_occup_history_id FROM occupational_history GROUP BY employee_id) as first_occ'), 'oh.occupHistory_id', '=', 'first_occ.first_occup_history_id')
        ->whereNotNull('oh.company_name')
        ->where('oh.company_name', '!=', '')
        ->select('oh.company_name', DB::raw('COUNT(DISTINCT oh.employee_id) as total_workers'))
        ->groupBy('oh.company_name')
        ->get()
        ->keyBy('company_name');

    DB::table('company')->get(['company_id', 'company_name'])->each(function ($company) use ($currentCompanyCounts) {
        $countRow = $currentCompanyCounts->get($company->company_name);
        DB::table('company')->where('company_id', $company->company_id)->update([
            'total_workers' => (int) ($countRow->total_workers ?? 0),
        ]);
    });
};
$resolveChemicalOptions = static function (): array {
    $presetChemicals = [
        'Lead (Inorganic & Organic)',
        'Organophosphate pesticides',
        'Benzene',
        'Carbon Disulphide',
        'n-Hexane',
        'Trichloroethylene',
        'Arsenic (inorganic)',
        'Cadmium',
        'Chromium VI',
        'Mercury',
        'Nickel',
        'Manganese',
        'Toluene',
        'Xylene',
    ];

    $savedChemicals = DB::table('chemical_information')
        ->whereNotNull('chemicals')
        ->where('chemicals', '!=', '')
        ->distinct()
        ->orderBy('chemicals')
        ->pluck('chemicals')
        ->all();

    $allChemicals = array_values(array_unique(array_filter(array_merge($presetChemicals, $savedChemicals))));
    natcasesort($allChemicals);

    return array_values($allChemicals);
};
$upsertRow = static function (string $table, array $keys, array $attributes, string $primaryKey) {
    static $primaryKeyMeta = [];

    $query = DB::table($table);
    foreach ($keys as $column => $value) {
        $query->where($column, $value);
    }

    $existing = $query->first();
    if ($existing) {
        DB::table($table)->where($primaryKey, $existing->{$primaryKey})->update($attributes);
        return $existing->{$primaryKey};
    }

    $insertData = array_merge($keys, $attributes);
    $metaKey = $table.'|'.$primaryKey;
    if (! array_key_exists($metaKey, $primaryKeyMeta)) {
        $safePrimaryKey = str_replace("'", "''", $primaryKey);
        $column = collect(DB::select("SHOW COLUMNS FROM `{$table}` LIKE '{$safePrimaryKey}'"))->first();
        $primaryKeyMeta[$metaKey] = [
            'autoIncrement' => $column ? str_contains(strtolower((string) ($column->Extra ?? '')), 'auto_increment') : false,
        ];
    }

    if (! ($primaryKeyMeta[$metaKey]['autoIncrement'] ?? false) && ! array_key_exists($primaryKey, $insertData)) {
        $insertData[$primaryKey] = ((int) DB::table($table)->max($primaryKey)) + 1;
        DB::table($table)->insert($insertData);
        return $insertData[$primaryKey];
    }

    return DB::table($table)->insertGetId($insertData);
};
$buildUsechh5iiData = static function (?int $companyId, ?string $chemicalName, $dateExamined, ?int $surveillanceId = null): array {
    $companyId = (int) ($companyId ?? 0);
    $chemicalName = trim((string) ($chemicalName ?? ''));
    $dateExamined = $dateExamined ? date('Y-m-d', strtotime((string) $dateExamined)) : null;
    $surveillanceId = (int) ($surveillanceId ?? 0);

    if ($companyId <= 0 && $surveillanceId > 0) {
        $fallback = DB::table('chemical_information')->where('surveillance_id', $surveillanceId)->first(['company_id', 'chemicals', 'examination_date']);
        if ($fallback) {
            $companyId = (int) ($fallback->company_id ?? 0);
            if ($chemicalName === '') {
                $chemicalName = trim((string) ($fallback->chemicals ?? ''));
            }
            if (empty($dateExamined) && !empty($fallback->examination_date)) {
                $dateExamined = date('Y-m-d', strtotime((string) $fallback->examination_date));
            }
        }
    }

    $companyLabel = '';
    if ($companyId > 0) {
        $companyLabel = trim((string) (DB::table('company')->where('company_id', $companyId)->value('company_name') ?? ''));
    }

    $rawRows = collect();
    if ($companyId > 0 && $chemicalName !== '' && !empty($dateExamined)) {
        $rawRows = DB::table('declaration as d')
            ->join('employee as e', 'd.employee_id', '=', 'e.employee_id')
            ->join('chemical_information as ci', function ($join) {
                $join->on('d.employee_id', '=', 'ci.employee_id')
                    ->on('d.surveillance_id', '=', 'ci.surveillance_id');
            })
            ->join('ms_findings as ms', function ($join) {
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
            ->where('ci.chemicals', $chemicalName)
            ->whereDate('ci.examination_date', $dateExamined)
            ->where('ms.conclusion_fitness', 'Not Fit')
            ->orderBy('e.employee_firstName')
            ->orderBy('e.employee_lastName')
            ->get([
                'd.declaration_id', 'd.employee_id', 'd.surveillance_id', 'd.company_id',
                'e.employee_firstName', 'e.employee_lastName', 'e.employee_NRIC', 'e.employee_passportNo', 'e.employee_gender',
                'ci.examination_type', DB::raw('(SELECT job_title FROM occupational_history WHERE occupational_history.employee_id = d.employee_id ORDER BY occupHistory_id ASC LIMIT 1) as job_title'),
                'ms.history_of_health', 'ms.clinical_findings as ms_clinical_findings', 'ms.CF_work_related', 'ms.TO_work_related', 'ms.BM_work_related', 'ms.conclusion_fitness',
                'cf.result_clinical_findings',
                't.blood_count', 't.renal_function', 't.liver_function', 't.chest_xray', 't.spirometry_FEV1', 't.spirometry_FVC', 't.spirometry_FEV_FVC',
                'bm.biological_exposure', 'bm.baseline_results', 'bm.baseline_annual',
                'r.MRPdate_start', 'r.recommencation_type', 'r.notes'
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
        if (!empty($row->recommencation_type)) { $parts[] = trim((string) $row->recommencation_type); }
        if (!empty($row->notes)) { $parts[] = trim((string) $row->notes); }
        return $parts === [] ? 'N/A' : implode(', ', array_values(array_unique($parts)));
    };

    return [
        'companyLabel' => $companyLabel,
        'chemicalLabel' => $chemicalName,
        'dateExamined' => $dateExamined,
        'abnormalRows' => $rawRows->map(static function ($row) use ($formatTargetOrgan, $formatWorkRelated, $formatBei, $formatRecommendation) {
            $employeeName = trim((string) (($row->employee_firstName ?? '') . ' ' . ($row->employee_lastName ?? '')));
            $identity = trim((string) (($row->employee_NRIC ?? '') !== '' ? ($row->employee_NRIC ?? '') : ($row->employee_passportNo ?? '')));
            $clinical = trim((string) (($row->ms_clinical_findings ?? $row->result_clinical_findings ?? '')));
            $history = trim((string) ($row->history_of_health ?? ''));
            return [
                'declaration_id' => (int) ($row->declaration_id ?? 0),
                'employee_id' => (int) ($row->employee_id ?? 0),
                'surveillance_id' => (int) ($row->surveillance_id ?? 0),
                'company_id' => (int) ($row->company_id ?? 0),
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
                'conclusion' => trim((string) ($row->conclusion_fitness ?? '')) ?: 'N/A'
            ];
        })->values()->all(),
    ];
};

$isFilledRow = static function ($row, array $ignore = []): bool {
    if (! $row) {
        return false;
    }

    $ignore = array_flip(array_merge(['employee_id', 'surveillance_id', 'company_id', 'doctor_id'], $ignore));
    foreach ((array) $row as $key => $value) {
        if (isset($ignore[$key])) {
            continue;
        }
        if ($value !== null && $value !== '' && $value !== '0') {
            return true;
        }
    }

    return false;
};

$historyFieldNames = [
    'breathing_difficulty', 'cough', 'sore_throat', 'sneezing', 'chest_pain', 'palpitation', 'limb_oedema',
    'drowsiness', 'dizziness', 'headache', 'confusion', 'lethargy', 'nausea', 'vomiting',
    'eye_irritations', 'blurred_vision', 'blisters', 'burns', 'itching', 'rash', 'redness',
    'abdominal_pain', 'abdominal_mass', 'jaundice', 'diarrhoea', 'loss_of_weight', 'loss_of_appetite', 'dysuria', 'haematuria',
];
$physicalRequiredFields = [
    'weight', 'height', 'BMI', 'bp_systolic', 'bp_distolic', 'pulse_rate', 'respiratory_rate',
    'general_appearances', 's1_s2', 'murmur', 'ear_nose_throat', 'visual_acuity_right', 'visual_acuity_left',
    'colour_blindness', 'gas_tenderness', 'abdominal_mass', 'lymph_nodes', 'splenomegaly', 'kidney_tenderness',
    'ballotable', 'jaundice', 'hepatomegaly', 'muscle_tone', 'muscle_tenderness', 'power', 'sensation',
    'sound', 'air_entry', 'reproductive', 'skin',
];
$findingsRequiredFields = [
    'history_of_health', 'clinical_findings', 'CF_work_related', 'target_organ',
    'TO_work_related', 'biological_monitoring', 'BM_work_related', 'pregnancy_breastFeding', 'conclusion_fitness',
];

$hasAllValues = static function ($row, array $fields): bool {
    if (! $row) {
        return false;
    }

    foreach ($fields as $field) {
        $value = data_get($row, $field);
        if ($value === null || $value === '') {
            return false;
        }
    }

    return true;
};

$isBiologicalComplete = static function ($row): bool {
    if (! $row) {
        return false;
    }

    $baselineLines = preg_split('/\r\n|\r|\n/', (string) ($row->baseline_results ?? ''));
    $annualLines = preg_split('/\r\n|\r|\n/', (string) ($row->baseline_annual ?? ''));
    $count = max(count($baselineLines), count($annualLines));
    $hasAtLeastOneCompleteRow = false;

    for ($index = 0; $index < $count; $index++) {
        $baselineLine = trim((string) ($baselineLines[$index] ?? ''));
        $annualLine = trim((string) ($annualLines[$index] ?? ''));
        if ($baselineLine === '' && $annualLine === '') {
            continue;
        }

        $parts = explode('::', $baselineLine, 2);
        $determinant = trim((string) ($parts[0] ?? ''));
        $baseline = trim((string) ($parts[1] ?? ''));
        if ($determinant === '' || $baseline === '' || $annualLine === '') {
            return false;
        }

        $hasAtLeastOneCompleteRow = true;
    }

    return $hasAtLeastOneCompleteRow;
};

$computeSectionStatuses = static function (
    $chemicalInfo,
    $historyOfHealth,
    $clinicalFindings,
    $physicalExam,
    $targetOrgan,
    $biologicalMonitoring,
    $fitnessRow,
    $msFindings,
    $recommendationData
) use ($hasAllValues, $historyFieldNames, $physicalRequiredFields, $findingsRequiredFields, $isBiologicalComplete): array {
    $respiratorComplete = false;
    if ($fitnessRow && ($fitnessRow->result ?? '') !== '') {
        $respiratorComplete = ($fitnessRow->result ?? '') !== 'Not fit'
            || trim((string) ($fitnessRow->remarks ?? '')) !== '';
    }

    $clinicalComplete = false;
    if ($clinicalFindings && ($clinicalFindings->result_clinical_findings ?? '') !== '') {
        $clinicalComplete = ($clinicalFindings->result_clinical_findings ?? '') !== 'Yes'
            || trim((string) ($clinicalFindings->elaboration ?? '')) !== '';
    }

    $targetComplete = $hasAllValues($targetOrgan, [
        'blood_count', 'renal_function', 'liver_function', 'chest_xray', 'spirometry_FEV1', 'spirometry_FVC', 'spirometry_FEV_FVC',
    ]);

    $recommendationComplete = $hasAllValues($recommendationData, ['recommencation_type', 'MRPdate_start', 'MRPdate_end', 'nextReview_date']);

    return [
        'chemical' => $hasAllValues($chemicalInfo, ['company_name', 'chemicals', 'examination_type', 'examination_date']),
        'history' => $hasAllValues($historyOfHealth, $historyFieldNames),
        'clinical' => $clinicalComplete,
        'physical' => $hasAllValues($physicalExam, $physicalRequiredFields),
        'target' => $targetComplete,
        'biological' => $isBiologicalComplete($biologicalMonitoring),
        'respirator' => $respiratorComplete,
        'findings' => $hasAllValues($msFindings, $findingsRequiredFields),
        'recommendation' => $recommendationComplete,
    ];
};

$loadCurrentSurveillance = static function (?Request $request = null) use ($resolveDoctorProfile, $computeSectionStatuses, $resolveChemicalOptions): array {
    $session = $request?->session();
    $freshRecord = (string) ($request?->query('fresh') ?? '') === '1'
        || (bool) ($session?->pull('fresh_surveillance_record', false));
    $declarationId = (int) ($request?->query('declaration_id') ?? $session?->get('current_declaration_id') ?? 0);
    $surveillanceId = (int) ($request?->query('surveillance_id') ?? $session?->get('current_surveillance_id') ?? 0);
    $companyId = (int) ($request?->query('company_id') ?? $session?->get('current_company_id') ?? 0);
    $employeeId = (int) ($request?->query('employee_id') ?? $session?->get('current_employee_id') ?? 0);

    $declaration = null;
    if (! $freshRecord && $declarationId > 0) {
        $declaration = DB::table('declaration')->where('declaration_id', $declarationId)->first();
    }
    if (! $freshRecord && ! $declaration && $surveillanceId > 0 && $employeeId > 0) {
        $declaration = DB::table('declaration')->where('surveillance_id', $surveillanceId)->where('employee_id', $employeeId)->latest('declaration_id')->first();
    }
    if (! $freshRecord && ! $declaration && $surveillanceId > 0) {
        $declaration = DB::table('declaration')->where('surveillance_id', $surveillanceId)->latest('declaration_id')->first();
    }

    if ($declaration) {
        $declarationId = (int) $declaration->declaration_id;
        $surveillanceId = (int) $declaration->surveillance_id;
        $employeeId = (int) $declaration->employee_id;
        $companyId = (int) $declaration->company_id;
    }

    $chemicalInfo = $surveillanceId > 0 ? DB::table('chemical_information')->where('surveillance_id', $surveillanceId)->first() : null;
    if ($chemicalInfo) {
        $surveillanceId = (int) $chemicalInfo->surveillance_id;
        $companyId = $companyId ?: (int) $chemicalInfo->company_id;
        $employeeId = $employeeId ?: (int) $chemicalInfo->employee_id;
    }

    $company = $companyId > 0 ? DB::table('company')->where('company_id', $companyId)->first() : DB::table('company')->latest('company_id')->first();
    $employee = $employeeId > 0 ? DB::table('employee')->where('employee_id', $employeeId)->first() : DB::table('employee')->latest('employee_id')->first();
    if ($company) { $companyId = (int) $company->company_id; }
    if ($employee) { $employeeId = (int) $employee->employee_id; }

    $doctor = null;
    if ($declaration && !empty($declaration->doctor_id)) {
        $doctor = DB::table('doctor')->where('doctor_id', $declaration->doctor_id)->first();
    }
    if (! $doctor && $chemicalInfo && !empty($chemicalInfo->doctor_id)) {
        $doctor = DB::table('doctor')->where('doctor_id', $chemicalInfo->doctor_id)->first();
    }
    if (! $doctor) {
        $doctor = $resolveDoctorProfile();
    }

    $historyOfHealth = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('history_of_health')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
    $clinicalFindings = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('clinical_findings')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
    $physicalExam = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('physical_examination')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
    $targetOrgan = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('target_organ')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
    $biologicalMonitoring = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('biological_monitoring')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
    $fitnessRow = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('fitness_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
    $msFindings = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('ms_findings')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
    $recommendationData = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('recommendation')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;

    return [
        'declaration' => $declaration,
        'declarationId' => $declarationId,
        'surveillanceId' => $surveillanceId,
        'selectedCompany' => $company,
        'selectedEmployee' => $employee,
        'doctor' => $doctor,
        'chemicalInfo' => $chemicalInfo,
        'historyOfHealth' => $historyOfHealth,
        'clinicalFindings' => $clinicalFindings,
        'physicalExam' => $physicalExam,
        'targetOrgan' => $targetOrgan,
        'biologicalMonitoring' => $biologicalMonitoring,
        'fitnessRespirator' => (object) ['fitness_result' => $fitnessRow->result ?? null, 'fitness_justification' => $fitnessRow->remarks ?? null],
        'msFindings' => $msFindings,
        'recommendationData' => $recommendationData,
        'sectionStatuses' => $computeSectionStatuses(
            $chemicalInfo,
            $historyOfHealth,
            $clinicalFindings,
            $physicalExam,
            $targetOrgan,
            $biologicalMonitoring,
            $fitnessRow,
            $msFindings,
            $recommendationData
        ),
    ];
};

$render = static function (string $view, ?callable $extra = null) use ($resolveViewData, $loadCurrentSurveillance, $refreshCompanyWorkerTotals, $computeSectionStatuses, $buildUsechh5iiData) {
    return function (...$routeArgs) use ($view, $extra, $resolveViewData, $loadCurrentSurveillance, $refreshCompanyWorkerTotals, $computeSectionStatuses, $buildUsechh5iiData) {
        $data = $resolveViewData();

        switch ($view) {
            case 'auth.surveillance_company':
                $refreshCompanyWorkerTotals();
                $data['companies'] = DB::table('company')->orderByDesc('company_id')->get();
                $data['companyTotal'] = $data['companies']->count();
                $data['selectedCompany'] = null;
                break;
            case 'auth.surveillance_employee':
                $selectedCompanyId = (int) (request()->query('company_id') ?? request()->session()->get('current_company_id') ?? 0);
                if ($selectedCompanyId > 0) {
                    request()->session()->put('current_company_id', $selectedCompanyId);
                }
                $selectedCompany = $selectedCompanyId > 0 ? DB::table('company')->where('company_id', $selectedCompanyId)->first() : null;
                $data['selectedCompany'] = $selectedCompany;
                $currentCompanySubquery = DB::table('occupational_history')
                    ->select('employee_id', DB::raw('MIN(occupHistory_id) as current_occ_id'))
                    ->groupBy('employee_id');
                $employeeQuery = DB::table('employee as e')
                    ->leftJoinSub($currentCompanySubquery, 'current_occ', function ($join) {
                        $join->on('e.employee_id', '=', 'current_occ.employee_id');
                    })
                    ->leftJoin('occupational_history as oh', 'current_occ.current_occ_id', '=', 'oh.occupHistory_id')
                    ->select('e.*', 'oh.company_name as current_company_name');
                if ($selectedCompany && !empty($selectedCompany->company_name)) {
                    $employeeQuery->where('oh.company_name', $selectedCompany->company_name);
                }
                $data['employees'] = $employeeQuery->orderByDesc('e.employee_id')->get();
                $data['employeeTotal'] = $data['employees']->count();
                break;
            case 'auth.surveillance_list':
                $selectedEmployeeId = (int) (request()->query('employee_id') ?? request()->session()->get('current_employee_id') ?? 0);
                $selectedCompanyId = (int) (request()->query('company_id') ?? request()->session()->get('current_company_id') ?? 0);
                if ($selectedEmployeeId > 0) {
                    request()->session()->put('current_employee_id', $selectedEmployeeId);
                }
                if ($selectedCompanyId > 0) {
                    request()->session()->put('current_company_id', $selectedCompanyId);
                }
                $data['selectedEmployee'] = $selectedEmployeeId > 0 ? DB::table('employee')->where('employee_id', $selectedEmployeeId)->first() : null;
                $data['selectedCompany'] = $selectedCompanyId > 0 ? DB::table('company')->where('company_id', $selectedCompanyId)->first() : null;
                $recordQuery = DB::table('declaration as d')
                    ->leftJoin('employee as e', 'd.employee_id', '=', 'e.employee_id')
                    ->leftJoin('company as c', 'd.company_id', '=', 'c.company_id')
                    ->leftJoin('chemical_information as ci', 'd.surveillance_id', '=', 'ci.surveillance_id')
                    ->select('d.*', 'e.employee_firstName', 'e.employee_lastName', 'c.company_name', 'ci.examination_date');
                if ($selectedEmployeeId > 0) {
                    $recordQuery->where('d.employee_id', $selectedEmployeeId);
                }
                if ($selectedCompanyId > 0) {
                    $recordQuery->where('d.company_id', $selectedCompanyId);
                }
                $data['records'] = $recordQuery->orderByDesc('d.declaration_id')->get();
                $data['recordTotal'] = $data['records']->count();
                break;
            case 'auth.declaration':
            case 'auth.surveillance_examination':
                $data = array_merge($data, $loadCurrentSurveillance(request()));
                break;
            case 'auth.surveillance_summaryEmpReport':
            case 'auth.surveillance_fitnessReport':
            case 'auth.surveillance_summaryReport':
            case 'auth.surveillance_removalReport':
            case 'auth.surveillance_abnormalReport':
                $data = array_merge($data, $loadCurrentSurveillance(request()));
                if ($view === 'auth.surveillance_summaryEmpReport') {
                    $employeeId = (int) ($data['selectedEmployee']->employee_id ?? 0);
                    $cutoffDate = $data['chemicalInfo']->examination_date ?? null;
                    $chemicalLabel = trim((string) ($data['chemicalInfo']->chemicals ?? ''));
                    $workerName = trim((string) ((($data['selectedEmployee']->employee_firstName ?? '') . ' ' . ($data['selectedEmployee']->employee_lastName ?? ''))));
                    $historyRows = collect();
                    if ($employeeId > 0) {
                        $historyQuery = DB::table('chemical_information as ci')
                            ->leftJoin('ms_findings as ms', function ($join) {
                                $join->on('ci.employee_id', '=', 'ms.employee_id')
                                    ->on('ci.surveillance_id', '=', 'ms.surveillance_id');
                            })
                            ->leftJoin('clinical_findings as cf', function ($join) {
                                $join->on('ci.employee_id', '=', 'cf.employee_id')
                                    ->on('ci.surveillance_id', '=', 'cf.surveillance_id');
                            })
                            ->leftJoin('target_organ as t', function ($join) {
                                $join->on('ci.employee_id', '=', 't.employee_id')
                                    ->on('ci.surveillance_id', '=', 't.surveillance_id');
                            })
                            ->leftJoin('biological_monitoring as bm', function ($join) {
                                $join->on('ci.employee_id', '=', 'bm.employee_id')
                                    ->on('ci.surveillance_id', '=', 'bm.surveillance_id');
                            })
                            ->leftJoin('recommendation as r', function ($join) {
                                $join->on('ci.employee_id', '=', 'r.employee_id')
                                    ->on('ci.surveillance_id', '=', 'r.surveillance_id');
                            })
                            ->leftJoin('doctor as d', 'ci.doctor_id', '=', 'd.doctor_id')
                            ->where('ci.employee_id', $employeeId);
                        if (!empty($chemicalLabel)) {
                            $historyQuery->where('ci.chemicals', $chemicalLabel);
                        }
                        if (!empty($cutoffDate)) {
                            $historyQuery->whereDate('ci.examination_date', '<=', $cutoffDate);
                        }
                        $historyRows = $historyQuery
                            ->orderBy('ci.examination_date')
                            ->orderBy('ci.surveillance_id')
                            ->get([
                                'ci.surveillance_id',
                                'ci.examination_date',
                                'ci.examination_type',
                                'ms.history_of_health',
                                'ms.clinical_findings as ms_clinical_findings',
                                'ms.CF_work_related',
                                'ms.TO_work_related',
                                'ms.BM_work_related',
                                'ms.conclusion_fitness',
                                'cf.result_clinical_findings',
                                't.blood_count',
                                't.renal_function',
                                't.liver_function',
                                't.chest_xray',
                                't.spirometry_FEV1',
                                't.spirometry_FVC',
                                't.spirometry_FEV_FVC',
                                'bm.biological_exposure',
                                'bm.baseline_results',
                                'bm.baseline_annual',
                                'r.MRPdate_start',
                                'd.doctor_firstName',
                                'd.doctor_lastName',
                                'd.OHD_registrationNo'
                            ]);
                    }
                    $formatDoctor = static function ($row): string {
                        $name = trim((string) ((($row->doctor_firstName ?? '') . ' ' . ($row->doctor_lastName ?? ''))));
                        $reg = trim((string) ($row->OHD_registrationNo ?? ''));
                        return trim($name . ($reg !== '' ? ' (' . $reg . ')' : ''));
                    };
                    $formatTargetOrgan = static function ($row): string {
                        $parts = [];
                        if (!empty($row->blood_count)) { $parts[] = 'Full blood count'; }
                        if (!empty($row->renal_function)) { $parts[] = 'Renal Profile'; }
                        if (!empty($row->liver_function)) { $parts[] = 'Liver Profile'; }
                        if (!empty($row->chest_xray)) { $parts[] = 'Chest X-ray'; }
                        if ($row->spirometry_FEV1 !== null || $row->spirometry_FVC !== null || $row->spirometry_FEV_FVC !== null) { $parts[] = 'Spirometry'; }
                        return implode(', ', $parts);
                    };
                    $formatWorkRelated = static function ($row): string {
                        $flags = [trim((string) ($row->CF_work_related ?? '')), trim((string) ($row->TO_work_related ?? '')), trim((string) ($row->BM_work_related ?? ''))];
                        $answered = array_values(array_filter($flags, static fn($v) => $v !== ''));
                        if (in_array('Yes', $answered, true)) { return 'Yes'; }
                        if (!empty($answered) && count(array_filter($answered, static fn($v) => $v === 'No')) === count($answered)) { return 'No'; }
                        return '';
                    };
                    $formatBei = static function ($row): string {
                        foreach ([$row->baseline_annual ?? null, $row->baseline_results ?? null, $row->biological_exposure ?? null] as $value) {
                            $value = trim((string) ($value ?? ''));
                            if ($value !== '') { return $value; }
                        }
                        return '';
                    };
                    $data['workerName'] = $workerName;
                    $data['chemicalLabel'] = $chemicalLabel;
                    $data['reportRows'] = $historyRows->map(static function ($row) use ($formatDoctor, $formatTargetOrgan, $formatWorkRelated, $formatBei) {
                        return [
                            'ms_date' => $row->examination_date,
                            'type_of_assessment' => trim((string) ($row->examination_type ?? 'Medical Surveillance')),
                            'history_health_effects' => trim((string) ($row->history_of_health ?? '')),
                            'clinical_findings' => trim((string) (($row->ms_clinical_findings ?? $row->result_clinical_findings ?? ''))),
                            'target_organ' => $formatTargetOrgan($row),
                            'bei_determinant' => $formatBei($row),
                            'work_relatedness' => $formatWorkRelated($row),
                            'conclusion' => trim((string) ($row->conclusion_fitness ?? '')),
                            'mrp_date' => $row->MRPdate_start,
                            'doctor' => $formatDoctor($row),
                        ];
                    })->values()->all();
                }
                if ($view === 'auth.surveillance_removalReport') {
                    $employeeId = (int) ($data['selectedEmployee']->employee_id ?? 0);
                    $surveillanceId = (int) ($data['surveillanceId'] ?? 0);
                    $data['fitnessReportRow'] = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('fitness_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
                    $data['existingRemoval'] = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('removal_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
                }
                break;
            case 'auth.surveillance_confirm':
                $bundle = $loadCurrentSurveillance(request());
                $employee = $bundle['selectedEmployee'];
                $company = $bundle['selectedCompany'];
                $chemical = $bundle['chemicalInfo'];
                $fitness = $bundle['msFindings'];
                $data = array_merge($data, $bundle, [
                    'confirmCompanyName' => $company->company_name ?? '-',
                    'confirmRegistrationNo' => $company->mykpp_registration_no ?? '-',
                    'confirmEmployeeName' => trim((string) (($employee->employee_firstName ?? '') . ' ' . ($employee->employee_lastName ?? ''))),
                    'confirmIdentity' => $employee->employee_NRIC ?? $employee->employee_passportNo ?? '-',
                    'confirmExamType' => $chemical->examination_type ?? '-',
                    'confirmResult' => $fitness->conclusion_fitness ?? '-',
                ]);
                break;
            case 'auth.surveillance_report':
                $data['totalCompanies'] = DB::table('company')->count();
                $data['totalEmployees'] = DB::table('employee')->count();
                $data['pendingReviews'] = DB::table('declaration')->where(function ($query) {
                    $query->whereNull('employee_signature')
                        ->orWhereNull('doctor_signature')
                        ->orWhereNull('employee_date')
                        ->orWhereNull('doctor_date');
                })->count();
                break;
            case 'auth.general_examination':
                $declarations = DB::table('declaration as d')
                    ->leftJoin('employee as e', 'd.employee_id', '=', 'e.employee_id')
                    ->leftJoin('company as c', 'd.company_id', '=', 'c.company_id')
                    ->leftJoin('chemical_information as ci', 'd.surveillance_id', '=', 'ci.surveillance_id')
                    ->select(
                        'd.declaration_id',
                        'd.employee_id',
                        'd.company_id',
                        'd.surveillance_id',
                        'd.employee_date',
                        'd.doctor_date',
                        'd.employee_signature',
                        'd.doctor_signature',
                        'e.employee_firstName',
                        'e.employee_lastName',
                        'c.company_name',
                        'ci.examination_date'
                    )
                    ->orderByDesc('d.declaration_id')
                    ->get();
                $surveillanceExamRows = [];
                foreach ($declarations as $declaration) {
                    $employeeName = trim(((string) ($declaration->employee_firstName ?? '')) . ' ' . ((string) ($declaration->employee_lastName ?? '')));
                    $companyName = (string) ($declaration->company_name ?? '-');
                    $dateExamined = $declaration->examination_date ?: ($declaration->doctor_date ?: ($declaration->employee_date ?: date('Y-m-d')));
                    $routeParams = [
                        'declaration_id' => $declaration->declaration_id,
                        'employee_id' => $declaration->employee_id,
                        'company_id' => $declaration->company_id,
                        'surveillance_id' => $declaration->surveillance_id,
                    ];
                    $declarationComplete = !empty($declaration->employee_signature)
                        && !empty($declaration->doctor_signature)
                        && !empty($declaration->employee_date)
                        && !empty($declaration->doctor_date);

                    $chemicalInfo = $declaration->surveillance_id
                        ? DB::table('chemical_information')->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;
                    $historyOfHealth = $declaration->surveillance_id
                        ? DB::table('history_of_health')->where('employee_id', $declaration->employee_id)->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;
                    $clinicalFindings = $declaration->surveillance_id
                        ? DB::table('clinical_findings')->where('employee_id', $declaration->employee_id)->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;
                    $physicalExam = $declaration->surveillance_id
                        ? DB::table('physical_examination')->where('employee_id', $declaration->employee_id)->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;
                    $targetOrgan = $declaration->surveillance_id
                        ? DB::table('target_organ')->where('employee_id', $declaration->employee_id)->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;
                    $biologicalMonitoring = $declaration->surveillance_id
                        ? DB::table('biological_monitoring')->where('employee_id', $declaration->employee_id)->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;
                    $fitnessRow = $declaration->surveillance_id
                        ? DB::table('fitness_report')->where('employee_id', $declaration->employee_id)->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;
                    $msFindings = $declaration->surveillance_id
                        ? DB::table('ms_findings')->where('employee_id', $declaration->employee_id)->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;
                    $recommendationData = $declaration->surveillance_id
                        ? DB::table('recommendation')->where('employee_id', $declaration->employee_id)->where('surveillance_id', $declaration->surveillance_id)->first()
                        : null;

                    $sectionStatuses = $computeSectionStatuses(
                        $chemicalInfo,
                        $historyOfHealth,
                        $clinicalFindings,
                        $physicalExam,
                        $targetOrgan,
                        $biologicalMonitoring,
                        $fitnessRow,
                        $msFindings,
                        $recommendationData
                    );
                    $examinationComplete = !in_array(false, $sectionStatuses, true);

                    $surveillanceExamRows[] = [
                        'module' => 'surveillance',
                        'filter' => 'declaration',
                        'employee_name' => $employeeName !== '' ? $employeeName : 'Not set',
                        'company' => $companyName !== '' ? $companyName : '-',
                        'stage' => 'Declaration',
                        'status' => $declarationComplete ? 'Completed' : 'Incomplete',
                        'status_key' => $declarationComplete ? 'completed' : 'incomplete',
                        'date_examined' => $dateExamined,
                        'href' => route('surveillance.declaration', $routeParams),
                    ];
                    $surveillanceExamRows[] = [
                        'module' => 'surveillance',
                        'filter' => 'examination',
                        'employee_name' => $employeeName !== '' ? $employeeName : 'Not set',
                        'company' => $companyName !== '' ? $companyName : '-',
                        'stage' => 'Examination',
                        'status' => $examinationComplete ? 'Completed' : 'Incomplete',
                        'status_key' => $examinationComplete ? 'completed' : 'incomplete',
                        'date_examined' => $dateExamined,
                        'href' => route('surveillance.examination', $routeParams),
                    ];
                }
                $data['surveillanceExamRows'] = $surveillanceExamRows;
                break;
            case 'auth.general_report':
                $employees = DB::table('employee')->orderByDesc('employee_id')->get();
                $declarations = DB::table('declaration as d')
                    ->leftJoin('employee as e', 'd.employee_id', '=', 'e.employee_id')
                    ->leftJoin('company as c', 'd.company_id', '=', 'c.company_id')
                    ->leftJoin('chemical_information as ci', 'd.surveillance_id', '=', 'ci.surveillance_id')
                    ->leftJoin('fitness_report as fr', function ($join) {
                        $join->on('d.employee_id', '=', 'fr.employee_id')
                            ->on('d.surveillance_id', '=', 'fr.surveillance_id');
                    })
                    ->leftJoin('ms_findings as ms', function ($join) {
                        $join->on('d.employee_id', '=', 'ms.employee_id')
                            ->on('d.surveillance_id', '=', 'ms.surveillance_id');
                    })
                    ->leftJoin('summary_report as sr', function ($join) {
                        $join->on('d.employee_id', '=', 'sr.employee_id')
                            ->on('d.surveillance_id', '=', 'sr.surveillance_id');
                    })
                    ->leftJoin('removal_report as rr', function ($join) {
                        $join->on('d.employee_id', '=', 'rr.employee_id')
                            ->on('d.surveillance_id', '=', 'rr.surveillance_id');
                    })
                    ->select(
                        'd.declaration_id','d.employee_id','d.company_id','d.surveillance_id','d.employee_date','d.doctor_date',
                        'e.employee_firstName','e.employee_lastName','e.employee_telephone','e.employee_NRIC','e.employee_passportNo',
                        'c.company_name',
                        'ci.chemicals','ci.examination_date',
                        'fr.result as fitness_result',
                        'ms.conclusion_fitness as ms_conclusion_fitness',
                        'sr.summary_id',
                        'rr.removalReport_id'
                    )
                    ->orderByDesc('d.declaration_id')
                    ->get();
                $surveillanceRows = [];
                foreach ($employees as $employeeRow) {
                    $employeeName = trim(((string) ($employeeRow->employee_firstName ?? '')) . ' ' . ((string) ($employeeRow->employee_lastName ?? '')));
                    $surveillanceRows[] = [
                        'module' => 'surveillance',
                        'filter' => 'usechh 1',
                        'employee_name' => $employeeName !== '' ? $employeeName : 'Not set',
                        'company' => '-',
                        'phone_no' => (string) ($employeeRow->employee_telephone ?? '-'),
                        'identity_no' => (string) (($employeeRow->employee_NRIC ?? '') !== '' ? ($employeeRow->employee_NRIC ?? '') : ($employeeRow->employee_passportNo ?? '-')),
                        'chemical_name' => '-',
                        'status' => 'N/A',
                        'status_key' => 'na',
                        'date_examined' => date('Y-m-d'),
                        'href' => route('surveillance.employee.edit', ['id' => $employeeRow->employee_id]),
                        'pdf_href' => route('pdf.usechh1', ['employee_id' => $employeeRow->employee_id]),
                    ];
                }
                $usechh5iiGroups = [];
                foreach ($declarations as $record) {
                    $employeeName = trim(((string) ($record->employee_firstName ?? '')) . ' ' . ((string) ($record->employee_lastName ?? '')));
                    $companyName = (string) ($record->company_name ?? '-');
                    $chemicalName = (string) ($record->chemicals ?? '-');
                    $dateExamined = $record->examination_date ?: ($record->doctor_date ?: ($record->employee_date ?: date('Y-m-d')));
                    $query = ['declaration_id' => $record->declaration_id, 'employee_id' => $record->employee_id, 'company_id' => $record->company_id, 'surveillance_id' => $record->surveillance_id];
                    $surveillanceRows[] = [
                        'module' => 'surveillance', 'filter' => 'usechh 2', 'employee_name' => $employeeName !== '' ? $employeeName : 'Not set', 'company' => $companyName !== '' ? $companyName : '-', 'chemical_name' => $chemicalName !== '' ? $chemicalName : '-', 'status' => 'N/A', 'status_key' => 'na', 'date_examined' => $dateExamined, 'href' => route('surveillance.report.summary-employee', $query), 'pdf_href' => route('pdf.usechh2', $query),
                    ];
                    $surveillanceRows[] = [
                        'module' => 'surveillance', 'filter' => 'usechh 3', 'employee_name' => $employeeName !== '' ? $employeeName : 'Not set', 'company' => $companyName !== '' ? $companyName : '-', 'chemical_name' => $chemicalName !== '' ? $chemicalName : '-', 'status' => !empty($record->fitness_result) ? 'Completed' : 'Incomplete', 'status_key' => !empty($record->fitness_result) ? 'completed' : 'incomplete', 'date_examined' => $dateExamined, 'href' => route('surveillance.report.fitness', $query), 'pdf_href' => route('pdf.usechh3', $query),
                    ];
                    $surveillanceRows[] = [
                        'module' => 'surveillance', 'filter' => 'usechh 4', 'employee_name' => $employeeName !== '' ? $employeeName : 'Not set', 'company' => $companyName !== '' ? $companyName : '-', 'chemical_name' => $chemicalName !== '' ? $chemicalName : '-', 'status' => !empty($record->summary_id) ? 'Completed' : 'Incomplete', 'status_key' => !empty($record->summary_id) ? 'completed' : 'incomplete', 'date_examined' => $dateExamined, 'href' => route('surveillance.report.summary', $query), 'pdf_href' => route('pdf.usechh4', $query),
                    ];
                    $surveillanceRows[] = [
                        'module' => 'surveillance', 'filter' => 'usechh 5i', 'employee_name' => $employeeName !== '' ? $employeeName : 'Not set', 'company' => $companyName !== '' ? $companyName : '-', 'chemical_name' => $chemicalName !== '' ? $chemicalName : '-', 'status' => !empty($record->removalReport_id) ? 'Completed' : 'Incomplete', 'status_key' => !empty($record->removalReport_id) ? 'completed' : 'incomplete', 'date_examined' => $dateExamined, 'href' => route('surveillance.report.removal', $query), 'pdf_href' => route('pdf.usechh5i', $query),
                    ];
                    if (strcasecmp((string) ($record->ms_conclusion_fitness ?? ''), 'Not Fit') === 0 && !empty($record->company_id) && $chemicalName !== '' && !empty($dateExamined)) {
                        $normalizedDate = date('Y-m-d', strtotime((string) $dateExamined));
                        $groupKey = implode('|', [(int) $record->company_id, $chemicalName, $normalizedDate]);
                        if (!isset($usechh5iiGroups[$groupKey])) {
                            $groupQuery = [
                                'declaration_id' => $record->declaration_id,
                                'company_id' => $record->company_id,
                                'surveillance_id' => $record->surveillance_id,
                                'chemical_name' => $chemicalName,
                                'date_examined' => $normalizedDate,
                            ];
                            $usechh5iiGroups[$groupKey] = [
                                'module' => 'surveillance', 'filter' => 'usechh 5ii', 'employee_name' => '-', 'company' => $companyName !== '' ? $companyName : '-', 'chemical_name' => $chemicalName !== '' ? $chemicalName : '-', 'status' => 'N/A', 'status_key' => 'na', 'date_examined' => $dateExamined, 'href' => route('surveillance.report.abnormal', $groupQuery), 'pdf_href' => route('pdf.usechh5ii', $groupQuery),
                            ];
                        }
                    }
                }
                if ($usechh5iiGroups !== []) {
                    uasort($usechh5iiGroups, static function ($a, $b) {
                        return [strtolower((string) ($a['company'] ?? '')), strtolower((string) ($a['chemical_name'] ?? '')), (string) ($a['date_examined'] ?? '')] <=> [strtolower((string) ($b['company'] ?? '')), strtolower((string) ($b['chemical_name'] ?? '')), (string) ($b['date_examined'] ?? '')];
                    });
                }
                $surveillanceRows = array_merge($surveillanceRows, array_values($usechh5iiGroups));
                $data['surveillanceReportRows'] = $surveillanceRows;
                break;
            case 'auth.edit_surveillanceComp':
            case 'auth.delete_surveillanceComp':
                $data['companyData'] = DB::table('company')->where('company_id', $routeArgs[0] ?? request()->route('id'))->first();
                break;
            case 'auth.edit_surveillanceEmp':
            case 'auth.delete_surveillanceEmp':
            case 'auth.surveillance_usechh1Report':
                $employeeId = (int) ($routeArgs[0] ?? request()->route('id'));
                $data['employeeData'] = DB::table('employee')->where('employee_id', $employeeId)->first();
                $data['medicalHistoryData'] = DB::table('medical_history')->where('employee_id', $employeeId)->first();
                $occupationalRows = DB::table('occupational_history')->where('employee_id', $employeeId)->orderBy('occupHistory_id')->get()->values();
                $data['occupationalHistoryRows'] = $occupationalRows;
                $data['currentOccupationalData'] = $occupationalRows->first();
                $data['pastOccupationalHistoryRows'] = $occupationalRows->slice(1)->values();
                $data['personalSocialHistoryData'] = DB::table('personal_social_history')->where('employee_id', $employeeId)->first();
                $data['trainingHistoryData'] = DB::table('training_history')->where('employee_id', $employeeId)->first();
                break;
            case 'auth.edit_surveillanceRecord':
            case 'auth.delete_surveillanceRecord':
                $data['declarationData'] = DB::table('declaration as d')
                    ->leftJoin('employee as e', 'd.employee_id', '=', 'e.employee_id')
                    ->leftJoin('company as c', 'd.company_id', '=', 'c.company_id')
                    ->select('d.*', 'e.employee_firstName', 'e.employee_lastName', 'c.company_name')
                    ->where('d.declaration_id', $routeArgs[0] ?? request()->route('id'))
                    ->first();
                break;
        }

        if ($extra) {
            $extraData = $extra(...$routeArgs);
            if (is_array($extraData)) {
                $data = array_merge($data, $extraData);
            }
        }

        return view($view, $data);
    };
};

$renderPdf = static function (string $view, string $filename, string $paper = 'a4', string $orientation = 'portrait', ?callable $extra = null) use ($resolveViewData, $loadCurrentSurveillance, $refreshCompanyWorkerTotals, $buildUsechh5iiData) {
    return function (...$routeArgs) use ($view, $filename, $paper, $orientation, $resolveViewData, $extra, $loadCurrentSurveillance, $refreshCompanyWorkerTotals, $buildUsechh5iiData) {
        $data = array_merge($resolveViewData(), ['pdfMode' => true]);

if ($view === 'auth.surveillance_company') {
            $refreshCompanyWorkerTotals();
            $data['companies'] = DB::table('company')->where('total_workers', '>', 0)->orderByDesc('company_id')->get();
            $data['companyTotal'] = $data['companies']->count();
        } elseif ($view === 'auth.surveillance_employee') {
            $data['employees'] = DB::table('employee')->orderByDesc('employee_id')->get();
            $data['employeeTotal'] = DB::table('employee')->count();
        } elseif ($view === 'auth.surveillance_report') {
            $data['totalCompanies'] = DB::table('company')->count();
            $data['totalEmployees'] = DB::table('employee')->count();
            $data['pendingReviews'] = DB::table('declaration')->where(function ($query) {
                $query->whereNull('employee_signature')
                    ->orWhereNull('doctor_signature')
                    ->orWhereNull('employee_date')
                    ->orWhereNull('doctor_date');
            })->count();
        } elseif (in_array($view, ['auth.surveillance_summaryEmpReport', 'auth.surveillance_fitnessReport', 'auth.surveillance_summaryReport', 'auth.surveillance_removalReport', 'auth.surveillance_abnormalReport'], true)) {
            $data = array_merge($data, $loadCurrentSurveillance(request()));
            if ($view === 'auth.surveillance_summaryEmpReport') {
                $employeeId = (int) ($data['selectedEmployee']->employee_id ?? 0);
                $cutoffDate = $data['chemicalInfo']->examination_date ?? null;
                $chemicalLabel = trim((string) ($data['chemicalInfo']->chemicals ?? ''));
                $workerName = trim((string) ((($data['selectedEmployee']->employee_firstName ?? '') . ' ' . ($data['selectedEmployee']->employee_lastName ?? ''))));
                $historyRows = collect();
                if ($employeeId > 0) {
                    $historyQuery = DB::table('chemical_information as ci')
                        ->leftJoin('ms_findings as ms', function ($join) {
                            $join->on('ci.employee_id', '=', 'ms.employee_id')
                                ->on('ci.surveillance_id', '=', 'ms.surveillance_id');
                        })
                        ->leftJoin('clinical_findings as cf', function ($join) {
                            $join->on('ci.employee_id', '=', 'cf.employee_id')
                                ->on('ci.surveillance_id', '=', 'cf.surveillance_id');
                        })
                        ->leftJoin('target_organ as t', function ($join) {
                            $join->on('ci.employee_id', '=', 't.employee_id')
                                ->on('ci.surveillance_id', '=', 't.surveillance_id');
                        })
                        ->leftJoin('biological_monitoring as bm', function ($join) {
                            $join->on('ci.employee_id', '=', 'bm.employee_id')
                                ->on('ci.surveillance_id', '=', 'bm.surveillance_id');
                        })
                        ->leftJoin('recommendation as r', function ($join) {
                            $join->on('ci.employee_id', '=', 'r.employee_id')
                                ->on('ci.surveillance_id', '=', 'r.surveillance_id');
                        })
                        ->leftJoin('doctor as d', 'ci.doctor_id', '=', 'd.doctor_id')
                        ->where('ci.employee_id', $employeeId);
                    if (!empty($chemicalLabel)) {
                        $historyQuery->where('ci.chemicals', $chemicalLabel);
                    }
                    if (!empty($cutoffDate)) {
                        $historyQuery->whereDate('ci.examination_date', '<=', $cutoffDate);
                    }
                    $historyRows = $historyQuery
                        ->orderBy('ci.examination_date')
                        ->orderBy('ci.surveillance_id')
                        ->get([
                            'ci.surveillance_id',
                            'ci.examination_date',
                            'ci.examination_type',
                            'ms.history_of_health',
                            'ms.clinical_findings as ms_clinical_findings',
                            'ms.CF_work_related',
                            'ms.TO_work_related',
                            'ms.BM_work_related',
                            'ms.conclusion_fitness',
                            'cf.result_clinical_findings',
                            't.blood_count',
                            't.renal_function',
                            't.liver_function',
                            't.chest_xray',
                            't.spirometry_FEV1',
                            't.spirometry_FVC',
                            't.spirometry_FEV_FVC',
                            'bm.biological_exposure',
                            'bm.baseline_results',
                            'bm.baseline_annual',
                            'r.MRPdate_start',
                            'd.doctor_firstName',
                            'd.doctor_lastName',
                            'd.OHD_registrationNo',
                        ]);
                }
                $formatDoctor = static function ($row): string {
                    $name = trim((string) ((($row->doctor_firstName ?? '') . ' ' . ($row->doctor_lastName ?? ''))));
                    $reg = trim((string) ($row->OHD_registrationNo ?? ''));
                    return trim($name . ($reg !== '' ? ' (' . $reg . ')' : ''));
                };
                $formatTargetOrgan = static function ($row): string {
                    $parts = [];
                    if (!empty($row->blood_count)) { $parts[] = 'Full blood count'; }
                    if (!empty($row->renal_function)) { $parts[] = 'Renal Profile'; }
                    if (!empty($row->liver_function)) { $parts[] = 'Liver Profile'; }
                    if (!empty($row->chest_xray)) { $parts[] = 'Chest X-ray'; }
                    if ($row->spirometry_FEV1 !== null || $row->spirometry_FVC !== null || $row->spirometry_FEV_FVC !== null) { $parts[] = 'Spirometry'; }
                    return implode(', ', $parts);
                };
                $formatWorkRelated = static function ($row): string {
                    $flags = [trim((string) ($row->CF_work_related ?? '')), trim((string) ($row->TO_work_related ?? '')), trim((string) ($row->BM_work_related ?? ''))];
                    $answered = array_values(array_filter($flags, static fn($v) => $v !== ''));
                    if (in_array('Yes', $answered, true)) { return 'Yes'; }
                    if (!empty($answered) && count(array_filter($answered, static fn($v) => $v === 'No')) === count($answered)) { return 'No'; }
                    return '';
                };
                $formatBei = static function ($row): string {
                    foreach ([$row->baseline_annual ?? null, $row->baseline_results ?? null, $row->biological_exposure ?? null] as $value) {
                        $value = trim((string) ($value ?? ''));
                        if ($value !== '') { return $value; }
                    }
                    return '';
                };
                $data['workerName'] = $workerName;
                $data['chemicalLabel'] = $chemicalLabel;
                $data['reportRows'] = $historyRows->map(static function ($row) use ($formatDoctor, $formatTargetOrgan, $formatWorkRelated, $formatBei) {
                    return [
                        'ms_date' => $row->examination_date,
                        'type_of_assessment' => trim((string) ($row->examination_type ?? 'Medical Surveillance')),
                        'history_health_effects' => trim((string) ($row->history_of_health ?? '')),
                        'clinical_findings' => trim((string) (($row->ms_clinical_findings ?? $row->result_clinical_findings ?? ''))),
                        'target_organ' => $formatTargetOrgan($row),
                        'bei_determinant' => $formatBei($row),
                        'work_relatedness' => $formatWorkRelated($row),
                        'conclusion' => trim((string) ($row->conclusion_fitness ?? '')),
                        'mrp_date' => $row->MRPdate_start,
                        'doctor' => $formatDoctor($row),
                    ];
                })->values()->all();
            }
            if ($view === 'auth.surveillance_abnormalReport') {
                $abnormalBundle = $buildUsechh5iiData(
                    (int) (request()->query('company_id') ?? ($data['selectedCompany']->company_id ?? 0)),
                    request()->query('chemical_name') ?? ($data['chemicalInfo']->chemicals ?? null),
                    request()->query('date_examined') ?? ($data['chemicalInfo']->examination_date ?? null),
                    (int) (request()->query('surveillance_id') ?? ($data['surveillanceId'] ?? 0))
                );
                $data['companyLabel'] = $abnormalBundle['companyLabel'];
                $data['chemicalLabel'] = $abnormalBundle['chemicalLabel'];
                $data['dateExamined'] = $abnormalBundle['dateExamined'];
                $data['abnormalRows'] = $abnormalBundle['abnormalRows'];
            }
            if ($view === 'auth.surveillance_removalReport') {
                $employeeId = (int) ($data['selectedEmployee']->employee_id ?? 0);
                $surveillanceId = (int) ($data['surveillanceId'] ?? 0);
                $data['fitnessReportRow'] = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('fitness_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
                $data['existingRemoval'] = ($employeeId > 0 && $surveillanceId > 0) ? DB::table('removal_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first() : null;
            }
        }

        if ($extra) {
            $extraData = $extra(...$routeArgs);
            if (is_array($extraData)) {
                $data = array_merge($data, $extraData);
            }
        }

        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->stream($filename);
    };
};

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', $render('auth.dashboard'))->name('dashboard');
Route::get('/profile', $render('auth.profile'))->name('profile');
Route::get('/settings', $render('auth.settings'))->name('settings');
Route::get('/account_settings.php', $render('auth.account_settings'))->name('account.settings');
Route::get('/logout.php', $render('auth.logout'))->name('logout.page');

Route::get('/general_report.php', $render('auth.general_report'))->name('general.report');
Route::get('/general_examination.php', $render('auth.general_examination'))->name('general.examination');

Route::get('/PDF_company.php', $renderPdf('auth.surveillance_company', 'company.pdf', 'a4', 'landscape'))->name('pdf.company');
Route::get('/PDF_employee.php', $renderPdf('auth.surveillance_employee', 'employee.pdf', 'a4', 'landscape'))->name('pdf.employee');
Route::get('/PDF_USECHH1.php', $renderPdf('auth.surveillance_usechh1Report', 'usechh1.pdf', 'a4', 'portrait'))->name('pdf.usechh1');
Route::get('/PDF_USECHH2.php', $renderPdf('auth.surveillance_summaryEmpReport', 'usechh2.pdf', 'a4', 'landscape'))->name('pdf.usechh2');
Route::get('/PDF_USECHH3.php', $renderPdf('auth.surveillance_fitnessReport', 'usechh3.pdf'))->name('pdf.usechh3');
Route::get('/PDF_USECHH4.php', $renderPdf('auth.surveillance_summaryReport', 'usechh4.pdf'))->name('pdf.usechh4');
Route::get('/PDF_USECHH5i.php', $renderPdf('auth.surveillance_removalReport', 'usechh5i.pdf'))->name('pdf.usechh5i');
Route::get('/PDF_USECHH5ii.php', $renderPdf('auth.surveillance_abnormalReport', 'usechh5ii.pdf', 'a4', 'landscape'))->name('pdf.usechh5ii');
Route::get('/PDF_surveillanceReport.php', $renderPdf('auth.surveillance_report', 'surveillance-report.pdf'))->name('pdf.surveillance-report');
Route::get('/PDF_questionnaire.php', $renderPdf('auth.audiometry_questionnaire', 'questionnaire.pdf', 'a4', 'landscape'))->name('pdf.questionnaire');
Route::get('/PDF_audioReport.php', $renderPdf('auth.audiometry_report', 'audio-report.pdf'))->name('pdf.audio-report');

Route::get('/surveillance_company.php', $render('auth.surveillance_company'))->name('surveillance.company');
Route::get('/surveillance_employee.php', $render('auth.surveillance_employee'))->name('surveillance.employee');
Route::get('/surveillance_list.php', $render('auth.surveillance_list'))->name('surveillance.list');
Route::get('/declaration.php', $render('auth.declaration'))->name('surveillance.declaration');
Route::get('/surveillance_examination.php', function () use ($render) {
    $declarationId = (int) (request()->query('declaration_id') ?? request()->session()->get('current_declaration_id') ?? 0);
    if ($declarationId <= 0) {
        return redirect()->route('surveillance.declaration')->withErrors(['employee_signature' => 'Please save declaration first before proceeding to examination.']);
    }

    $handler = $render('auth.surveillance_examination');
    return $handler();
})->name('surveillance.examination');
Route::get('/surveillance_confirm.php', $render('auth.surveillance_confirm'))->name('surveillance.confirm');
Route::get('/surveillance_report.php', function () use ($loadCurrentSurveillance) {
    $bundle = $loadCurrentSurveillance(request());
    $query = array_filter([
        'declaration_id' => $bundle['declarationId'] ?? null,
        'surveillance_id' => $bundle['surveillanceId'] ?? null,
        'employee_id' => $bundle['selectedEmployee']->employee_id ?? null,
        'company_id' => $bundle['selectedCompany']->company_id ?? null,
    ], static fn ($value) => !empty($value));

    $conclusion = trim((string) ($bundle['msFindings']->conclusion_fitness ?? ''));
    if (strcasecmp($conclusion, 'Not Fit') === 0) {
        return redirect()->route('surveillance.report.removal', $query);
    }

    return redirect()->route('surveillance.report.fitness', $query);
})->name('surveillance.report');
Route::get('/surveillance_usechh1Report.php', $render('auth.surveillance_usechh1Report'))->name('surveillance.report.usechh1');
Route::get('/surveillance_summaryEmpReport.php', $render('auth.surveillance_summaryEmpReport'))->name('surveillance.report.summary-employee');
Route::get('/surveillance_fitnessReport.php', $render('auth.surveillance_fitnessReport'))->name('surveillance.report.fitness');
Route::get('/surveillance_summaryReport.php', $render('auth.surveillance_summaryReport'))->name('surveillance.report.summary');
Route::get('/surveillance_removalReport.php', $render('auth.surveillance_removalReport'))->name('surveillance.report.removal');
Route::get('/surveillance_abnormalReport.php', $render('auth.surveillance_abnormalReport'))->name('surveillance.report.abnormal');

Route::get('/audiometry_company.php', $render('auth.audiometry_company'))->name('audiometry.company');
Route::get('/audiometry_employee.php', $render('auth.audiometry_employee'))->name('audiometry.employee');
Route::get('/audiometry_list.php', $render('auth.audiometry_list'))->name('audiometry.list');
Route::get('/audiometry_questionnaire.php', $render('auth.audiometry_questionnaire'))->name('audiometry.questionnaire');
Route::get('/audiometry_examination.php', $render('auth.audiometry_examination'))->name('audiometry.examination');
Route::get('/audiometry_confirm.php', $render('auth.audiometry_confirm'))->name('audiometry.confirm');
Route::get('/audiometry_report.php', $render('auth.audiometry_report'))->name('audiometry.report');
Route::get('/new_questionnaire.php', $render('auth.new_questionnaire'))->name('audiometry.questionnaire.new');

Route::get('/new_company.php', $render('auth.new_company'))->name('company.new');
Route::get('/new_employee.php', $render('auth.new_employee'))->name('employee.new');
Route::get('/new_surveillanceRecord.php', $render('auth.new_surveillanceRecord'))->name('surveillance.record.new');

Route::get('/login.php', $render('auth.login'))->name('login');
Route::get('/forgot_password.php', $render('auth.forgot_password'))->name('password.request');
Route::get('/surveillance/company/new', $render('auth.new_company'))->name('surveillance.company.new');
Route::get('/surveillance/company/{id}/edit', $render('auth.edit_surveillanceComp'))->name('surveillance.company.edit');
Route::get('/surveillance/company/{id}/delete', $render('auth.delete_surveillanceComp'))->name('surveillance.company.delete');
Route::get('/surveillance/employee/new', $render('auth.new_employee'))->name('surveillance.employee.new');
Route::get('/surveillance/employee/{id}/edit', $render('auth.edit_surveillanceEmp'))->name('surveillance.employee.edit');

Route::get('/surveillance/company/{id}/select', function (Request $request, $id) {
    $companyId = (int) $id;
    if ($companyId > 0 && DB::table('company')->where('company_id', $companyId)->exists()) {
        $request->session()->put('current_company_id', $companyId);
        $request->session()->forget(['current_employee_id', 'current_surveillance_id', 'current_declaration_id']);
    }

    return redirect()->route('surveillance.employee', ['company_id' => $companyId]);
})->name('surveillance.company.select');
Route::get('/surveillance/employee/{id}/select', function (Request $request, $id) {
    $employeeId = (int) $id;
    if ($employeeId > 0 && DB::table('employee')->where('employee_id', $employeeId)->exists()) {
        $request->session()->put('current_employee_id', $employeeId);
        $request->session()->forget(['current_surveillance_id', 'current_declaration_id']);
    }

    return redirect()->route('surveillance.list', [
        'employee_id' => $employeeId,
        'company_id' => (int) ($request->session()->get('current_company_id') ?? 0),
    ]);
})->name('surveillance.employee.select');
Route::get('/surveillance/record/start', function (Request $request) {
    $companyId = (int) ($request->query('company_id') ?? $request->session()->get('current_company_id') ?? 0);
    $employeeId = (int) ($request->query('employee_id') ?? $request->session()->get('current_employee_id') ?? 0);

    if ($companyId <= 0 || $employeeId <= 0) {
        return redirect()->route('surveillance.list')->withErrors(['record' => 'Please choose company and employee first.']);
    }

    $request->session()->put('current_company_id', $companyId);
    $request->session()->put('current_employee_id', $employeeId);
    $request->session()->forget(['current_surveillance_id', 'current_declaration_id']);
    $request->session()->put('fresh_surveillance_record', true);

    return redirect()->route('surveillance.declaration', [
        'fresh' => 1,
        'company_id' => $companyId,
        'employee_id' => $employeeId,
    ]);
})->name('surveillance.record.start');

Route::get('/surveillance/record/{id}/edit', $render('auth.edit_surveillanceRecord'))->name('surveillance.record.edit');
Route::get('/surveillance/record/{id}/delete', $render('auth.delete_surveillanceRecord'))->name('surveillance.record.delete');

Route::get('/audiometry/company/new', $render('auth.new_company'))->name('audiometry.company.new');
Route::get('/audiometry/company/{id}/edit', $render('auth.edit_audioComp'))->name('audiometry.company.edit');
Route::get('/audiometry/company/{id}/delete', $render('auth.delete_audioComp'))->name('audiometry.company.delete');
Route::get('/audiometry/employee/new', $render('auth.new_employee'))->name('audiometry.employee.new');
Route::get('/audiometry/employee/{id}/edit', $render('auth.edit_audioEmp'))->name('audiometry.employee.edit');
Route::get('/audiometry/employee/{id}/delete', $render('auth.delete_audioEmp'))->name('audiometry.employee.delete');
Route::get('/audiometry/record/{id}/edit', $render('auth.edit_audioRecord'))->name('audiometry.record.edit');
Route::get('/audiometry/record/{id}/delete', $render('auth.delete_audioRecord'))->name('audiometry.record.delete');
Route::get('/audiometry/questionnaire/{id}/edit', $render('auth.edit_questionnaire'))->name('audiometry.questionnaire.edit');
Route::get('/audiometry/questionnaire/{id}/delete', $render('auth.delete_questionnaire'))->name('audiometry.questionnaire.delete');
$flashBack = static function (string $message) {
    return function () use ($message) {
        return redirect()->back()->with('status', $message);
    };
};

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    return redirect()->route('login')
        ->withErrors(['auth' => 'Invalid username or password.'])
        ->withInput($request->only('username'));
})->name('login.store');
Route::post('/forgot-password', $flashBack('Password reset request submitted.'))->name('password.email');
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

Route::post('/settings/header/upload', function (Request $request) use ($storeUserAsset) {
    return $storeUserAsset($request, 'header_image', 'header_path', 'headers', 'Header image updated.');
})->name('settings.header.upload');
Route::post('/settings/header/delete', function () use ($deleteUserAsset) {
    return $deleteUserAsset('header_path', 'Header image deleted.');
})->name('settings.header.delete');
Route::post('/settings/signature/upload', function (Request $request) use ($storeUserBase64Asset) {
    return $storeUserBase64Asset($request, 'signature_data', 'signature_path', 'signatures', 'Signature image updated.');
})->name('settings.signature.upload');
Route::post('/settings/signature/delete', function () use ($deleteUserAsset) {
    return $deleteUserAsset('signature_path', 'Signature image deleted.');
})->name('settings.signature.delete');

Route::post('/account/profile-photo/upload', function (Request $request) use ($storeUserAsset) {
    return $storeUserAsset($request, 'profile_photo', 'profile_photo_path', 'profiles', 'Profile photo updated.');
})->name('account.profile-photo.upload');
Route::post('/account/profile-photo/delete', function () use ($deleteUserAsset) {
    return $deleteUserAsset('profile_photo_path', 'Profile photo deleted.');
})->name('account.profile-photo.delete');
Route::post('/account/password/update', function (Request $request) use ($resolveSettingsUser) {
    $user = $resolveSettingsUser();
    if (! $user) {
        return redirect()->route('login')->withErrors(['auth' => 'Please log in first.']);
    }

    $validated = $request->validate([
        'current_password' => ['required', 'string'],
        'new_password' => ['required', 'string', 'min:6', 'same:new_password_confirmation'],
        'new_password_confirmation' => ['required', 'string', 'min:6'],
    ]);

    if (! Hash::check($validated['current_password'], $user->password)) {
        return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
    }

    DB::table('users')->where('user_id', $user->user_id)->update([
        'password' => Hash::make($validated['new_password']),
    ]);

    return redirect()->back()->with('status', 'Password updated.');
})->name('account.password.update');


Route::get('/surveillance/employee/{id}/delete-alias', function ($id) use ($render) {
    return ($render('auth.delete_surveillanceEmp'))($id);
})->name('surveillance.employee.delete');
Route::post('/surveillance/company/store', function (Request $request) use ($combinePhone) {
    $validated = $request->validate([
        'company_name' => ['required', 'string', 'max:150'],
        'mykpp_registration_no' => ['nullable', 'string', 'max:100'],
        'company_address' => ['nullable', 'string', 'max:255'],
        'company_postcode' => ['nullable', 'string', 'max:10'],
        'company_district' => ['nullable', 'string', 'max:100'],
        'company_state' => ['nullable', 'string', 'max:100'],
        'company_phone_code' => ['nullable', 'string', 'max:10'],
        'company_telephone' => ['nullable', 'string', 'max:20'],
        'company_email' => ['nullable', 'string', 'max:150'],
        'company_fax' => ['nullable', 'string', 'max:30'],
        'total_workers' => ['nullable', 'integer', 'min:0'],
    ]);

    $companyId = DB::table('company')->insertGetId([
        'company_name' => $validated['company_name'],
        'mykpp_registration_no' => $validated['mykpp_registration_no'] ?? null,
        'company_address' => $validated['company_address'] ?? null,
        'company_postcode' => $validated['company_postcode'] ?? null,
        'company_district' => $validated['company_district'] ?? null,
        'company_state' => $validated['company_state'] ?? null,
        'company_telephone' => $combinePhone($validated['company_phone_code'] ?? null, $validated['company_telephone'] ?? null),
        'company_email' => $validated['company_email'] ?? null,
        'company_fax' => $validated['company_fax'] ?? null,
        'total_workers' => (int) ($validated['total_workers'] ?? 0),
    ]);

    $request->session()->put('current_company_id', $companyId);

    return redirect()->route('surveillance.company')->with('status', 'Company saved successfully.');
})->name('surveillance.company.store');
Route::post('/surveillance/company/update', function (Request $request) use ($combinePhone) {
    $validated = $request->validate([
        'company_id' => ['required', 'integer'],
        'company_name' => ['required', 'string', 'max:150'],
        'mykpp_registration_no' => ['nullable', 'string', 'max:100'],
        'company_address' => ['nullable', 'string', 'max:255'],
        'company_postcode' => ['nullable', 'string', 'max:10'],
        'company_district' => ['nullable', 'string', 'max:100'],
        'company_state' => ['nullable', 'string', 'max:100'],
        'company_phone_code' => ['nullable', 'string', 'max:10'],
        'company_telephone' => ['nullable', 'string', 'max:20'],
        'company_email' => ['nullable', 'string', 'max:150'],
        'company_fax' => ['nullable', 'string', 'max:30'],
        'total_workers' => ['nullable', 'integer', 'min:0'],
    ]);

    DB::table('company')->where('company_id', $validated['company_id'])->update([
        'company_name' => $validated['company_name'],
        'mykpp_registration_no' => $validated['mykpp_registration_no'] ?? null,
        'company_address' => $validated['company_address'] ?? null,
        'company_postcode' => $validated['company_postcode'] ?? null,
        'company_district' => $validated['company_district'] ?? null,
        'company_state' => $validated['company_state'] ?? null,
        'company_telephone' => $combinePhone($validated['company_phone_code'] ?? null, $validated['company_telephone'] ?? null),
        'company_email' => $validated['company_email'] ?? null,
        'company_fax' => $validated['company_fax'] ?? null,
        'total_workers' => (int) ($validated['total_workers'] ?? 0),
    ]);

    $request->session()->put('current_company_id', (int) $validated['company_id']);

    return redirect()->route('surveillance.company')->with('status', 'Company updated successfully.');
})->name('surveillance.company.update');
Route::post('/surveillance/company/destroy', function (Request $request) {
    $companyId = (int) $request->input('company_id');
    if ($companyId > 0) {
        DB::table('company')->where('company_id', $companyId)->delete();
        if ((int) $request->session()->get('current_company_id') === $companyId) {
            $request->session()->forget('current_company_id');
        }
    }

    return redirect()->route('surveillance.company')->with('status', 'Company deleted successfully.');
})->name('surveillance.company.destroy');
Route::post('/surveillance/employee/store', function (Request $request) use ($combinePhone, $syncLatestCompany, $refreshCompanyWorkerTotals) {
    $validated = $request->validate([
        'employee_firstName' => ['required', 'string', 'max:100'],
        'employee_lastName' => ['required', 'string', 'max:100'],
        'employee_NRIC' => ['nullable', 'string', 'max:20'],
        'employee_passportNo' => ['nullable', 'string', 'max:30'],
        'employee_DOB' => ['nullable', 'date'],
        'employee_gender' => ['nullable', 'string', 'max:20'],
        'current_job_title' => ['required', 'string', 'max:150'],
        'current_company_name' => ['required', 'string', 'max:150'],
        'current_employment_duration' => ['required', 'string', 'max:100'],
        'current_chemical_exposure_duration' => ['required', 'string', 'max:100'],
        'current_chemical_exposure_incidents' => ['required', 'string'],
        'employee_address' => ['nullable', 'string', 'max:255'],
        'employee_postcode' => ['nullable', 'string', 'max:10'],
        'employee_district' => ['nullable', 'string', 'max:100'],
        'employee_state' => ['nullable', 'string', 'max:100'],
        'employee_phone_code' => ['nullable', 'string', 'max:10'],
        'employee_telephone' => ['nullable', 'string', 'max:20'],
        'employee_email' => ['nullable', 'string', 'max:150'],
        'employee_ethnicity' => ['nullable', 'string', 'max:50'],
        'employee_citizenship' => ['nullable', 'string', 'max:50'],
        'employee_martialStatus' => ['nullable', 'string', 'max:50'],
        'no_of_children' => ['nullable', 'integer'],
        'years_married' => ['nullable', 'integer'],
        'employee_sign' => ['nullable', 'string'],
        'diagnosed_history' => ['nullable', 'string'],
        'medication_history' => ['nullable', 'string'],
        'admitted_history' => ['nullable', 'string'],
        'family_history' => ['nullable', 'string'],
        'others_history' => ['nullable', 'string'],
        'occup_job_title' => ['nullable', 'array'],
        'occup_job_title.*' => ['nullable', 'string', 'max:150'],
        'occup_company_name' => ['nullable', 'array'],
        'occup_company_name.*' => ['nullable', 'string', 'max:150'],
        'employment_duration' => ['nullable', 'array'],
        'employment_duration.*' => ['nullable', 'string', 'max:100'],
        'chemical_exposure_duration' => ['nullable', 'array'],
        'chemical_exposure_duration.*' => ['nullable', 'string', 'max:100'],
        'chemical_exposure_incidents' => ['nullable', 'array'],
        'chemical_exposure_incidents.*' => ['nullable', 'string'],
        'smoking_history' => ['nullable', 'string', 'max:50'],
        'years_of_smoking' => ['nullable', 'integer'],
        'no_of_cigarettes' => ['nullable', 'integer'],
        'vaping_history' => ['nullable', 'string', 'max:10'],
        'years_of_vaping' => ['nullable', 'integer'],
        'hobby' => ['nullable', 'string'],
        'handling_of_chemical' => ['nullable', 'string', 'max:10'],
        'chemical_comments' => ['nullable', 'string'],
        'sign_symptoms' => ['nullable', 'string', 'max:10'],
        'sign_comments' => ['nullable', 'string'],
        'chemical_poisoning' => ['nullable', 'string', 'max:10'],
        'poisoning_comments' => ['nullable', 'string'],
        'proper_PPE' => ['nullable', 'string', 'max:10'],
        'proper_comments' => ['nullable', 'string'],
        'PPE_usage' => ['nullable', 'string', 'max:10'],
        'usage_comments' => ['nullable', 'string'],
    ]);

    $employeeId = DB::table('employee')->insertGetId([
        'employee_firstName' => $validated['employee_firstName'],
        'employee_lastName' => $validated['employee_lastName'],
        'employee_NRIC' => $validated['employee_NRIC'] ?? null,
        'employee_passportNo' => $validated['employee_passportNo'] ?? null,
        'employee_DOB' => $validated['employee_DOB'] ?? null,
        'employee_gender' => $validated['employee_gender'] ?? null,
        'employee_address' => $validated['employee_address'] ?? null,
        'employee_postcode' => $validated['employee_postcode'] ?? null,
        'employee_district' => $validated['employee_district'] ?? null,
        'employee_state' => $validated['employee_state'] ?? null,
        'employee_telephone' => $combinePhone($validated['employee_phone_code'] ?? null, $validated['employee_telephone'] ?? null),
        'employee_email' => $validated['employee_email'] ?? null,
        'employee_ethnicity' => $validated['employee_ethnicity'] ?? null,
        'employee_citizenship' => $validated['employee_citizenship'] ?? null,
        'employee_martialStatus' => $validated['employee_martialStatus'] ?? null,
        'no_of_children' => $validated['no_of_children'] ?? 0,
        'years_married' => $validated['years_married'] ?? 0,
        'employee_sign' => $validated['employee_sign'] ?? null,
    ]);

    DB::table('medical_history')->updateOrInsert(
        ['employee_id' => $employeeId],
        [
            'diagnosed_history' => $validated['diagnosed_history'] ?? null,
            'medication_history' => $validated['medication_history'] ?? null,
            'admitted_history' => $validated['admitted_history'] ?? null,
            'family_history' => $validated['family_history'] ?? null,
            'others_history' => $validated['others_history'] ?? null,
        ]
    );

    $currentOccupationalRow = [
        'job_title' => trim((string) ($validated['current_job_title'] ?? '')),
        'company_name' => trim((string) ($validated['current_company_name'] ?? '')),
        'employment_duration' => trim((string) ($validated['current_employment_duration'] ?? '')),
        'chemical_exposure_duration' => trim((string) ($validated['current_chemical_exposure_duration'] ?? '')),
        'chemical_exposure_incidents' => trim((string) ($validated['current_chemical_exposure_incidents'] ?? '')),
    ];
    $occupationalRows = [];
    $jobTitles = $validated['occup_job_title'] ?? [];
    $companyNames = $validated['occup_company_name'] ?? [];
    $employmentDurations = $validated['employment_duration'] ?? [];
    $exposureDurations = $validated['chemical_exposure_duration'] ?? [];
    $incidents = $validated['chemical_exposure_incidents'] ?? [];
    $rowCount = max(count($jobTitles), count($companyNames), count($employmentDurations), count($exposureDurations), count($incidents));
    DB::table('occupational_history')->where('employee_id', $employeeId)->delete();
    $occupationalRows[] = $currentOccupationalRow;
    DB::table('occupational_history')->insert(array_merge($currentOccupationalRow, [
        'employee_id' => $employeeId,
    ]));
    for ($index = 0; $index < $rowCount; $index++) {
        $row = [
            'job_title' => trim((string) ($jobTitles[$index] ?? '')),
            'company_name' => trim((string) ($companyNames[$index] ?? '')),
            'employment_duration' => trim((string) ($employmentDurations[$index] ?? '')),
            'chemical_exposure_duration' => trim((string) ($exposureDurations[$index] ?? '')),
            'chemical_exposure_incidents' => trim((string) ($incidents[$index] ?? '')),
        ];
        if (implode('', $row) === '') {
            continue;
        }
        $occupationalRows[] = $row;
        DB::table('occupational_history')->insert(array_merge($row, [
            'employee_id' => $employeeId,
        ]));
    }

    DB::table('personal_social_history')->updateOrInsert(
        ['employee_id' => $employeeId],
        [
            'smoking_history' => $validated['smoking_history'] ?? null,
            'years_of_smoking' => $validated['years_of_smoking'] ?? null,
            'no_of_cigarettes' => $validated['no_of_cigarettes'] ?? null,
            'vaping_history' => $validated['vaping_history'] ?? null,
            'years_of_vaping' => $validated['years_of_vaping'] ?? null,
            'hobby' => $validated['hobby'] ?? null,
        ]
    );
    DB::table('training_history')->updateOrInsert(
        ['employee_id' => $employeeId],
        [
            'handling_of_chemical' => $validated['handling_of_chemical'] ?? null,
            'chemical_comments' => $validated['chemical_comments'] ?? null,
            'sign_symptoms' => $validated['sign_symptoms'] ?? null,
            'sign_comments' => $validated['sign_comments'] ?? null,
            'chemical_poisoning' => $validated['chemical_poisoning'] ?? null,
            'poisoning_comments' => $validated['poisoning_comments'] ?? null,
            'proper_PPE' => $validated['proper_PPE'] ?? null,
            'proper_comments' => $validated['proper_comments'] ?? null,
            'PPE_usage' => $validated['PPE_usage'] ?? null,
            'usage_comments' => $validated['usage_comments'] ?? null,
        ]
    );

    $request->session()->put('current_employee_id', $employeeId);
    $latestCompanyId = $syncLatestCompany([$currentOccupationalRow]);
    if ($latestCompanyId) {
        $request->session()->put('current_company_id', $latestCompanyId);
    }

    return redirect()->route('surveillance.employee')->with('status', 'Employee saved successfully.');
})->name('surveillance.employee.store');
Route::post('/surveillance/employee/update', function (Request $request) use ($combinePhone, $syncLatestCompany, $refreshCompanyWorkerTotals) {
    $validated = $request->validate([
        'employee_id' => ['required', 'integer'],
        'employee_firstName' => ['required', 'string', 'max:100'],
        'employee_lastName' => ['required', 'string', 'max:100'],
        'employee_NRIC' => ['nullable', 'string', 'max:20'],
        'employee_passportNo' => ['nullable', 'string', 'max:30'],
        'employee_DOB' => ['nullable', 'date'],
        'employee_gender' => ['nullable', 'string', 'max:20'],
        'current_job_title' => ['required', 'string', 'max:150'],
        'current_company_name' => ['required', 'string', 'max:150'],
        'current_employment_duration' => ['required', 'string', 'max:100'],
        'current_chemical_exposure_duration' => ['required', 'string', 'max:100'],
        'current_chemical_exposure_incidents' => ['required', 'string'],
        'employee_address' => ['nullable', 'string', 'max:255'],
        'employee_postcode' => ['nullable', 'string', 'max:10'],
        'employee_district' => ['nullable', 'string', 'max:100'],
        'employee_state' => ['nullable', 'string', 'max:100'],
        'employee_phone_code' => ['nullable', 'string', 'max:10'],
        'employee_telephone' => ['nullable', 'string', 'max:20'],
        'employee_email' => ['nullable', 'string', 'max:150'],
        'employee_ethnicity' => ['nullable', 'string', 'max:50'],
        'employee_citizenship' => ['nullable', 'string', 'max:50'],
        'employee_martialStatus' => ['nullable', 'string', 'max:50'],
        'no_of_children' => ['nullable', 'integer'],
        'years_married' => ['nullable', 'integer'],
        'employee_sign' => ['nullable', 'string'],
        'diagnosed_history' => ['nullable', 'string'],
        'medication_history' => ['nullable', 'string'],
        'admitted_history' => ['nullable', 'string'],
        'family_history' => ['nullable', 'string'],
        'others_history' => ['nullable', 'string'],
        'occup_job_title' => ['nullable', 'array'],
        'occup_job_title.*' => ['nullable', 'string', 'max:150'],
        'occup_company_name' => ['nullable', 'array'],
        'occup_company_name.*' => ['nullable', 'string', 'max:150'],
        'employment_duration' => ['nullable', 'array'],
        'employment_duration.*' => ['nullable', 'string', 'max:100'],
        'chemical_exposure_duration' => ['nullable', 'array'],
        'chemical_exposure_duration.*' => ['nullable', 'string', 'max:100'],
        'chemical_exposure_incidents' => ['nullable', 'array'],
        'chemical_exposure_incidents.*' => ['nullable', 'string'],
        'smoking_history' => ['nullable', 'string', 'max:50'],
        'years_of_smoking' => ['nullable', 'integer'],
        'no_of_cigarettes' => ['nullable', 'integer'],
        'vaping_history' => ['nullable', 'string', 'max:10'],
        'years_of_vaping' => ['nullable', 'integer'],
        'hobby' => ['nullable', 'string'],
        'handling_of_chemical' => ['nullable', 'string', 'max:10'],
        'chemical_comments' => ['nullable', 'string'],
        'sign_symptoms' => ['nullable', 'string', 'max:10'],
        'sign_comments' => ['nullable', 'string'],
        'chemical_poisoning' => ['nullable', 'string', 'max:10'],
        'poisoning_comments' => ['nullable', 'string'],
        'proper_PPE' => ['nullable', 'string', 'max:10'],
        'proper_comments' => ['nullable', 'string'],
        'PPE_usage' => ['nullable', 'string', 'max:10'],
        'usage_comments' => ['nullable', 'string'],
    ]);

    DB::table('employee')->where('employee_id', $validated['employee_id'])->update([
        'employee_firstName' => $validated['employee_firstName'],
        'employee_lastName' => $validated['employee_lastName'],
        'employee_NRIC' => $validated['employee_NRIC'] ?? null,
        'employee_passportNo' => $validated['employee_passportNo'] ?? null,
        'employee_DOB' => $validated['employee_DOB'] ?? null,
        'employee_gender' => $validated['employee_gender'] ?? null,
        'employee_address' => $validated['employee_address'] ?? null,
        'employee_postcode' => $validated['employee_postcode'] ?? null,
        'employee_district' => $validated['employee_district'] ?? null,
        'employee_state' => $validated['employee_state'] ?? null,
        'employee_telephone' => $combinePhone($validated['employee_phone_code'] ?? null, $validated['employee_telephone'] ?? null),
        'employee_email' => $validated['employee_email'] ?? null,
        'employee_ethnicity' => $validated['employee_ethnicity'] ?? null,
        'employee_citizenship' => $validated['employee_citizenship'] ?? null,
        'employee_martialStatus' => $validated['employee_martialStatus'] ?? null,
        'no_of_children' => $validated['no_of_children'] ?? 0,
        'years_married' => $validated['years_married'] ?? 0,
        'employee_sign' => $validated['employee_sign'] ?? null,
    ]);

    DB::table('medical_history')->updateOrInsert(
        ['employee_id' => (int) $validated['employee_id']],
        [
            'diagnosed_history' => $validated['diagnosed_history'] ?? null,
            'medication_history' => $validated['medication_history'] ?? null,
            'admitted_history' => $validated['admitted_history'] ?? null,
            'family_history' => $validated['family_history'] ?? null,
            'others_history' => $validated['others_history'] ?? null,
        ]
    );

    $currentOccupationalRow = [
        'job_title' => trim((string) ($validated['current_job_title'] ?? '')),
        'company_name' => trim((string) ($validated['current_company_name'] ?? '')),
        'employment_duration' => trim((string) ($validated['current_employment_duration'] ?? '')),
        'chemical_exposure_duration' => trim((string) ($validated['current_chemical_exposure_duration'] ?? '')),
        'chemical_exposure_incidents' => trim((string) ($validated['current_chemical_exposure_incidents'] ?? '')),
    ];
    $occupationalRows = [];
    $jobTitles = $validated['occup_job_title'] ?? [];
    $companyNames = $validated['occup_company_name'] ?? [];
    $employmentDurations = $validated['employment_duration'] ?? [];
    $exposureDurations = $validated['chemical_exposure_duration'] ?? [];
    $incidents = $validated['chemical_exposure_incidents'] ?? [];
    $rowCount = max(count($jobTitles), count($companyNames), count($employmentDurations), count($exposureDurations), count($incidents));
    DB::table('occupational_history')->where('employee_id', (int) $validated['employee_id'])->delete();
    $occupationalRows[] = $currentOccupationalRow;
    DB::table('occupational_history')->insert(array_merge($currentOccupationalRow, [
        'employee_id' => (int) $validated['employee_id'],
    ]));
    for ($index = 0; $index < $rowCount; $index++) {
        $row = [
            'job_title' => trim((string) ($jobTitles[$index] ?? '')),
            'company_name' => trim((string) ($companyNames[$index] ?? '')),
            'employment_duration' => trim((string) ($employmentDurations[$index] ?? '')),
            'chemical_exposure_duration' => trim((string) ($exposureDurations[$index] ?? '')),
            'chemical_exposure_incidents' => trim((string) ($incidents[$index] ?? '')),
        ];
        if (implode('', $row) === '') {
            continue;
        }
        $occupationalRows[] = $row;
        DB::table('occupational_history')->insert(array_merge($row, [
            'employee_id' => (int) $validated['employee_id'],
        ]));
    }

    DB::table('personal_social_history')->updateOrInsert(
        ['employee_id' => (int) $validated['employee_id']],
        [
            'smoking_history' => $validated['smoking_history'] ?? null,
            'years_of_smoking' => $validated['years_of_smoking'] ?? null,
            'no_of_cigarettes' => $validated['no_of_cigarettes'] ?? null,
            'vaping_history' => $validated['vaping_history'] ?? null,
            'years_of_vaping' => $validated['years_of_vaping'] ?? null,
            'hobby' => $validated['hobby'] ?? null,
        ]
    );
    DB::table('training_history')->updateOrInsert(
        ['employee_id' => (int) $validated['employee_id']],
        [
            'handling_of_chemical' => $validated['handling_of_chemical'] ?? null,
            'chemical_comments' => $validated['chemical_comments'] ?? null,
            'sign_symptoms' => $validated['sign_symptoms'] ?? null,
            'sign_comments' => $validated['sign_comments'] ?? null,
            'chemical_poisoning' => $validated['chemical_poisoning'] ?? null,
            'poisoning_comments' => $validated['poisoning_comments'] ?? null,
            'proper_PPE' => $validated['proper_PPE'] ?? null,
            'proper_comments' => $validated['proper_comments'] ?? null,
            'PPE_usage' => $validated['PPE_usage'] ?? null,
            'usage_comments' => $validated['usage_comments'] ?? null,
        ]
    );

    $request->session()->put('current_employee_id', (int) $validated['employee_id']);
    $latestCompanyId = $syncLatestCompany([$currentOccupationalRow]);
    if ($latestCompanyId) {
        $request->session()->put('current_company_id', $latestCompanyId);
    }

    return redirect()->route('surveillance.employee')->with('status', 'Employee updated successfully.');
})->name('surveillance.employee.update');
Route::post('/surveillance/employee/destroy', function (Request $request) use ($refreshCompanyWorkerTotals) {
    $employeeId = (int) ($request->input('employee_id') ?: $request->session()->get('current_employee_id'));
    if ($employeeId > 0) {
        DB::table('employee')->where('employee_id', $employeeId)->delete();
        if ((int) $request->session()->get('current_employee_id') === $employeeId) {
            $request->session()->forget('current_employee_id');
        }
    }

    return redirect()->route('surveillance.employee')->with('status', 'Employee deleted successfully.');
})->name('surveillance.employee.destroy');
Route::post('/surveillance/record/update', function (Request $request) {
    $validated = $request->validate([
        'declaration_id' => ['required', 'integer'],
        'employee_date' => ['nullable', 'date'],
        'doctor_date' => ['nullable', 'date'],
    ]);

    DB::table('declaration')->where('declaration_id', $validated['declaration_id'])->update([
        'employee_date' => $validated['employee_date'] ?? null,
        'doctor_date' => $validated['doctor_date'] ?? null,
    ]);

    return redirect()->route('surveillance.list')->with('status', 'Record updated successfully.');
})->name('surveillance.record.update');
Route::post('/surveillance/record/destroy', function (Request $request) {
    $declarationId = (int) $request->input('declaration_id');
    if ($declarationId > 0) {
        DB::table('declaration')->where('declaration_id', $declarationId)->delete();
        if ((int) $request->session()->get('current_declaration_id') === $declarationId) {
            $request->session()->forget('current_declaration_id');
        }
    }

    return redirect()->route('surveillance.list')->with('status', 'Record deleted successfully.');
})->name('surveillance.record.destroy');
Route::post('/surveillance/declaration/save', function (Request $request) use ($ensureDoctorProfile, $decodeSignatureDataUrl) {
    $doctor = $ensureDoctorProfile();
    $companyId = (int) ($request->input('company_id') ?: $request->session()->get('current_company_id') ?: DB::table('company')->max('company_id'));
    $employeeId = (int) ($request->input('employee_id') ?: $request->session()->get('current_employee_id') ?: DB::table('employee')->max('employee_id'));
    $declarationId = (int) ($request->input('declaration_id') ?: 0);

    if ($companyId <= 0 || $employeeId <= 0) {
        return redirect()->route('surveillance.declaration')->withErrors(['company_name' => 'Please create at least one company and one employee first.']);
    }

    $company = DB::table('company')->where('company_id', $companyId)->first();
    $existingDeclaration = $declarationId > 0
        ? DB::table('declaration')->where('declaration_id', $declarationId)->first()
        : null;

    if ($existingDeclaration && !empty($existingDeclaration->surveillance_id)) {
        $surveillanceId = (int) $existingDeclaration->surveillance_id;
        DB::table('chemical_information')->where('surveillance_id', $surveillanceId)->update([
            'company_name' => $company->company_name ?? null,
            'employee_id' => $employeeId,
            'doctor_id' => $doctor->doctor_id,
            'company_id' => $companyId,
        ]);
    } else {
        $surveillanceId = DB::table('chemical_information')->insertGetId([
            'company_name' => $company->company_name ?? null,
            'employee_id' => $employeeId,
            'doctor_id' => $doctor->doctor_id,
            'company_id' => $companyId,
            'chemicals' => null,
            'examination_type' => null,
            'examination_date' => null,
        ]);
    }

    $data = [
        'employee_signature' => $decodeSignatureDataUrl($request->input('employee_signature')),
        'employee_date' => $request->input('employee_date') ?: null,
        'doctor_signature' => $decodeSignatureDataUrl($request->input('doctor_signature')),
        'doctor_date' => $request->input('doctor_date') ?: null,
        'surveillance_id' => $surveillanceId,
        'employee_id' => $employeeId,
        'doctor_id' => $doctor->doctor_id,
        'company_id' => $companyId,
    ];

    if ($existingDeclaration) {
        DB::table('declaration')->where('declaration_id', $existingDeclaration->declaration_id)->update($data);
        $declarationId = (int) $existingDeclaration->declaration_id;
    } else {
        $declarationId = DB::table('declaration')->insertGetId($data);
    }

    $request->session()->put('current_company_id', $companyId);
    $request->session()->put('current_employee_id', $employeeId);
    $request->session()->put('current_surveillance_id', $surveillanceId);
    $request->session()->put('current_declaration_id', $declarationId);

    return redirect()->route('surveillance.examination', [
        'company_id' => $companyId,
        'employee_id' => $employeeId,
        'surveillance_id' => $surveillanceId,
        'declaration_id' => $declarationId,
    ])->with('status', 'Declaration saved successfully.');
})->name('surveillance.declaration.save');
Route::post('/surveillance/chemical-option/store', function (Request $request) {
    $validated = $request->validate([
        'chemical_name' => ['required', 'string', 'max:255'],
    ]);

    $chemicalName = trim((string) ($validated['chemical_name'] ?? ''));
    if ($chemicalName === '') {
        return response()->json(['message' => 'Chemical name is required.'], 422);
    }

    $existing = DB::table('chemical_options')
        ->whereRaw('LOWER(chemical_name) = ?', [strtolower($chemicalName)])
        ->first();

    if (! $existing) {
        DB::table('chemical_options')->insert([
            'chemical_name' => $chemicalName,
        ]);
    }

    return response()->json(['chemical_name' => $chemicalName]);
})->name('surveillance.chemical-option.store');
Route::post('/surveillance/examination/save', function (Request $request) use ($ensureDoctorProfile, $upsertRow, $computeSectionStatuses, $historyFieldNames) {
    $doctor = $ensureDoctorProfile();
    $employeeId = (int) ($request->input('employee_id') ?: $request->session()->get('current_employee_id'));
    $companyId = (int) ($request->input('company_id') ?: $request->session()->get('current_company_id'));
    $surveillanceId = (int) $request->input('surveillance_id');
    $declarationId = (int) $request->input('declaration_id');
    $examDate = $request->input('examination_date') ?: null;
    $expectsJson = $request->expectsJson()
        || $request->ajax()
        || $request->header('X-Requested-With') === 'XMLHttpRequest'
        || $request->input('autosave') === '1';

    if ($employeeId <= 0 || $companyId <= 0) {
        if ($expectsJson) {
            return response()->json(['message' => 'Employee and company are required.'], 422);
        }

        return redirect()->route('surveillance.examination')->withErrors(['employee_id' => 'Employee and company are required.']);
    }

    $matchingSurveillance = null;
    if ($surveillanceId <= 0 && $employeeId > 0 && $examDate) {
        $matchingSurveillance = DB::table('chemical_information')
            ->where('employee_id', $employeeId)
            ->where('examination_date', $examDate)
            ->latest('surveillance_id')
            ->first();
    }

    if ($matchingSurveillance) {
        $surveillanceId = (int) $matchingSurveillance->surveillance_id;
    }

    if ($surveillanceId > 0) {
        DB::table('chemical_information')->where('surveillance_id', $surveillanceId)->update([
            'chemicals' => $request->input('chemicals'),
            'examination_type' => $request->input('examination_type'),
            'examination_date' => $examDate,
            'company_name' => $request->input('company_name'),
            'employee_id' => $employeeId,
            'doctor_id' => $doctor->doctor_id,
            'company_id' => $companyId,
        ]);
    } else {
        $surveillanceId = DB::table('chemical_information')->insertGetId([
            'chemicals' => $request->input('chemicals'),
            'examination_type' => $request->input('examination_type'),
            'examination_date' => $examDate,
            'company_name' => $request->input('company_name'),
            'employee_id' => $employeeId,
            'doctor_id' => $doctor->doctor_id,
            'company_id' => $companyId,
        ]);
    }

    if ($declarationId > 0) {
        DB::table('declaration')->where('declaration_id', $declarationId)->update([
            'surveillance_id' => $surveillanceId,
            'employee_id' => $employeeId,
            'doctor_id' => $doctor->doctor_id,
            'company_id' => $companyId,
        ]);
    }

    $historyPayload = ['others_symptoms' => $request->input('others_effect') ?: null];
    foreach ($historyFieldNames as $field) {
        $inputName = $field;
        if ($field === 'abdominal_mass') {
            $inputName = 'history_abdominal_mass';
        } elseif ($field === 'jaundice') {
            $inputName = 'history_jaundice';
        }
        $historyPayload[$field] = $request->input($inputName) ?: null;
    }
    $upsertRow('history_of_health', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], $historyPayload, 'hoh_id');

    $upsertRow('clinical_findings', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], ['result_clinical_findings' => $request->input('result_clinical_findings'), 'elaboration' => $request->input('elaboration')], 'chHistory_id');
    $upsertRow('physical_examination', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], ['weight' => $request->input('weight') ?: null, 'height' => $request->input('height') ?: null, 'BMI' => $request->input('BMI') ?: null, 'bp_systolic' => $request->input('bp_systolic') ?: null, 'bp_distolic' => $request->input('bp_distolic') ?: null, 'pulse_rate' => $request->input('pulse_rate') ?: null, 'respiratory_rate' => $request->input('respiratory_rate') ?: null, 'general_appearances' => $request->input('general_appearances'), 's1_s2' => $request->input('s1_s2'), 'murmur' => $request->input('murmur'), 'ear_nose_throat' => $request->input('ear_nose_throat'), 'visual_acuity_right' => $request->input('visual_acuity_right'), 'visual_acuity_left' => $request->input('visual_acuity_left'), 'colour_blindness' => $request->input('colour_blindness'), 'gas_tenderness' => $request->input('gas_tenderness'), 'abdominal_mass' => $request->input('abdominal_mass'), 'lymph_nodes' => $request->input('lymph_nodes'), 'splenomegaly' => $request->input('splenomegaly'), 'kidney_tenderness' => $request->input('kidney_tenderness'), 'ballotable' => $request->input('ballotable'), 'jaundice' => $request->input('jaundice'), 'hepatomegaly' => $request->input('hepatomegaly'), 'muscle_tone' => $request->input('muscle_tone'), 'muscle_tenderness' => $request->input('muscle_tenderness'), 'power' => $request->input('power'), 'sensation' => $request->input('sensation'), 'sound' => $request->input('sound'), 'air_entry' => $request->input('air_entry'), 'reproductive' => $request->input('reproductive'), 'skin' => $request->input('skin'), 'others' => $request->input('others')], 'pexamHistory_id');
    $upsertRow('target_organ', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], ['blood_count' => $request->input('blood_count'), 'blood_comments' => $request->input('blood_comments'), 'renal_function' => $request->input('renal_function'), 'renal_comments' => $request->input('renal_comments'), 'liver_function' => $request->input('liver_function'), 'liver_comments' => $request->input('liver_comments'), 'chest_xray' => $request->input('chest_xray'), 'chest_comments' => $request->input('chest_comments'), 'spirometry_FEV1' => $request->input('spirometry_FEV1') ?: null, 'spirometry_FVC' => $request->input('spirometry_FVC') ?: null, 'spirometry_FEV_FVC' => $request->input('spirometry_FEV_FVC') ?: null, 'spirometry_comments' => $request->input('spirometry_comments')], 'target_id');
    $upsertRow('biological_monitoring', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], ['biological_exposure' => null, 'baseline_results' => $request->input('baseline_results'), 'baseline_annual' => $request->input('baseline_annual')], 'bioMonitor_id');
    $upsertRow('fitness_report', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], ['result' => $request->input('fitness_result') ?: null, 'remarks' => $request->input('fitness_justification') ?: null, 'company_id' => $companyId, 'doctor_id' => $doctor->doctor_id], 'fitnessReport_id');
    $upsertRow('ms_findings', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], ['history_of_health' => $request->input('history_of_health'), 'clinical_findings' => $request->input('clinical_findings'), 'CF_work_related' => $request->input('CF_work_related'), 'target_organ' => $request->input('target_organ'), 'TO_work_related' => $request->input('TO_work_related'), 'biological_monitoring' => $request->input('biological_monitoring'), 'BM_work_related' => $request->input('BM_work_related'), 'pregnancy_breastFeding' => $request->input('pregnancy_breastFeding'), 'conclusion_fitness' => $request->input('conclusion_fitness')], 'msFindings_id');
    $upsertRow('recommendation', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], ['recommencation_type' => $request->input('recommencation_type'), 'MRPdate_start' => $request->input('MRPdate_start') ?: null, 'MRPdate_end' => $request->input('MRPdate_end') ?: null, 'nextReview_date' => $request->input('nextReview_date') ?: null, 'notes' => $request->input('notes')], 'recommendation_id');

    $chemicalInfo = DB::table('chemical_information')->where('surveillance_id', $surveillanceId)->first();
    $historyOfHealth = DB::table('history_of_health')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first();
    $clinicalFindings = DB::table('clinical_findings')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first();
    $physicalExam = DB::table('physical_examination')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first();
    $targetOrgan = DB::table('target_organ')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first();
    $biologicalMonitoring = DB::table('biological_monitoring')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first();
    $fitnessRow = DB::table('fitness_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first();
    $msFindings = DB::table('ms_findings')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first();
    $recommendationData = DB::table('recommendation')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first();
    $sectionStatuses = $computeSectionStatuses($chemicalInfo, $historyOfHealth, $clinicalFindings, $physicalExam, $targetOrgan, $biologicalMonitoring, $fitnessRow, $msFindings, $recommendationData);

    $request->session()->put('current_surveillance_id', $surveillanceId);
    $request->session()->put('current_employee_id', $employeeId);
    $request->session()->put('current_company_id', $companyId);
    if ($declarationId > 0) {
        $request->session()->put('current_declaration_id', $declarationId);
    }

    if ($expectsJson) {
        return response()->json([
            'surveillance_id' => $surveillanceId,
            'declaration_id' => $declarationId,
            'sectionStatuses' => $sectionStatuses,
            'message' => 'Examination saved successfully.',
        ]);
    }

    return redirect()->route('surveillance.report', ['declaration_id' => $declarationId, 'surveillance_id' => $surveillanceId, 'employee_id' => $employeeId, 'company_id' => $companyId])->with('status', 'Examination saved successfully.');
})->name('surveillance.examination.save');
Route::post('/surveillance/fitness-report/save', function (Request $request) use ($ensureDoctorProfile, $upsertRow) {
    $doctor = $ensureDoctorProfile();
    $employeeId = (int) ($request->input('employee_id') ?: $request->session()->get('current_employee_id') ?: 0);
    $companyId = (int) ($request->input('company_id') ?: $request->session()->get('current_company_id') ?: 0);
    $surveillanceId = (int) ($request->input('surveillance_id') ?: $request->session()->get('current_surveillance_id') ?: 0);
    $fitnessResult = trim((string) $request->input('fitness_result', ''));
    $remarks = trim((string) $request->input('remarks', ''));

    if ($employeeId <= 0 || $companyId <= 0 || $surveillanceId <= 0) {
        return redirect()->back()->withErrors(['remarks' => 'Employee, company, and surveillance record are required before saving the certificate.'])->withInput();
    }

    if ($fitnessResult === '') {
        $fitnessResult = trim((string) (DB::table('ms_findings')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->value('conclusion_fitness') ?? ''));
    }

    if ($fitnessResult === '') {
        return redirect()->back()->withErrors(['remarks' => 'Examination result is required before saving the certificate of fitness.'])->withInput();
    }

    $upsertRow('fitness_report', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], [
        'result' => $fitnessResult,
        'remarks' => $remarks !== '' ? $remarks : null,
        'company_id' => $companyId,
        'doctor_id' => $doctor->doctor_id,
    ], 'fitnessReport_id');

    return redirect()->route('general.report')->with('status', 'Certificate of fitness saved successfully.');
})->name('surveillance.report.fitness.save');

Route::post('/surveillance/removal-report/save', function (Request $request) use ($ensureDoctorProfile, $upsertRow) {
    $doctor = $ensureDoctorProfile();
    $employeeId = (int) ($request->input('employee_id') ?: $request->session()->get('current_employee_id') ?: 0);
    $companyId = (int) ($request->input('company_id') ?: $request->session()->get('current_company_id') ?: 0);
    $surveillanceId = (int) ($request->input('surveillance_id') ?: $request->session()->get('current_surveillance_id') ?: 0);
    $fitnessRow = ($employeeId > 0 && $surveillanceId > 0)
        ? DB::table('fitness_report')->where('employee_id', $employeeId)->where('surveillance_id', $surveillanceId)->first()
        : null;

    if ($employeeId <= 0 || $companyId <= 0 || $surveillanceId <= 0 || !$fitnessRow) {
        return redirect()->back()->withErrors(['removal_type' => 'A saved examination result is required before completing the removal report.']);
    }

    $reasonParts = array_values(array_filter([
        $request->boolean('reason_pregnancy') ? 'Pregnancy' : null,
        $request->boolean('reason_bm') ? 'Abnormal BM/BEM result' : null,
        $request->boolean('reason_breastfeeding') ? 'Breastfeeding' : null,
        $request->boolean('reason_clinical') ? 'Adverse health effects based on clinical findings' : null,
        $request->boolean('reason_target_organ') ? 'Target organ function test abnormality' : null,
        trim((string) $request->input('reason_other')) !== '' ? 'Other: ' . trim((string) $request->input('reason_other')) : null,
    ]));
    $removalType = $request->input('removal_type') ?: ($request->boolean('removal_type_permanent') ? 'Permanent' : ($request->boolean('removal_type_temporary') ? 'Temporary' : ''));

    if ($removalType === '' || empty($reasonParts)) {
        return redirect()->back()->withErrors(['removal_type' => 'Removal type and at least one recommendation reason are required.'])->withInput();
    }

    $upsertRow('removal_report', ['employee_id' => $employeeId, 'surveillance_id' => $surveillanceId], [
        'removal_type' => $removalType,
        'reasons_recommendations' => implode('; ', $reasonParts),
        'fitnessReport_id' => $fitnessRow->fitnessReport_id,
        'doctor_id' => $doctor->doctor_id,
        'company_id' => $companyId,
    ], 'removalReport_id');

    return redirect()->route('general.report')->with('status', 'Removal report saved successfully.');
})->name('surveillance.report.removal.save');









































































