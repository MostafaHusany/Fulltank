
<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('districts.Show Title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#showObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <div class="my-1">
        
        <div class="my-3 row">
            <label for="show-name" class="col-sm-2 col-form-label">@lang('districts.Governorate')</label>
            <div class="col-sm-10 row" style="direction: rtl">
                <div class="col-6">
                    <input type="text" disabled="disabled" class="form-control custome-ar-field" id="show-ar_name" placeholder="أسم المحافظة بالعربية">
                </div><!-- /.col-6 -->
                <div class="col-6">
                    <input type="text" disabled="disabled" class="form-control custome-en-field" id="show-en_name" placeholder="Governorate name in english">
                </div><!-- /.col-6 -->
            </div>
        </div><!-- /.my-3 -->
        
        <div class="my-3 row">
            <label class="col-sm-2">
                @lang('districts.Centers')
            </label>
            <div class="col-sm-10" style="height: 300px; overflow-y: scroll">
                <table class="table table-sm">
                    <tbody id="show-childrenTableContainer"></tbody>
                </table>
            </div><!-- /.col-sm-10 -->
        </div><!-- /.my-3 -->

    </div><!-- /.my-1 -->
</div><!-- /.card -->