<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
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
$profilePhotoUrl = ! empty($settings->profile_photo_path) ? asset($settings->profile_photo_path) : null;
$statusMessage = session('status');
$errorBag = $errors ?? null;
$displayName = trim((string) (($doctorProfile->doctor_firstName ?? '') . ' ' . ($doctorProfile->doctor_lastName ?? '')));
if ($displayName === '') {
    $displayName = (string) ($username ?? 'User');
}
?>
<style>
.account-page{display:grid;gap:18px}.page-head h1{margin:0;font-size:1.9rem}.page-head p{margin:6px 0 0;color:#6b7280}.notice{padding:12px 14px;border-radius:14px;border:1px solid #a7f3d0;background:#ecfdf3;color:#065f46}.error-box{padding:12px 14px;border-radius:14px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b}.account-grid{display:grid;grid-template-columns:340px 1fr;gap:18px}.card{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:20px}.profile-card{display:grid;gap:16px;align-content:start}.profile-preview{display:grid;justify-items:center;gap:12px;text-align:center}.profile-avatar{width:124px;height:124px;border-radius:999px;background:linear-gradient(135deg,#e4f3e8,#cde9d6);border:1px solid #d7ebdd;display:flex;align-items:center;justify-content:center;overflow:hidden;font-size:2.4rem;font-weight:700;color:#237343}.profile-avatar img{width:100%;height:100%;object-fit:cover}.profile-name{font-size:1.1rem;font-weight:700}.profile-role{color:#6b7280}.form-card h2,.profile-card h2{margin:0 0 12px;font-size:1.2rem}.upload-form,.password-form{display:grid;gap:12px}.upload-form input[type=file],.password-form input{border:1px solid #d1d5db;border-radius:12px;padding:11px 12px;background:#fff;width:100%}.field{display:grid;gap:6px}.field span{font-size:.88rem;color:#475569}.password-wrap{position:relative}.password-wrap input{padding-right:72px}.toggle-password{position:absolute;right:12px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:#6b7280;font-size:.88rem;font-weight:600;cursor:pointer;padding:4px 6px}.toggle-password:hover{color:#319755}.actions{display:flex;gap:10px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;text-decoration:none;cursor:pointer;font:inherit}.btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}.btn.danger{background:#fff;border-color:#fecaca;color:#dc2626}.tips{margin-top:10px;padding:14px;border-radius:16px;background:#f8fafc;border:1px solid #e5e7eb;color:#475569}.tips ul{margin:0;padding-left:18px;display:grid;gap:6px}@media (max-width:980px){.account-grid{grid-template-columns:1fr}}
</style>
<div class="account-page">
    <div class="page-head">
        <h1>Account Settings</h1>
        <p>Change your account password and profile picture.</p>
    </div>

    <?php if (! empty($statusMessage)): ?>
        <div class="notice"><?php echo $esc($statusMessage); ?></div>
    <?php endif; ?>

    <?php if ($errorBag && $errorBag->any()): ?>
        <div class="error-box"><?php echo $esc($errorBag->first()); ?></div>
    <?php endif; ?>

    <div class="account-grid">
        <section class="card profile-card">
            <h2>Profile Picture</h2>
            <div class="profile-preview">
                <div class="profile-avatar">
                    <?php if ($profilePhotoUrl): ?>
                        <img src="<?php echo $esc($profilePhotoUrl); ?>" alt="Profile Photo">
                    <?php else: ?>
                        <?php echo $esc(strtoupper(substr($displayName, 0, 1))); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="profile-name"><?php echo $esc($displayName); ?></div>
                    <div class="profile-role"><?php echo $esc($doctorProfile->doctor_email ?? ($username ?? 'User')); ?></div>
                </div>
            </div>
            <form class="upload-form" method="POST" action="<?php echo $esc(route('account.profile-photo.upload')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
                <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                <div class="actions">
                    <button class="btn primary" type="submit">Upload Profile Picture</button>
                </div>
            </form>
            <form method="POST" action="<?php echo $esc(route('account.profile-photo.delete')); ?>">
                <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
                <button class="btn danger" type="submit"<?php echo $profilePhotoUrl ? '' : ' disabled'; ?>>Delete Profile Picture</button>
            </form>
        </section>

        <section class="card form-card">
            <h2>Change Password</h2>
            <form class="password-form" method="POST" action="<?php echo $esc(route('account.password.update')); ?>">
                <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
                <label class="field">
                    <span>Current Password</span>
                    <div class="password-wrap">
                        <input id="current_password" type="password" name="current_password" required>
                        <button type="button" class="toggle-password" data-target="current_password">Show</button>
                    </div>
                </label>
                <label class="field">
                    <span>New Password</span>
                    <div class="password-wrap">
                        <input id="new_password" type="password" name="new_password" minlength="6" required>
                        <button type="button" class="toggle-password" data-target="new_password">Show</button>
                    </div>
                </label>
                <label class="field">
                    <span>Confirm New Password</span>
                    <div class="password-wrap">
                        <input id="new_password_confirmation" type="password" name="new_password_confirmation" minlength="6" required>
                        <button type="button" class="toggle-password" data-target="new_password_confirmation">Show</button>
                    </div>
                </label>
                <div class="actions">
                    <button class="btn primary" type="submit">Update Password</button>
                </div>
            </form>
            <div class="tips">
                <ul>
                    <li>Use at least 6 characters</li>
                    <li>After changing password, use the new password on your next login</li>
                    <li>Your profile photo is stored for the logged-in account</li>
                </ul>
            </div>
        </section>
    </div>
</div>
<?php medis_render_navigation_end(); ?>
<script>
document.querySelectorAll('.toggle-password').forEach(function (button) {
    button.addEventListener('click', function () {
        var target = document.getElementById(button.getAttribute('data-target'));
        if (!target) {
            return;
        }
        var isPassword = target.getAttribute('type') === 'password';
        target.setAttribute('type', isPassword ? 'text' : 'password');
        button.textContent = isPassword ? 'Hide' : 'Show';
    });
});
</script>
</body>
</html>