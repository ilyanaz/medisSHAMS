<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audiometry List</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';

$esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$records = $records ?? collect();
$recordTotal = $recordTotal ?? (is_countable($records) ? count($records) : 0);
$selectedEmployee = $selectedEmployee ?? null;
$selectedCompany = $selectedCompany ?? null;
$employeeId = $selectedEmployee->employee_id ?? request()->query('employee_id') ?? '';
$companyId = $selectedCompany->company_id ?? request()->query('company_id') ?? '';

$companyUrl = function_exists('route') ? route('audiometry.company') : 'audiometry_company.php';
$employeeUrl = function_exists('route')
    ? route('audiometry.employee', array_filter(['company_id' => $companyId]))
    : 'audiometry_employee.php' . ($companyId !== '' ? '?company_id=' . urlencode((string) $companyId) : '');
$questionnaireUrl = function_exists('route')
    ? route('audiometry.questionnaire', array_filter(['employee_id' => $employeeId, 'company_id' => $companyId]))
    : 'audiometry_questionnaire.php?' . http_build_query(array_filter(['employee_id' => $employeeId, 'company_id' => $companyId]));
$listUrl = function_exists('route')
    ? route('audiometry.list', array_filter(['employee_id' => $employeeId, 'company_id' => $companyId]))
    : 'audiometry_list.php?' . http_build_query(array_filter(['employee_id' => $employeeId, 'company_id' => $companyId]));
$examUrl = function_exists('route')
    ? route('audiometry.examination', array_filter(['employee_id' => $employeeId, 'company_id' => $companyId]))
    : 'audiometry_examination.php?' . http_build_query(array_filter(['employee_id' => $employeeId, 'company_id' => $companyId]));
$reportUrl = function_exists('route')
    ? route('audiometry.report', array_filter(['employee_id' => $employeeId, 'company_id' => $companyId]))
    : 'audiometry_report.php?' . http_build_query(array_filter(['employee_id' => $employeeId, 'company_id' => $companyId]));

$steps = [
    ['label' => 'Company', 'url' => $companyUrl],
    ['label' => 'Employee', 'url' => $employeeUrl],
    ['label' => 'Questionnaire', 'url' => $questionnaireUrl],
    ['label' => 'Audiometry List', 'url' => $listUrl, 'active' => true],
    ['label' => 'Examination', 'url' => $examUrl],
    ['label' => 'Report', 'url' => $reportUrl],
];

$selectedEmployeeName = trim((string) (($selectedEmployee->employee_firstName ?? '') . ' ' . ($selectedEmployee->employee_lastName ?? '')));

medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => 'audiometry',
]);
?>
<style>
.flow{display:grid;gap:28px}
.stepper{border:0;border-radius:0;background:transparent;padding:0;margin:0}
.stepper h3{display:none}
.step-list{position:relative;display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:6px;align-items:start;padding-bottom:6px}
.step-list::before{content:"";position:absolute;left:20px;right:20px;top:19px;height:2px;background:#d7dee8;z-index:0}
.step-link{position:relative;z-index:1;display:grid;justify-items:center;gap:8px;padding:0 4px;border-radius:14px;text-decoration:none;color:#374151;border:1px solid transparent;background:transparent;text-align:center}
.step-link.active{color:#14321f;font-weight:700}
.step-index{width:38px;height:38px;border-radius:999px;border:1px solid #9ca3af;background:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700}
.step-link.active .step-index{background:#389B5B;border-color:#389B5B;color:#fff}
.step-label{font-size:.82rem;line-height:1.25;max-width:96px}
.content{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;margin-top:2px}
.head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.head h2{margin:0 0 12px;font-size:1.8rem}
.head p,.selected-note{margin:6px 0 0;color:#6b7280}
.top-actions{display:flex;gap:10px;flex-wrap:wrap}
.btn,.next,.filter-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151}
.next{background:#389B5B;border-color:#389B5B;color:#fff}
.toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px}
.toolbar-left,.toolbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.toolbar input{border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;min-width:280px}
.filter-btn{font-size:.9rem;cursor:pointer}
.filter-btn.is-active{background:#389B5B;border-color:#389B5B;color:#fff}
.table{width:100%;border-collapse:collapse;margin-top:14px}
.table th,.table td{padding:14px 10px;text-align:left;border-top:1px solid #edf0f2}
.table th{font-size:.8rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em}
.table-name-link{color:#0f172a;text-decoration:none;font-weight:600}
.table-name-link:hover{color:#389B5B;text-decoration:underline}
.action-icons{display:flex;gap:10px}
.icon-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.8}
.icon-btn{color:#111827}
.icon-btn.delete{color:#ef4444}
.tag{display:inline-flex;padding:5px 10px;border-radius:999px;font-weight:600;font-size:.76rem}
.ok{background:#dcfce7;color:#166534}
.warn{background:#fef3c7;color:#92400e}
.empty{padding:22px 10px;color:#6b7280;text-align:center}
.bottom{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px}
.pager{color:#6b7280;font-size:.84rem}
@media(max-width:1100px){.stepper{padding:14px}.step-list{grid-template-columns:repeat(3,minmax(0,1fr))}.step-label{max-width:none}}
</style>

<div class="flow">
    <aside class="stepper">
        <div class="step-list">
            <?php foreach ($steps as $index => $step): ?>
                <a class="step-link<?php echo !empty($step['active']) ? ' active' : ''; ?>" href="<?php echo $esc($step['url']); ?>">
                    <span class="step-index"><?php echo $index + 1; ?></span>
                    <span class="step-label"><?php echo $esc($step['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <section class="content">
        <div class="head">
            <div>
                <h2>Audiometry List</h2>
                <p>Review audiometry records for the selected employee before examination and report generation.</p>
                <?php if ($selectedEmployeeName !== ''): ?>
                    <div class="selected-note">
                        Current employee:
                        <strong><?php echo $esc($selectedEmployeeName); ?></strong>
                        <?php if (!empty($selectedCompany->company_name)): ?>
                            from <strong><?php echo $esc($selectedCompany->company_name); ?></strong>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="top-actions">
                <a class="btn" href="#">Import</a>
                <a class="next" href="<?php echo $esc($examUrl); ?>">+ Add Record</a>
            </div>
        </div>

        <div class="toolbar">
            <div class="toolbar-left">
                <input id="audiometrySearch" type="text" placeholder="Search audiometry record">
            </div>
            <div class="toolbar-right">
                <button type="button" class="filter-btn" data-status-filter="pending">Pending</button>
                <button type="button" class="filter-btn" data-status-filter="completed">Completed</button>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Record ID</th>
                    <th>Employee</th>
                    <th>Company</th>
                    <th>Scheduled Date</th>
                    <th>Result</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recordTotal > 0): ?>
                    <?php foreach ($records as $row): ?>
                        <?php
                        $name = trim((string) (($row->employee_firstName ?? '') . ' ' . ($row->employee_lastName ?? '')));
                        $editUrl = function_exists('route')
                            ? route('audiometry.record.edit', ['id' => $row->audiometry_id])
                            : 'edit_audioRecord.php?id=' . urlencode((string) $row->audiometry_id);
                        $deleteUrl = function_exists('route')
                            ? route('audiometry.record.delete', ['id' => $row->audiometry_id])
                            : 'delete_audioRecord.php?id=' . urlencode((string) $row->audiometry_id);
                        $statusClass = ($row->status ?? '') === 'completed' ? 'ok' : 'warn';
                        $statusLabel = ($row->status ?? '') === 'completed' ? 'Completed' : 'Pending';
                        $resultLabel = !empty($row->audio_recommendation) ? $row->audio_recommendation : 'Pending';
                        ?>
                        <tr data-status="<?php echo $esc($row->status ?? 'pending'); ?>">
                            <td>#AUD-L<?php echo $esc($row->audiometry_id); ?></td>
                            <td><a class="table-name-link" href="<?php echo $esc($editUrl); ?>"><?php echo $esc($name !== '' ? $name : 'Not set'); ?></a></td>
                            <td><?php echo $esc($row->company_name ?? '-'); ?></td>
                            <td><?php echo $esc($row->audioTest_date ?? '-'); ?></td>
                            <td><?php echo $esc($resultLabel); ?></td>
                            <td><span class="tag <?php echo $esc($statusClass); ?>"><?php echo $esc($statusLabel); ?></span></td>
                            <td>
                                <div class="action-icons">
                                    <a class="icon-btn" href="<?php echo $esc($editUrl); ?>" title="View">
                                        <svg viewBox="0 0 24 24"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>
                                    <a class="icon-btn" href="<?php echo $esc($editUrl); ?>" title="Edit">
                                        <svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg>
                                    </a>
                                    <a class="icon-btn delete" href="<?php echo $esc($deleteUrl); ?>" title="Delete">
                                        <svg viewBox="0 0 24 24"><path d="M4 7h16"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M6 7l1 13h10l1-13"></path><path d="M9 7V4h6v3"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="empty" colspan="7">No audiometry records found for the selected employee.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="bottom">
            <span class="pager">
                <?php echo $recordTotal > 0 ? 'Showing 1-' . number_format($recordTotal) . ' of ' . number_format($recordTotal) . ' records' : 'Showing 0 of 0 records'; ?>
            </span>
            <div>
                <a class="btn" href="<?php echo $esc($questionnaireUrl); ?>">Back</a>
                <a class="next" href="<?php echo $esc($examUrl); ?>">Next</a>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    var searchInput = document.getElementById('audiometrySearch');
    var section = searchInput ? searchInput.closest('.content') : null;
    if (!section) return;
    var rows = Array.prototype.slice.call(section.querySelectorAll('.table tbody tr[data-status]'));
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
</script>

<?php medis_render_navigation_end(); ?>
</body>
</html>
