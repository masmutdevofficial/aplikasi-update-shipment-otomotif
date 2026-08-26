@extends('layouts.vendor')

@section('title', 'Upload Dokumen — Shipment Otomotif')
@section('page-title', 'Upload Dokumen')
@section('breadcrumb')
    <li class="breadcrumb-item active">Upload Dokumen</li>
@endsection

@section('content')
<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-upload"></i> Dokumen Shipment</h3>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">
            Pilih foto dokumen PNG/JPEG maksimal 5 MB. Dokumen yang sudah ada dapat dilihat atau diganti.
        </p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="table-documents" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{ $identityLabel }}</th>
                        <th>Dokumen</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
#table-documents td:last-child { min-width: 360px; }
.document-upload-form { display:flex; align-items:center; flex-wrap:wrap; gap:6px; }
.document-upload-form .form-control { max-width:190px; padding:4px 8px; font-size:12px; }
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = @json(csrf_token());
    const escapeHtml = value => $('<div>').text(value ?? '').html();

    $('#table-documents').DataTable({
        processing:true,
        serverSide:true,
        ajax:{
            url:@json(route('vendor.documents.data')),
            type:'POST',
            headers:{'X-CSRF-TOKEN':csrfToken},
        },
        pageLength:25,
        lengthMenu:[[10,25,50,100],['10','25','50','100']],
        columns:[
            {data:'row_number',name:'row_number',orderable:false,searchable:false},
            {data:'identifier',name:'identifier',render:value => `<code>${escapeHtml(value)}</code>`},
            {
                data:null,
                name:'document',
                orderable:false,
                searchable:false,
                render:data => {
                    const viewButton = data.document_url
                        ? `<a href="${escapeHtml(data.document_url)}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fas fa-image"></i> Lihat</a>`
                        : '';
                    const buttonLabel = data.document_url ? 'Ganti' : 'Upload';

                    return `<div class="document-upload-form">
                        ${viewButton}
                        <form method="POST" action="${escapeHtml(data.upload_url)}" enctype="multipart/form-data" class="document-upload-form">
                            <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                            <input type="file" name="document" class="form-control" accept="image/png,image/jpeg" capture="environment" required>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload"></i> ${buttonLabel}</button>
                        </form>
                    </div>`;
                },
            },
        ],
        language:{
            search:'Cari:', lengthMenu:'Tampilkan _MENU_ data per halaman',
            info:'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty:'Tidak ada data shipment',
            emptyTable:'Belum ada data shipment', infoFiltered:'(difilter dari _MAX_ total data)',
            zeroRecords:'Tidak ada data yang cocok',
            paginate:{first:'«',last:'»',next:'›',previous:'‹'},
        },
        order:[[1,'asc']],
    });
});
</script>
@endpush
