@if($row->receipt_image)
    <button type="button" class="btn btn-sm btn-outline-info view-receipt-btn"
        data-image-url="{{ route('admin.settlements.viewReceipt', ['id' => $row->id]) }}">
        <i class="fas fa-receipt"></i>
    </button>
@else
    <span class="text-muted">---</span>
@endif
