@php
    $statusConfig = [
        'pending'   => ['class' => 'warning', 'icon' => 'fa-clock'],
        'completed' => ['class' => 'success', 'icon' => 'fa-check-circle'],
        'refunded'  => ['class' => 'info',    'icon' => 'fa-undo'],
        'cancelled' => ['class' => 'secondary', 'icon' => 'fa-times-circle'],
    ];

    $config = $statusConfig[$row->status] ?? ['class' => 'secondary', 'icon' => 'fa-question'];
    $statusText = __('fuel_transactions.status_' . $row->status);
@endphp

<span class="badge bg-{{ $config['class'] }}">
    <i class="fas {{ $config['icon'] }} me-1"></i>
    {{ $statusText }}
</span>

@if($row->type === 'manual_admin')
<br>
<small class="text-muted">
    <i class="fas fa-user-shield"></i> @lang('fuel_transactions.manual')
</small>
@endif
