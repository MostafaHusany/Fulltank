<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('layouts.details')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#showObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>
    <div>
        <table class="table">
            <tbody>
                <tr><td>@lang('stations.Name')</td><td id="show-name"></td></tr>
                <tr><td>@lang('stations.Governorate')</td><td id="show-governorate"></td></tr>
                <tr><td>@lang('stations.District')</td><td id="show-district"></td></tr>
                <tr><td>@lang('stations.Address')</td><td id="show-address"></td></tr>
                <tr><td>@lang('stations.Manager Name')</td><td id="show-manager_name"></td></tr>
                <tr><td>@lang('stations.Phone 1')</td><td id="show-phone_1"></td></tr>
                <tr><td>@lang('stations.Phone 2')</td><td id="show-phone_2"></td></tr>
                <tr><td>@lang('stations.Fuel Types')</td><td id="show-fuel_types"></td></tr>
            </tbody>
        </table>
    </div>
</div>
