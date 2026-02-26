<!-- Modal -->
<div class="modal fade" id="studentAccountModal" tabindex="-1" aria-labelledby="studentAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="studentAccountModalLabel">@lang('admission_students.Update_Account')</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                
                <div class="my-3 row">
                    <label for="auth-name" class="col-sm-3 col-form-label">@lang('admission_students.name') <span class="text-danger float-end">*</span></label>
                    
                    <div class="col-sm-9">
                        <input disabled="disabled" type="text" id="auth-name" class="form-control">
                        <div style="padding: 5px 7px; display: none" id="auth-nameErr" class="err-msg mt-2 alert alert-danger">
                        </div>
                    </div><!-- /.col-sm-9 -->
                </div><!-- /.my-3 -->

                <div class="my-3 row">
                    <label for="auth-email" class="col-sm-3 col-form-label">@lang('admission_students.email') <span class="text-danger float-end">*</span></label>
                    
                    <div class="col-sm-9">
                        <input type="email" id="auth-email" class="form-control">
                        <div style="padding: 5px 7px; display: none" id="auth-emailErr" class="err-msg mt-2 alert alert-danger">
                        </div>
                    </div><!-- /.col-sm-9 -->
                </div><!-- /.my-3 -->

                <div class="my-3 row">
                    <label for="auth-password" class="col-sm-3 col-form-label">@lang('admission_students.password') <span class="text-danger float-end">*</span></label>
                    
                    <div class="col-sm-9">
                        <input id="auth-password" class="form-control">
                        <div style="padding: 5px 7px; display: none" id="auth-passwordErr" class="err-msg mt-2 alert alert-danger">
                        </div>

                        <div class="alert alert-warning my-3 text-sm px-2 py-1">
                            @lang('admission_students.password_comment')
                        </div>
                    </div><!-- /.col-sm-9 -->

                </div><!-- /.my-3 -->

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('admission_students.Close')</button>
                <button type="button" class="btn btn-primary" id="auth-update-user">@lang('admission_students.Update')</button>
            </div>
        </div>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@push('custome-js')
<script>
$(document).ready(function () {

    const Store = (() => {
        const meta = {
            student: null,
        };

        const setters = {
            fetch: async (student_id) => {
                try {
                    let res = await axios.get(`{{ route('admin.admissionStudents.index') }}/${student_id}`);

                    let { data, success, msg } = res.data;

                    if (!success) throw msg;

                    meta.student = {...data};

                    return true;
                } catch (err) {
                    failerToast(err);
                }
            },

            update: async (formData) => {
                if (!meta.student) return -1;
                
                try {
                    let res = await axios.post(`{{ route('admin.admissionStudents.index') }}/${meta.student.id}`, formData);

                    let { data, success, msg } = res.data;

                    if (!success) throw msg;

                    meta.student = {...data};
                    
                    successToast(msg);

                    return true;
                } catch (err) {
                    typeof(err) == String 
                    ? failerToast(err)
                    : Object.keys(err).forEach(key => failerToast(err[key]));
                }

                return false;
            }, 
        };

        const getters = {
            student: () => ({...meta.student})
        };

        return {
            setters,
            getters,
        }
    })();

    const View = (() => {
        const meta = {
            fields: [
                'email', 'password'
            ],

            myModal: new bootstrap.Modal(document.getElementById('studentAccountModal'))
        };

        const renders = {
            toggleForm: () => {
                meta.myModal.toggle();
            },

            form: (student) => {
                $('#auth-password').val('');
                $('#auth-name').val(student.name);
                $('#auth-email').val(student.user.email);
            },
        };

        const crawler = {
            formData: () => {
                let is_valied = true;
                let formData  = new FormData;

                formData.append('_method', 'PUT');
                formData.append('_token',  '{{ csrf_token() }}');
                formData.append('update_user',  true);

                meta.fields.forEach(field => {
                    let tmp = $(`#auth-${field}`).val();
                    
                    if (tmp) {
                        formData.append(field, tmp);
                        $(`#auth-${field}`).css('border', '');
                    } else if (field == 'email') {
                        is_valied = false;
                        $(`#auth-email`).css('border', '1px solid red');
                    }
                    
                });

                return is_valied 
                    ? formData
                    : is_valied;
            }
        };

        return { 
            renders,
            crawler
        }
    })();

    const init = (() => {
        const { renders, crawler } = View;
        const { setters, getters } = Store;

        $('#dataTable').on('click', '.edit-student-account', async function () {
            let target_id = $(this).data('target');

            if (!target_id) return -1;

            toggleBtn(this);
            
            let is_success = await setters.fetch(target_id);
            
            toggleBtn(this, false);
            
            renders.form(getters.student());
            renders.toggleForm();
        });

        $('#auth-update-user').on('click', async function () {
            let formData = crawler.formData();
            console.log('formData, update: ', formData);
            
            if (!formData) return -1;
            
            toggleBtn(this);

            let is_success = await setters.update(formData);

            toggleBtn(this, false);
            
            if (!is_success) return -1;

            renders.toggleForm();
        });

    })();
    
});
</script>
@endpush