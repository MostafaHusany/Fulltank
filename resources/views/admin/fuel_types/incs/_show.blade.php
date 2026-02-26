
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
                <tr>
                    <td>@lang('fuel_types.Name')</td>
                    <td id="show-name"></td>
                </tr>
                <tr>
                    <td>@lang('fuel_types.Price')</td>
                    <td id="show-price_per_liter"></td>
                </tr>
                <tr>
                    <td>@lang('fuel_types.Description')</td>
                    <td id="show-description"></td>
                </tr>
                <tr>
                    <td>@lang('layouts.Active')</td>
                    <td id="show-is_active"></td>
                </tr>
                <tr>
                    <td>@lang('fuel_types.Last Updated')</td>
                    <td id="show-updated_at"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
