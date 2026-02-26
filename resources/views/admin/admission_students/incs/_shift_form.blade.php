<!-- Modal -->
<div class="modal fade" id="shfitingStudents" tabindex="-1" aria-labelledby="shfitingStudentsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shfitingStudentsLabel">@lang('admission_students.shifting_students')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="my-3 row">
                    <label for="shifting-semester_id" class="col-sm-3 col-form-label">@lang('semesters.Title') <span class="text-danger float-end">*</span></label>
                    
                    <div class="col-sm-9">
                        <select id="shifting-semester_id" class="form-control"></select>
                        <div style="padding: 5px 7px; display: none" id="shifting-semester_idErr" class="err-msg mt-2 alert alert-danger">
                        </div>
                    </div><!-- /.col-sm-9 -->
                </div><!-- /.my-3 -->

                <div class="my-3 row">
                    <label class="col-sm-3 col-form-label">@lang('school_grades.Grade') <span class="text-danger float-end">*</span></label>
                    
                    <div class="col-sm-5">
                        <select id="shifting-grade_id" data-target="#shifting-level_id" class="form-control"></select>
                        <div style="padding: 5px 7px; display: none" id="shifting-grade_idErr" class="err-msg mt-2 alert alert-danger">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <select id="shifting-level_id" class="form-control" disabled="disabled"></select>
                        <div style="padding: 5px 7px; display: none" id="shifting-level_idErr" class="err-msg mt-2 alert alert-danger">
                        </div>
                    </div>
                </div><!-- /.my-3 -->

                <div class="my-3" style="height: 350px; overflow-y: auto;">
                    <table class="table table-sm text-center">
                        <thead>
                            <tr>
                                <th>#</th>   
                                <th>@lang('admission_students.Name')</th>
                                <th>@lang('school_grades.Grade')</th>
                                <th>@lang('school_grades.Level')</th>
                            </tr>
                        </thead>
                        <tbody id="shifting-students-tbody"></tbody>
                    </table>
                </div><!-- /.my-3 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.Close')</button>
                <button type="button" class="btn btn-warning" id="shifting-students-update">@lang('layouts.Update')</button>
            </div>
        </div>
    </div>
</div>

@push('custome-js-2')
<script>
$(document).ready(function () {    
    const Store = (() => {
        const meta = {
            students : []
        };
        
        const setters = {
            fetchStudents: async (students_list) => {
                try {
                    let res = await axios.get(`{{ route('admin.admissionStudents.index') }}`, {
                        params: {
                            students_list: students_list
                        }
                    });

                    let { data, success, msg } = res.data;

                    if (!success)
                    toastr.error(msg);

                    meta.students = [...data];

                } catch (err) {
                    window.failerToast(err);
                }
            },

            storeStudents : async (formData) => {
                try {
                    let res = await axios.post(`{{ route('admin.admissionStudents.shift') }}`, {
                        _token: '{{ csrf_token() }}',
                        shifting: true,
                        students_list: helpers.studentsIds(),
                        ...formData
                    });

                    let { success, msg } = res.data;

                    if (!success)
                    throw msg;

                    window.successToast(msg);

                    return true;
                } catch (err) {
                    window.failerToast(err);
                }

                return false;
            }
        };

        const getters = {
            students : () => {
                return [...meta.students];
            }
        };

        const helpers = {
            studentsIds : () => {
                return meta.students.map(student => student.id);
            }
        }

        return {
            setters, 
            getters
        }
    })();

    const View  = (() => {
        const fields = [
            'semester_id',
            'grade_id',
            'level_id'
        ];

        const renders = {
            students : (students) => {

                let rows = '';
                
                students.forEach((student, index) => {
                    let grade = Boolean(student.grade) 
                        ? (is_ar ? student.grade.ar_title : student.grade.en_title)
                        : '---';

                    let level = Boolean(student.level) 
                        ? (is_ar ? student.level.ar_title : student.level.en_title)
                        : '---';

                    rows += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${student.name}</td>
                            <td>${grade}</td>
                            <td>${level}</td>
                        </tr>
                    `;
                });

                $('#shifting-students-tbody').html(rows);
            },

            toggleBtn : (objectBtn, open = true) => {
                if (open) {
                    $(objectBtn).attr('disabled', 'disabled');
                    $(window.loddingSpinnerEl).fadeIn(500);
                } else {
                    $(objectBtn).removeAttr('disabled');
                    $(window.loddingSpinnerEl).fadeOut(500);
                }
                
            }
        };

        const crawler = {
            formData : () => {
                let data = {};
                let is_valied = true;

                fields.forEach(field => {
                    let value = $(`#shifting-${field}`).val();

                    if (!Boolean(value)) {
                        is_valied = false;
                        $(`#shifting-${field}Err`).slideDown(500).text(`@lang('layouts.field_required')`);
                    } else {
                        data[field] = value;
                        $(`#shifting-${field}Err`).slideUp(500);
                    }

                });

                return is_valied ? data : false;
            },

            getSelectedRows : () => {
                let selected_els = $('.record-selector:checked');
                let selected_ids = Array.from(selected_els).map(el => $(el).val())
                
                return selected_ids;
            }
        };

        return {
            renders, 
            crawler
        }
    })();

    const init = (async () => {
        
        const myModal = new bootstrap.Modal(document.getElementById('shfitingStudents'), {
            keyboard: false
        });

        const { setters, getters } = Store;
        const { renders, crawler } = View;

        $(`#dataTable`).on('click', '.shifting-btn', async function () {
            let student_id = $(this).data('target');

            if (!student_id) return;

            renders.toggleBtn(this, true);

            await setters.fetchStudents([student_id]);

            renders.students(getters.students());

            renders.toggleBtn(this, false);
            myModal.toggle();

        });

        $('#shifting-students-update').on('click', async function () {
            let data = crawler.formData();

            if (!data) return;
            
            renders.toggleBtn(this, true);

            await setters.storeStudents(data) && myModal.hide();

            renders.toggleBtn(this, false);
            myModal.toggle();

            $('.relode-btn').trigger('click');
        });

        $('.bulk-shifting-btn').on('click', async function () {
            let selected_ids = crawler.getSelectedRows();
            
            if (!selected_ids.length) {
                failerToast('@lang("admission_students.not_students_selected")');
                return;
            }
            
            renders.toggleBtn(this, true);

            await setters.fetchStudents(selected_ids);

            renders.students(getters.students());

            renders.toggleBtn(this, false);
            myModal.toggle();
        });


    })();

});
</script>
@endpush