<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('reports.Client Statement')</title>
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
            width: 25%;
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
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
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
        <h1>@lang('reports.Client Statement')</h1>
        <p>{{ $data['client']['name'] }}</p>
        <p>@lang('reports.Period'): {{ $data['period']['from'] }} - {{ $data['period']['to'] }}</p>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td><strong>@lang('reports.Client'):</strong> {{ $data['client']['name'] }}</td>
                <td><strong>@lang('reports.Phone'):</strong> {{ $data['client']['phone'] ?? '---' }}</td>
                <td><strong>@lang('reports.Email'):</strong> {{ $data['client']['email'] ?? '---' }}</td>
            </tr>
        </table>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <div class="value">{{ number_format($data['opening_balance'], 2) }}</div>
            <div class="label">@lang('reports.Opening Balance') (EGP)</div>
        </div>
        <div class="stat-box">
            <div class="value positive">{{ number_format($data['total_credits'], 2) }}</div>
            <div class="label">@lang('reports.Total Credits') (EGP)</div>
        </div>
        <div class="stat-box">
            <div class="value negative">{{ number_format($data['total_debits'], 2) }}</div>
            <div class="label">@lang('reports.Total Debits') (EGP)</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ number_format($data['closing_balance'], 2) }}</div>
            <div class="label">@lang('reports.Closing Balance') (EGP)</div>
        </div>
    </div>

    <h3>@lang('reports.Transactions')</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('reports.Date')</th>
                <th>@lang('reports.Type')</th>
                <th>@lang('reports.Amount')</th>
                <th>@lang('reports.Balance')</th>
                <th>@lang('reports.Notes')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['transactions']->items() as $idx => $txn)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($txn->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $txn->type }}</td>
                    <td class="{{ $txn->amount >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($txn->amount, 2) }} EGP
                    </td>
                    <td>{{ number_format($txn->after_balance, 2) }} EGP</td>
                    <td>{{ $txn->notes ?? '---' }}</td>
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
