
<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('users.Show User')</h5>
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
                <td>@lang('users.Name')</td>
                <td id="show-name"></td>
            </tr>
            <tr>
                <td>@lang('users.Email')</td>
                <td id="show-email"></td>
            </tr>
            <tr>
                <td>@lang('users.Phone')</td>
                <td id="show-phone"></td>
            </tr>
            <tr>
                <td>@lang('users.Category')</td>
                <td id="show-category"></td>
            </tr>
            
            <tr>
                <td>@lang('users.Role')</td>
                <td id="show-role"></td>
            </tr>
            <tr>
                <td>@lang('users.Permissions')</td>
                <td id="show-permissions"></td>
            </tr>
        
        </table>
    </div>
</div>