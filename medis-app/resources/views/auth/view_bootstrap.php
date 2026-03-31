<?php

if (!function_exists('collect')) {
    function collect($items = [])
    {
        if ($items === null) {
            return [];
        }

        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return [$items];
    }
}

if (!function_exists('old')) {
    function old($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
}

if (!function_exists('session')) {
    function session($key = null, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if ($key === null) {
            return $_SESSION ?? [];
        }

        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(16));
        }

        return $_SESSION['_token'];
    }
}

if (!function_exists('asset')) {
    function asset($path)
    {
        return '/' . ltrim((string) $path, '/');
    }
}

if (!function_exists('route')) {
    function route($name, $parameters = [])
    {
        $routes = [
            'dashboard' => 'dashboard.php',
            'profile' => 'profile.php',
            'settings' => 'settings.php',
            'account.settings' => 'account_settings.php',
            'account.password.update' => 'account_settings.php',
            'account.profile-photo.upload' => 'account_settings.php',
            'account.profile-photo.delete' => 'account_settings.php',
            'logout.page' => 'logout.php',
            'general.report' => 'general_report.php',
            'general.examination' => 'surveillance_examination.php',
            'surveillance.company' => 'surveillance_company.php',
            'surveillance.company.new' => 'new_company.php',
            'surveillance.company.store' => 'new_company.php',
            'surveillance.company.edit' => 'edit_surveillanceComp.php',
            'surveillance.company.update' => 'edit_surveillanceComp.php',
            'surveillance.company.delete' => 'delete_surveillanceComp.php',
            'surveillance.employee' => 'surveillance_employee.php',
            'surveillance.employee.new' => 'new_employee.php',
            'surveillance.employee.store' => 'new_employee.php',
            'surveillance.employee.edit' => 'edit_surveillanceEmp.php',
            'surveillance.employee.update' => 'edit_surveillanceEmp.php',
            'surveillance.employee.delete' => 'delete_surveillanceEmp.php',
            'surveillance.list' => 'surveillance_list.php',
            'surveillance.record.edit' => 'edit_surveillanceRecord.php',
            'surveillance.record.delete' => 'delete_surveillanceRecord.php',
            'surveillance.declaration' => 'declaration.php',
            'surveillance.declaration.save' => 'declaration.php',
            'surveillance.examination' => 'surveillance_examination.php',
            'surveillance.examination.save' => 'surveillance_examination.php',
            'surveillance.confirm' => 'surveillance_confirm.php',
            'surveillance.report' => 'surveillance_report.php',
            'surveillance.chemical-option.store' => 'surveillance_examination.php',
            'audiometry.company' => 'audiometry_company.php',
            'audiometry.company.new' => 'new_company.php',
            'audiometry.company.edit' => 'edit_audioComp.php',
            'audiometry.company.delete' => 'delete_audioComp.php',
            'audiometry.employee' => 'audiometry_employee.php',
            'audiometry.employee.new' => 'new_employee.php',
            'audiometry.employee.edit' => 'edit_audioEmp.php',
            'audiometry.employee.delete' => 'delete_audioEmp.php',
            'audiometry.questionnaire' => 'audiometry_questionnaire.php',
            'audiometry.questionnaire.new' => 'new_questionnaire.php',
            'audiometry.questionnaire.edit' => 'edit_questionnaire.php',
            'audiometry.questionnaire.delete' => 'delete_questionnaire.php',
            'audiometry.list' => 'audiometry_list.php',
            'audiometry.examination' => 'audiometry_examination.php',
            'audiometry.confirm' => 'audiometry_confirm.php',
            'audiometry.report' => 'audiometry_report.php',
            'audiometry.record.edit' => 'edit_audioRecord.php',
            'audiometry.record.delete' => 'delete_audioRecord.php',
            'admin.dashboard' => 'admin_dashboard.php',
            'admin.company' => 'admin_company.php',
            'admin.employee' => 'admin_employee.php',
            'admin.clinic' => 'admin_clinic.php',
            'admin.settings' => 'admin_settings.php',
            'pdf.questionnaire' => 'PDF_questionnaire.php',
            'pdf.audio-report' => 'PDF_audioReport.php',
            'pdf.employee' => 'PDF_employee.php',
            'pdf.usechh1' => 'PDF_USECHH1.php',
            'pdf.usechh2' => 'PDF_USECHH2.php',
            'pdf.usechh3' => 'PDF_USECHH3.php',
            'pdf.usechh4' => 'PDF_USECHH4.php',
            'pdf.usechh5i' => 'PDF_USECHH5i.php',
            'pdf.usechh5ii' => 'PDF_USECHH5ii.php',
        ];

        $url = $routes[$name] ?? '#';
        if ($url === '#' || empty($parameters)) {
            return $url;
        }

        $query = http_build_query(array_filter($parameters, static fn($value) => $value !== null && $value !== ''));
        return $query !== '' ? $url . '?' . $query : $url;
    }
}
