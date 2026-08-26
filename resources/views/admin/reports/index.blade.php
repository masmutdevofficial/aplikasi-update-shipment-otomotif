@extends('layouts.admin')

@php
    $reportOptions = [
        'dso' => ['label' => 'DSO', 'icon' => 'fa-truck', 'query' => ['type' => 'dso']],
        'tso' => ['label' => 'TSO', 'icon' => 'fa-truck-loading', 'query' => ['type' => 'tso']],
        'iso-darat' => ['label' => 'ISO Darat', 'icon' => 'fa-road', 'query' => ['type' => 'iso', 'iso_type' => 'darat']],
        'iso-laut' => ['label' => 'ISO Laut', 'icon' => 'fa-ship', 'query' => ['type' => 'iso', 'iso_type' => 'laut']],
    ];
    $monthOptions = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $reportLabel = $reportOptions[$selectedReport]['label'];
    $selectedQuery = $reportOptions[$selectedReport]['query'];
@endphp

@section('title', 'Laporan '.$reportLabel.' — Shipment Otomotif')
@section('page-title', 'Laporan Shipment')
@section('breadcrumb')
    <li class="breadcrumb-item active">Laporan {{ $reportLabel }}</li>
@endsection

@section('content')
<div class="card report-selector-card">
    <div class="card-body">
        <span class="report-selector-label">Pilih Dashboard</span>
        <p class="report-selector-description">Pilih dashboard yang ingin ditampilkan.</p>
        <nav class="report-tabs" aria-label="Pilihan laporan shipment">
            @foreach ($reportOptions as $type => $option)
                <a href="{{ route('admin.reports.index', array_filter([...$option['query'], 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                   class="report-tab {{ $selectedReport === $type ? 'active' : '' }}"
                   @if ($selectedReport === $type) aria-current="page" @endif>
                    <i class="fas {{ $option['icon'] }}"></i> {{ $option['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="report-period-row">
            <span class="report-period-label"><i class="fas fa-calendar-alt"></i> Filter Periode</span>
            <form method="GET" action="{{ route('admin.reports.index') }}" class="report-filter-form">
                <input type="hidden" name="type" value="{{ str_starts_with($selectedReport, 'iso-') ? 'iso' : $selectedReport }}">
                @if (str_starts_with($selectedReport, 'iso-'))
                    <input type="hidden" name="iso_type" value="{{ $selectedReport === 'iso-laut' ? 'laut' : 'darat' }}">
                @endif
                <div class="report-filter-field">
                    <label for="reportMonth">Bulan</label>
                    <select id="reportMonth" name="month" class="form-select report-period-input">
                        <option value="">Semua Bulan</option>
                        @foreach ($monthOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedMonth === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="report-filter-field">
                    <label for="reportYear">Tahun</label>
                    <select id="reportYear" name="year" class="form-select report-period-input">
                        <option value="">Semua Tahun</option>
                        @foreach ($availableYears as $value)
                            <option value="{{ $value }}" @selected($selectedYear === $value)>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan</button>
                <a href="{{ route('admin.reports.index', $selectedQuery) }}" class="btn btn-default">Reset</a>
                <a href="{{ route('admin.reports.export', array_filter([...$selectedQuery, 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                   class="btn btn-success"><i class="fas fa-file-excel"></i> Export</a>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-table"></i> Laporan Shipment {{ $reportLabel }}</h3></div>
    <div class="card-body p-0">
        <div class="report-table-scroll">
            <table id="table-reports" class="table table-hover table-striped mb-0" style="font-size:.85rem;">
                <thead><tr>
                    @foreach ($reportColumns as $column)
                        <th>{{ $column['label'] }}</th>
                    @endforeach
                </tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
.report-selector-card { border-left:4px solid var(--primary); }
.report-selector-label { display:block; margin-bottom:3px; color:#343a40; font-size:15px; font-weight:600; }
.report-selector-description { margin:0 0 16px; color:#6c757d; font-size:13px; }
.report-tabs { display:flex; align-items:center; gap:6px; padding-bottom:12px; overflow-x:auto; border-bottom:1px solid #e2e8f0; }
.report-tab { display:inline-flex; flex:0 0 auto; align-items:center; gap:7px; padding:10px 16px; color:#475569; font-size:13px; font-weight:600; text-decoration:none; border:1px solid #e2e8f0; border-radius:9px; background:#f8fafc; transition:.2s ease; }
.report-tab:hover { color:#1d4ed8; text-decoration:none; border-color:#93c5fd; background:#eff6ff; transform:translateY(-1px); }
.report-tab.active { color:#fff; border-color:#2563eb; background:linear-gradient(135deg,#2563eb,#1d4ed8); box-shadow:0 5px 12px rgba(37,99,235,.24); }
.report-period-row { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; padding-top:14px; overflow-x:auto; }
.report-period-label { flex:0 0 auto; padding-bottom:9px; color:#475569; font-size:13px; font-weight:600; }
.report-filter-form { display:flex; flex:0 0 auto; align-items:flex-end; gap:8px; min-width:max-content; }
.report-filter-field label { display:block; margin-bottom:4px; color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
.report-period-input { min-width:145px; }
.report-table-scroll { width:100%; overflow-x:auto; }
#table-reports th, #table-reports td { min-width:120px; white-space:nowrap; }
#table-reports th:first-child, #table-reports td:first-child { min-width:55px; text-align:center; }
@media (max-width:576px) { .report-period-row { align-items:flex-start; flex-direction:column; gap:6px; } .report-filter-form { width:max-content; } }
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reportColumns = @json($reportColumns);
    const escapeHtml = value => $('<div>').text(value ?? '-').html();
    const renderers = {
        code: value => `<code>${escapeHtml(value)}</code>`,
        result: value => {
            const badge = value === 'OTD' ? 'badge-success' : (value === 'LATE' ? 'badge-danger' : 'badge-secondary');
            return `<span class="badge ${badge}">${escapeHtml(value)}</span>`;
        },
        delay: value => {
            const cssClass = Number(value) > 0 ? 'text-danger font-weight-bold' : '';
            return `<span class="${cssClass}">${escapeHtml(value)}</span>`;
        },
        document: value => value && value !== '-'
            ? `<a href="${escapeHtml(value)}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" aria-label="Buka dokumen"><i class="fas fa-image"></i></a>`
            : '<span class="text-muted">-</span>',
        text: value => escapeHtml(value),
    };

    $('#table-reports').DataTable({
        processing:true,
        serverSide:true,
        ajax:{
            url:@json(route('admin.reports.data')),
            type:'POST',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            data:function (payload) {
                payload.type = @json(str_starts_with($selectedReport, 'iso-') ? 'iso' : $selectedReport);
                payload.iso_type = @json(str_starts_with($selectedReport, 'iso-') ? ($selectedReport === 'iso-laut' ? 'laut' : 'darat') : null);
                payload.month = @json($selectedMonth);
                payload.year = @json($selectedYear);
            },
        },
        columns:reportColumns.map(column => ({
            data:column.data,
            name:column.data,
            orderable:column.orderable,
            searchable:column.orderable,
            render:(value, type) => type === 'display'
                ? (renderers[column.kind] ?? renderers.text)(value)
                : value,
        })),
        pageLength:25, lengthMenu:[[10,25,50,100],['10','25','50','100']], scrollX:true,
        language:{ search:'Cari:', lengthMenu:'Tampilkan _MENU_ data per halaman', info:'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty:'Tidak ada data', emptyTable:'Belum ada data laporan {{ $reportLabel }}', infoFiltered:'(difilter dari _MAX_ total data)', zeroRecords:'Tidak ada data yang cocok', paginate:{first:'«',last:'»',next:'›',previous:'‹'} },
        order:[[1,'asc']]
    });
});
</script>
@endpush
