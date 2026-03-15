<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Audiometry List</title></head><body>
<?php
require __DIR__ . '/navigation.php';
$esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$steps = [
    ['label' => 'Company', 'url' => 'audiometry_company.php'],
    ['label' => 'Employee', 'url' => 'audiometry_employee.php'],
    ['label' => 'Questionnaire', 'url' => 'audiometry_questionnaire.php'],
    ['label' => 'Audiometry List', 'url' => 'audiometry_list.php'],
    ['label' => 'Examination', 'url' => 'audiometry_examination.php'],
    ['label' => 'Confirm', 'url' => 'audiometry_confirm.php'],
    ['label' => 'Report', 'url' => 'audiometry_report.php'],
];
?>
<?php $steps[3]['active']=true; medis_render_navigation_start(['clinicName'=>$clinicName ?? 'Medis SHAMS','clinicLogoUrl'=>$clinicLogoUrl ?? null,'username'=>$username ?? 'User','active'=>'audiometry']); ?>
<style>
.flow{display:grid;gap:28px}.stepper{border:0;border-radius:0;background:transparent;padding:0;margin:0}.stepper h3{display:none}.step-list{position:relative;display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:6px;align-items:start;padding-bottom:6px}.step-list::before{content:"";position:absolute;left:20px;right:20px;top:19px;height:2px;background:#d7dee8;z-index:0}.step-link{position:relative;z-index:1;display:grid;justify-items:center;gap:8px;padding:0 4px;border-radius:14px;text-decoration:none;color:#374151;border:1px solid transparent;background:transparent;text-align:center}.step-link.active{color:#14321f;font-weight:700}.step-index{width:38px;height:38px;border-radius:999px;border:1px solid #9ca3af;background:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700}.step-link.active .step-index{background:#389B5B;border-color:#389B5B;color:#fff}.step-label{font-size:.82rem;line-height:1.25;max-width:96px}.content{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;margin-top:2px}.head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.head h2{margin:0 0 12px;font-size:1.8rem}.head p{margin:6px 0 0;color:#6b7280}.top-actions{display:flex;gap:10px;flex-wrap:wrap}.btn,.next,.filter-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151}.next{background:#389B5B;border-color:#389B5B;color:#fff}.toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px}.toolbar-left,.toolbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.toolbar input{border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;min-width:280px}.filter-btn{font-size:.9rem;cursor:pointer}.filter-btn.is-active{background:#389B5B;border-color:#389B5B;color:#fff}.table{width:100%;border-collapse:collapse;margin-top:14px}.table th,.table td{padding:14px 10px;text-align:left;border-top:1px solid #edf0f2}.table th{font-size:.8rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em}.action-icons{display:flex;gap:10px}.icon-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.8}.icon-btn{color:#111827}.icon-btn.delete{color:#ef4444}.tag{display:inline-flex;padding:5px 10px;border-radius:999px;font-weight:600;font-size:.76rem}.ok{background:#dcfce7;color:#166534}.warn{background:#fef3c7;color:#92400e}.off{background:#f3f4f6;color:#6b7280}.red{background:#fee2e2;color:#991b1b}.bottom{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px}.pager{color:#6b7280;font-size:.84rem}.summary{display:grid;gap:14px;margin-top:18px}.box{border:1px solid #e5e7eb;border-radius:18px;padding:16px;background:#fafafa}.box h3{margin:0 0 10px}.row{display:flex;justify-content:space-between;gap:14px;padding:8px 0;border-bottom:1px dashed #e5e7eb}.row:last-child{border-bottom:none}.k{color:#6b7280}.v{font-weight:600}.report-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:18px}.stat{border:1px solid #e5e7eb;border-radius:18px;padding:16px;background:#fff}.stat span{display:block;color:#6b7280;font-size:.86rem}.stat strong{display:block;margin-top:8px;font-size:1.9rem}.panel{border:1px solid #e5e7eb;border-radius:18px;padding:18px;background:#fff;margin-top:16px}.panel h3{margin:0 0 10px}.list{display:grid;gap:10px}.list-item{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:12px 0;border-top:1px solid #edf0f2}.list-item:first-child{border-top:0;padding-top:0}@media(max-width:1100px){.stepper{padding:14px}.step-list{grid-template-columns:repeat(4,minmax(0,1fr))}.step-label{max-width:none}.report-grid{grid-template-columns:1fr}}
</style>
<div class="flow"><aside class="stepper"><div class="step-list"><?php foreach($steps as $index => $step): ?><a class="step-link<?php echo !empty($step['active']) ? ' active' : ''; ?>" href="<?php echo $esc($step['url']); ?>"><span class="step-index"><?php echo $index + 1; ?></span><span class="step-label"><?php echo $esc($step['label']); ?></span></a><?php endforeach; ?></div></aside><section class="content"><div class="head"><div><h2>Audiometry List</h2><p>Review audiometry records before examination and confirmation.</p></div><div class="top-actions"><a class="btn" href="#">Import</a><a class="next" href="edit_audioRecord.php">+ Add Record</a></div></div><div class="toolbar"><div class="toolbar-left"><input id="audiometrySearch" type="text" placeholder="Search audiometry record"></div><div class="toolbar-right"><button type="button" class="filter-btn" data-status-filter="pending">Pending</button><button type="button" class="filter-btn" data-status-filter="completed">Completed</button></div></div><table class="table"><thead><tr><th>Record ID</th><th>Employee</th><th>Company</th><th>Scheduled Date</th><th>Result</th><th>Status</th><th>Action</th></tr></thead><tbody><tr data-status="pending"><td>#AUD-L201</td><td>Nur Hidayah</td><td>Alpha Engineering</td><td>13 Mar 2026</td><td>Pending</td><td><span class="tag warn">Pending</span></td><td><div class="action-icons"><a class="icon-btn" href="edit_audioRecord.php" title="View"><svg viewBox="0 0 24 24"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6z"></path><circle cx="12" cy="12" r="3"></circle></svg></a><a class="icon-btn" href="edit_audioRecord.php" title="Edit"><svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg></a><a class="icon-btn delete" href="delete_audioRecord.php"><svg viewBox="0 0 24 24"><path d="M4 7h16"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M6 7l1 13h10l1-13"></path><path d="M9 7V4h6v3"></path></svg></a></div></td></tr><tr data-status="completed"><td>#AUD-L200</td><td>Siti Nursyafiqah</td><td>Gamma Plantations</td><td>10 Mar 2026</td><td>Normal</td><td><span class="tag ok">Completed</span></td><td><div class="action-icons"><a class="icon-btn" href="edit_audioRecord.php" title="View"><svg viewBox="0 0 24 24"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6z"></path><circle cx="12" cy="12" r="3"></circle></svg></a><a class="icon-btn" href="edit_audioRecord.php" title="Edit"><svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg></a><a class="icon-btn delete" href="delete_audioRecord.php"><svg viewBox="0 0 24 24"><path d="M4 7h16"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M6 7l1 13h10l1-13"></path><path d="M9 7V4h6v3"></path></svg></a></div></td></tr></tbody></table><div class="bottom"><span class="pager">Showing 1-2 of 4,220 records</span><div><a class="btn" href="audiometry_questionnaire.php">Back</a><a class="next" href="audiometry_examination.php">Next</a></div></div></section></div><script>
(function () {
    var searchInput = document.getElementById('audiometrySearch');
    var section = searchInput ? searchInput.closest('.content') : null;
    if (!section) return;
    var rows = Array.prototype.slice.call(section.querySelectorAll('.table tbody tr'));
    var pager = section.querySelector('.pager');
    var filterButtons = Array.prototype.slice.call(section.querySelectorAll('[data-status-filter]'));
    var activeStatus = '';
    var totalRows = rows.length;

    function updateRows() {
        var query = (searchInput.value || '').trim().toLowerCase();
        var visibleCount = 0;
        rows.forEach(function (row) {
            var matchesSearch = query === '' || (row.textContent || '').toLowerCase().indexOf(query) !== -1;
            var matchesStatus = activeStatus === '' || row.getAttribute('data-status') === activeStatus;
            var show = matchesSearch && matchesStatus;
            row.style.display = show ? '' : 'none';
            if (show) visibleCount += 1;
        });
        if (pager) {
            pager.textContent = visibleCount === 0
                ? 'Showing 0 of ' + totalRows.toLocaleString() + ' records'
                : 'Showing 1-' + visibleCount.toLocaleString() + ' of ' + totalRows.toLocaleString() + ' records';
        }
    }

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var status = button.getAttribute('data-status-filter') || '';
            activeStatus = activeStatus === status ? '' : status;
            filterButtons.forEach(function (item) {
                item.classList.toggle('is-active', item === button && activeStatus !== '');
            });
            updateRows();
        });
    });

    searchInput.addEventListener('input', updateRows);
})();
</script><?php medis_render_navigation_end(); ?></body></html>



