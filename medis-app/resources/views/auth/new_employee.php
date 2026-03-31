<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Employee</title>
<style>
:root{--line:#e5e7eb;--bg:rgba(15,23,42,.55);--panel:#fff;--text:#111827;--muted:#6b7280;--green:#389B5B;--red:#ef4444}
*{box-sizing:border-box}
body{margin:0;font-family:"Poppins","Segoe UI",Tahoma,Geneva,Verdana,sans-serif;background:#f3f4f6}
.overlay{min-height:100vh;background:var(--bg);display:grid;place-items:center;padding:24px}.modal{width:min(1060px,100%);background:var(--panel);border-radius:20px;box-shadow:0 20px 55px rgba(15,23,42,.25);padding:26px}
h1{margin:0 0 6px;font-size:2.1rem}.muted{margin:0;color:var(--muted)}
.panel,.subpanel{margin-top:18px;border:1px solid #e8ebf2;border-radius:16px;padding:16px}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.field{display:block;font-size:.9rem;color:#1f2937}
.field input,.field select,.field textarea,.field .phone-group{margin-top:6px}
input,select,textarea{width:100%;border:1px solid #d7dce7;border-radius:10px;padding:11px 12px;font:inherit}
textarea{min-height:90px;resize:vertical}.full{grid-column:1/-1}.phone-group{display:grid;grid-template-columns:130px 1fr;gap:8px}
.hidden{display:none}.actions{margin-top:18px;display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap}
.btn{border:1px solid #d1d5db;border-radius:10px;padding:10px 14px;background:#fff;color:#374151;text-decoration:none;font-size:.92rem;display:inline-flex;align-items:center;gap:6px;cursor:pointer}
.btn.primary{background:var(--green);border-color:var(--green);color:#fff;font-weight:600}.btn.danger{color:var(--red);border-color:#fecaca;background:#fff}.btn.small{padding:8px 12px;font-size:.85rem}
.req{color:#dc2626}.error{margin-top:14px;padding:12px 14px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:12px}
.section-title{margin:0;font-size:1rem;font-weight:700;color:#111827}.section-copy{margin:4px 0 0;color:#6b7280;font-size:.84rem}.section-header{margin-bottom:12px}
.repeat-list{display:grid;gap:12px}.repeat-card{border:1px solid #e8ebf2;border-radius:14px;padding:14px;background:#fbfcfd}.repeat-card-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px}.repeat-card-title{font-size:.92rem;font-weight:700;color:#111827}
.repeat-actions{display:flex;justify-content:flex-end;margin-top:12px}.history-stack{display:grid;gap:12px}.history-row{display:grid;grid-template-columns:minmax(300px,1.45fr) minmax(160px,0.9fr) minmax(160px,0.9fr);gap:14px;align-items:start}.history-row.two-inputs{grid-template-columns:minmax(300px,1.45fr) minmax(220px,1fr)}.history-choice{display:grid;gap:8px;align-self:start;padding-right:8px}.history-choice-label{font-size:.9rem;font-weight:600;color:#111827}.history-radio-group{display:grid;gap:8px;align-items:start;justify-items:start;min-height:34px;padding:0;border:none;border-radius:0}.history-choice .history-radio-group{margin-top:0}.history-field{margin-top:2px}.history-radio{display:inline-flex;align-items:center;gap:8px;font-size:.9rem;font-weight:500;color:#111827}.history-radio input{width:20px;height:20px;margin:0;accent-color:#389B5B;cursor:pointer;flex-shrink:0}.full-width-label{display:block}.full-width-label .history-radio-group{margin-top:10px}.history-field.hidden{display:none}.training-table{width:100%;border-collapse:separate;border-spacing:0;overflow:hidden;border:1px solid #e5e7eb;border-radius:14px}.training-table th,.training-table td{padding:12px;vertical-align:top;border-bottom:1px solid #e5e7eb}.training-table tr:last-child th,.training-table tr:last-child td{border-bottom:none}.training-table th{width:28%;background:#f8fafc;text-align:left;font-size:.92rem;color:#111827;font-weight:600}.training-table td{background:#fff}.training-table .comment-field textarea{min-height:78px}.training-radio-group{display:flex;gap:22px;align-items:center;justify-content:center;flex-wrap:wrap;min-height:44px;padding-left:18px}.training-radio{display:inline-flex;align-items:center;gap:8px;font-size:.9rem;font-weight:500;color:#111827}.training-radio input{width:20px;height:20px;margin:0;accent-color:#389B5B;cursor:pointer;flex-shrink:0}@media (max-width:860px){.history-row,.history-row.two-inputs{grid-template-columns:1fr}.training-table,.training-table tbody,.training-table tr,.training-table th,.training-table td{display:block;width:100%}.training-table th{border-bottom:none;padding-bottom:6px}.training-table td{padding-top:0}}
@media (max-width:860px){.grid{grid-template-columns:1fr}.phone-group{grid-template-columns:1fr}.repeat-card-head{align-items:flex-start;flex-direction:column}}
</style>
</head>
<body>
<?php
require_once __DIR__ . '/view_bootstrap.php';
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$backUrl = function_exists('route') ? route('surveillance.employee') : '#';
$occupationalRows = old('occup_company_name', null);
$occupationalRows = is_array($occupationalRows) ? count($occupationalRows) : 1;
$occupationalRows = max(1, (int) $occupationalRows);
?>
<div class="overlay">
<form class="modal" method="post" action="<?php echo $esc(route('surveillance.employee.store')); ?>" id="newEmployeeForm" novalidate>
<input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
<h1>New Employee</h1>
<p class="muted">All fields required, with conditional rules for NRIC/Passport and marital status.</p>
<?php if (isset($errors) && $errors->any()): ?>
<div class="error"><?php echo $esc($errors->first()); ?></div>
<?php endif; ?>

<div class="panel">
<div class="section-header">
<div class="section-title">Employee Details</div>
<p class="section-copy">Basic employee information for surveillance registration.</p>
</div>
<div class="grid">
<label class="field">First Name <span class="req">*</span><input name="employee_firstName" type="text" value="<?php echo $esc(old('employee_firstName')); ?>" placeholder="Enter first name" required></label>
<label class="field">Last Name <span class="req">*</span><input name="employee_lastName" type="text" value="<?php echo $esc(old('employee_lastName')); ?>" placeholder="Enter last name" required></label>
<label class="field">NRIC <span class="req">*</span><input id="employee_NRIC" name="employee_NRIC" type="text" value="<?php echo $esc(old('employee_NRIC')); ?>" placeholder="Enter NRIC"></label>
<label class="field">Passport No <span class="req">*</span><input id="employee_passportNo" name="employee_passportNo" type="text" value="<?php echo $esc(old('employee_passportNo')); ?>" placeholder="Enter passport number"></label>
<label class="field">Date of Birth <span class="req">*</span><input name="employee_DOB" type="date" value="<?php echo $esc(old('employee_DOB')); ?>" required></label>
<label class="field">Gender <span class="req">*</span><select name="employee_gender" required><option value="">Select</option><option value="Male"<?php echo old('employee_gender') === 'Male' ? ' selected' : ''; ?>>Male</option><option value="Female"<?php echo old('employee_gender') === 'Female' ? ' selected' : ''; ?>>Female</option></select></label>
<label class="field full">Address <span class="req">*</span><input name="employee_address" type="text" value="<?php echo $esc(old('employee_address')); ?>" placeholder="Enter address" required></label>
<label class="field">Postcode <span class="req">*</span><input name="employee_postcode" type="text" value="<?php echo $esc(old('employee_postcode')); ?>" placeholder="Enter postcode" pattern="^[0-9]{4,10}$" required></label>
<label class="field">District <span class="req">*</span><input name="employee_district" type="text" value="<?php echo $esc(old('employee_district')); ?>" placeholder="Enter district" required></label>
<label class="field">State <span class="req">*</span><input name="employee_state" type="text" value="<?php echo $esc(old('employee_state')); ?>" placeholder="Enter state" required></label>
<label class="field">Telephone <span class="req">*</span><div class="phone-group"><select name="employee_phone_code" required><option value="">Code</option><?php foreach (['+60','+65','+62','+66','+1','+44'] as $code): ?><option value="<?php echo $esc($code); ?>"<?php echo old('employee_phone_code', '+60') === $code ? ' selected' : ''; ?>><?php echo $esc($code); ?></option><?php endforeach; ?></select><input name="employee_telephone" type="tel" value="<?php echo $esc(old('employee_telephone')); ?>" inputmode="numeric" placeholder="Phone number" pattern="^[0-9]{7,12}$" required></div></label>
<label class="field">Email <span class="req">*</span><input name="employee_email" type="email" value="<?php echo $esc(old('employee_email')); ?>" placeholder="Enter email" required></label>
<label class="field">Ethnicity <span class="req">*</span><select name="employee_ethnicity" id="employee_ethnicity" required><option value="">Select</option><?php foreach(['Malay','Chinese','Indian','Orang Asli','Others'] as $option): ?><option value="<?php echo $esc($option); ?>"<?php echo old('employee_ethnicity') === $option ? ' selected' : ''; ?>><?php echo $esc($option); ?></option><?php endforeach; ?></select></label>
<label class="field hidden" id="employee_ethnicity_other_wrap">Ethnicity (Please justify) <span class="req">*</span><textarea id="employee_ethnicity_other" name="employee_ethnicity_other" placeholder="Write your justification"><?php echo $esc(old('employee_ethnicity_other')); ?></textarea></label>
<label class="field">Citizenship <span class="req">*</span><select name="employee_citizenship" id="employee_citizenship" required><option value="">Select</option><?php foreach(['Malaysian Citizen','Others'] as $option): ?><option value="<?php echo $esc($option); ?>"<?php echo old('employee_citizenship') === $option ? ' selected' : ''; ?>><?php echo $esc($option); ?></option><?php endforeach; ?></select></label>
<label class="field hidden" id="employee_citizenship_other_wrap">Citizenship (Please justify) <span class="req">*</span><textarea id="employee_citizenship_other" name="employee_citizenship_other" placeholder="Write your justification"><?php echo $esc(old('employee_citizenship_other')); ?></textarea></label>
<label class="field">Marital Status <span class="req">*</span><select name="employee_martialStatus" id="employee_martialStatus" required><option value="">Select</option><?php foreach(['Single','Married','Others'] as $option): ?><option value="<?php echo $esc($option); ?>"<?php echo old('employee_martialStatus') === $option ? ' selected' : ''; ?>><?php echo $esc($option); ?></option><?php endforeach; ?></select></label>
<label class="field hidden" id="employee_martial_other_wrap">Marital Status (Please justify) <span class="req">*</span><textarea id="employee_martial_other" name="employee_martial_other" placeholder="Write your justification"><?php echo $esc(old('employee_martial_other')); ?></textarea></label>
<label class="field" id="no_of_children_wrap">No. of Children <span class="req">*</span><input id="no_of_children" name="no_of_children" type="number" min="0" value="<?php echo $esc(old('no_of_children')); ?>" placeholder="Enter number of children" required></label>
<label class="field" id="years_married_wrap">Years Married <span class="req">*</span><input id="years_married" name="years_married" type="number" min="0" value="<?php echo $esc(old('years_married')); ?>" placeholder="Enter years married" required></label>
</div>
</div>

<div class="subpanel">
<div class="section-header">
<div class="section-title">Medical History</div>
<p class="section-copy">Employee baseline medical details for future surveillance reference.</p>
</div>
<div class="grid">
<label class="field full">Diagnosed History <span class="req">*</span><textarea name="diagnosed_history" placeholder="Insert diagnosed history" required><?php echo $esc(old('diagnosed_history')); ?></textarea></label>
<label class="field full">Medication History <span class="req">*</span><textarea name="medication_history" placeholder="Insert medication history" required><?php echo $esc(old('medication_history')); ?></textarea></label>
<label class="field full">Admitted History <span class="req">*</span><textarea name="admitted_history" placeholder="Insert admitted history" required><?php echo $esc(old('admitted_history')); ?></textarea></label>
<label class="field full">Family History <span class="req">*</span><textarea name="family_history" placeholder="Insert family history" required><?php echo $esc(old('family_history')); ?></textarea></label>
<label class="field full">Other History <span class="req">*</span><textarea name="others_history" placeholder="Insert other history" required><?php echo $esc(old('others_history')); ?></textarea></label>
</div>
</div>

<div class="subpanel">
<div class="section-header">
<div class="section-title">Occupational & Company History</div>
<p class="section-copy">Add the current company first, then keep past company records below for employment history.</p>
</div>
<div class="repeat-card">
<div class="repeat-card-head">
<div class="repeat-card-title">Current Company Record</div>
</div>
<div class="grid">
<label class="field">Job Title <span class="req">*</span><input type="text" name="current_job_title" value="<?php echo $esc(old('current_job_title')); ?>" placeholder="Enter current job title" required></label>
<label class="field">Company Name <span class="req">*</span><input type="text" name="current_company_name" value="<?php echo $esc(old('current_company_name')); ?>" placeholder="Enter current company name" required></label>
<label class="field">Employment Duration <span class="req">*</span><input type="text" name="current_employment_duration" value="<?php echo $esc(old('current_employment_duration')); ?>" placeholder="Enter employment duration"></label>
<label class="field">Chemical Exposure Duration <span class="req">*</span><input type="text" name="current_chemical_exposure_duration" value="<?php echo $esc(old('current_chemical_exposure_duration')); ?>" placeholder="Enter exposure duration"></label>
<label class="field full">Chemical Exposure Incidents <span class="req">*</span><textarea name="current_chemical_exposure_incidents" placeholder="Insert chemical exposure incidents" required><?php echo $esc(old('current_chemical_exposure_incidents')); ?></textarea></label>
</div>
</div>
<div class="repeat-list" id="occupationalHistoryList">
<?php for ($index = 0; $index < $occupationalRows; $index++): ?>
<div class="repeat-card" data-occup-row>
<div class="repeat-card-head">
<div class="repeat-card-title">Past Company Record <?php echo $index + 1; ?></div>
<button class="btn danger small" type="button" data-remove-occup-row>Delete</button>
</div>
<div class="grid">
<label class="field">Job Title<input type="text" name="occup_job_title[]" value="<?php echo $esc(old('occup_job_title.' . $index)); ?>" placeholder="Enter job title"></label>
<label class="field">Company Name<input type="text" name="occup_company_name[]" value="<?php echo $esc(old('occup_company_name.' . $index)); ?>" placeholder="Enter company name"></label>
<label class="field">Employment Duration<input type="text" name="employment_duration[]" value="<?php echo $esc(old('employment_duration.' . $index)); ?>" placeholder="Enter employment duration"></label>
<label class="field">Chemical Exposure Duration<input type="text" name="chemical_exposure_duration[]" value="<?php echo $esc(old('chemical_exposure_duration.' . $index)); ?>" placeholder="Enter exposure duration"></label>
<label class="field full">Chemical Exposure Incidents<textarea name="chemical_exposure_incidents[]" placeholder="Insert chemical exposure incidents"><?php echo $esc(old('chemical_exposure_incidents.' . $index)); ?></textarea></label>
</div>
</div>
<?php endfor; ?>
</div>
<div class="repeat-actions">
<button class="btn small" type="button" id="addOccupationalRow">+ Add Past Company</button>
</div>
</div>

<div class="subpanel">
<div class="section-header">
<div class="section-title">Personal &amp; Social History</div>
<p class="section-copy">Smoking, vaping, and hobby details.</p>
</div>
<div class="history-stack">
<div class="history-row">
<div class="history-choice">
<div class="history-choice-label">Smoking History</div>
<div class="history-radio-group" id="smoking_history_group">
<label class="history-radio"><input type="radio" name="smoking_history" value="Current" required<?php echo old('smoking_history') === 'Current' ? ' checked' : ''; ?>>Current</label>
<label class="history-radio"><input type="radio" name="smoking_history" value="Ex-smoker"<?php echo old('smoking_history') === 'Ex-smoker' ? ' checked' : ''; ?>>Ex-smoker</label>
<label class="history-radio"><input type="radio" name="smoking_history" value="Non-smoker"<?php echo old('smoking_history') === 'Non-smoker' ? ' checked' : ''; ?>>Non-smoker</label>
</div>
</div>
<label class="field history-field" id="years_of_smoking_wrap">Years of Smoking <span class="req">*</span><input type="number" min="0" name="years_of_smoking" value="<?php echo $esc(old('years_of_smoking')); ?>" placeholder="Enter years"></label>
<label class="field history-field" id="no_of_cigarettes_wrap">No. of Cigarettes <span class="req">*</span><input type="number" min="0" name="no_of_cigarettes" value="<?php echo $esc(old('no_of_cigarettes')); ?>" placeholder="Enter count"></label>
</div>
<div class="history-row two-inputs">
<div class="history-choice">
<div class="history-choice-label">Vaping History</div>
<div class="history-radio-group" id="vaping_history_group">
<label class="history-radio"><input type="radio" name="vaping_history" value="Yes" required<?php echo old('vaping_history') === 'Yes' ? ' checked' : ''; ?>>Yes</label>
<label class="history-radio"><input type="radio" name="vaping_history" value="No"<?php echo old('vaping_history') === 'No' ? ' checked' : ''; ?>>No</label>
</div>
</div>
<label class="field history-field" id="years_of_vaping_wrap">Years of Vaping <span class="req">*</span><input type="number" min="0" name="years_of_vaping" value="<?php echo $esc(old('years_of_vaping')); ?>" placeholder="Enter years"></label>
</div>
<label class="field full">Hobby <span class="req">*</span><textarea name="hobby" placeholder="Insert hobby details" required><?php echo $esc(old('hobby')); ?></textarea></label>
</div>
</div>

<div class="subpanel">
<div class="section-header">
<div class="section-title">Training History</div>
<p class="section-copy">Training and PPE understanding information.</p>
</div>
<table class="training-table">
<tbody>
<tr>
<th>Handling of Chemical</th>
<td><div class="training-radio-group"><label class="training-radio"><input type="radio" name="handling_of_chemical" value="Yes" required<?php echo old('handling_of_chemical') === 'Yes' ? ' checked' : ''; ?>>Yes</label><label class="training-radio"><input type="radio" name="handling_of_chemical" value="No"<?php echo old('handling_of_chemical') === 'No' ? ' checked' : ''; ?>>No</label></div></td>
<td class="comment-field"><textarea name="chemical_comments" placeholder="Insert handling comments" required><?php echo $esc(old('chemical_comments')); ?></textarea></td>
</tr>
<tr>
<th>Sign &amp; Symptoms Knowledge</th>
<td><div class="training-radio-group"><label class="training-radio"><input type="radio" name="sign_symptoms" value="Yes" required<?php echo old('sign_symptoms') === 'Yes' ? ' checked' : ''; ?>>Yes</label><label class="training-radio"><input type="radio" name="sign_symptoms" value="No"<?php echo old('sign_symptoms') === 'No' ? ' checked' : ''; ?>>No</label></div></td>
<td class="comment-field"><textarea name="sign_comments" placeholder="Insert sign and symptoms comments" required><?php echo $esc(old('sign_comments')); ?></textarea></td>
</tr>
<tr>
<th>Chemical Poisoning Knowledge</th>
<td><div class="training-radio-group"><label class="training-radio"><input type="radio" name="chemical_poisoning" value="Yes" required<?php echo old('chemical_poisoning') === 'Yes' ? ' checked' : ''; ?>>Yes</label><label class="training-radio"><input type="radio" name="chemical_poisoning" value="No"<?php echo old('chemical_poisoning') === 'No' ? ' checked' : ''; ?>>No</label></div></td>
<td class="comment-field"><textarea name="poisoning_comments" placeholder="Insert poisoning comments" required><?php echo $esc(old('poisoning_comments')); ?></textarea></td>
</tr>
<tr>
<th>Proper PPE Knowledge</th>
<td><div class="training-radio-group"><label class="training-radio"><input type="radio" name="proper_PPE" value="Yes" required<?php echo old('proper_PPE') === 'Yes' ? ' checked' : ''; ?>>Yes</label><label class="training-radio"><input type="radio" name="proper_PPE" value="No"<?php echo old('proper_PPE') === 'No' ? ' checked' : ''; ?>>No</label></div></td>
<td class="comment-field"><textarea name="proper_comments" placeholder="Insert proper PPE comments" required><?php echo $esc(old('proper_comments')); ?></textarea></td>
</tr>
<tr>
<th>PPE Usage</th>
<td><div class="training-radio-group"><label class="training-radio"><input type="radio" name="PPE_usage" value="Yes" required<?php echo old('PPE_usage') === 'Yes' ? ' checked' : ''; ?>>Yes</label><label class="training-radio"><input type="radio" name="PPE_usage" value="No"<?php echo old('PPE_usage') === 'No' ? ' checked' : ''; ?>>No</label></div></td>
<td class="comment-field"><textarea name="usage_comments" placeholder="Insert PPE usage comments" required><?php echo $esc(old('usage_comments')); ?></textarea></td>
</tr>
</tbody>
</table>
</div>

<div class="actions">
<a class="btn" href="<?php echo $esc($backUrl); ?>">Cancel</a>
<button class="btn primary" type="submit">Submit Employee</button>
</div>
</form>
</div>

<template id="occupationalRowTemplate">
<div class="repeat-card" data-occup-row>
<div class="repeat-card-head">
<div class="repeat-card-title">Past Company Record</div>
<button class="btn danger small" type="button" data-remove-occup-row>Delete</button>
</div>
<div class="grid">
<label class="field">Job Title<input type="text" name="occup_job_title[]" placeholder="Enter job title"></label>
<label class="field">Company Name<input type="text" name="occup_company_name[]" placeholder="Enter company name"></label>
<label class="field">Employment Duration<input type="text" name="employment_duration[]" placeholder="Enter employment duration"></label>
<label class="field">Chemical Exposure Duration<input type="text" name="chemical_exposure_duration[]" placeholder="Enter exposure duration"></label>
<label class="field full">Chemical Exposure Incidents<textarea name="chemical_exposure_incidents[]" placeholder="Insert chemical exposure incidents"></textarea></label>
</div>
</div>
</template>

<script>
(function(){
const form=document.getElementById('newEmployeeForm');
const nric=document.getElementById('employee_NRIC');
const passport=document.getElementById('employee_passportNo');
const marital=document.getElementById('employee_martialStatus');
const children=document.getElementById('no_of_children');
const years=document.getElementById('years_married');
const childrenWrap=document.getElementById('no_of_children_wrap');
const yearsWrap=document.getElementById('years_married_wrap');
const occupationalList=document.getElementById('occupationalHistoryList');
const occupationalTemplate=document.getElementById('occupationalRowTemplate');
const addOccupationalRow=document.getElementById('addOccupationalRow');
const setupOthers=function(selectId,wrapId,inputId){const select=document.getElementById(selectId);const wrap=document.getElementById(wrapId);const input=document.getElementById(inputId);const sync=function(){const isOther=select.value==='Others';wrap.classList.toggle('hidden',!isOther);input.required=isOther;if(!isOther){input.value='';}};select.addEventListener('change',sync);sync();};
const syncOccupationalTitles=function(){occupationalList.querySelectorAll('[data-occup-row]').forEach(function(row,index){const title=row.querySelector('.repeat-card-title');if(title){title.textContent='Past Company Record '+(index+1);}const remove=row.querySelector('[data-remove-occup-row]');if(remove){remove.disabled=occupationalList.querySelectorAll('[data-occup-row]').length===1;remove.style.opacity=remove.disabled?'0.5':'1';}});};
const bindOccupationalRow=function(row){const remove=row.querySelector('[data-remove-occup-row]');if(remove){remove.addEventListener('click',function(){if(occupationalList.querySelectorAll('[data-occup-row]').length>1){row.remove();syncOccupationalTitles();}});}};
addOccupationalRow.addEventListener('click',function(){const fragment=occupationalTemplate.content.cloneNode(true);const row=fragment.querySelector('[data-occup-row]');occupationalList.appendChild(fragment);bindOccupationalRow(occupationalList.lastElementChild);syncOccupationalTitles();});
occupationalList.querySelectorAll('[data-occup-row]').forEach(bindOccupationalRow);
syncOccupationalTitles();
setupOthers('employee_ethnicity','employee_ethnicity_other_wrap','employee_ethnicity_other');
setupOthers('employee_citizenship','employee_citizenship_other_wrap','employee_citizenship_other');
setupOthers('employee_martialStatus','employee_martial_other_wrap','employee_martial_other');
const syncIdRequirement=function(){const hasNric=nric.value.trim()!=='';const hasPassport=passport.value.trim()!=='';nric.setCustomValidity('');passport.setCustomValidity('');if(!hasNric&&!hasPassport){nric.setCustomValidity('NRIC or Passport No is required.');passport.setCustomValidity('NRIC or Passport No is required.');}};
const syncMarital=function(){const isSingle=marital.value==='Single';children.required=!isSingle;years.required=!isSingle;childrenWrap.classList.toggle('hidden',isSingle);yearsWrap.classList.toggle('hidden',isSingle);if(isSingle){children.value='';years.value='';children.setCustomValidity('');years.setCustomValidity('');}};
nric.addEventListener('input',syncIdRequirement);
passport.addEventListener('input',syncIdRequirement);
marital.addEventListener('change',syncMarital);
syncIdRequirement();
syncMarital();
form.addEventListener('submit',function(event){syncIdRequirement();syncMarital();if(!form.checkValidity()){event.preventDefault();form.reportValidity();}});
})();
</script>
</body>
</html>

















