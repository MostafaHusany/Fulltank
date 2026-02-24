<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>تعليق علي داوي</title>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.8/axios.min.js" integrity="sha512-v8+bPcpk4Sj7CKB11+gK/FnsbgQ15jTwZamnBf/xDmiQDcgOIYufBo6Acu1y30vrk8gg5su4x0CG3zfPaq5Fcg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        
        <!-- Toastify -->
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.rtl.min.css" integrity="sha512-VNBisELNHh6+nfDjsFXDA6WgXEZm8cfTEcMtfOZdx0XTRoRbr/6Eqb2BjqxF4sNFzdvGIt+WqxKgn0DSfh2kcA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js" integrity="sha512-ykZ1QQr0Jy/4ZkvKuqWn4iF3lqPZyij9iRv6sGqLRdTPkY69YX6+7wvVGmsdBbiIfN/8OdsI7HABjvEok6ZopQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <style>
            body {
                background-image: url(/static_images/exams.webp);
                background-size: cover;
                background-position: center;
            }
        </style>
    </head>

    <body>
        <div class="container py-4 text-right">

            <div id="success-msg" class="alert alert-success my-4 text-center" style="display: none;">
                <h3>تم اضافة التعليق بنجاح !</h3>
            </div>

           <div id="create-form" class="card card-body">
                <legend>تعليق علي نشاط داوي</legend>
                
                <hr/>

                <div method="POST" action="{{ route('feedback.store') }}">
                
                    <div class="my-3 row">
                        <label for="name" class="col-sm-2 col-form-label">@lang('feedbacks.Name')</label>
                        
                        <div class="col-10">
                            <input type="text" class="form-control" id="name" placeholder="@lang('feedbacks.Name')">
                            <div style="padding: 5px 7px; display: none" id="nameErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="phone" class="col-sm-2 col-form-label">@lang('feedbacks.Phone')</label>
                        
                        <div class="col-10">
                            <input type="text" class="form-control" id="phone" placeholder="@lang('feedbacks.Phone')">
                            <div style="padding: 5px 7px; display: none" id="phoneErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="email" class="col-sm-2 col-form-label">@lang('feedbacks.Email')</label>
                        
                        <div class="col-10">
                            <input type="text" class="form-control" id="email" placeholder="@lang('feedbacks.Email')">
                            <div style="padding: 5px 7px; display: none" id="emailErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="age" class="col-sm-2 col-form-label">@lang('feedbacks.Age') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <input type="number" min="10" max="80" class="form-control" id="age" placeholder="@lang('feedbacks.Age')">
                            <div style="padding: 5px 7px; display: none" id="ageErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="gender" class="col-sm-2 col-form-label">@lang('feedbacks.Gender') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="gender" placeholder="@lang('feedbacks.Gender')">
                                <option value="">@lang('feedbacks.Select_Gender')</option>
                                <option value="male">@lang('feedbacks.Male')</option>
                                <option value="female">@lang('feedbacks.Female')</option>
                            </select>
                            <div style="padding: 5px 7px; display: none" id="genderErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="is_disabled" class="col-sm-2 col-form-label">@lang('feedbacks.Has_Disablity') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="is_disabled" placeholder="@lang('feedbacks.Has_Disablity')">
                                <option value="">@lang('feedbacks.Select_Disablity_Status')</option>
                                <option value="1">@lang('feedbacks.Has_Disablity')</option>
                                <option value="0">@lang('feedbacks.No_Disablity')</option>
                            </select>
                            <div style="padding: 5px 7px; display: none" id="is_disabledErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="role" class="col-sm-2 col-form-label">@lang('feedbacks.Role') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="role" placeholder="@lang('feedbacks.Has_Disablity')">
                                <option value="">@lang('feedbacks.Select_Role')</option>
                                <option value="facilitator">@lang('feedbacks.Facilitator')</option>
                                <option value="trainer">@lang('feedbacks.Trainer')</option>
                                <option value="participant">@lang('feedbacks.Participant')</option>
                            </select>
                            <div style="padding: 5px 7px; display: none" id="roleErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    {{--
                    <div class="my-3 row">
                        <label for="priority_level" class="col-sm-2 col-form-label">@lang('feedbacks.Priority_Level') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="priority_level" placeholder="@lang('feedbacks.Has_Disablity')">
                                <option value="">@lang('feedbacks.Select_Priority')</option>
                                <option value="low">@lang('feedbacks.Low')</option>
                                <option value="medium">@lang('feedbacks.Medium')</option>
                                <option value="heigh">@lang('feedbacks.Heigh')</option>
                                <option value="immediate">@lang('feedbacks.Immediate')</option>
                            </select>
                            <div style="padding: 5px 7px; display: none" id="priority_levelErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    --}}
                    
                    <div class="my-3 row">
                        <label for="gove_id" class="col-sm-2 col-form-label">@lang('feedbacks.Gove') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="gove_id"></select>
                            <div style="padding: 5px 7px; display: none" id="gove_idErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="dawwie_activitie_id" class="col-sm-2 col-form-label">@lang('feedbacks.Dawwie_Activitie') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="dawwie_activitie_id"></select>
                            <div style="padding: 5px 7px; display: none" id="dawwie_activitie_idErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="trainer_id" class="col-sm-2 col-form-label">@lang('feedbacks.Trainer') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="trainer_id"></select>
                            <div style="padding: 5px 7px; display: none" id="trainer_idErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="feedback_type_id" class="col-sm-2 col-form-label">@lang('feedbacks.Feedback_Type') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="feedback_type_id" data-target="#aspect_id"></select>
                            <div style="padding: 5px 7px; display: none" id="feedback_type_idErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="aspect_id" class="col-sm-2 col-form-label">@lang('feedbacks.Aspect') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <select class="form-control" id="aspect_id" disabled="disabled"></select>
                            <div style="padding: 5px 7px; display: none" id="aspect_idErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="rating" class="col-sm-2 col-form-label">@lang('feedbacks.Rating') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <input type="number" class="form-control" id="rating" min="0" max="5">
                            <div style="padding: 5px 7px; display: none" id="ratingErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    <div class="my-3 row">
                        <label for="details" class="col-sm-2 col-form-label">@lang('feedbacks.Details') <span class="text-danger float-end">*</span></label>
                        
                        <div class="col-10">
                            <textarea class="form-control" id="details"></textarea>
                            <div style="padding: 5px 7px; display: none" id="detailsErr" class="err-msg mt-2 alert alert-danger">
                            </div>
                        </div><!-- /.col-10 -->
                    </div><!-- /.my-3 -->
                    
                    
                    <button class="submit-feedback btn btn-primary float-end">
                        <span class="mx-1">حفظ التعليق</span>
                        
                        <div class="spinner-border spinner-border-sm text-light" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                </div>
            </div><!-- /.card -->

        </div><!-- /.container -->

    </body>

    <script>
        window.lang = "{{ \LaravelLocalization::getCurrentLocale() }}";
        window.base_url = "{{ url('/') }}";

        window.successToast = (msg) => {
            Toastify({
                text: msg,
                className: "info",
                offset: {
                    x: 20, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
                    y: 50 // vertical axis - can be a number or a string indicating unity. eg: '2em'
                },
                style: {
                    color: '#0f5132',
                    background: '#d1e7dd',
                    borderColor: '#badbcc'
                }
            }).showToast();
        };

        window.failerToast = (msg) => {
            Toastify({
                text: msg,
                className: "info",
                offset: {
                    x: 20, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
                    y: 50 // vertical axis - can be a number or a string indicating unity. eg: '2em'
                },
                style: {
                    color: '#842029',
                    background: '#f8d7da',
                    borderColor: '#f5c2c7'
                }
            }).showToast();
        };

        window.loddingSpinnerEl = $('#loddingSpinner');
    </script>
    
    <script>
    $(document).ready(function () {
        let lang = 'ar';
        
        $('#gove_id, #edit-gove_id, #s-gove_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("layouts.Select_Governorate")',
            ajax: {
                url: '{{ route("search.districts") }}',
                dataType: 'json',
                delay: 150,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name || '',
                                id  : item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#feedback_type_id, #edit-feedback_type_id, #s-feedback_type_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("layouts.Select_Feedback_Type")',
            ajax: {
                url: '{{ route("search.feedbackTypes") }}',
                dataType: 'json',
                delay: 150,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.title,
                                id  : item.id
                            }
                        })
                    };
                },
                cache: true
            }
        }).change(function () {
            window.feedback_type_id = $(this).val(); 
            let target = $(this).data('target');
            
            if (window.feedback_type_id != null) {
                target == '#edit-aspect_id' 
                    ? $(target).removeAttr('disabled')
                    : $(target).val('').removeAttr('disabled').trigger('change');
            } else {
                $(target).val('').attr('disabled', 'disabled').trigger('change');
            }
        });

        $('#aspect_id, #edit-aspect_id, #s-aspect_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("layouts.Select_Umbrella_Initiative")',
            ajax: {
                url: '{{ route("search.feedbackTypes") }}',
                dataType: 'json',
                delay: 150,
                data: function (params) {
                    var query = {
                        q  : params.term,
                        feedback_type_id : window.feedback_type_id
                    }
                    return query;
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.title,
                                id  : item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#dawwie_activitie_id, #edit-dawwie_activitie_id, #s-dawwie_activitie_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("layouts.Select_Dawwie_Activitie")',
            ajax: {
                url: '{{ route("search.dawwieActivities") }}',
                dataType: 'json',
                delay: 150,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: lang == 'ar' ? item.ar_title : item.en_title,
                                id  : item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#partner_id, #edit-partner_id, #s-partner_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("layouts.Select_Partner")',
            ajax: {
                url: '{{ route("search.partners") }}',
                dataType: 'json',
                delay: 150,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id  : item.id
                            }
                        })
                    };
                },
                cache: true
            }
        }).change(function () {
            window.group_id = $(this).val(); 
            let target = $(this).data('target');
            
            if (window.group_id != null) {
                target == '#edit-trainer_id' 
                    ? $(target).removeAttr('disabled')
                    : $(target).val('').removeAttr('disabled').trigger('change');
            } else {
                $(target).val('').attr('disabled', 'disabled').trigger('change');
            }
        });
        
        $('#trainer_id, #edit-trainer_id, #s-trainer_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("layouts.Select_Trainer")',
            ajax: {
                url: '{{ route("search.trainers") }}',
                dataType: 'json',
                delay: 150,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id  : item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });

    });

    const Store = (() => {
        const meta = {};

        const setters = {
            store: async (meta) => {
                try {
                    let res = await axios.post(`{{ route('feedback.store') }}`, {
                        _token : "{{ csrf_token() }}",
                        ...meta
                    });

                    let { data, success, msg } = res.data;

                    // console.log(data, success, msg);

                    if (!success) {
                        failerToast(msg);

                        throw msg;
                    }
                    
                    // successToast(msg);

                } catch (err) {
                    typeof(err) == 'string' && failerToast(err);
                                        
                    return [false, err]    
                }

                return [true, false]
            }
        };

        const getters = {};

        return {
            setters,
            getters
        }
    })();

    const View = (() => {
        const fields = [
            'name', 'email', 'phone', 'age',
            'is_disabled', 'gender', 'role', 'rating', 'details', // 'priority_level'
            'gove_id',  'dawwie_activitie_id', 'feedback_type_id', 'aspect_id', 'trainer_id',
        ];

        const crawler = {
            formDate : () => {
                let data = {};

                fields.forEach(field => {
                    let tmp = $(`#${field}`).val();
                    
                    if (tmp)
                    data[field] = tmp;
                });

                return data;
            }
        }

        const animation = {
            showValidationErr : (msgs, prefix = '') => {
                let keys = Object.keys(msgs);

                keys.forEach(key => {
                    // for case of sub validation specialy for images !!
                    let tmp_key = (key.split('.'))[0];
                    $(`#${prefix}${tmp_key}Err`).html(msgs[key]).slideDown(500);
                });
            }
        }

        return {
            crawler,
            animation
        }
    })();

    $(document).ready(async function () {

        const { setters, getters } = Store;
        const { crawler, animation } = View;

        $('.submit-feedback').on('click', async function (e) {
            e.preventDefault();

            $('.err-msg').slideUp(500);

            $(this).attr('disabled', 'disabled');
            $(this).find('.spinner-border').show(500);

            let data = crawler.formDate();

            let [success, msg] = await setters.store(data);

            if (!success)
            animation.showValidationErr(msg);
            else 
            $('#create-form').fadeOut(500) && $('#success-msg').slideDown(500);

            $(this).removeAttr('disabled');
            $(this).find('.spinner-border').hide(500);

        });
    });
    </script>
</html>