<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Declaration</title></head>
<body>
<?php
require __DIR__ . '/navigation.php';
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$toSignatureDataUrl = static function ($value) {
    $value = (string) ($value ?? '');
    if ($value === '') {
        return '';
    }
    if (strpos($value, 'data:image') === 0) {
        return $value;
    }
    return 'data:image/png;base64,' . base64_encode($value);
};
$statusMessage = session('status');
$stepHistory = function_exists('route') ? route('surveillance.list') : '#';
$stepExam = function_exists('route') ? route('surveillance.examination') : '#';
$saveDeclarationUrl = function_exists('route') ? route('surveillance.declaration.save') : '#';
$steps = [
    ['label' => 'Company'],
    ['label' => 'Employee'],
    ['label' => 'Surveillance List'],
    ['label' => 'Declaration', 'active' => true],
    ['label' => 'Examination'],
    ['label' => 'Report'],
];
medis_render_navigation_start(['clinicName'=>$clinicName ?? 'Medis SHAMS','clinicLogoUrl'=>$clinicLogoUrl ?? null,'username'=>$username ?? 'User','active'=>'surveillance','pageSubtitle'=>'Review declaration and collect signatures']);
?>
<style>.flow{display:grid;grid-template-rows:auto minmax(0,1fr);gap:28px;height:calc(100vh - 130px);min-height:0}.stepper{border:0;border-radius:0;background:transparent;padding:0;margin:0}.stepper h3{display:none}.step-list{position:relative;display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:6px;align-items:start;padding-bottom:6px}.step-list::before{content:"";position:absolute;left:20px;right:20px;top:19px;height:2px;background:#d7dee8;z-index:0}.step-link{position:relative;z-index:1;display:grid;justify-items:center;gap:8px;padding:0 4px;border-radius:14px;color:#374151;background:transparent;text-align:center;cursor:default}.step-link.active{color:#14321f;font-weight:700}.step-index{width:38px;height:38px;border-radius:999px;border:1px solid #9ca3af;background:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700}.step-link.active .step-index{background:#389B5B;border-color:#389B5B;color:#fff}.step-label{font-size:.82rem;line-height:1.25;max-width:96px}.content{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;overflow:auto;min-height:0;margin-top:2px}.head h2{margin:0 0 12px;font-size:1.8rem}.head p{margin:6px 0 0;color:#6b7280}.status{margin-top:14px;padding:10px 12px;border:1px solid #a7f3d0;background:#ecfdf3;color:#065f46;border-radius:12px}.statement{margin-top:16px;display:grid;gap:16px}.meta-grid,.sign-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.meta-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.field{display:grid;gap:6px}.field input{border:1px solid #d1d5db;border-radius:12px;padding:10px 12px}.sign-card{border:1px solid #e5e7eb;border-radius:16px;padding:14px}.signature-pad{height:180px;border:1px dashed #cbd5e1;border-radius:12px;background:#fcfcfd}.signature-pad canvas{width:100%;height:100%;display:block}.actions{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px}.actions-right{display:flex;gap:10px;flex-wrap:wrap}.btn,.next{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;cursor:pointer}.next{background:#389B5B;border-color:#389B5B;color:#fff}@media(max-width:1100px){.stepper{padding:14px}.step-list{grid-template-columns:repeat(3,minmax(0,1fr))}.step-label{max-width:none}.meta-grid,.sign-grid{grid-template-columns:1fr}}</style>
<div class="flow"><aside class="stepper"><div class="step-list"><?php foreach($steps as $index => $step): ?><div class="step-link<?php echo !empty($step['active']) ? ' active' : ''; ?>"><span class="step-index"><?php echo $index + 1; ?></span><span class="step-label"><?php echo $esc($step['label']); ?></span></div><?php endforeach; ?></div></aside><section class="content"><div class="head"><h2>Declaration</h2><p>Please review and sign electronically before continuing.</p></div><?php if (!empty($statusMessage)): ?><div class="status"><?php echo $esc($statusMessage); ?></div><?php endif; ?><form method="POST" action="<?php echo $esc($saveDeclarationUrl); ?>" id="declarationForm"><input type="hidden" name="company_id" value="<?php echo $esc(old('company_id', $selectedCompany->company_id ?? '')); ?>"><input type="hidden" name="employee_id" value="<?php echo $esc(old('employee_id', $selectedEmployee->employee_id ?? '')); ?>"><input type="hidden" name="declaration_id" value="<?php echo $esc(old('declaration_id', $declarationId ?? $declaration->declaration_id ?? '')); ?>"><input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>"><input type="hidden" name="employee_signature" id="employee_signature" value="<?php echo $esc(old('employee_signature', $toSignatureDataUrl($declaration->employee_signature ?? ''))); ?>"><input type="hidden" name="doctor_signature" id="doctor_signature" value="<?php echo $esc(old('doctor_signature', $toSignatureDataUrl($declaration->doctor_signature ?? ''))); ?>"><div class="statement"><strong>Declaration</strong><p>This is to certify that the above statement is true. I hereby give consent to the Occupational Health Doctor (OHD) to perform medical examination, necessary tests, and communicate with the employer the results of my medical examination and work capability.</p><div class="meta-grid"><label class="field">Company Name<input type="text" name="company_name" value="<?php echo $esc(old('company_name', $selectedCompany->company_name ?? '')); ?>"></label><label class="field">First Name<input type="text" name="employee_firstName" value="<?php echo $esc(old('employee_firstName', $selectedEmployee->employee_firstName ?? '')); ?>"></label><label class="field">Last Name<input type="text" name="employee_lastName" value="<?php echo $esc(old('employee_lastName', $selectedEmployee->employee_lastName ?? '')); ?>"></label></div><div class="sign-grid"><div class="sign-card"><strong>Signed by</strong><div class="signature-pad"><canvas id="signerPad"></canvas></div><label class="field">Date<input type="date" name="employee_date" value="<?php echo $esc(old('employee_date', date('Y-m-d'))); ?>" required></label><button class="btn" type="button" data-clear="signerPad">Clear Signature</button></div><div class="sign-card"><strong>Witnessed by Doctor</strong><div class="signature-pad"><canvas id="doctorPad"></canvas></div><label class="field">Date<input type="date" name="doctor_date" value="<?php echo $esc(old('doctor_date', date('Y-m-d'))); ?>" required></label><button class="btn" type="button" data-clear="doctorPad">Clear Signature</button></div></div></div><div class="actions"><a class="btn" href="<?php echo $esc($stepHistory); ?>">Back</a><div class="actions-right"><button class="next" type="submit">Save &amp; Continue</button></div></div></form></section></div><script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>(function(){
const setupPad=function(canvasId,inputId){const canvas=document.getElementById(canvasId);const input=document.getElementById(inputId);if(!canvas||!input||typeof SignaturePad==='undefined'){return null;}const pad=new SignaturePad(canvas,{minWidth:1.5,maxWidth:2.5,penColor:'#111827'});const resize=function(){const ratio=Math.max(window.devicePixelRatio||1,1);const rect=canvas.getBoundingClientRect();canvas.width=rect.width*ratio;canvas.height=rect.height*ratio;canvas.getContext('2d').scale(ratio,ratio);pad.clear();if(input.value&&input.value.indexOf('data:image')===0){pad.fromDataURL(input.value);}};window.addEventListener('resize',resize);resize();return{clear:function(){pad.clear();input.value='';},save:function(){input.value=pad.toDataURL('image/png');},isEmpty:function(){return pad.isEmpty();}};};const signer=setupPad('signerPad','employee_signature');const doctor=setupPad('doctorPad','doctor_signature');document.querySelectorAll('[data-clear]').forEach(function(btn){btn.addEventListener('click',function(){const target=btn.getAttribute('data-clear');if(target==='signerPad'&&signer)signer.clear();if(target==='doctorPad'&&doctor)doctor.clear();});});document.getElementById('declarationForm').addEventListener('submit',function(event){if(!signer||!doctor||signer.isEmpty()||doctor.isEmpty()){event.preventDefault();alert('Please provide both signatures before saving.');return;}signer.save();doctor.save();});})();</script>
<?php medis_render_navigation_end(); ?>
</body></html>










