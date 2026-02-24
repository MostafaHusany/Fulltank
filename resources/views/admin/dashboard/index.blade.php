@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('layouts.Dashboard')</h1>
@endpush

@section('content')

    <div class="row my-3">
        
        <div class="col-md-6">
            <div class="rounded-1 p-3 mb-2 bg-primary text-white">
                <i class="fas fa-users fa-3x"></i>    
                <span class="fs-2 float-end" id="stat-employees">
                    <span style="display: none" class="count"></span>

                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
                <hr/>

                <a class=" text-white" href="#">@lang('layouts.Employees')</a>
            </div>
        </div><!-- /.col-md-6 -->

        <div class="col-md-6">
            <div class="rounded-1 p-3 mb-2 bg-primary text-white">
                <i class="fas fa-chalkboard-teacher fa-3x"></i> 
                <span class="fs-2 float-end" id="stat-teachers">
                    <span style="display: none" class="count"></span>

                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
                <hr/>

                <a class=" text-white" href="#">@lang('layouts.Teachers')</a>
            </div>
        </div><!-- /.col-md-6 -->

    </div><!-- /.row -->
    
    <div class="row mb-3">

        <div class="col-md-6">
            <div class="card card-body">
                <canvas id="employee-categories-counts"></canvas>
            </div><!-- /.card -->
        </div><!-- /.col-md-6 -->
        
        <div class="col-md-6">
            <div class="card card-body">
                <canvas id="teacher-in-levels"></canvas>
            </div><!-- /.card -->
        </div><!-- /.col-md-6 -->

    </div><!-- /.row -->

    <hr class="my-3"/>
    
    <div class="row my-3">

        <div class="col-md-6">
            <div class="rounded-1 p-3 mb-2 bg-primary text-white">
                <i class="fas fa-male fa-3x"></i>
                <span class="fs-2 float-end" id="stat-male_students">
                    <span style="display: none" class="count"></span>

                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
                <hr/>

                <a class=" text-white" href="#">@lang('layouts.Male_Students')</a>
            </div>
        </div><!-- /.col-md-6 -->

        <div class="col-md-6">
            <div class="rounded-1 p-3 mb-2 bg-primary text-white">
                <i class="fas fa-female fa-3x"></i>
                <span class="fs-2 float-end" id="stat-female_students">
                    <span style="display: none" class="count"></span>

                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
                <hr/>

                <a class=" text-white" href="#">@lang('layouts.Female_Students')</a>
            </div>
        </div><!-- /.col-md-6 -->

    </div><!-- /.row -->

    
    <div class="row mb-3">
    
        <div class="col-md-6">
            <div class="card card-body">
                <canvas id="students-levels"></canvas>
            </div><!-- /.card -->
        </div><!-- /.col-md-6 -->

    </div><!-- /.row -->

    <hr class="my-3"/>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="rounded-1 p-3 mb-2 bg-primary text-white">
                <i class="fas fa-chart-line fa-3x"></i>
                <span class="fs-2 float-end" id="stat-expected">
                    <span style="display: none" class="count"></span>

                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
                <hr/>

                <a class=" text-white" href="">@lang('student_payments.Expected_Total')</a>
            </div>
        </div><!-- /.col-md-3 -->
        
        <div class="col-md-3">
            <div class="rounded-1 p-3 mb-2 bg-primary text-white">    
                <i class="fas fa-chart-pie fa-3x"></i>
                <span class="fs-2 float-end" id="stat-real">
                    <span style="display: none" class="count"></span>

                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
                <hr/>

                <a class=" text-white" href="">@lang('student_payments.Real_Total')</a>
            </div>
        </div><!-- /.col-md-3 -->

        <div class="col-md-3">
            <div class="rounded-1 p-3 mb-2 bg-primary text-white"> 
                <i class="fas fa-chart-pie fa-3x"></i>
                <span class="fs-2 float-end" id="stat-cash">
                    <span style="display: none" class="count"></span>

                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
                <hr/>

                <a class=" text-white" href="">@lang('student_payments.Total_Cash')</a>
            </div>
        </div><!-- /.col-md-3 -->

        <div class="col-md-3">
            <div class="rounded-1 p-3 mb-2 bg-primary text-white"> 
                <i class="fas fa-chart-pie fa-3x"></i>
                <span class="fs-2 float-end" id="stat-online">
                    <span style="display: none" class="count"></span>

                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
                <hr/>

                <a class=" text-white" href="">@lang('student_payments.Total_Online')</a>
            </div>
        </div><!-- /.col-md-3 -->
    </div><!-- /.row -->

    <div class="card card-body">
        <canvas id="acquisitions"></canvas>
    </div><!-- /.card -->


@endSection

@push('custome-js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script> 
$(document).ready(function () {

    

});
</script>

<script>
$('document').ready(function () {
    
    function renderChart ({elementId, data, title, label, count}) {
        new Chart(
            document.getElementById(elementId),
            {
                type: 'bar',
                data: {
                    labels: data.map(row => row[label]),
                    datasets: [
                        {
                            label: title,
                            data: data.map(row => row[count])
                        }
                    ]
                }
            }
        );
    }

    const renderStatistics = async () => {
        const target_el = [
            'employees', 'teachers', 'male_students', 'female_students',
            'expected','real', 'cash', 'online'
        ];

        const res = await axios.get(`{{ route('admin.dashboard.index') }}?get_counts=true`);

        const { data, success } = res.data;
        
        if (success) {
            target_el.forEach(el => {
                $(`#stat-${el}`).find('.spinner-border').hide(500);
                $(`#stat-${el}`).find('.count').text(data[el]).hide(500).show(500);
            });

            $('#workshops-count').text(data.workShops);
        }

        let student_counts = Boolean(data.student_counts) ? data.student_counts : [];

        new Chart(
            document.getElementById('students-levels'),
            {
                type: 'bar',
                data: {
                    labels: student_counts.map(row => row.level_name),
                    datasets: [
                        {
                            label: `@lang('dashboard.students_levels')`,
                            data: student_counts.map(row => row.total_students)
                        }
                    ]
                }
            }
        );

        let employee_counts = Boolean(data.employee_counts) ? data.employee_counts : [];
        
        new Chart(
            document.getElementById('employee-categories-counts'),
            {
                type: 'bar',
                data: {
                    labels: employee_counts.map(row => row.category),
                    datasets: [
                        {
                            label: `@lang('dashboard.employees_category')`,
                            data: employee_counts.map(row => row.total)
                        }
                    ]
                }
            }
        );
        
        let teacher_counts = Boolean(data.teacher_counts) ? data.teacher_counts : [];
        
        new Chart(
            document.getElementById('teacher-in-levels'),
            {
                type: 'bar',
                data: {
                    labels: teacher_counts.map(row => row.level_name),
                    datasets: [
                        {
                            label: `@lang('dashboard.teachers_in_each_level')`,
                            data: teacher_counts.map(row => row.teacher_count)
                        }
                    ]
                }
            }
        );

        let incomes = Boolean(data.incomes) ? data.incomes: [];

        new Chart(
            document.getElementById('acquisitions'),
            {
                type: 'bar',
                data: {
                    labels: incomes.map(row => row.academic_year),
                    datasets: [
                        {
                            label: `@lang('dashboard.income_by_academic_year')`,
                            data: incomes.map(row => row.total_income)
                        }
                    ]
                }
            }
        );

    }

    const inite = (() => {
        renderStatistics();
    })();

});
</script>
@endpush