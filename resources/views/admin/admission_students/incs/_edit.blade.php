<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('admission_students.Update_Title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <div>
        <input type="hidden" id="edit-id">

        <div class="my-3 row">
            <label for="edit-semester_id" class="col-sm-3 col-form-label">@lang('semesters.Title') <span class="text-danger float-end">*</span></label>
            
            <div class="col-sm-9">
                <select id="edit-semester_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="edit-semester_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <div class="my-2 row">
            <label for="edit-admission_id" class="col-sm-3 col-form-label">@lang('student_payments.admission_id') <span class="text-danger float-end">*</span></label>
            
            <div class="col-md-9 mb-2">
                <select type="text" class="form-control custome-en-field" id="edit-admission_id" placeholder="@lang('student_payments.admission_id')" ></select>

                <div style="padding: 5px 7px; display: none" id="admission_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-md-9 -->
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="edit-first_name" class="col-sm-3 col-form-label">@lang('admission_students.first_name') <span class="text-danger float-end">*</span></label>
            
            <div class="col-sm-9 row">
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="edit-first_name" placeholder="@lang('admission_students.first_name')">
                    <div style="padding: 5px 7px; display: none" id="edit-first_nameErr" class="err-msg mt-2 alert alert-danger">
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="edit-second_name" placeholder="@lang('admission_students.second_name')">
                    <div style="padding: 5px 7px; display: none" id="edit-second_nameErr" class="err-msg mt-2 alert alert-danger">
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="edit-third_name" placeholder="@lang('admission_students.third_name')">
                    <div style="padding: 5px 7px; display: none" id="edit-third_nameErr" class="err-msg mt-2 alert alert-danger">
                    </div>
                </div>
            </div><!-- /.row -->
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="edit-birth_date" class="col-sm-3 col-form-label">@lang('admission_students.birth_date') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="date" class="form-control" id="edit-birth_date" placeholder="@lang('admission_students.birth_date')">
                <div style="padding: 5px 7px; display: none" id="edit-birth_dateErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="edit-gender" class="col-sm-3 col-form-label">@lang('admission_students.Gender') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" id="edit-gender" placeholder="@lang('admission_students.Gender')">
                    <option value="">@lang('admission_students.Select_Gender')</option>
                    <option value="male">@lang('admission_students.male')</option>
                    <option value="female">@lang('admission_students.female')</option>
                </select>
                <div style="padding: 5px 7px; display: none" id="edit-genderErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-3 row">
            <label class="col-sm-3 col-form-label">@lang('school_grades.Grade') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-5">
                <select id="edit-grade_id" data-target="#edit-level_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="edit-grade_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
            <div class="col-sm-4">
                <select id="edit-level_id" class="form-control" disabled="disabled"></select>
                <div style="padding: 5px 7px; display: none" id="edit-level_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-3 -->

        <div class="my-2 row">
            <label for="edit-bus" class="col-sm-3 col-form-label">@lang('admission_students.bus') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" id="edit-bus" placeholder="@lang('admission_students.bus')">
                    <option value="">@lang('admission_students.select_bus_status')</option>
                    <option value="no_bus">@lang('admission_students.no_bus')</option>
                    <option value="two_direction">@lang('admission_students.two_direction')</option>
                    <option value="pickup_trip">@lang('admission_students.pickup_trip')</option>
                    <option value="drop_trip">@lang('admission_students.drop_trip')</option>
                </select>
                <div style="padding: 5px 7px; display: none" id="edit-busErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->


        <button class="update-object btn btn-warning float-end">@lang('admission_students.Update_Title')</button>
    </div>
</div>

@push('custome-js-2')
<script>
$(document).ready(function () {

    const init = (async () => {
        
    })();

});
</script>
@endpush