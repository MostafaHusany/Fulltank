
<div style="display: none" id="manageCenters" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('districts.Create Title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#manageCenters" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <div class="my-1">
        <div class="my-3 row">
            <input type="hidden" id="centers">

            <label for="child_district_field" class="col-sm-2">@lang('districts.Center')</label>

            <div class="col-4">
                <input class="form-control custome-en-field" id="center-en_name" placeholder="Districts name in english">
            </div><!-- /.col-4 -->
            
            <div class="col-4">
                <input class="form-control custome-ar-field" id="center-ar_name" placeholder="أسم المركز بالعربية">
            </div><!-- /.col-4 -->
            
            <div class="col-sm-1">
                <button id="add-center" class="btn btn-sm btn-primary mt-1">
                    <i class="fas fa-plus-circle"></i>
                </button>
            </div><!-- /.col-sm-1 -->
        </div><!-- /.my-3 -->

        <div class="mt-5 row">
            <div class="col-sm-2"></div>
            
            <div class="col-sm-10" style="height: 300px; overflow-y: scroll">
                <table class="table table-sm text-center">
                    <tr>
                        <td>@lang('districts.Centers')</td>
                        <td>@lang('districts.Centers')</td>
                        <td>@lang('layouts.Actions')</td>
                    </tr>

                    <tbody id="centersTable">
                        
                    </tbody>
                    
                    <div style="padding: 5px 7px; display:none; width:95%; margin-right: auto;" id="districtsErr" class="err-msg mt-2 alert alert-danger"></span>
                </table>
            </div><!-- /.col-sm-10 -->
        </div><!-- /.my-3 -->

        {{--
            <button class="create-draft btn btn-secondary float-end mx-2">@lang('drafts.Save Draft')</button>
        --}}
    </div>
</div>


@push('custome-js-2')
<script>
$(document).ready(function () {

    const Store = (() => {
        const meta = {
            gove    : null, 
            centers : [],
        };

        const setters = {
            async store (formData) {
                try {
                    let res = await axios.post("{{ route('admin.districts.index') }}", {
                        ...formData,
                        _token      : `{{ csrf_token() }}`,
                        category    : 'cent',
                        district_id : meta.gove.id,
                    });

                    let { data, success, msg } = res.data;

                    if (!success)
                    throw msg;

                    meta.centers.push(data);
                    
                    successToast(msg);

                    return true;
                } catch (err) {
                    failerToast(msg);
                }

                return false;
            },

            async update (formData, center_id) {
                try {
                    let res = await axios.post(`{{ route('admin.districts.index') }}/${center_id}`, {
                        ...formData,
                        _token      : `{{ csrf_token() }}`,
                        _method     : 'PUT',
                        category    : 'cent',
                        district_id : meta.gove.id,
                    });

                    let { data, success, msg } = res.data;

                    if (!success)
                    throw msg;

                    meta.centers = meta.centers.map(center => center.id == center_id ? data : center);
                    
                    successToast(msg);

                    return true;
                } catch (err) {
                    failerToast(msg);
                }

                return false;
            },

            async delete (center_id) {
                try {
                    let res = await axios.post(`{{ route('admin.districts.index') }}/${center_id}`, {
                        _token  : `{{ csrf_token() }}`,
                        _method : 'DELETE',
                    });

                    let { data, success, msg } = res.data;

                    if (!success)
                    throw msg;

                    meta.centers = meta.centers.filter(center => center.id != center_id);

                    successToast(msg);

                    return true;
                } catch (err) {
                    failerToast(msg);
                }

                return false;
            }
        };

        const getters = {
            fetchGove : async (gove_id) => {
                if (!Boolean(gove_id))
                return false;

                try {
                    let res = await axios.get(`{{ route('admin.districts.index') }}/${gove_id}`);

                    let { data, success, msg } = res.data;

                    if (!success)
                    throw msg;

                    meta.gove    = {...data};
                    meta.centers = [...data.children];

                    return {gove : meta.gove, centers : meta.centers}
                } catch (err) {
                    failerToast(err);
                }
            },

            getCenters : () => {
                return [...meta.centers].reverse()
            }
        };

        return {
            setters,
            getters
        }

    })();
    
    const View  = (() => {
        
        let fields = ['ar_name', 'en_name'];

        const renders = {
            centers (centers_list) {
                let centers_el = '';

                centers_list.forEach(center => {
                    centers_el += `
                        <tr>
                            <td>
                                <input value="${center.en_name}" id="center-en_name${center.id}" placeholder="Districts name in english" class="form-control"/>
                            </td>
                            <td>
                                <input value="${center.ar_name}" id="center-ar_name${center.id}" placeholder="أسم المركز بالعربية" class="form-control"/>
                            </td>
                            <td>
                                <button class="update-center btn btn-sm btn-warning" data-target="${center.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="delete-center btn btn-sm btn-danger" data-target="${center.id}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                $('#centersTable').html(centers_el);
            },

            toggleLoading (btnObj, is_show = true) {
                if (is_show) {
                    $(btnObj).attr('disabled', 'disabled');
                    $(window.loddingSpinnerEl).fadeIn(500);
                } else {
                    $(btnObj).removeAttr('disabled', 'disabled');
                    $(window.loddingSpinnerEl).fadeOut(500);
                }
            },

            toggleForm (is_show = true) {
                if (is_show) {
                    $('#objectsCard').slideUp(500);
                    $('#manageCenters').slideDown(500);
                } else {
                    $('#objectsCard').slideDown(500);
                    $('#manageCenters').slideUp(500);
                }
            },

            clearForm () {
                fields.forEach(field => $(`#center-${field}`).val(''));
            }
        };
        
        const crawler = {
            getFormData (prefix = '') {
                let data      = {};
                let is_valied = true;

                fields.forEach(field => {
                    let selector = `#center-${field + prefix}`;
                    let val      = $(selector).val();

                    if (!Boolean(val)) {
                        is_valied = false;
                        $(selector).css('border-color', 'red');
                    } else {
                        data[field] = val;
                        $(selector).css('border-color', '');
                    }
                });

                return is_valied ? data : is_valied;
            },
        };

        return {
            renders,
            crawler
        }
    })();

    const init = (() => {
        const { renders, crawler } = View;
        const { setters, getters } = Store;

        $('#dataTable').on('click', '.manage-centers', async function () {
            let target_id = $(this).data('target');

            if (!Boolean(target_id))
            return false;

            renders.toggleLoading(this);
            
            if (await getters.fetchGove(target_id)) {
                renders.centers(getters.getCenters());
                renders.toggleForm();
            }
            
            renders.toggleLoading(this, false);
        });

        $('#add-center').on('click', async function () {
            let formData = crawler.getFormData();
            
            if (!Boolean(formData))
            return false;

            renders.toggleLoading(this);

            if (await setters.store(formData)) {
                renders.clearForm();
                renders.centers(getters.getCenters());
            }

            renders.toggleLoading(this, false);


        });

        $('#centersTable').on('click', '.delete-center', async function () {

            let target_id       = $(this).data('target');
            let confirm_flag    = confirm(`@lang('districts.confirm_delete_center')`);

            if (!Boolean(target_id))
            return false;

            renders.toggleLoading(this);

            if (await setters.delete(target_id)) {
                renders.centers(getters.getCenters());
            }

            renders.toggleLoading(this, false);


        });

        $('#centersTable').on('click', '.update-center', async function () {

            let target_id   = $(this).data('target');
            let formData    = crawler.getFormData(target_id);
            
            if (!Boolean(target_id))
            return false;

            renders.toggleLoading(this);

            if (await setters.update(formData, target_id)) {
                renders.centers(getters.getCenters());
            }

            renders.toggleLoading(this, false);

        });

    })();

});
</script>
@endpush