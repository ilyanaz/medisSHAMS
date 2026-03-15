<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => 'settings',
]);
$settings = $settingsData ?? (object) [];
$headerUrl = ! empty($settings->header_path) ? asset($settings->header_path) : null;
$signatureUrl = ! empty($settings->signature_path) ? asset($settings->signature_path) : null;
$statusMessage = session('status');
$errorBag = $errors ?? null;
?>
<style>
.settings-page{display:grid;gap:18px}.settings-head h1{margin:0;font-size:1.9rem}.settings-head p{margin:6px 0 0;color:#6b7280}.notice{padding:12px 14px;border-radius:14px;border:1px solid #a7f3d0;background:#ecfdf3;color:#065f46}.error-box{padding:12px 14px;border-radius:14px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b}.settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.asset-card{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:20px;display:grid;gap:14px}.asset-card h2{margin:0;font-size:1.2rem}.asset-card p{margin:0;color:#6b7280}.preview{border:1px dashed #cbd5e1;border-radius:18px;background:#f8fafc;min-height:240px;display:flex;align-items:center;justify-content:center;overflow:hidden}.preview img{width:100%;height:100%;object-fit:contain;background:#fff}.preview.empty{color:#94a3b8;font-size:.92rem;padding:18px;text-align:center}.upload-form{display:grid;gap:12px}.signature-pad-wrap{display:grid;gap:10px}.signature-pad-box{border:1px solid #cbd5e1;border-radius:16px;background:#fff;overflow:hidden}.signature-pad-box canvas{display:block;width:100%;height:220px}.actions{display:flex;gap:10px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;text-decoration:none;cursor:pointer;font:inherit}.btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}.btn.danger{background:#fff;border-color:#fecaca;color:#dc2626}.tips{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:20px}.tips h3{margin:0 0 10px}.tips ul{margin:0;padding-left:18px;color:#475569;display:grid;gap:8px}@media (max-width:980px){.settings-grid{grid-template-columns:1fr}}
</style>
<div class="settings-page">
    <div class="settings-head">
        <h1>Settings</h1>
        <p>Upload and manage your header image and signature.</p>
    </div>

    <?php if (! empty($statusMessage)): ?>
        <div class="notice"><?php echo $esc($statusMessage); ?></div>
    <?php endif; ?>

    <?php if ($errorBag && $errorBag->any()): ?>
        <div class="error-box"><?php echo $esc($errorBag->first()); ?></div>
    <?php endif; ?>

    <div class="settings-grid">
        <section class="asset-card">
            <div>
                <h2>Header Image</h2>
                <p>Upload the clinic header image used in your reports or documents.</p>
            </div>
            <div class="preview<?php echo $headerUrl ? '' : ' empty'; ?>">
                <?php if ($headerUrl): ?>
                    <img src="<?php echo $esc($headerUrl); ?>" alt="Header Preview">
                <?php else: ?>
                    <span>No header image uploaded yet.</span>
                <?php endif; ?>
            </div>
            <form class="upload-form" method="POST" action="<?php echo $esc(route('settings.header.upload')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
                <input type="file" name="header_image" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                <div class="actions">
                    <button class="btn primary" type="submit">Upload Header</button>
                </div>
            </form>
            <form method="POST" action="<?php echo $esc(route('settings.header.delete')); ?>">
                <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
                <button class="btn danger" type="submit"<?php echo $headerUrl ? '' : ' disabled'; ?>>Delete Header</button>
            </form>
        </section>

        <section class="asset-card">
            <div>
                <h2>Signature Image</h2>
                <p>Upload the doctor signature image used in confirmation and document output.</p>
            </div>
            <div class="preview<?php echo $signatureUrl ? '' : ' empty'; ?>">
                <?php if ($signatureUrl): ?>
                    <img src="<?php echo $esc($signatureUrl); ?>" alt="Signature Preview">
                <?php else: ?>
                    <span>No signature image uploaded yet.</span>
                <?php endif; ?>
            </div>
            <form class="upload-form" method="POST" action="<?php echo $esc(route('settings.signature.upload')); ?>" id="settingsSignatureForm">
                <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
                <input type="hidden" name="signature_data" id="settings_signature_data">
                <div class="signature-pad-wrap">
                    <div class="signature-pad-box"><canvas id="settingsSignaturePad"></canvas></div>
                    <div class="actions">
                        <button class="btn" type="button" id="settingsSignatureClear">Clear</button>
                        <button class="btn primary" type="submit">Save Signature</button>
                    </div>
                </div>
            </form>
            <form method="POST" action="<?php echo $esc(route('settings.signature.delete')); ?>">
                <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
                <button class="btn danger" type="submit"<?php echo $signatureUrl ? '' : ' disabled'; ?>>Delete Signature</button>
            </form>
        </section>
    </div>

    <section class="tips">
        <h3>Upload Tips</h3>
        <ul>
            <li>Accepted formats: PNG, JPG, JPEG, WEBP</li>
            <li>Recommended: wide image for header, transparent PNG for signature</li>
            <li>Uploaded files are saved per logged-in user</li>
        </ul>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
(function(){
    var canvas = document.getElementById('settingsSignaturePad');
    var form = document.getElementById('settingsSignatureForm');
    var hidden = document.getElementById('settings_signature_data');
    var clearBtn = document.getElementById('settingsSignatureClear');
    if (!canvas || !form || !hidden || typeof SignaturePad === 'undefined') {
        return;
    }
    var pad = new SignaturePad(canvas, { minWidth: 1.5, maxWidth: 2.5, penColor: '#111827' });
    var initialSrc = <?php echo json_encode($signatureUrl ?: ''); ?>;
    var resize = function(){
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        var rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        pad.clear();
        if (initialSrc) {
            pad.fromDataURL(initialSrc);
        }
    };
    window.addEventListener('resize', resize);
    resize();
    clearBtn.addEventListener('click', function(){
        pad.clear();
        hidden.value = '';
    });
    form.addEventListener('submit', function(event){
        if (pad.isEmpty()) {
            event.preventDefault();
            alert('Please provide a signature before saving.');
            return;
        }
        hidden.value = pad.toDataURL('image/png');
    });
})();
</script><?php medis_render_navigation_end(); ?>
</body>
</html>


