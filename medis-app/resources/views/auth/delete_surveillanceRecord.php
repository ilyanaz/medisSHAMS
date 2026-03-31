<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Surveillance Record</title>
    <style>
        :root{--line:#e5e7eb;--bg:rgba(15,23,42,.55);--panel:#fff;--text:#111827;--muted:#6b7280;--danger:#dc2626}
        *{box-sizing:border-box}body{margin:0;font-family:"Poppins","Segoe UI",Tahoma,Geneva,Verdana,sans-serif;background:#f3f4f6}
        .overlay{min-height:100vh;background:var(--bg);display:grid;place-items:center;padding:24px}
        .modal{width:min(820px,100%);background:var(--panel);border-radius:20px;box-shadow:0 20px 55px rgba(15,23,42,.25);padding:26px}
        h1{margin:0 0 6px;font-size:2rem}.muted{margin:0;color:var(--muted)}
        .alert{margin-top:16px;padding:12px 14px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:12px}
        .box{margin-top:16px;border:1px solid #e8ebf2;border-radius:16px;padding:16px;background:#fafafa;display:grid;gap:8px}
        .actions{margin-top:18px;display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap}
        .btn{border:1px solid #d1d5db;border-radius:10px;padding:10px 14px;background:#fff;color:#374151;text-decoration:none;font-size:.92rem;display:inline-flex;align-items:center;gap:6px;cursor:pointer}
        .btn.primary{background:var(--danger);border-color:var(--danger);color:#fff;font-weight:600}
    </style>
</head>
<body>
<?php
require_once __DIR__ . '/view_bootstrap.php';
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$backUrl = function_exists('route') ? route('surveillance.list') : '#';
$record = isset($declarationData) && $declarationData ? $declarationData : (object) [];
$employeeName = trim(((string) ($record->employee_firstName ?? '')) . ' ' . ((string) ($record->employee_lastName ?? '')));
?>
<div class="overlay"><form class="modal" method="post" action="<?php echo $esc(route('surveillance.record.destroy')); ?>"><input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>"><input type="hidden" name="declaration_id" value="<?php echo $esc($record->declaration_id ?? ''); ?>"><h1>Delete Surveillance Record</h1><p class="muted">Confirm deletion for the selected declaration record.</p><div class="alert">This action cannot be undone. Please confirm before continuing.</div><div class="box"><div><strong>Record ID:</strong> #SUR<?php echo $esc($record->declaration_id ?? ''); ?></div><div><strong>Employee:</strong> <?php echo $esc($employeeName !== '' ? $employeeName : 'Not set'); ?></div><div><strong>Company:</strong> <?php echo $esc($record->company_name ?? 'Not set'); ?></div><div><strong>Employee Date:</strong> <?php echo $esc($record->employee_date ?? '-'); ?></div><div><strong>Doctor Date:</strong> <?php echo $esc($record->doctor_date ?? '-'); ?></div></div><div class="actions"><a class="btn" href="<?php echo $esc($backUrl); ?>">Cancel</a><button class="btn primary" type="submit">Delete Record</button></div></form></div>
</body>
</html>

