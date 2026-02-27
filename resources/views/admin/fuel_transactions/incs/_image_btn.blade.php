@if($row->meter_image)
<button type="button" 
        class="btn btn-sm btn-outline-info view-meter-image" 
        data-image-url="{{ route('admin.fuelTransactions.viewImage', $row->id) }}"
        title="@lang('fuel_transactions.View Image')">
    <i class="fas fa-image"></i>
</button>
@else
<span class="text-muted">---</span>
@endif
