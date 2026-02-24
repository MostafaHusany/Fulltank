
<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('roles.Show Role')</h5>
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
            <tr>
                <td>@lang('roles.Name')</td>
                <td id="show-name"></td>
            </tr>
            <tr>
                <td>@lang('roles.Description')</td>
                <td id="show-description"></td>
            </tr>
            <tr>
                <td>@lang('roles.Permissions')</td>
                <td id="show-permissions"></td>
            </tr>
        </table>
    </div>

    <h3>@lang('roles.Assigned_Users')</h3>
    <div style="height: 300px; overflow-y: scroll">
        <table class="table">
            <tr>
                <td>#</td>
                <td>@lang('roles.Name')</td>
                <td>@lang('roles.Category')</td>
                <td>@lang('roles.Email')</td>
                <td>@lang('roles.Phone')</td>
            </tr>
            <tbody id="show-users">

            </tbody>
        </table>
    </div>
</div>