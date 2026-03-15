<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Surveillance Record</title>
    <style>
        :root{--line:#e5e7eb;--bg:rgba(15,23,42,.55);--panel:#fff;--text:#111827;--muted:#6b7280;--green:#389B5B}
        *{box-sizing:border-box}body{margin:0;font-family:"Poppins","Segoe UI",Tahoma,Geneva,Verdana,sans-serif;background:#f3f4f6}
        .overlay{min-height:100vh;background:var(--bg);display:grid;place-items:center;padding:24px}
        .modal{width:min(980px,100%);background:var(--panel);border-radius:20px;box-shadow:0 20px 55px rgba(15,23,42,.25);padding:26px}
        h1{margin:0 0 6px;font-size:2.1rem}.muted{margin:0;color:var(--muted)}
        .panel{margin-top:18px;border:1px solid #e8ebf2;border-radius:16px;padding:16px}
        .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.field{display:block;font-size:.9rem;color:#1f2937}
        .field input,.field textarea{margin-top:6px}input,textarea{width:100%;border:1px solid #d7dce7;border-radius:10px;padding:11px 12px;font:inherit}
        textarea{min-height:90px;resize:vertical}.full{grid-column:1/-1}
        .actions{margin-top:14px;display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap}
        .btn{border:1px solid #d1d5db;border-radius:10px;padding:10px 14px;background:#fff;color:#374151;text-decoration:none;font-size:.92rem;display:inline-flex;align-items:center;gap:6px;cursor:pointer}
        .btn.primary{background:var(--green);border-color:var(--green);color:#fff;font-weight:600}
        .error{margin-top:14px;padding:12px 14px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:12px}
        @media (max-width:760px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<?php
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$backUrl = function_exists('route') ? route('surveillance.list') : '#';
$record = isset($declarationData) && $declarationData ? $declarationData : (object) [];
?>
<div class="overlay"><form class="modal" method="post" action="<?php echo $esc(route('surveillance.record.update')); ?>" id="editRecordForm" novalidate><input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>"><input type="hidden" name="declaration_id" value="<?php echo $esc($record->declaration_id ?? ''); ?>"><h1>Edit Surveillance Record</h1><p class="muted">Update declaration information stored in the current database.</p><?php if(isset($errors) && $errors->any()): ?><div class="error"><?php echo $esc($errors->first()); ?></div><?php endif; ?><div class="panel"><div class="grid"><label class="field">Company Name<input name="company_name" type="text" value="<?php echo $esc(old('company_name', $record->company_name ?? '')); ?>"></label><label class="field">Employee First Name<input name="employee_firstName" type="text" value="<?php echo $esc(old('employee_firstName', $record->employee_firstName ?? '')); ?>"></label><label class="field">Employee Last Name<input name="employee_lastName" type="text" value="<?php echo $esc(old('employee_lastName', $record->employee_lastName ?? '')); ?>"></label><label class="field">Employee Date<input name="employee_date" type="date" value="<?php echo $esc(old('employee_date', $record->employee_date ?? '')); ?>"></label><label class="field">Doctor Date<input name="doctor_date" type="date" value="<?php echo $esc(old('doctor_date', $record->doctor_date ?? '')); ?>"></label><label class="field full">Notes<textarea readonly><?php echo $esc(!empty($record->employee_signature) || !empty($record->doctor_signature) ? 'Signatures already captured for this declaration record.' : 'No signature image stored for this declaration record yet.'); ?></textarea></label></div></div><div class="actions"><a class="btn" href="<?php echo $esc($backUrl); ?>">Cancel</a><button class="btn primary" type="submit">Update Record</button></div></form></div>
<script>(function(){const form=document.getElementById('editRecordForm');form.addEventListener('submit',function(event){if(!form.checkValidity()){event.preventDefault();form.reportValidity();}});})();</script>
</body>
</html>
