<!-- START FILTERS BAR -->
<div class="row mb-4 bg-light p-3 rounded">

    <div class="col-md-2 filter-group filter-date">
        <div class="my-2">
            <label for="filter-date-from">@lang('reports.Date From')</label>
            <input type="date" class="form-control" id="filter-date-from" 
                   value="{{ now()->subMonth()->format('Y-m-d') }}">
        </div>
    </div><!-- /.col-md-2 -->

    <div class="col-md-2 filter-group filter-date">
        <div class="my-2">
            <label for="filter-date-to">@lang('reports.Date To')</label>
            <input type="date" class="form-control" id="filter-date-to" 
                   value="{{ now()->format('Y-m-d') }}">
        </div>
    </div><!-- /.col-md-2 -->

    <div class="col-md-3 filter-group filter-client" style="display: none;">
        <div class="my-2">
            <label for="filter-client">@lang('reports.Client')</label>
            <select class="form-control" id="filter-client"></select>
        </div>
    </div><!-- /.col-md-3 -->

    <div class="col-md-2 filter-group filter-governorate" style="display: none;">
        <div class="my-2">
            <label for="filter-governorate">@lang('reports.Governorate')</label>
            <select class="form-control" id="filter-governorate">
                <option value="">@lang('layouts.all')</option>
                @foreach($governorates as $gov)
                    <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                @endforeach
            </select>
        </div>
    </div><!-- /.col-md-2 -->

    <div class="col-md-3 filter-group filter-station" style="display: none;">
        <div class="my-2">
            <label for="filter-station">@lang('reports.Station')</label>
            <select class="form-control" id="filter-station"></select>
        </div>
    </div><!-- /.col-md-3 -->

    <div class="col-md-2">
        <div class="my-2">
            <label class="d-block">&nbsp;</label>
            <button class="btn btn-primary w-100" id="generate-report">
                <i class="fas fa-chart-bar me-1"></i> @lang('reports.Generate Report')
            </button>
        </div>
    </div><!-- /.col-md-2 -->

</div><!-- /.row -->
<!-- END FILTERS BAR -->
