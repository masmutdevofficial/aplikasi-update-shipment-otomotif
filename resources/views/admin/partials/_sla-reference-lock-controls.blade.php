@once
    @push('styles')
.sla-reference-table .sla-lock-column {
    width: 54px;
    min-width: 54px;
    text-align: center;
    vertical-align: middle;
}
.sla-reference-table .sla-reference-row.is-unlocked {
    background: #f0fdf4 !important;
}
.sla-reference-table .sla-reference-input[readonly] {
    color: #495057;
    cursor: not-allowed;
    background: #e9ecef;
    box-shadow: none;
}
.sla-reference-table .sla-row-lock-toggle {
    width: 34px;
}
    @endpush

    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.sla-row-lock-toggle');

        if (!button) return;

        const row = button.closest('.sla-reference-row');
        const shouldLock = row.dataset.locked === 'false';

        row.dataset.locked = shouldLock ? 'true' : 'false';
        row.classList.toggle('is-unlocked', !shouldLock);
        row.querySelectorAll('.sla-reference-input').forEach((input) => {
            input.readOnly = shouldLock;
        });

        button.classList.toggle('btn-warning', shouldLock);
        button.classList.toggle('btn-success', !shouldLock);
        button.setAttribute('aria-pressed', shouldLock ? 'false' : 'true');
        button.setAttribute('aria-label', shouldLock ? 'Buka kunci baris' : 'Kunci baris');
        button.title = shouldLock ? 'Buka kunci untuk mengubah data' : 'Kunci kembali baris ini';
        button.querySelector('i').className = shouldLock ? 'fas fa-lock' : 'fas fa-unlock';
    });
});
</script>
    @endpush
@endonce
