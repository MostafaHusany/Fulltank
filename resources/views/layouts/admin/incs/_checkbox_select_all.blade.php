<div class="text-center">
    <input type="checkbox" id="checkbox-select-all">
</div>



@push('custome-js-2')
<script>
$('document').ready(function () {
    var call_num = 0

    $('#checkbox-select-all').on('click', function () {
        let is_checked = $(this).prop('checked');

        $('.record-selector').prop('checked', is_checked)
    });
});
</script>
@endpush