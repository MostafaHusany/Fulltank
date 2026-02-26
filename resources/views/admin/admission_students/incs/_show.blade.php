
<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('admission_students.Show_Title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#showObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <div>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="show-students-tab" data-bs-toggle="tab" data-bs-target="#show-students" type="button" role="tab" aria-controls="show-students" aria-selected="false">@lang('admission_students.Student_Main_Info')</button>
            </li><!-- /.nav-item -->

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="show-main-tab" data-bs-toggle="tab" data-bs-target="#show-main" type="button" role="tab" aria-controls="show-main" aria-selected="true">@lang('admissions.Admission')</button>
            </li><!-- /.nav-item -->

        </ul><!-- /.nav -->

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="show-students" role="tabpanel" aria-labelledby="show-students-tab">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>@lang('admission_students.Name')</td>
                            <td id="show-name"></td>
                        </tr>

                        <tr>
                            <td>@lang('admission_students.birth_date')</td>
                            <td id="show-birth_date"></td>
                        </tr>
                        
                        <tr>
                            <td>@lang('school_grades.Grade')</td>
                            <td id="show-grade"></td>
                        </tr>

                        <tr>
                            <td>@lang('school_grades.Level')</td>
                            <td id="show-level"></td>
                        </tr>
                        
                        <tr>
                            <td>@lang('admission_students.status')</td>
                            <td id="show-status"></td>
                        </tr>
                    </tbody>
                </table>
            </div><!-- /.tab-pane -->

            <div class="tab-pane fade" id="show-main" role="tabpanel" aria-labelledby="show-main-tab">

                <table class="table">
                                    
                    <tr>
                        <td>@lang('semesters.Title')</td>
                        <td id="show-semester"></td>
                    </tr>

                    <tr>
                        <td>@lang('admissions.family_status')</td>
                        <td id="show-family_status"></td>
                    </tr>

                    <tr>
                        <td colspan="2"><b>@lang('admissions.Guardian_Info')</b></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_1_name')</td>
                        <td id="show-responsible_1_name"></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_1_id_num')</td>
                        <td id="show-responsible_1_id_num"></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_1_phone_num')</td>
                        <td id="show-responsible_1_phone_num"></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_1_email')</td>
                        <td id="show-responsible_1_email"></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_1_classification')</td>
                        <td id="show-responsible_1_classification"></td>
                    </tr>
                    
                    <tr>
                        <td colspan="2"></td>
                    </tr>

                    <tr>
                        <td>@lang('admissions.responsible_2_name')</td>
                        <td id="show-responsible_2_name"></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_2_id_num')</td>
                        <td id="show-responsible_2_id_num"></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_2_phone_num')</td>
                        <td id="show-responsible_2_phone_num"></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_2_email')</td>
                        <td id="show-responsible_2_email"></td>
                    </tr>
                    
                    <tr>
                        <td>@lang('admissions.responsible_2_classification')</td>
                        <td id="show-responsible_2_classification"></td>
                    </tr>
                    
                </table>
                
            </div><!-- /.tab-pane -->

        </div>

    </div>
</div>