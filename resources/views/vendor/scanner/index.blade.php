@extends('layouts.vendor')

@section('title', 'Scan VIN — Shipment Otomotif')
@section('page-title', 'Scan VIN Kendaraan')
@section('breadcrumb')
    <li class="breadcrumb-item active">Scan VIN</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center mb-3">
            <span class="badge badge-primary" style="margin-left:8px;">{{ $vendor->position }}</span>
        </div>

        {{-- Camera Section --}}
        <div class="card mb-3" id="camera-section">
            <div class="card-body p-0 text-center">
                <video id="camera-video" class="w-100 rounded-top" autoplay playsinline style="max-height: 400px; object-fit: cover; display: none;"></video>
                <canvas id="camera-canvas" style="display: none;"></canvas>

                <div id="camera-placeholder" class="" style="padding:40px 20px;">
                    <i class="fas fa-camera" style="font-size:2rem;color:var(--secondary);"></i>
                    <p class="text-muted mt-2 mb-0">Klik tombol di bawah untuk memulai kamera.</p>
                </div>

                <img id="captured-image" class="w-100 rounded-top" style="max-height: 400px; object-fit: contain; display: none;" alt="Captured">
            </div>
            <div class="card-footer" style="background:#fff;">
                <div class="d-flex gap-2 justify-content-center">
                    <button id="btn-start-camera" class="btn btn-primary" onclick="startCamera()">
                        <i class="fas fa-camera"></i> Mulai Kamera
                    </button>
                    <button id="btn-capture" class="btn btn-success" onclick="captureImage()" style="display: none;">
                        <i class="fas fa-camera"></i> Ambil Gambar
                    </button>
                    <button id="btn-retake" class="btn btn-default" onclick="retakePhoto()" style="display: none;">
                        <i class="fas fa-redo"></i> Ulangi
                    </button>
                    <button id="btn-process" class="btn btn-warning" onclick="processOcr()" style="display: none;">
                        <i class="fas fa-microchip"></i> Proses OCR
                    </button>
                </div>
            </div>
        </div>

        {{-- Manual Input Section --}}
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-keyboard"></i> Input Manual VIN</h6>
                <p class="text-muted small">Jika OCR gagal, Anda dapat memasukkan VIN secara manual.</p>
                <div class="input-group">
                    <input type="text" id="vin-input" class="form-control form-control-lg text-uppercase"
                           placeholder="Masukkan 17 karakter VIN"
                           maxlength="17" minlength="17"
                           style="letter-spacing: 2px; font-family: monospace;">
                    <span class="input-group-text" id="vin-counter">0/17</span>
                </div>
            </div>
        </div>

        @if(auth()->user()->vendor && auth()->user()->vendor->position === 'AT PtD (Dooring)')
        {{-- Document Link — Only for AT PtD (Dooring) --}}
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-link"></i> Link Dokumen (Opsional)</h6>
                <input type="url" id="document-link" class="form-control"
                       placeholder="https://drive.google.com/...">
                <small class="text-muted">Link Google Drive / Sharepoint untuk dokumen pendukung.</small>
            </div>
        </div>
        @endif

        {{-- Confirm Button --}}
        <div style="width:100%;">
            <button id="btn-confirm" class="btn btn-primary btn-lg btn-block" onclick="confirmScan()" disabled>
                <i class="fas fa-check-circle"></i> Simpan Hasil Scan
            </button>
        </div>

        {{-- Result Section --}}
        <div id="result-section" class="mt-3" style="display: none;">
            <div id="result-alert" class="alert" role="alert"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let videoStream = null;

    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    const placeholder = document.getElementById('camera-placeholder');
    const capturedImg = document.getElementById('captured-image');
    const vinInput = document.getElementById('vin-input');
    const vinCounter = document.getElementById('vin-counter');
    const btnStartCamera = document.getElementById('btn-start-camera');
    const btnCapture = document.getElementById('btn-capture');
    const btnRetake = document.getElementById('btn-retake');
    const btnProcess = document.getElementById('btn-process');
    const btnConfirm = document.getElementById('btn-confirm');
    const resultSection = document.getElementById('result-section');
    const resultAlert = document.getElementById('result-alert');

    // VIN input counter and validation
    vinInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        vinCounter.textContent = this.value.length + '/17';

        if (this.value.length === 17) {
            vinCounter.classList.add('text-bg-success');
            vinCounter.classList.remove('text-bg-danger');
            btnConfirm.disabled = false;
        } else {
            vinCounter.classList.remove('text-bg-success');
            if (this.value.length > 0) {
                vinCounter.classList.add('text-bg-danger');
            } else {
                vinCounter.classList.remove('text-bg-danger');
            }
            btnConfirm.disabled = true;
        }
    });

    async function startCamera() {
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } }
            });
            video.srcObject = videoStream;
            video.style.display = 'block';
            placeholder.style.display = 'none';
            capturedImg.style.display = 'none';
            btnStartCamera.style.display = 'none';
            btnCapture.style.display = 'inline-block';
            btnRetake.style.display = 'none';
            btnProcess.style.display = 'none';
        } catch (err) {
            showResult('error', 'Gagal mengakses kamera: ' + err.message);
        }
    }

    function captureImage() {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        capturedImg.src = canvas.toDataURL('image/png');
        capturedImg.style.display = 'block';
        video.style.display = 'none';

        // Stop camera
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
        }

        btnCapture.style.display = 'none';
        btnRetake.style.display = 'inline-block';
        btnProcess.style.display = 'inline-block';
    }

    function retakePhoto() {
        capturedImg.style.display = 'none';
        btnRetake.style.display = 'none';
        btnProcess.style.display = 'none';
        startCamera();
    }

    async function processOcr() {
        btnProcess.disabled = true;
        btnProcess.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

        try {
            const imageData = canvas.toDataURL('image/png');

            const response = await fetch('{{ route("vendor.scanner.scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ image: imageData }),
            });

            const data = await response.json();

            if (data.success && data.vin) {
                vinInput.value = data.vin;
                vinInput.dispatchEvent(new Event('input'));
                showResult('success', 'VIN berhasil dideteksi: <strong>' + escapeHtml(data.vin) + '</strong>. Periksa dan konfirmasi.');
            } else {
                showResult('warning', escapeHtml(data.error || 'OCR gagal. Silakan input VIN secara manual.'));
            }
        } catch (err) {
            showResult('danger', 'Terjadi kesalahan: ' + escapeHtml(err.message));
        } finally {
            btnProcess.disabled = false;
            btnProcess.innerHTML = '<i class="fas fa-microchip"></i> Proses OCR';
        }
    }

    async function confirmScan() {
        const vin = vinInput.value.trim();
        if (vin.length !== 17) {
            showResult('danger', 'VIN harus tepat 17 karakter.');
            return;
        }

        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

        try {
            const docLinkEl = document.getElementById('document-link');
            const documentLink = docLinkEl ? docLinkEl.value.trim() : '';

            const response = await fetch('{{ route("vendor.scanner.confirm") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    no_rangka: vin,
                    document_link: documentLink || null,
                }),
            });

            const data = await response.json();

            if (data.success) {
                showResult('success',
                    '<i class="fas fa-check-circle"></i> ' + escapeHtml(data.message) +
                    '<br><small>VIN: ' + escapeHtml(data.data.no_rangka) + ' | Tanggal: ' + escapeHtml(data.data.scan_date) + '</small>'
                );
                // Reset form for next scan
                vinInput.value = '';
                vinInput.dispatchEvent(new Event('input'));
                const docLinkReset = document.getElementById('document-link');
                if (docLinkReset) docLinkReset.value = '';
            } else {
                showResult('danger', escapeHtml(data.error || 'Gagal menyimpan data.'));
                btnConfirm.disabled = false;
            }
        } catch (err) {
            showResult('danger', 'Terjadi kesalahan: ' + escapeHtml(err.message));
            btnConfirm.disabled = false;
        } finally {
            btnConfirm.innerHTML = '<i class="fas fa-check-circle"></i> Simpan Hasil Scan';
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function showResult(type, message) {
        resultSection.style.display = 'block';
        resultAlert.className = 'alert alert-' + type;
        resultAlert.innerHTML = message;
        resultSection.scrollIntoView({ behavior: 'smooth' });
    }
</script>
@endpush
