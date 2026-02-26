<div class="text-center">
    @if(isset($row_object->current_payemt))
        @if ($row_object->current_payemt->status == 'paied')
        <span class="badge bg-success">paied</span>
        @else 
        <span class="badge bg-warning">wating</span>
        @endif
    @else 
        <span class="badge bg-danger">no payment record</span>
    @endif

</div>