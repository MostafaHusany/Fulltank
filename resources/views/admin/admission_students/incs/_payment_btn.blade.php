<div class="text-center">
    <a href="{{ route('admin.studentPayments.index') }}?reference_number={{$row_object->admission->reference_number}}" class="btn btn-info btn-sm">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>{{ $payments_count }}</span>
    </a>
</div>