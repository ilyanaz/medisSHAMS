<?php
require __DIR__ . '/navigation.php';

$esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$sourceUrl = function_exists('route') ? route('surveillance.report.summary') : 'surveillance_summaryReport.php';
$backUrl = function_exists('route') ? route('general.report') : 'general_report.php';

medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => 'report',
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PDF USECHH 4</title>
</head>
<body>
<style>
.pdf-page{display:grid;gap:18px;color:#0f172a;font-family:"Poppins","Segoe UI",Tahoma,Geneva,Verdana,sans-serif}.page-head h2{margin:0;font-size:1.9rem}.page-head p{margin:8px 0 0;color:#6b7280}.preview-shell{display:grid;gap:16px}.preview-card{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:18px;box-shadow:0 10px 30px rgba(15,23,42,.04)}.preview-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.meta-box{border:1px solid #e5e7eb;border-radius:16px;background:#fafafa;padding:14px}.meta-box span{display:block;color:#6b7280;font-size:.84rem}.meta-box strong{display:block;margin-top:6px;font-size:1rem;color:#111827}.preview-frame-card{border:1px solid #e5e7eb;border-radius:22px;background:#fff;overflow:hidden}.preview-frame-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:16px 18px;border-bottom:1px solid #edf0f2}.preview-frame-head h3{margin:0;font-size:1.05rem}.preview-frame-head p{margin:4px 0 0;color:#6b7280;font-size:.9rem}.preview-actions{display:flex;gap:10px;flex-wrap:wrap}.btn,.next{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;font:inherit;cursor:pointer}.next{background:#389B5B;border-color:#389B5B;color:#fff}.preview-iframe{display:block;width:100%;height:900px;border:0;background:#fff}.notice{padding:0 18px 18px;color:#6b7280;font-size:.9rem}@media(max-width:980px){.preview-meta{grid-template-columns:1fr}.preview-iframe{height:680px}}
</style>
<div class="pdf-page">
    <section class="page-head">
        <h2>PDF USECHH 4</h2>
        <p>Preview the USECHH 4 summary report for medical surveillance before printing.</p>
    </section>

    <section class="preview-shell">
        <div class="preview-meta">
            <div class="meta-box">
                <span>PDF Group</span>
                <strong>USECHH 4</strong>
            </div>
            <div class="meta-box">
                <span>Preview Source</span>
                <strong>USECHH 4 Summary Report</strong>
            </div>
            <div class="meta-box">
                <span>Mode</span>
                <strong>Print Page</strong>
            </div>
        </div>

        <section class="preview-frame-card">
            <div class="preview-frame-head">
                <div>
                    <h3>Preview</h3>
                    <p>Open the print page and print the document directly from here.</p>
                </div>
                <div class="preview-actions">
                    <a class="btn" href="<?php echo $esc($backUrl); ?>">Back</a>
                    
                    <button class="next" type="button" onclick="window.frames['pdfPreviewFrame'].print()">Print</button>
                </div>
            </div>
            <iframe class="preview-iframe" name="pdfPreviewFrame" src="<?php echo $esc($sourceUrl); ?>" title="PDF USECHH 4 preview"></iframe>
            <div class="notice">Use the Print button to print this document directly.</div>
        </section>
    </section>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>