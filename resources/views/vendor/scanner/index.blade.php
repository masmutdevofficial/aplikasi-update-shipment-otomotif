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
                    <button id="btn-crop" class="btn btn-info" onclick="openCrop()" style="display: none;">
                        <i class="fas fa-crop-alt"></i> Crop
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

{{-- Crop Modal --}}
<div id="crop-modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.85); flex-direction:column; align-items:center; justify-content:center;">
    <div style="width:100%; max-width:700px; padding:12px; box-sizing:border-box;">
        <p class="text-white text-center mb-2" style="font-size:.85rem;">
            <i class="fas fa-info-circle"></i> Seret untuk memindahkan, seret sudut/tepi untuk mengubah ukuran area crop.
        </p>
        {{-- Crop canvas wrapper (scrollable on small screens) --}}
        <div style="position:relative; display:inline-block; width:100%; touch-action:none;">
            <canvas id="crop-canvas" style="display:block; width:100%; cursor:crosshair; border:2px solid #fff; border-radius:6px;"></canvas>
            {{-- SVG overlay — drawn by JS --}}
        </div>
        <div class="d-flex gap-2 justify-content-center mt-3">
            <button class="btn btn-secondary" onclick="closeCrop()">
                <i class="fas fa-times"></i> Batal
            </button>
            <button class="btn btn-success" onclick="applyCrop()">
                <i class="fas fa-check"></i> Terapkan Crop
            </button>
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
    const btnRetake  = document.getElementById('btn-retake');
    const btnCrop    = document.getElementById('btn-crop');
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
        btnRetake.style.display  = 'inline-block';
        btnCrop.style.display    = 'inline-block';
        btnProcess.style.display = 'inline-block';
    }

    function retakePhoto() {
        capturedImg.style.display = 'none';
        btnRetake.style.display   = 'none';
        btnCrop.style.display     = 'none';
        btnProcess.style.display  = 'none';
        startCamera();
    }

    // ─── Crop tool ────────────────────────────────────────────────────────────
    const cropModal  = document.getElementById('crop-modal');
    const cropCanvas = document.getElementById('crop-canvas');
    const cropCtx    = cropCanvas.getContext('2d');

    // Internal (unscaled) image dimensions stored in canvas
    let cropImg   = new Image();
    let cropBox   = { x: 0, y: 0, w: 0, h: 0 };   // in canvas pixels
    let dragState = null;   // { mode, startX, startY, origBox }
    const HANDLE  = 16;     // handle hit-area in DISPLAY pixels

    function openCrop() {
        // Load current captured image into crop canvas at full resolution
        cropImg = new Image();
        cropImg.onload = () => {
            cropCanvas.width  = cropImg.naturalWidth;
            cropCanvas.height = cropImg.naturalHeight;
            // Default crop box: 60% centred
            const mw = Math.round(cropImg.naturalWidth  * 0.6);
            const mh = Math.round(cropImg.naturalHeight * 0.6);
            cropBox = {
                x: Math.round((cropImg.naturalWidth  - mw) / 2),
                y: Math.round((cropImg.naturalHeight - mh) / 2),
                w: mw,
                h: mh,
            };
            drawCrop();
        };
        cropImg.src = capturedImg.src;
        cropModal.style.display = 'flex';
    }

    function closeCrop() {
        cropModal.style.display = 'none';
    }

    function drawCrop() {
        const cw = cropCanvas.width;
        const ch = cropCanvas.height;
        cropCtx.clearRect(0, 0, cw, ch);
        cropCtx.drawImage(cropImg, 0, 0);

        // Darken outside crop area
        cropCtx.fillStyle = 'rgba(0,0,0,0.5)';
        cropCtx.fillRect(0, 0, cw, cropBox.y);                                         // top
        cropCtx.fillRect(0, cropBox.y + cropBox.h, cw, ch - cropBox.y - cropBox.h);    // bottom
        cropCtx.fillRect(0, cropBox.y, cropBox.x, cropBox.h);                          // left
        cropCtx.fillRect(cropBox.x + cropBox.w, cropBox.y, cw - cropBox.x - cropBox.w, cropBox.h); // right

        // Crop border
        cropCtx.strokeStyle = '#fff';
        cropCtx.lineWidth   = Math.max(2, cw / 400);
        cropCtx.strokeRect(cropBox.x, cropBox.y, cropBox.w, cropBox.h);

        // Rule-of-thirds grid
        cropCtx.strokeStyle = 'rgba(255,255,255,0.35)';
        cropCtx.lineWidth   = 1;
        for (let i = 1; i < 3; i++) {
            const gx = cropBox.x + cropBox.w * i / 3;
            const gy = cropBox.y + cropBox.h * i / 3;
            cropCtx.beginPath(); cropCtx.moveTo(gx, cropBox.y); cropCtx.lineTo(gx, cropBox.y + cropBox.h); cropCtx.stroke();
            cropCtx.beginPath(); cropCtx.moveTo(cropBox.x, gy); cropCtx.lineTo(cropBox.x + cropBox.w, gy); cropCtx.stroke();
        }

        // Corner handles
        const hs = Math.max(8, Math.round(cw / 60));
        cropCtx.fillStyle = '#fff';
        [
            [cropBox.x,             cropBox.y],
            [cropBox.x + cropBox.w, cropBox.y],
            [cropBox.x,             cropBox.y + cropBox.h],
            [cropBox.x + cropBox.w, cropBox.y + cropBox.h],
        ].forEach(([hx, hy]) => cropCtx.fillRect(hx - hs/2, hy - hs/2, hs, hs));
    }

    function applyCrop() {
        // Draw cropped region onto the main canvas and update preview
        canvas.width  = cropBox.w;
        canvas.height = cropBox.h;
        canvas.getContext('2d').drawImage(
            cropImg,
            cropBox.x, cropBox.y, cropBox.w, cropBox.h,
            0, 0, cropBox.w, cropBox.h
        );
        capturedImg.src = canvas.toDataURL('image/png');
        closeCrop();
    }

    // Scale display pixels → canvas pixels
    function toCanvasCoords(clientX, clientY) {
        const rect  = cropCanvas.getBoundingClientRect();
        const scaleX = cropCanvas.width  / rect.width;
        const scaleY = cropCanvas.height / rect.height;
        return [
            (clientX - rect.left) * scaleX,
            (clientY - rect.top)  * scaleY,
        ];
    }

    function hitTest(cx, cy) {
        const rect  = cropCanvas.getBoundingClientRect();
        const scaleX = cropCanvas.width  / rect.width;
        const H = HANDLE * scaleX;   // handle size in canvas pixels
        const { x, y, w, h } = cropBox;
        // Corners first (priority)
        if (Math.abs(cx - x)     < H && Math.abs(cy - y)     < H) return 'nw';
        if (Math.abs(cx - x - w) < H && Math.abs(cy - y)     < H) return 'ne';
        if (Math.abs(cx - x)     < H && Math.abs(cy - y - h) < H) return 'sw';
        if (Math.abs(cx - x - w) < H && Math.abs(cy - y - h) < H) return 'se';
        // Edges
        if (Math.abs(cy - y)     < H && cx > x && cx < x + w) return 'n';
        if (Math.abs(cy - y - h) < H && cx > x && cx < x + w) return 's';
        if (Math.abs(cx - x)     < H && cy > y && cy < y + h) return 'w';
        if (Math.abs(cx - x - w) < H && cy > y && cy < y + h) return 'e';
        // Inside → move
        if (cx > x && cx < x + w && cy > y && cy < y + h) return 'move';
        return null;
    }

    function clampBox(b) {
        const minSz = 20;
        b.x = Math.max(0, Math.min(b.x, cropCanvas.width  - minSz));
        b.y = Math.max(0, Math.min(b.y, cropCanvas.height - minSz));
        b.w = Math.max(minSz, Math.min(b.w, cropCanvas.width  - b.x));
        b.h = Math.max(minSz, Math.min(b.h, cropCanvas.height - b.y));
        return b;
    }

    function onCropPointerDown(e) {
        e.preventDefault();
        const [cx, cy] = toCanvasCoords(
            e.touches ? e.touches[0].clientX : e.clientX,
            e.touches ? e.touches[0].clientY : e.clientY
        );
        const mode = hitTest(cx, cy);
        if (!mode) return;
        dragState = { mode, startX: cx, startY: cy, origBox: { ...cropBox } };
    }

    function onCropPointerMove(e) {
        e.preventDefault();
        if (!dragState) return;
        const [cx, cy] = toCanvasCoords(
            e.touches ? e.touches[0].clientX : e.clientX,
            e.touches ? e.touches[0].clientY : e.clientY
        );
        const dx = cx - dragState.startX;
        const dy = cy - dragState.startY;
        const o  = dragState.origBox;
        let b = { ...o };

        switch (dragState.mode) {
            case 'move': b.x = o.x + dx; b.y = o.y + dy; break;
            case 'nw':   b.x = o.x + dx; b.y = o.y + dy; b.w = o.w - dx; b.h = o.h - dy; break;
            case 'ne':                    b.y = o.y + dy; b.w = o.w + dx; b.h = o.h - dy; break;
            case 'sw':   b.x = o.x + dx;                  b.w = o.w - dx; b.h = o.h + dy; break;
            case 'se':                                     b.w = o.w + dx; b.h = o.h + dy; break;
            case 'n':                     b.y = o.y + dy;                  b.h = o.h - dy; break;
            case 's':                                                       b.h = o.h + dy; break;
            case 'w':    b.x = o.x + dx;                  b.w = o.w - dx;                  break;
            case 'e':                                      b.w = o.w + dx;                  break;
        }
        cropBox = clampBox(b);
        drawCrop();
    }

    function onCropPointerUp(e) {
        e.preventDefault();
        dragState = null;
    }

    // Mouse events
    cropCanvas.addEventListener('mousedown',  onCropPointerDown);
    cropCanvas.addEventListener('mousemove',  onCropPointerMove);
    cropCanvas.addEventListener('mouseup',    onCropPointerUp);
    cropCanvas.addEventListener('mouseleave', onCropPointerUp);
    // Touch events
    cropCanvas.addEventListener('touchstart', onCropPointerDown, { passive: false });
    cropCanvas.addEventListener('touchmove',  onCropPointerMove, { passive: false });
    cropCanvas.addEventListener('touchend',   onCropPointerUp,   { passive: false });
    // ─── End crop tool ────────────────────────────────────────────────────────

    async function processOcr() {
        btnProcess.disabled = true;
        btnProcess.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

        try {
            const imageData = canvas.toDataURL('image/jpeg', 0.85);
            const sizeKB = Math.round(imageData.length * 0.75 / 1024);

            if (sizeKB > 2500) {
                showResult('warning', 'Ukuran gambar terlalu besar (' + sizeKB + ' KB). Silakan input VIN secara manual.');
                return;
            }

            let response;
            try {
                response = await fetch('{{ route("vendor.scanner.scan") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ image: imageData }),
                });
            } catch (networkErr) {
                const url = '{{ route("vendor.scanner.scan") }}';
                const isHttps = location.protocol === 'https:';
                const urlIsHttp = url.startsWith('http://');
                let hint = '';
                if (isHttps && urlIsHttp) {
                    hint = ' (Mixed content: halaman HTTPS mencoba request ke HTTP — hubungi administrator.)';
                } else {
                    hint = ' Periksa koneksi internet Anda.';
                }
                showResult('danger', 'Gagal menghubungi server: ' + escapeHtml(networkErr.message) + hint);
                return;
            }

            if (response.status === 419) {
                showResult('danger', 'Sesi kedaluwarsa (419). Silakan <a href="" onclick="location.reload()">muat ulang halaman</a> dan coba lagi.');
                return;
            }

            if (response.status === 413) {
                showResult('warning', 'Gambar terlalu besar untuk diproses server (413). Silakan input VIN secara manual.');
                return;
            }

            let data;
            try {
                data = await response.json();
            } catch (parseErr) {
                showResult('danger', 'Respons server tidak valid (HTTP ' + response.status + '). Kemungkinan server error — hubungi administrator.');
                return;
            }

            if (data.success && data.vin) {
                vinInput.value = data.vin;
                vinInput.dispatchEvent(new Event('input'));
                showResult('success', 'VIN berhasil dideteksi: <strong>' + escapeHtml(data.vin) + '</strong>. Periksa dan konfirmasi.');
            } else {
                showResult('warning', escapeHtml(data.error || 'OCR gagal. Silakan input VIN secara manual.'));
            }
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

            let response;
            try {
                response = await fetch('{{ route("vendor.scanner.confirm") }}', {
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
            } catch (networkErr) {
                const url = '{{ route("vendor.scanner.confirm") }}';
                const isHttps = location.protocol === 'https:';
                const urlIsHttp = url.startsWith('http://');
                let hint = '';
                if (isHttps && urlIsHttp) {
                    hint = ' (Mixed content: halaman HTTPS mencoba request ke HTTP — hubungi administrator.)';
                } else {
                    hint = ' Periksa koneksi internet Anda.';
                }
                showResult('danger', 'Gagal menghubungi server: ' + escapeHtml(networkErr.message) + hint);
                btnConfirm.disabled = false;
                return;
            }

            if (response.status === 419) {
                showResult('danger', 'Sesi kedaluwarsa (419). Silakan <a href="" onclick="location.reload()">muat ulang halaman</a> dan coba lagi.');
                btnConfirm.disabled = false;
                return;
            }

            let data;
            try {
                data = await response.json();
            } catch (parseErr) {
                showResult('danger', 'Respons server tidak valid (HTTP ' + response.status + '). Kemungkinan server error — hubungi administrator.');
                btnConfirm.disabled = false;
                return;
            }

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
