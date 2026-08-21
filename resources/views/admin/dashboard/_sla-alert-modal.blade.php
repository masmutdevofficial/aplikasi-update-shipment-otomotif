<div class="dashboard-stage-alert-modal" id="dashboardStageAlertModal" aria-hidden="true">
    <button type="button" class="dashboard-stage-alert-backdrop" data-alert-modal-close aria-label="Tutup modal"></button>
    <div class="dashboard-stage-alert-dialog" role="dialog" aria-modal="true" aria-labelledby="dashboardStageAlertTitle">
        <div class="dashboard-stage-alert-header">
            <div>
                <span class="dashboard-stage-alert-eyebrow"><i class="fas fa-bell"></i> Detail Alert SLA</span>
                <h3 id="dashboardStageAlertTitle">Alert Shipment</h3>
                <p id="dashboardStageAlertSummary" class="mb-0"></p>
            </div>
            <button type="button" class="dashboard-stage-alert-close" data-alert-modal-close aria-label="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="dashboard-stage-alert-content" id="dashboardStageAlertContent"></div>
        <div class="dashboard-stage-alert-footer">
            <button type="button" class="btn btn-default" data-alert-modal-close>Tutup</button>
        </div>
    </div>
</div>

@once
    @push('styles')
.dashboard-alert-trigger {
    cursor: pointer;
    outline: none;
}

.dashboard-alert-trigger:focus-visible {
    box-shadow: 0 0 0 4px rgba(37, 99, 235, .3), 0 10px 20px rgba(15, 23, 42, .2);
}

.dashboard-metric-alert-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 7px;
    padding: 4px 7px;
    color: #7f1d1d;
    font-size: 10px;
    font-weight: 700;
    border-radius: 999px;
    background: #fef2f2;
    box-shadow: 0 2px 5px rgba(15, 23, 42, .12);
}

.dashboard-stage-alert-modal {
    position: fixed;
    z-index: 2000;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.dashboard-stage-alert-modal.is-open {
    display: flex;
}

.dashboard-stage-alert-backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    background: rgba(15, 23, 42, .62);
    backdrop-filter: blur(2px);
}

.dashboard-stage-alert-dialog {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    width: min(780px, 100%);
    max-height: min(760px, calc(100vh - 40px));
    overflow: hidden;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .35);
}

.dashboard-stage-alert-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 22px;
    color: #fff;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
}

.dashboard-stage-alert-eyebrow {
    display: block;
    margin-bottom: 5px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    opacity: .85;
}

.dashboard-stage-alert-header h3 {
    margin: 0 0 4px;
    font-size: 21px;
}

.dashboard-stage-alert-header p {
    font-size: 12px;
    opacity: .85;
}

.dashboard-stage-alert-close {
    display: inline-flex;
    flex: 0 0 34px;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    color: #fff;
    border: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, .16);
    cursor: pointer;
}

.dashboard-stage-alert-content {
    display: grid;
    gap: 9px;
    padding: 16px 18px;
    overflow-y: auto;
    background: #f8fafc;
}

.dashboard-stage-alert-item {
    display: grid;
    grid-template-columns: 34px minmax(0, 1fr) auto;
    align-items: center;
    gap: 11px;
    padding: 11px 13px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #fff;
}

.dashboard-stage-alert-item-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    border-radius: 50%;
    background: #f1f5f9;
}

.dashboard-stage-alert-item-message {
    color: #334155;
    font-size: 13px;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.dashboard-stage-alert-item-status {
    padding: 5px 8px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
    border-radius: 999px;
}

.dashboard-stage-alert-item-status.warning {
    color: #854d0e;
    background: #fef3c7;
}

.dashboard-stage-alert-item-status.danger {
    color: #991b1b;
    background: #fee2e2;
}

.dashboard-stage-alert-footer {
    padding: 12px 18px;
    text-align: right;
    border-top: 1px solid #e2e8f0;
    background: #fff;
}

@media (max-width: 576px) {
    .dashboard-stage-alert-modal {
        align-items: flex-end;
        padding: 0;
    }

    .dashboard-stage-alert-dialog {
        max-height: 90vh;
        border-radius: 14px 14px 0 0;
    }

    .dashboard-stage-alert-item {
        grid-template-columns: 30px minmax(0, 1fr);
    }

    .dashboard-stage-alert-item-status {
        grid-column: 2;
        justify-self: start;
    }
}
    @endpush

    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('dashboardStageAlertModal');
    if (!modal) return;

    const stageAlerts = {{ Illuminate\Support\Js::from($dashboardSlaAlerts['stages'] ?? []) }};
    const title = document.getElementById('dashboardStageAlertTitle');
    const summary = document.getElementById('dashboardStageAlertSummary');
    const content = document.getElementById('dashboardStageAlertContent');
    let activeTrigger = null;

    const closeModal = function () {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (activeTrigger) activeTrigger.focus();
    };

    const appendAlert = function (message, severity, number) {
        const item = document.createElement('div');
        item.className = 'dashboard-stage-alert-item';

        const itemNumber = document.createElement('span');
        itemNumber.className = 'dashboard-stage-alert-item-number';
        itemNumber.textContent = number;

        const itemMessage = document.createElement('span');
        itemMessage.className = 'dashboard-stage-alert-item-message';
        itemMessage.textContent = message;

        const itemStatus = document.createElement('span');
        itemStatus.className = 'dashboard-stage-alert-item-status ' + severity;
        itemStatus.textContent = severity === 'danger' ? 'Melewati SLA' : 'Deadline Mendekat';

        item.append(itemNumber, itemMessage, itemStatus);
        content.appendChild(item);
    };

    const openModal = function (trigger) {
        const alerts = stageAlerts[trigger.dataset.alertStage] || { warning: [], danger: [] };
        const total = alerts.warning.length + alerts.danger.length;
        if (total === 0) return;

        activeTrigger = trigger;
        title.textContent = trigger.dataset.alertLabel;
        summary.textContent = total + ' alert shipment memerlukan perhatian';
        content.replaceChildren();

        let number = 1;
        alerts.danger.forEach(function (message) { appendAlert(message, 'danger', number++); });
        alerts.warning.forEach(function (message) { appendAlert(message, 'warning', number++); });

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        modal.querySelector('.dashboard-stage-alert-close').focus();
    };

    document.querySelectorAll('.dashboard-alert-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () { openModal(trigger); });
        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openModal(trigger);
            }
        });
    });

    modal.querySelectorAll('[data-alert-modal-close]').forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });
});
</script>
    @endpush
@endonce
