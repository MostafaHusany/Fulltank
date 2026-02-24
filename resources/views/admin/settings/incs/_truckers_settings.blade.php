
@push('custome-plugin')
<style>
    .form-container {
        position: relative;
    }

    .form-spinner {
        position: absolute;

        width: 100%;
        height: 100%;
        background: #ddd;
        opacity: 0.5;
        z-index: 10;
    }
</style>
@endpush

<div class="card card-body form-container">      
    <div id="trucking-price-loader" class="form-spinner d-flex justify-content-center align-items-center">
        <div class="spinner-grow" style="width: 5rem; height: 5rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div style="height: 400px; overflow-y: scroll">
        <div class="my-3 row" style="width: 95%;">
            <div class="col-md-3">
                <label class="form-label" for="price-from">@lang('settings.From In Kilometer')</label>
                <input id="price-from" type="number" min="1" step="1" class="form-control">
            </div><!-- /.col-3 -->
            
            <div class="col-md-3">
                <label class="form-label" for="price-to">@lang('settings.To In Kilometer')</label>
                <input id="price-to" type="number" min="1" step="1" class="form-control">
            </div><!-- /.col-3 -->
            
            <div class="col-md-3">
                <label class="form-label" for="price">@lang('settings.Price In') {{ env('APP_CURRENCY') }}</label>
                <input id="price" type="number" min="1" step="1" class="form-control">
            </div><!-- /.col-3 -->
            
            <div class="col-md-3">
                <button class="add-price btn btn-primary btn-sm" style="width: 100px; margin-top: 33px" type="button">
                    <i class="fas fa-plus-circle"></i>
                </button>
            </div><!-- /.col-3 -->
        </div>

        <table class="table text-center">
            <thead>
                <th>@lang('settings.From In Kilometer')</th>
                <th>@lang('settings.To In Kilometer')</th>
                <th>@lang('settings.Price In') {{ env('APP_CURRENCY') }}</th>
                <th>@lang('layouts.Actions')</th>
            </thead>
            <tbody id="trucking-price-list"></tbody>
        </table>

    </div>

    <div class="mt-2">
        <button id="update-price-list" class="btn btn-warning float-end">
            @lang('settings.Update Trucking Price List')
        </button>
    </div><!-- /.mt-1 -->
</div>


@push('custome-js')
<script>
$('document').ready(function () {
    
    const Store = (() => {
        let data = [];

        const setters = {
            setPriceList : (priceList) => {
                data = [...priceList];
            },

            addPrice : (newPrice) => {
                newPrice.id = Math.round(Math.random() * 1000);
                data.push(newPrice);
            },

            deletePrice : (id) => {
                data = data.filter(priceObj => priceObj.id != Number(id));
            }
        };

        const getters = {
            isValied : (newPriceObj) => {
                let is_valied = true;

                data.forEach(priceObj => {
                    if (priceObj.from == newPriceObj.from || newPriceObj.from <= 0) {
                        is_valied = false;
                    } else if (priceObj.to == newPriceObj.to || newPriceObj.to <= 0) {
                        is_valied = false;
                    } else if (newPriceObj.to == newPriceObj.from) {
                        is_valied = false;
                    } else if (newPriceObj.price <= 0) {
                        is_valied = false;
                    }
                });

                return is_valied;
            },

            getData  : () => {
                return [...data]
            }
        };

        return {
            setters,
            getters
        }
    })();

    const View = (() => {
        const render = (data) => {
            let price_list = '';

            data.forEach(pricingObj => {
                price_list += `
                    <tr>
                        <td>${pricingObj.from} / Kilometer</td>
                        <td>${pricingObj.to} / Kilometer</td>
                        <td>${pricingObj.price} {{ env('APP_CURRENCY') }}</td>
                        <td>
                            <button data-target="${pricingObj.id}" class="delete-price btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    <tr>
                `;
            });

            $('#trucking-price-list').html(price_list);
        }

        const toastMsg = (message = '', type = 'warning') => {
            let msgStyle ={
                'success' : {
                    color: '#0f5132', background: '#d1e7dd', borderColor: '#badbcc'
                },
                'danger' : {
                    color: '#842029', background: '#f8d7da', borderColor: '#f5c2c7'
                },
                'warning' : {
                    color: '#664d03', background: '#fff3cd', borderColor: '#ffecb5'
                }
            };

            Toastify({
                text: message,
                className: "info",
                offset: {
                    x: 20, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
                    y: 50 // vertical axis - can be a number or a string indicating unity. eg: '2em'
                },
                style: msgStyle[type]
            }).showToast();
        }

        return {
            render,
            toastMsg
        }
    })();

    const Controller = ((store, view) => {
        const { getters, setters }      = store;
        const { render, toastMsg }  = view;

        $('#trucking-price-list').on('click', '.delete-price', function (e) {
            e.preventDefault();

            let target_id = $(this).data('target');
            setters.deletePrice(target_id);
            render(getters.getData());
        });

        $('.add-price').on('click', function (e) {
            e.preventDefault();

            const data = {
                from  : $('#price-from').val(),
                to    : $('#price-to').val(),
                price : $('#price').val(),
            };

            if (getters.isValied(data)) {
                setters.addPrice(data);
                render(getters.getData());
            } else {
                toastMsg("@lang('settings.range is not valied')", 'warning');
            }

        });

        $('#update-price-list').on('click', function () {
            const data = getters.getData();

            $(this).attr('disabled', 'disabled');
            
            axios.post(`{{ route('admin.settings.index') }}`, {
                _token : "{{ csrf_token() }}",
                flag   : 'trucking_price_list',
                data,
            }).then((res) => {
                const { msg, success } = res.data;
                if (success) {
                    toastMsg(msg, 'success');
                } else {
                    toastMsg(msg, 'warning');
                }
            }).catch((err) => {
                toastMsg(err, 'danger');
            }).finally(() => {
                $(this).removeAttr('disabled');
            });
        });

        (() => {

            axios.get(`{{ route('admin.settings.index') }}`, {
                params: {
                    flag : 'trucking_price_list'
                }
            }).then(res => {
                const { data, success, msg } = res.data;
                
                if (success) {
                    setters.setPriceList(data);

                    render(getters.getData());
                }

            }).catch(err => {
                toastMsg("__('settings.something_went_wrong')", 'danger')
            }).finally(() => {
                console.log($('#trucking-price-loader'));
                $('#trucking-price-loader').removeClass('d-flex');
                $('#trucking-price-loader').css('display', 'none');
            });
        })();
    })(Store, View);

});
</script>
@endpush