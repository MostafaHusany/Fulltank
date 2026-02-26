@php
    $map = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
    $class = $map[$row->status] ?? 'secondary';
@endphp
<span class="badge bg-{{ $class }}">@lang("deposit_requests.{$row->status}")</span>
