<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('clients.Show Client')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#showObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    <hr/>
    <div>
        <table class="table">
            <tr>
                <td>@lang('clients.Name')</td>
                <td id="show-name">---</td>
            </tr>
            <tr>
                <td>@lang('clients.Company Name')</td>
                <td id="show-company_name">---</td>
            </tr>
            <tr>
                <td>@lang('clients.Client Type')</td>
                <td id="show-client_category_name">---</td>
            </tr>
            <tr>
                <td>@lang('clients.Email')</td>
                <td id="show-email">---</td>
            </tr>
            <tr>
                <td>@lang('clients.Phone')</td>
                <td id="show-phone">---</td>
            </tr>
        </table>
    </div>
</div>
