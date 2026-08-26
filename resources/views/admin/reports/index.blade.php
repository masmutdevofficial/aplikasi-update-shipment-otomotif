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
    $performanceColumns = $reportConfig
        ? collect($reportConfig['performance']['stages'])->map(fn ($stage, $key) => [
            'key' => $key,
            'label' => $stage['label'],
        ])
        : collect();
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
                @if ($selectedReport === 'dso')
                    <thead><tr>
                        <th>No</th><th>Lokasi</th><th>No. DO</th><th>Type</th><th>No. Rangka</th>
                        <th>No. Engine</th><th>Warna</th><th>Asal PDC</th><th>Kota</th><th>Tujuan</th>
                        <th>Terima DO</th><th>Keluar PDC</th><th>Kapal</th><th>Keberangkatan</th>
                        <th>AT Storage Port</th><th>ATD Kapal (Loading)</th><th>ATA Kapal</th><th>ATA Storage Port (Destination)</th>
                        <th>DO Release to Pickup</th><th>Storage Port</th><th>Dwelling Origin</th>
                        <th>Kapal (Aboard)</th><th>Storage Port (Destination)</th><th>Dwelling Destination</th>
                        <th>SLA Actual</th><th>SLA Customer</th><th>Result</th><th>Keterlambatan (Hari)</th>
                        <th>Max Arrival</th><th>Progress</th>
                        <th>Dokumen</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($shipments as $shipment)
                            @php $reportRow = \App\Services\ReportService::flattenShipment($shipment); @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $shipment->lokasi }}</td><td>{{ $shipment->no_do }}</td><td>{{ $shipment->type_kendaraan }}</td>
                                <td><code>{{ $shipment->no_rangka }}</code></td><td>{{ $shipment->no_engine }}</td>
                                <td>{{ $shipment->warna }}</td><td>{{ $shipment->asal_pdc }}</td><td>{{ $shipment->kota }}</td>
                                <td>{{ $shipment->tujuan_pengiriman }}</td><td>{{ $shipment->terima_do?->format('d-M-y') ?? '-' }}</td>
                                <td>{{ $shipment->keluar_dari_pdc?->format('d-M-y') ?? '-' }}</td>
                                <td>{{ $shipment->nama_kapal ?? '-' }}</td><td>{{ $shipment->keberangkatan_kapal?->format('d-M-y') ?? '-' }}</td>
                                <td>{{ $reportRow['at_storage_port'] ?? '-' }}</td>
                                <td>{{ $reportRow['atd_kapal_loading'] ?? '-' }}</td>
                                <td>{{ $reportRow['ata_kapal'] ?? '-' }}</td>
                                <td>{{ $reportRow['ata_storage_port_destination'] ?? '-' }}</td>
                                <td>{{ $reportRow['lead_time_do_release_pickup'] ?? '-' }}</td>
                                <td>{{ $reportRow['lead_time_storage_port'] ?? '-' }}</td>
                                <td>{{ $reportRow['dwelling_origin'] ?? '-' }}</td>
                                <td>{{ $reportRow['lead_time_kapal_aboard'] ?? '-' }}</td>
                                <td>{{ $reportRow['lead_time_storage_destination'] ?? '-' }}</td>
                                <td>{{ $reportRow['dwelling_destination'] ?? '-' }}</td>
                                <td>{{ $reportRow['sla_actual'] ?? '-' }}</td>
                                <td>{{ $reportRow['sla_customer'] ?? '-' }}</td>
                                <td><span class="badge {{ $reportRow['sla_result'] === 'OTD' ? 'badge-success' : ($reportRow['sla_result'] === 'LATE' ? 'badge-danger' : 'badge-secondary') }}">{{ $reportRow['sla_result'] }}</span></td>
                                <td class="{{ ($reportRow['delay_days'] ?? 0) > 0 ? 'text-danger font-weight-bold' : '' }}">{{ $reportRow['delay_days'] ?? '-' }}</td>
                                <td>{{ $reportRow['max_arrival'] ?? '-' }}</td>
                                <td>{{ $reportRow['progress'] }}</td>
                                <td>
                                    @php $document = $shipment->shipmentUpdates->first(fn ($update) => $update->document_path); @endphp
                                    @if ($document)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk(config('filesystems.document_disk'))->url($document->document_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fas fa-image"></i></a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @else
                    <thead><tr>
                        <th>No</th>
                        @foreach ($reportConfig['fields'] as $fieldConfig)<th>{{ $fieldConfig['label'] }}</th>@endforeach
                        @foreach ($performanceColumns as $column)<th>{{ $column['label'] }}</th>@endforeach
                        <th>SLA Actual</th><th>Result</th><th>Keterlambatan (Hari)</th><th>Max Arrival</th><th>Progress</th><th>Dokumen</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($shipments as $shipment)
                            @php $metrics = \App\Support\SpecialShipmentPerformance::calculate($selectedReport, $shipment); @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                @foreach ($reportConfig['fields'] as $field => $fieldConfig)
                                    @php $value = $field === 'sla_customer' ? $metrics['sla_customer'] : $shipment->{$field}; @endphp
                                    <td>
                                        @if ($fieldConfig['type'] === 'date')
                                            {{ $value?->format('d-M-y') ?? '-' }}
                                        @elseif (in_array($field, ['no_rangka', 'noka', 'no_spb'], true))
                                            <code>{{ $value ?? '-' }}</code>
                                        @else
                                            {{ $value ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                                @foreach ($performanceColumns as $column)<td>{{ $metrics[$column['key']] ?? '-' }}</td>@endforeach
                                <td>{{ $metrics['sla_actual'] ?? '-' }}</td>
                                <td><span class="badge {{ $metrics['sla_result'] === 'OTD' ? 'badge-success' : ($metrics['sla_result'] === 'LATE' ? 'badge-danger' : 'badge-secondary') }}">{{ $metrics['sla_result'] }}</span></td>
                                <td class="{{ ($metrics['delay_days'] ?? 0) > 0 ? 'text-danger font-weight-bold' : '' }}">{{ $metrics['delay_days'] ?? '-' }}</td>
                                <td>{{ $metrics['max_arrival']?->format('d-M-y') ?? '-' }}</td><td>{{ $metrics['progress'] }}</td>
                                <td>
                                    @php $documentUrl = \App\Services\ReportService::specialDocumentUrl($specialDocumentUrls, $shipment->{$reportConfig['identity']}); @endphp
                                    @if ($documentUrl)
                                        <a href="{{ $documentUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fas fa-image"></i></a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif
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
    $('#table-reports').DataTable({
        pageLength:25, lengthMenu:[[10,25,50,100,-1],['10','25','50','100','Semua']], scrollX:true,
        language:{ search:'Cari:', lengthMenu:'Tampilkan _MENU_ data per halaman', info:'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty:'Tidak ada data', emptyTable:'Belum ada data laporan {{ $reportLabel }}', infoFiltered:'(difilter dari _MAX_ total data)', zeroRecords:'Tidak ada data yang cocok', paginate:{first:'«',last:'»',next:'›',previous:'‹'} },
        columnDefs:[{orderable:false,targets:[0]}], order:[[1,'asc']]
    });
});
</script>
@endpush
