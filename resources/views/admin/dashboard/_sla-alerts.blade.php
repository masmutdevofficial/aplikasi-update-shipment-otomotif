@if ($dashboardSlaAlerts['warning'] !== [])
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Deadline Shipment Mendekat</h3>
        </div>
        <div class="card-body">
            <ul class="mb-0 pl-3">
                @foreach ($dashboardSlaAlerts['warning'] as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if ($dashboardSlaAlerts['danger'] !== [])
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-times-circle"></i> Shipment Melewati SLA</h3>
        </div>
        <div class="card-body">
            <ul class="mb-0 pl-3">
                @foreach ($dashboardSlaAlerts['danger'] as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
