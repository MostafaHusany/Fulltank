
<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row mb-2">
    
    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-semesters">@lang('semesters.Title')</label>
            <select class="form-control" id="s-semesters" multiple="multiple"></select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->

    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-reference_number">@lang('admissions.reference_number')</label>
            <input type="text" class="form-control" id="s-reference_number">
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->

    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-status">@lang('admissions.status')</label>
            <select class="form-control" id="s-status">
                <option value="">@lang('layouts.all')</option>
                <option value="wating">@lang('admissions.wating')</option>
                <option value="accepted">@lang('admissions.accepted')</option>
                <option value="rejected">@lang('admissions.rejected')</option>
            </select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->

    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-name">@lang('admission_students.Name')</label>
            <input class="form-control" id="s-name" />
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->

    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-guardian_name">@lang('admissions.Guardian')</label>
            <input type="text" class="form-control" id="s-guardian_name">
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->

    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-guardian_phone">@lang('admissions.Guardian_Phone')</label>
            <input type="text" class="form-control" id="s-guardian_phone">
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->


    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-grades">@lang('school_grades.Grades')</label>
            <select class="form-control" id="s-grades" data-target="#s-levels" multiple="multiple"></select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->
    
    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-levels">@lang('school_grades.Levels')</label>
            <select class="form-control" id="s-levels" disabled="disabled" multiple="multiple"></select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->
    
    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-classes">@lang('school_classes.name')</label>
            <select class="form-control" id="s-classes" multiple="multiple"></select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->

    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-classes">@lang('admission_students.Gender')</label>
            <select class="form-control" id="s-gender">
                <option value="">@lang('layouts.all')</option>
                <option value="male">@lang('admission_students.male')</option>
                <option value="female">@lang('admission_students.female')</option>
            </select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->
    

</div><!-- /.row --> 
<!-- END   SEARCH BAR -->