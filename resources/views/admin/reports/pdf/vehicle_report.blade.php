<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('reports.Vehicle Report')</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-box {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 5px;
        }
        .stats-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-box .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table.data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        table.data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>@lang('reports.Vehicle Report')</h1>
        <p>{{ $data['vehicle']['plate_number'] }}</p>
        <p>@lang('reports.Period'): {{ $data['period']['from'] }} - {{ $data['period']['to'] }}</p>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td><strong>@lang('reports.Plate Number'):</strong> {{ $data['vehicle']['plate_number'] }}</td>
                <td><strong>@lang('reports.Model'):</strong> {{ $data['vehicle']['model'] ?? '---' }}</td>
                <td><strong>@lang('reports.Client'):</strong> {{ $data['vehicle']['client_name'] }}</td>
            </tr>
        </table>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <div class="value">{{ number_format($data['stats']['transaction_count']) }}</div>
            <div class="label">@lang('reports.Transactions')</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ number_format($data['stats']['total_liters'], 2) }}</div>
            <div class="label">@lang('reports.Total Liters')</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ number_format($data['stats']['total_amount'], 2) }}</div>
            <div class="label">@lang('reports.Total Amount') (EGP)</div>
        </div>
    </div>

    <h3>@lang('reports.Fuel Transactions')</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('reports.Date')</th>
                <th>@lang('reports.Station')</th>
                <th>@lang('reports.Fuel Type')</th>
                <th>@lang('reports.Liters')</th>
                <th>@lang('reports.Amount')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['transactions']->items() as $idx => $txn)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($txn->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $txn->station->name ?? '---' }}</td>
                    <td>{{ $txn->fuelType->name ?? '---' }}</td>
                    <td>{{ number_format($txn->actual_liters, 2) }} L</td>
                    <td>{{ number_format($txn->total_amount, 2) }} EGP</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">@lang('reports.No transactions found')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>@lang('reports.Generated on') {{ now()->format('d/m/Y H:i') }}</p>
        <p>FullTank Fuel Management System</p>
    </div>
</body>
</html>
