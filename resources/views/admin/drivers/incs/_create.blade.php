
<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row g-2 align-items-center">
        <div class="col-8 col-md-6">
            <h5 class="mb-0">@lang('drivers.Create Driver')</h5>
        </div>
        <div class="col-4 col-md-6 text-end">
            <button type="button" class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <hr class="my-3">

    <form action="/" id="objectForm" enctype="multipart/form-data">
        @include('admin.drivers.incs._form_fields', ['prefix' => ''])
        <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
            <button type="button" class="create-object btn btn-primary">@lang('drivers.Create Driver')</button>
        </div>
    </form>
</div>

