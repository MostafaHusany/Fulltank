@extends('layouts.admin.app')

@push('title')
    <h4 class="h4">Full-Cycle Simulator</h4>
@endpush

@push('custome-plugin')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<style>
    .simulator-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border-radius: 20px;
        color: #fff;
    }
    .step-item {
        display: flex;
        align-items: flex-start;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 8px;
        background: rgba(255,255,255,0.05);
        transition: all 0.3s ease;
    }
    .step-item.pending { opacity: 0.5; }
    .step-item.running { 
        background: rgba(255,193,7,0.2); 
        border-left: 4px solid #ffc107;
    }
    .step-item.success { 
        background: rgba(40,167,69,0.2); 
        border-left: 4px solid #28a745;
    }
    .step-item.failed { 
        background: rgba(220,53,69,0.2); 
        border-left: 4px solid #dc3545;
    }
    .step-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
    }
    .step-icon.pending { background: #6c757d; }
    .step-icon.running { background: #ffc107; color: #000; }
    .step-icon.success { background: #28a745; }
    .step-icon.failed { background: #dc3545; }
    .step-content { flex: 1; }
    .step-title { font-weight: 600; margin-bottom: 4px; }
    .step-data { 
        font-size: 0.8rem; 
        color: rgba(255,255,255,0.7);
        font-family: monospace;
    }
    .results-console {
        background: #0d1117;
        border-radius: 12px;
        padding: 16px;
        font-family: 'Consolas', monospace;
        font-size: 0.85rem;
        max-height: 300px;
        overflow-y: auto;
    }
    .results-console pre {
        margin: 0;
        color: #58a6ff;
        white-space: pre-wrap;
    }
    .config-card {
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 20px;
    }
    .big-button {
        padding: 20px 40px;
        font-size: 1.2rem;
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .big-button:hover:not(:disabled) {
        transform: scale(1.05);
    }
    .big-button:disabled {
        opacity: 0.6;
    }
    .spinner-grow-sm {
        width: 1rem;
        height: 1rem;
    }
    .summary-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Header Card --}}
            <div class="card simulator-card shadow-lg mb-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-rocket fa-4x mb-3" style="color: #ffc107;"></i>
                    <h2 class="mb-2">Full-Cycle Simulator</h2>
                    <p class="text-muted mb-4" style="color: rgba(255,255,255,0.7) !important;">
                        One-click simulation: Creates test data → Runs complete fueling flow → Verifies results → Cleans up
                    </p>

                    {{-- Configuration --}}
                    <div class="config-card d-inline-block mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="form-label mb-0">Fuel Amount (Liters)</label>
                            </div>
                            <div class="col-auto">
                                <input type="number" class="form-control bg-dark text-white border-secondary" 
                                       id="fuelAmount" value="20" min="1" max="100" style="width: 100px;">
                            </div>
                            <div class="col-auto">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="cleanupData" checked>
                                    <label class="form-check-label" for="cleanupData">Cleanup test data after</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="button" class="btn btn-warning big-button" id="startSimulation">
                            <i class="fas fa-play me-2"></i>Start Auto-Test
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Steps Checklist --}}
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-dark text-white">
                            <i class="fas fa-tasks me-2"></i>Simulation Steps
                        </div>
                        <div class="card-body bg-dark" id="stepsContainer">
                            <div class="step-item pending" data-step="generating_data">
                                <div class="step-icon pending"><i class="fas fa-database"></i></div>
                                <div class="step-content">
                                    <div class="step-title">Generate Test Data</div>
                                    <div class="step-data">Client, Vehicle, Driver, Station, Worker</div>
                                </div>
                            </div>
                            <div class="step-item pending" data-step="driver_login">
                                <div class="step-icon pending"><i class="fas fa-sign-in-alt"></i></div>
                                <div class="step-content">
                                    <div class="step-title">Driver Login</div>
                                    <div class="step-data">Authenticate & get token</div>
                                </div>
                            </div>
                            <div class="step-item pending" data-step="create_request">
                                <div class="step-icon pending"><i class="fas fa-gas-pump"></i></div>
                                <div class="step-content">
                                    <div class="step-title">Create Fueling Request</div>
                                    <div class="step-data">Generate OTP code</div>
                                </div>
                            </div>
                            <div class="step-item pending" data-step="worker_login">
                                <div class="step-icon pending"><i class="fas fa-hard-hat"></i></div>
                                <div class="step-content">
                                    <div class="step-title">Worker Login</div>
                                    <div class="step-data">Authenticate station worker</div>
                                </div>
                            </div>
                            <div class="step-item pending" data-step="verify_otp">
                                <div class="step-icon pending"><i class="fas fa-check-circle"></i></div>
                                <div class="step-content">
                                    <div class="step-title">Verify OTP</div>
                                    <div class="step-data">Scan & validate request</div>
                                </div>
                            </div>
                            <div class="step-item pending" data-step="confirm_fueling">
                                <div class="step-icon pending"><i class="fas fa-check-double"></i></div>
                                <div class="step-content">
                                    <div class="step-title">Confirm Fueling</div>
                                    <div class="step-data">Execute transaction</div>
                                </div>
                            </div>
                            <div class="step-item pending" data-step="verify_financials">
                                <div class="step-icon pending"><i class="fas fa-calculator"></i></div>
                                <div class="step-content">
                                    <div class="step-title">Verify Financials</div>
                                    <div class="step-data">Check balance & quota deductions</div>
                                </div>
                            </div>
                            <div class="step-item pending" data-step="cleanup">
                                <div class="step-icon pending"><i class="fas fa-broom"></i></div>
                                <div class="step-content">
                                    <div class="step-title">Cleanup</div>
                                    <div class="step-data">Remove test records</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Results Console --}}
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-terminal me-2"></i>Results Console</span>
                            <span class="badge bg-secondary" id="statusBadge">Idle</span>
                        </div>
                        <div class="card-body bg-dark">
                            <div class="results-console" id="resultsConsole">
                                <pre id="consoleOutput">// Waiting for simulation to start...</pre>
                            </div>

                            {{-- Summary Cards (shown after completion) --}}
                            <div id="summarySection" class="mt-3 d-none">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="summary-badge bg-success text-white text-center w-100">
                                            <i class="fas fa-check me-1"></i>
                                            <span id="summarySteps">0</span> Steps Passed
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="summary-badge bg-info text-white text-center w-100">
                                            <i class="fas fa-receipt me-1"></i>
                                            TX: <span id="summaryTxId">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Back Link --}}
            <div class="text-center">
                <a href="{{ route('admin.apiTester.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to API Test Lab
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
$(document).ready(function() {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000
    };

    var isRunning = false;

    function resetUI() {
        $('.step-item').removeClass('running success failed').addClass('pending');
        $('.step-icon').removeClass('running success failed').addClass('pending');
        $('.step-data').each(function() {
            $(this).text($(this).parent().parent().find('.step-title').data('original-data') || $(this).text());
        });
        $('#consoleOutput').text('// Starting simulation...\n');
        $('#summarySection').addClass('d-none');
        $('#statusBadge').removeClass('bg-success bg-danger').addClass('bg-warning').text('Running...');
    }

    function updateStep(key, status, data) {
        var stepEl = $(`.step-item[data-step="${key}"]`);
        var iconEl = stepEl.find('.step-icon');
        var dataEl = stepEl.find('.step-data');

        stepEl.removeClass('pending running success failed').addClass(status);
        iconEl.removeClass('pending running success failed').addClass(status);

        if (status === 'running') {
            iconEl.html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        } else if (status === 'success') {
            iconEl.html('<i class="fas fa-check"></i>');
        } else if (status === 'failed') {
            iconEl.html('<i class="fas fa-times"></i>');
        }

        if (data && Object.keys(data).length > 0) {
            var dataStr = Object.entries(data).map(([k, v]) => `${k}: ${v}`).join(' | ');
            dataEl.text(dataStr);
        }
    }

    function appendConsole(text) {
        var console = $('#consoleOutput');
        console.append(text + '\n');
        $('#resultsConsole').scrollTop($('#resultsConsole')[0].scrollHeight);
    }

    function formatJson(obj) {
        return JSON.stringify(obj, null, 2);
    }

    $('#startSimulation').on('click', function() {
        if (isRunning) return;

        isRunning = true;
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-grow spinner-grow-sm me-2"></span>Running...');

        resetUI();

        var fuelAmount = $('#fuelAmount').val();
        var cleanup = $('#cleanupData').is(':checked');

        appendConsole('> Initializing simulation...');
        appendConsole(`> Fuel Amount: ${fuelAmount}L`);
        appendConsole(`> Cleanup: ${cleanup ? 'Yes' : 'No'}`);
        appendConsole('');

        $.ajax({
            url: '{{ route("admin.apiSimulator.run") }}',
            method: 'POST',
            data: {
                fuel_amount: fuelAmount,
                cleanup: cleanup,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                appendConsole('='.repeat(50));
                appendConsole('SIMULATION COMPLETED SUCCESSFULLY!');
                appendConsole('='.repeat(50));
                appendConsole('');

                // Update all steps
                Object.values(response.steps).forEach(function(step) {
                    updateStep(step.key, step.status, step.data);
                    appendConsole(`[${step.status.toUpperCase()}] ${step.message}`);
                    if (step.data && Object.keys(step.data).length > 0) {
                        appendConsole('  └─ ' + JSON.stringify(step.data));
                    }
                });

                appendConsole('');
                appendConsole('SUMMARY:');
                appendConsole(formatJson(response.summary));

                $('#statusBadge').removeClass('bg-warning bg-danger').addClass('bg-success').text('Success');
                $('#summarySection').removeClass('d-none');
                $('#summarySteps').text(response.summary.total_steps);
                $('#summaryTxId').text(response.summary.transaction_id || 'N/A');

                toastr.success('Full cycle simulation completed!');
            },
            error: function(xhr) {
                var response = xhr.responseJSON || {};
                
                appendConsole('='.repeat(50));
                appendConsole('SIMULATION FAILED!');
                appendConsole('='.repeat(50));
                appendConsole('');
                appendConsole('Error: ' + (response.message || 'Unknown error'));
                appendConsole('');

                if (response.steps) {
                    Object.values(response.steps).forEach(function(step) {
                        updateStep(step.key, step.status, step.data);
                        appendConsole(`[${step.status.toUpperCase()}] ${step.message}`);
                        if (step.error) {
                            appendConsole('  └─ ERROR: ' + step.error);
                        }
                    });
                }

                $('#statusBadge').removeClass('bg-warning bg-success').addClass('bg-danger').text('Failed');
                toastr.error('Simulation failed: ' + (response.message || 'Unknown error'));
            },
            complete: function() {
                isRunning = false;
                btn.prop('disabled', false).html('<i class="fas fa-play me-2"></i>Start Auto-Test');
            }
        });
    });
});
</script>
@endpush
