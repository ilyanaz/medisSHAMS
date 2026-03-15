<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => 'dashboard',
]);

$doctor = $doctorProfile ?? (object) [];
$fullName = trim((string) (($doctor->doctor_firstName ?? '') . ' ' . ($doctor->doctor_lastName ?? '')));
if ($fullName === '') {
    $fullName = (string) ($username ?? 'Doctor');
}
$identity = (string) ($doctor->doctor_NRIC ?? $doctor->doctor_passportNo ?? '-');
$dob = !empty($doctor->doctor_DOB) ? date('d-m-Y', strtotime((string) $doctor->doctor_DOB)) : '-';
$doctorRole = !empty($doctor->doctor_username) ? 'Doctor' : 'Admin';
?>
<style>
.profile-page{display:grid;gap:18px}.profile-title{font-size:1.8rem;font-weight:700;margin:0}.profile-sub{margin:6px 0 0;color:#6b7280}.hero-card,.info-card{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:20px}.hero-card{display:flex;align-items:center;gap:18px;flex-wrap:wrap}.avatar-lg{width:88px;height:88px;border-radius:999px;background:linear-gradient(135deg,#e4f3e8,#cde9d6);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#237343;flex-shrink:0;border:1px solid #d8eadf}.hero-info h2{margin:0;font-size:1.55rem}.hero-info p{margin:6px 0 0;color:#6b7280}.card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}.card-head h3{margin:0;font-size:1.2rem}.edit-btn{display:inline-flex;align-items:center;gap:8px;border:1px solid #d1d5db;border-radius:12px;padding:9px 14px;text-decoration:none;color:#374151;background:#fff}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px 22px}.field{display:grid;gap:6px}.field span{font-size:.86rem;color:#6b7280}.field strong{font-size:1rem;color:#111827;font-weight:600}.field.full{grid-column:1/-1}@media (max-width:980px){.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:720px){.grid{grid-template-columns:1fr}.hero-card{align-items:flex-start}}
</style>
<div class="profile-page">
    <div>
        <h1 class="profile-title">My Profile</h1>
        <p class="profile-sub">View doctor information based on the `doctor` table.</p>
    </div>

    <section class="hero-card">
        <div class="avatar-lg"><?php echo $esc(strtoupper(substr($fullName, 0, 1))); ?></div>
        <div class="hero-info">
            <h2><?php echo $esc($fullName); ?></h2>
            <p><?php echo $esc($doctorRole); ?></p>
            <p><?php echo $esc(($doctor->doctor_district ?? '-') . ', ' . ($doctor->doctor_state ?? '-')); ?></p>
        </div>
    </section>

    <section class="info-card">
        <div class="card-head">
            <h3>Personal Information</h3>
            <a class="edit-btn" href="#">Edit</a>
        </div>
        <div class="grid">
            <div class="field"><span>First Name</span><strong><?php echo $esc($doctor->doctor_firstName ?? '-'); ?></strong></div>
            <div class="field"><span>Last Name</span><strong><?php echo $esc($doctor->doctor_lastName ?? '-'); ?></strong></div>
            <div class="field"><span>Date of Birth</span><strong><?php echo $esc($dob); ?></strong></div>
            <div class="field"><span>Email Address</span><strong><?php echo $esc($doctor->doctor_email ?? '-'); ?></strong></div>
            <div class="field"><span>Phone Number</span><strong><?php echo $esc($doctor->doctor_telephone ?? '-'); ?></strong></div>
            <div class="field"><span>User Role</span><strong><?php echo $esc($doctorRole); ?></strong></div>
            <div class="field"><span>NRIC / Passport</span><strong><?php echo $esc($identity); ?></strong></div>
            <div class="field"><span>Gender</span><strong><?php echo $esc($doctor->doctor_gender ?? '-'); ?></strong></div>
            <div class="field"><span>Martial Status</span><strong><?php echo $esc($doctor->doctor_martialStatus ?? '-'); ?></strong></div>
        </div>
    </section>

    <section class="info-card">
        <div class="card-head">
            <h3>Address</h3>
            <a class="edit-btn" href="#">Edit</a>
        </div>
        <div class="grid">
            <div class="field"><span>Citizenship</span><strong><?php echo $esc($doctor->doctor_citizenship ?? '-'); ?></strong></div>
            <div class="field"><span>District</span><strong><?php echo $esc($doctor->doctor_district ?? '-'); ?></strong></div>
            <div class="field"><span>Postal Code</span><strong><?php echo $esc($doctor->doctor_postcode ?? '-'); ?></strong></div>
            <div class="field"><span>State</span><strong><?php echo $esc($doctor->doctor_state ?? '-'); ?></strong></div>
            <div class="field full"><span>Address</span><strong><?php echo $esc($doctor->doctor_address ?? '-'); ?></strong></div>
        </div>
    </section>

    <section class="info-card">
        <div class="card-head">
            <h3>Professional Information</h3>
            <a class="edit-btn" href="#">Edit</a>
        </div>
        <div class="grid">
            <div class="field"><span>MMC No</span><strong><?php echo $esc($doctor->MMC_no ?? '-'); ?></strong></div>
            <div class="field"><span>OHD Registration No</span><strong><?php echo $esc($doctor->OHD_registrationNo ?? '-'); ?></strong></div>
            <div class="field"><span>Ethnicity</span><strong><?php echo $esc($doctor->doctor_ethnicity ?? '-'); ?></strong></div>
            <div class="field"><span>Fax</span><strong><?php echo $esc($doctor->doctor_fax ?? '-'); ?></strong></div>
            <div class="field"><span>Username</span><strong><?php echo $esc($doctor->doctor_username ?? ($username ?? '-')); ?></strong></div>
            <div class="field"><span>Doctor ID</span><strong><?php echo $esc($doctor->doctor_id ?? '-'); ?></strong></div>
        </div>
    </section>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>