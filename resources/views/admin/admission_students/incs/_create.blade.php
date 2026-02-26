
<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('admission_students.Create_Title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <div>
        

        <div class="my-3 row">
            <label for="dreafted_data" class="col-sm-3 col-form-label">@lang('drafts.Drafted Data')</label>
            <div class="col-sm-9">
                <select class="form-control" id="dreafted_data"></select>
            </div>
        </div><!-- /.my-3 -->

        <div class="my-3 row">
            <label for="semester_id" class="col-sm-3 col-form-label">@lang('semesters.Title') <span class="text-danger float-end">*</span></label>
            
            <div class="col-sm-9">
                <select id="semester_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="semester_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->
        
        <div class="my-2 row">
            <label for="admission_id" class="col-sm-3 col-form-label">@lang('student_payments.admission_id') <span class="text-danger float-end">*</span></label>
            
            <div class="col-md-9 mb-2">
                <select type="text" class="form-control custome-en-field" id="admission_id" placeholder="@lang('student_payments.admission_id')" ></select>

                <div style="padding: 5px 7px; display: none" id="admission_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-md-9 -->
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="first_name" class="col-sm-3 col-form-label">@lang('admission_students.first_name') <span class="text-danger float-end">*</span></label>
            
            <div class="col-sm-9 row">
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="first_name" placeholder="@lang('admission_students.first_name')">
                    <div style="padding: 5px 7px; display: none" id="first_nameErr" class="err-msg mt-2 alert alert-danger">
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="second_name" placeholder="@lang('admission_students.second_name')">
                    <div style="padding: 5px 7px; display: none" id="second_nameErr" class="err-msg mt-2 alert alert-danger">
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="third_name" placeholder="@lang('admission_students.third_name')">
                    <div style="padding: 5px 7px; display: none" id="third_nameErr" class="err-msg mt-2 alert alert-danger">
                    </div>
                </div>
            </div><!-- /.row -->
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="birth_date" class="col-sm-3 col-form-label">@lang('admission_students.birth_date') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="date" class="form-control" id="birth_date" placeholder="@lang('admission_students.birth_date')">
                <div style="padding: 5px 7px; display: none" id="birth_dateErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="gender" class="col-sm-3 col-form-label">@lang('admission_students.Gender') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" id="gender" placeholder="@lang('admission_students.Gender')">
                    <option value="">@lang('admission_students.Select_Gender')</option>
                    <option value="male">@lang('admission_students.male')</option>
                    <option value="female">@lang('admission_students.female')</option>
                </select>
                <div style="padding: 5px 7px; display: none" id="genderErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-3 row">
            <label class="col-sm-3 col-form-label">@lang('school_grades.Grade') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-5">
                <select id="grade_id" data-target="#level_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="grade_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
            <div class="col-sm-4">
                <select id="level_id" class="form-control" disabled="disabled"></select>
                <div style="padding: 5px 7px; display: none" id="level_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-3 -->

        <div class="my-2 row">
            <label for="bus" class="col-sm-3 col-form-label">@lang('admission_students.bus') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" id="bus" placeholder="@lang('admission_students.bus')">
                    <option value="">@lang('admission_students.select_bus_status')</option>
                    <option value="no_bus">@lang('admission_students.no_bus')</option>
                    <option value="two_direction">@lang('admission_students.two_direction')</option>
                    <option value="pickup_trip">@lang('admission_students.pickup_trip')</option>
                    <option value="drop_trip">@lang('admission_students.drop_trip')</option>
                </select>
                <div style="padding: 5px 7px; display: none" id="busErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->

        <button class="create-object btn btn-primary float-end">@lang('admission_students.Create_Title')</button>
        
        <button class="create-draft btn btn-secondary float-end mx-2">@lang('drafts.Save Draft')</button>
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