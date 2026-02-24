<div style="display: none" id="editObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('users.Update User')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="close-btn toggle-btn btn btn-default btn-sm" data-current-card="#editObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <div id="objectForm">
        <div class="my-3 row">
            <label for="edit-name" class="col-sm-2 col-form-label">@lang('users.Name')</label>
            <div class="col-sm-10">
                <input type="text" value="{{ $target_user->name }}" class="form-control" id="edit-name" placeholder="@lang('users.Name')" disabled="disabled">
                <div style="padding: 5px 7px; display: none" id="edit-nameErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-3 -->
        
        <div class="my-3 row">
            <label for="edit-email" class="col-sm-2 col-form-label">@lang('users.Email')</label>
            <div class="col-sm-10">
                <input type="email" value="{{ $target_user->email }}" class="form-control" id="edit-email" placeholder="@lang('users.Email')">
                <div style="padding: 5px 7px; display: none" id="edit-emailErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-3 -->
        
        <div class="my-3 row">
            <label for="edit-phone" class="col-sm-2 col-form-label">@lang('users.Phone')</label>
            <div class="col-sm-10">
                <input type="phone" value="{{ $target_user->phone }}" class="form-control" id="edit-phone" placeholder="@lang('users.Phone')">
                <div style="padding: 5px 7px; display: none" id="edit-phoneErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-3 -->
        
        <div class="my-3 row">
            <label for="edit-password_old" class="col-sm-2 col-form-label">@lang('users.Old Password')</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="edit-password_old" placeholder="@lang('users.Old Password')">
            </div>
        </div><!-- /.my-3 -->
        
        <div class="my-3 row">
            <label for="edit-password" class="col-sm-2 col-form-label">@lang('users.New Password')</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="edit-password" placeholder="@lang('users.New Password')">
            </div>
        </div><!-- /.my-3 -->
        
        <div class="my-3 row">
            <label for="edit-password_confirmation" class="col-sm-2 col-form-label">@lang('users.Confirm Password')</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="edit-password_confirmation" placeholder="@lang('users.Confirm Password')">
            </div>
        </div><!-- /.my-3 -->

        <button class="update-object btn btn-warning float-end mt-3">@lang('users.Update Account')</button>
    </div>
</div>

<script>
$(document).ready(function () {
    const fields = ['email', 'phone', 'password_old', 'password', 'password_confirmation']
    $('.update-object').click(function () {
        
        let data = getData();
        
        if (validateData(data)) {
            $('#loddingSpinner').show(500);
            
            data._token = "{{ csrf_token() }}";
            axios.post('{{ url("admin/my-profile") }}', data)
            .then(res => {
                const { data } = res;
                console.log(data.success);

                if (data.success) {
                    $('.close-btn').trigger('click');
                    $('#successAlert').text('You updated your profile successfuly !').slideDown(500);
                    setTimeout(() => {
                        $('#successAlert').text('').slideUp(500);
                    }, 3000);
                } else {
                    const { msg } = data;
                    const keys    = Object.keys(msg);
                    let ulElm     = document.createElement('ul');
                    let errMsgStr = '';

                    keys.forEach(key => {
                        msg[key].forEach(err => {
                            errMsgStr += `<li>${key} : ${err}</li>`;
                        });
                    });

                    ulElm.innerHTML = errMsgStr;
                    $('#dangerAlert').html('').append(ulElm).slideDown(500);
                }

                $('#loddingSpinner').hide(500);
            })
        }
    });

    function getData () {
        let data = {};

        fields.forEach(key => data[key] = $(`#edit-${key}`).val());

        return data;
    }

    function validateData (data) {
        let is_valied = true;

        fields.forEach(key => {
            if (!Boolean(data[key])) {
                is_valied = false;
                $(`#edit-${key}`).css('border', '1px solid red');
            } else {
                $(`#edit-${key}`).css('border', '');
            }
        });
        
        return is_valied;
    }
    
});
</script>