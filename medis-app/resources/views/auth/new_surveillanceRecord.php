<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Surveillance Record</title>
    <style>
        :root{--line:#e5e7eb;--bg:#f3f4f6;--panel:#fff;--text:#111827;--muted:#6b7280}
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--text);font-family:"Poppins","Segoe UI",Tahoma,Geneva,Verdana,sans-serif;padding:24px}
        .page{max-width:920px;margin:0 auto;background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:18px;display:grid;gap:14px}
        h1{margin:0;font-size:1.7rem}.muted{color:var(--muted);margin:0}
        .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
        .field{display:grid;gap:6px;font-size:.86rem}
        input,select,textarea{width:100%;border:1px solid #d1d5db;border-radius:10px;padding:10px 12px;font:inherit}
        textarea{min-height:96px;resize:vertical}.full{grid-column:1/-1}
        .actions{display:flex;justify-content:space-between;align-items:center;gap:8px}
        .btn{display:inline-flex;align-items:center;gap:6px;text-decoration:none;border:1px solid #d1d5db;border-radius:10px;padding:9px 14px;color:#374151;background:#fff;font-size:.9rem}
        .primary{background:#389B5B;border-color:#389B5B;color:#fff}
        .alert{padding:12px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px}
        .box{padding:12px;border:1px solid var(--line);border-radius:10px;background:#fafafa;display:grid;gap:6px}
        @media (max-width:760px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<?php
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$dashboardUrl = function_exists('route') ? route('dashboard') : '#';
?>
<div class="page">
    <div>
        <h1>New Surveillance Record</h1>
        <p class="muted">Create a new surveillance history record.</p>
    </div>
    <div class="grid">
        <label class="field">Name<input type="text" placeholder="Enter name"></label>
        <label class="field">Code / ID<input type="text" placeholder="Enter code"></label>
        <label class="field">Contact<input type="text" placeholder="Enter contact"></label>
        <label class="field">Status<select><option>Active</option><option>Pending</option><option>Inactive</option></select></label>
        <label class="field full">Notes<textarea placeholder="Enter notes"></textarea></label>
    </div>
    <div class="actions">
        <a class="btn" href="<?php echo $esc($dashboardUrl); ?>">Back to Dashboard</a>
        <a class="btn primary" href="#">Save Record</a>
    </div>
</div>
</body>
</html>
