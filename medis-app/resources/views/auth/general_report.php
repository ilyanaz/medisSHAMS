<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>General Report</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';
$esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'User',
    'active' => 'report',
    'pageSubtitle' => 'Manage report pages by module and form type',
]);

$companyNames = [];
foreach (($companies ?? []) as $company) {
    $name = trim((string) ($company->company_name ?? ''));
    if ($name !== '') {
        $companyNames[] = $name;
    }
}
if (empty($companyNames)) {
    $companyNames = ['Alpha Engineering', 'Beta Manufacturing', 'Gamma Plantations'];
}

$pickCompany = static function (int $index) use ($companyNames): string {
    return $companyNames[$index % count($companyNames)];
};

$audioRows = [
    ['module' => 'audiometry', 'filter' => 'questionnaire', 'employee_name' => 'Zul Hilmi', 'company' => $pickCompany(0), 'chemical_name' => 'Noise exposure', 'status' => 'Completed', 'status_key' => 'completed', 'date_examined' => '2026-03-09', 'href' => 'audiometry_questionnaire.php', 'pdf_href' => route('pdf.questionnaire')],
    ['module' => 'audiometry', 'filter' => 'report', 'employee_name' => 'Farah Nadia', 'company' => $pickCompany(1), 'chemical_name' => 'Noise exposure', 'status' => 'Incomplete', 'status_key' => 'incomplete', 'date_examined' => '2026-03-12', 'href' => 'audiometry_report.php', 'pdf_href' => route('pdf.audio-report')],
];
$rows = array_merge($surveillanceReportRows ?? [], $audioRows);
?>
<style>
.report-shell{display:grid;gap:18px}.report-head h2{margin:0;font-size:1.9rem}.report-head p{margin:8px 0 0;color:#6b7280}.manage-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:0;overflow:hidden}.module-bar{display:flex;gap:12px;padding:18px;border-bottom:1px solid #edf0f2;flex-wrap:wrap}.module-btn{appearance:none;border:1px solid #d1d5db;background:#fff;border-radius:12px;padding:12px 20px;font:inherit;font-weight:700;color:#374151;cursor:pointer;min-width:150px}.module-btn.active{background:#eef7f0;border-color:#b8d8c4;color:#166534}.subfilter-bar{display:flex;gap:18px;align-items:center;padding:0 18px;border-bottom:1px solid #edf0f2;flex-wrap:wrap}.subfilter-btn{appearance:none;border:0;background:transparent;padding:14px 0 12px;font:inherit;font-weight:600;color:#4b5563;cursor:pointer;position:relative;text-transform:uppercase;font-size:.82rem}.subfilter-btn.active{color:#166534}.subfilter-btn.active::after{content:"";position:absolute;left:0;right:0;bottom:-1px;height:2px;background:#389B5B;border-radius:999px}.toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #edf0f2;flex-wrap:wrap}.toolbar-left,.toolbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.toolbar-btn{display:inline-flex;align-items:center;gap:8px;border:1px solid #d1d5db;border-radius:10px;background:#fff;color:#374151;padding:9px 12px;text-decoration:none;font:inherit;cursor:pointer}.toolbar-btn.is-active{background:#eef7f0;border-color:#b8d8c4;color:#166534}.search{width:min(420px,100%);border:1px solid #d1d5db;border-radius:10px;padding:10px 12px;font:inherit}.filter-backdrop{display:none;position:fixed;inset:0;background:rgba(15,23,42,.18);z-index:120}.filter-backdrop.is-open{display:block}.filter-panel{display:none;position:fixed;top:110px;right:36px;width:min(520px,calc(100vw - 32px));padding:18px;border:1px solid #dbe3ea;border-radius:18px;background:#fff;box-shadow:0 26px 60px rgba(15,23,42,.16);z-index:121}.filter-panel.is-open{display:block}.filter-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.filter-panel-head h3{margin:0;font-size:1rem}.filter-close{border:0;background:transparent;color:#6b7280;font-size:1.35rem;line-height:1;cursor:pointer;padding:0 4px}.filter-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;align-items:end}.field{display:grid;gap:8px}.field label{font-size:.86rem;font-weight:600;color:#374151}.field input,.field select{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;font:inherit;background:#fff}.field.full{grid-column:1/-1}.field-actions{display:flex;gap:10px;align-items:center;justify-content:flex-end;grid-column:1/-1}.clear-btn,.apply-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:12px;padding:10px 14px;font:inherit;cursor:pointer;text-decoration:none}.clear-btn{border:1px solid #d1d5db;background:#fff;color:#374151}.apply-btn{border:1px solid #389B5B;background:#389B5B;color:#fff}.selection-bar{display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #edf0f2;background:#f8fbf8}.selection-bar span{font-size:.9rem;font-weight:600;color:#166534}.check-col{width:52px;text-align:center}.check-col input{width:16px;height:16px;accent-color:#389B5B;cursor:pointer}.report-table{width:100%;border-collapse:collapse}.report-table th,.report-table td{padding:16px 18px;text-align:left;border-top:1px solid #edf0f2;vertical-align:top}.report-table th{font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;background:#fafafa}.status{display:inline-flex;align-items:center;border-radius:999px;padding:5px 10px;font-weight:700;font-size:.76rem}.status.completed{background:#dcfce7;color:#166534}.status.pending,.status.incomplete{background:#fef3c7;color:#92400e}.status.na{background:#e5e7eb;color:#4b5563}.action-icons{display:flex;gap:10px;align-items:center}.icon-btn{display:inline-flex;align-items:center;justify-content:center;background:transparent;border:0;padding:0;color:#111827;cursor:pointer;text-decoration:none}.icon-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.8}.icon-btn.delete{color:#ef4444}.table-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 18px;border-top:1px solid #edf0f2;flex-wrap:wrap}.pager{color:#6b7280;font-size:.84rem}.empty-row td{text-align:center;color:#6b7280}@media(max-width:980px){.toolbar{align-items:stretch}.toolbar-left,.toolbar-right{width:100%}.toolbar-right{justify-content:flex-start}.search{width:100%}.subfilter-bar{gap:14px}.filter-panel{top:96px;right:16px}}@media(max-width:640px){.filter-grid{grid-template-columns:1fr}.filter-panel{top:88px;width:calc(100vw - 24px);right:12px}}
.hide-usechh1{display:none!important}.hide-usechh1-extra{display:none!important}</style>
<div class="report-shell">
    <section class="report-head">
        <h2>Manage Reports</h2>
        <p>For surveillance, All shows the full report list, while USECHH 1 shows employee records.</p>
    </section>

    <section class="manage-card">
        <div class="module-bar">
            <button class="module-btn active" type="button" data-module="surveillance">Surveillance</button>
            <button class="module-btn" type="button" data-module="audiometry">Audiometry</button>
        </div>

        <div class="subfilter-bar" id="subfilterBar"></div>

        <div class="toolbar">
            <div class="toolbar-left">
                <input class="search" id="reportSearch" type="text" placeholder="Search employee, company, or chemical">
            </div>
            <div class="toolbar-right">
                <button class="toolbar-btn" id="filterToggleBtn" type="button">Filter</button>
                <button class="toolbar-btn" type="button">Sort by</button>
                <button class="toolbar-btn" id="bulkExportBtn" type="button">Print</button>
            </div>
        </div>

        <div class="filter-backdrop" id="filterBackdrop"></div>
        <div class="filter-panel" id="filterPanel">
            <div class="filter-panel-head">
                <h3>Filter reports</h3>
                <button class="filter-close" id="filterCloseBtn" type="button" aria-label="Close filter">&times;</button>
            </div>
            <div class="filter-grid">
                <div class="field full">
                    <label for="filterSearch">Search</label>
                    <input id="filterSearch" type="text" placeholder="Search employee, company, or chemical">
                </div>
                <div class="field">
                    <label for="filterCompany">Company Name</label>
                    <select id="filterCompany">
                        <option value="">All companies</option>
                        <?php foreach ($companyNames as $companyName): ?>
                            <option value="<?php echo $esc($companyName); ?>"><?php echo $esc($companyName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="filterStatus">Status</label>
                    <select id="filterStatus">
                        <option value="">All status</option>
                        <option value="completed">Completed</option>
                        <option value="incomplete">Incomplete</option>
                    </select>
                </div>
                <div class="field full">
                    <label for="filterDate">Date Examined</label>
                    <input id="filterDate" type="date">
                </div>
                <div class="field-actions">
                    <button class="clear-btn" id="filterClearBtn" type="button">Clear filters</button>
                    <button class="apply-btn" id="filterApplyBtn" type="button">Apply</button>
                </div>
            </div>
        </div>

        <div class="selection-bar" id="selectionBar" style="display:none;"><span id="selectionCount">0 selected</span></div>

        <table class="report-table">
            <thead>
                <tr>
                    <th class="check-col"><input type="checkbox" id="selectAllRows" aria-label="Select all visible rows"></th>
                    <th class="col-employee">Employee Name</th>
                    <th class="col-company">Company Name</th>
                    <th class="col-usechh1-extra">No Phone</th>
                    <th class="col-usechh1-extra">IC / Passport No</th>
                    <th class="col-chemical">Chemical Name</th>
                    <th class="col-date">Date Examined</th>
                    <th class="col-status">Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="reportTableBody">
                <?php foreach ($rows as $row): ?>
                    <tr data-module="<?php echo $esc($row['module']); ?>" data-filter="<?php echo $esc($row['filter']); ?>" data-company="<?php echo $esc(strtolower($row['company'])); ?>" data-status="<?php echo $esc($row['status_key']); ?>" data-date="<?php echo $esc($row['date_examined']); ?>" data-pdf-href="<?php echo $esc($row['pdf_href'] ?? ''); ?>">
                        <td class="check-col"><input class="row-select" type="checkbox" aria-label="Select <?php echo $esc($row['employee_name']); ?>"></td>
                        <td class="col-employee"><?php echo $esc($row['employee_name']); ?></td>
                        <td class="col-company"><?php echo $esc($row['company']); ?></td>
                        <td class="col-usechh1-extra"><?php echo $esc($row['phone_no'] ?? '-'); ?></td>
                        <td class="col-usechh1-extra"><?php echo $esc($row['identity_no'] ?? '-'); ?></td>
                        <td class="col-chemical"><?php echo $esc($row['chemical_name']); ?></td>
                        <td class="col-date"><?php echo $esc(date('d M Y', strtotime($row['date_examined']))); ?></td>
                        <td class="col-status"><span class="status <?php echo $esc($row['status_key']); ?>"><?php echo $esc($row['status']); ?></span></td>
                        <td>
                            <div class="action-icons"><a class="icon-btn" href="<?php echo $esc($row['href']); ?>" title="View"><svg viewBox="0 0 24 24"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6z"></path><circle cx="12" cy="12" r="3"></circle></svg></a><a class="icon-btn" href="<?php echo $esc($row['href']); ?>" title="Edit"><svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg></a><button class="icon-btn delete" type="button" data-name="<?php echo $esc($row['employee_name']); ?>" title="Delete"><svg viewBox="0 0 24 24"><path d="M4 7h16"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M6 7l1 13h10l1-13"></path><path d="M9 7V4h6v3"></path></svg></button></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="empty-row" id="reportEmptyRow" style="display:none;">
                    <td colspan="7">No report records match the selected filter.</td>
                </tr>
            </tbody>
        </table>

        <div class="table-foot">
            <span class="pager" id="reportPager">Showing 0 records</span>
        </div>
    </section>
</div>
<script>
(function(){
    const moduleButtons = Array.prototype.slice.call(document.querySelectorAll('.module-btn'));
    const subfilterBar = document.getElementById('subfilterBar');
    const search = document.getElementById('reportSearch');
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const filterPanel = document.getElementById('filterPanel');
    const filterBackdrop = document.getElementById('filterBackdrop');
    const filterCloseBtn = document.getElementById('filterCloseBtn');
    const filterSearch = document.getElementById('filterSearch');
    const filterCompany = document.getElementById('filterCompany');
    const filterStatus = document.getElementById('filterStatus');
    const filterDate = document.getElementById('filterDate');
    const filterApplyBtn = document.getElementById('filterApplyBtn');
    const filterClearBtn = document.getElementById('filterClearBtn');
    const rows = Array.prototype.slice.call(document.querySelectorAll('#reportTableBody tr[data-module]'));
    const selectAllRows = document.getElementById('selectAllRows');
    const selectionBar = document.getElementById('selectionBar');
    const selectionCount = document.getElementById('selectionCount');
    const bulkExportBtn = document.getElementById('bulkExportBtn');
    const pager = document.getElementById('reportPager');
    const emptyRow = document.getElementById('reportEmptyRow');
    const deleteButtons = Array.prototype.slice.call(document.querySelectorAll('.icon-btn.delete'));
    const filtersByModule = {
        surveillance: ['all','usechh 1','usechh 2','usechh 3','usechh 4','usechh 5i','usechh 5ii'],
        audiometry: ['all','questionnaire','report']
    };
    let activeModule = 'surveillance';
    let activeFilter = 'all';

    const titleCase = function(value){
        return value.split(' ').map(function(part){
            return part.length ? part.charAt(0).toUpperCase() + part.slice(1) : part;
        }).join(' ');
    };

    const getMergedSearch = function(){
        const main = (search.value || '').trim();
        const panel = (filterSearch.value || '').trim();
        return [main, panel].filter(Boolean).join(' ').toLowerCase();
    };

    const setFilterOpen = function(open){
        if (!filterPanel || !filterToggleBtn) { return; }
        filterPanel.classList.toggle('is-open', open);
        filterToggleBtn.classList.toggle('is-active', open);
        if (filterBackdrop) { filterBackdrop.classList.toggle('is-open', open); }
    };

    const renderSubfilters = function(){
        const filters = filtersByModule[activeModule] || ['all'];
        subfilterBar.innerHTML = '';
        filters.forEach(function(filter){
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'subfilter-btn' + (filter === activeFilter ? ' active' : '');
            btn.setAttribute('data-filter', filter);
            btn.textContent = titleCase(filter);
            btn.addEventListener('click', function(){
                activeFilter = filter;
                renderSubfilters();
                renderRows();
            });
            subfilterBar.appendChild(btn);
        });
    };

    const getVisibleRows = function(){
        return rows.filter(function(row){ return row.style.display !== 'none'; });
    };

    const syncColumnVisibility = function(){
        const showUsechh1Extras = activeModule === 'surveillance' && activeFilter === 'usechh 1';
        const hideChemicalStatusDate = activeModule === 'surveillance' && activeFilter === 'usechh 1';
        const hideChemicalOnly = activeModule === 'audiometry';
        const isUsechh5ii = activeModule === 'surveillance' && activeFilter === 'usechh 5ii';
        document.querySelectorAll('.col-chemical').forEach(function(cell){
            cell.classList.toggle('hide-usechh1', hideChemicalStatusDate || hideChemicalOnly);
        });
        document.querySelectorAll('.col-date').forEach(function(cell){
            cell.classList.toggle('hide-usechh1', hideChemicalStatusDate);
        });
        document.querySelectorAll('.col-status').forEach(function(cell){
            cell.classList.toggle('hide-usechh1', hideChemicalStatusDate || isUsechh5ii);
        });
        document.querySelectorAll('.col-employee').forEach(function(cell){
            cell.classList.toggle('hide-usechh1', isUsechh5ii);
        });
        document.querySelectorAll('.col-usechh1-extra').forEach(function(cell){
            cell.classList.toggle('hide-usechh1-extra', !showUsechh1Extras);
        });
    };

    const getSelectedVisibleRows = function(){
        return getVisibleRows().filter(function(row){
            const checkbox = row.querySelector('.row-select');
            return checkbox && checkbox.checked;
        });
    };

    const syncSelectionUi = function(){
        const visibleRows = getVisibleRows();
        const selectedRows = getSelectedVisibleRows();
        if (selectionBar) {
            selectionBar.style.display = selectedRows.length ? 'flex' : 'none';
        }
        if (selectionCount) {
            selectionCount.textContent = selectedRows.length + ' selected';
        }
        if (selectAllRows) {
            selectAllRows.checked = visibleRows.length > 0 && selectedRows.length === visibleRows.length;
            selectAllRows.indeterminate = selectedRows.length > 0 && selectedRows.length < visibleRows.length;
        }
    };

    const exportSelectedRows = function(){
        const selectedRows = getSelectedVisibleRows();
        if (!selectedRows.length) {
            alert('Please select at least one report to print.');
            return;
        }
        const pdfUrls = selectedRows.map(function(row){
            return (row.getAttribute('data-pdf-href') || '').trim();
        }).filter(Boolean);
        if (!pdfUrls.length) {
            alert('No PDF route is available for the selected report.');
            return;
        }
        pdfUrls.forEach(function(url){
            window.open(url, '_blank');
        });
    };

    const renderRows = function(){
        const query = getMergedSearch();
        const companyValue = (filterCompany.value || '').trim().toLowerCase();
        const statusValue = (filterStatus.value || '').trim().toLowerCase();
        const dateValue = (filterDate.value || '').trim();
        let visible = 0;

        rows.forEach(function(row){
            const rowModule = row.getAttribute('data-module') || '';
            const rowFilter = row.getAttribute('data-filter') || '';
            const rowCompany = row.getAttribute('data-company') || '';
            const rowStatus = row.getAttribute('data-status') || '';
            const rowDate = row.getAttribute('data-date') || '';
            const text = (row.textContent || '').toLowerCase();
            const matchModule = rowModule === activeModule;
            const matchFilter = activeFilter === 'all' || rowFilter === activeFilter;
            const matchSearch = query === '' || text.indexOf(query) !== -1;
            const matchCompany = companyValue === '' || rowCompany === companyValue;
            const matchStatus = statusValue === '' || rowStatus === statusValue;
            const matchDate = dateValue === '' || rowDate === dateValue;
            const show = matchModule && matchFilter && matchSearch && matchCompany && matchStatus && matchDate;
            row.style.display = show ? '' : 'none';
            if (!show) {
                const checkbox = row.querySelector('.row-select');
                if (checkbox) { checkbox.checked = false; }
            }
            if (show) { visible += 1; }
        });

        if (emptyRow) {
            emptyRow.style.display = visible === 0 ? '' : 'none';
        }
        pager.textContent = visible === 0 ? 'Showing 0 records' : 'Showing 1-' + visible + ' of ' + visible + ' records';
        syncColumnVisibility();
        syncSelectionUi();
    };

    moduleButtons.forEach(function(btn){
        btn.addEventListener('click', function(){
            activeModule = btn.getAttribute('data-module');
            activeFilter = 'all';
            moduleButtons.forEach(function(item){ item.classList.toggle('active', item === btn); });
            renderSubfilters();
            renderRows();
        });
    });

    rows.forEach(function(row){
        const checkbox = row.querySelector('.row-select');
        if (checkbox) {
            checkbox.addEventListener('change', syncSelectionUi);
        }
    });

    deleteButtons.forEach(function(btn){
        btn.addEventListener('click', function(){
            const name = btn.getAttribute('data-name') || 'this record';
            alert('Delete action for ' + name + ' can be connected once the report routes and database actions are restored.');
        });
    });

    if (selectAllRows) {
        selectAllRows.addEventListener('change', function(){
            getVisibleRows().forEach(function(row){
                const checkbox = row.querySelector('.row-select');
                if (checkbox) { checkbox.checked = selectAllRows.checked; }
            });
            syncSelectionUi();
        });
    }

    if (filterToggleBtn) {
        filterToggleBtn.addEventListener('click', function(){
            setFilterOpen(!filterPanel.classList.contains('is-open'));
        });
    }
    if (filterCloseBtn) {
        filterCloseBtn.addEventListener('click', function(){ setFilterOpen(false); });
    }
    if (filterBackdrop) {
        filterBackdrop.addEventListener('click', function(){ setFilterOpen(false); });
    }
    document.addEventListener('keydown', function(event){
        if (event.key === 'Escape') {
            setFilterOpen(false);
        }
    });
    if (bulkExportBtn) { bulkExportBtn.addEventListener('click', exportSelectedRows); }
    if (search) { search.addEventListener('input', renderRows); }
    if (filterApplyBtn) { filterApplyBtn.addEventListener('click', function(){ renderRows(); setFilterOpen(false); }); }
    if (filterCompany) { filterCompany.addEventListener('change', renderRows); }
    if (filterStatus) { filterStatus.addEventListener('change', renderRows); }
    if (filterDate) { filterDate.addEventListener('change', renderRows); }
    if (filterSearch) { filterSearch.addEventListener('input', renderRows); }
    if (filterClearBtn) {
        filterClearBtn.addEventListener('click', function(){
            if (filterSearch) { filterSearch.value = ''; }
            if (filterCompany) { filterCompany.value = ''; }
            if (filterStatus) { filterStatus.value = ''; }
            if (filterDate) { filterDate.value = ''; }
            renderRows();
        });
    }

    renderSubfilters();
    renderRows();
}());
</script>
<?php medis_render_navigation_end(); ?>
</body>
</html>















