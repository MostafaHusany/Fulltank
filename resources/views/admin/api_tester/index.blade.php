@extends('layouts.admin.app')

@push('title')
    <h4 class="h4">API Test Lab</h4>
@endpush

@push('custome-plugin')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<style>
    .endpoint-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    .endpoint-card:hover, .endpoint-card.active {
        background-color: #f8f9fa;
        border-left-color: var(--bs-primary);
    }
    .endpoint-card.active {
        background-color: #e9ecef;
    }
    .method-badge {
        font-size: 0.7rem;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .method-post { background-color: #49cc90; color: #fff; }
    .method-get { background-color: #61affe; color: #fff; }
    .response-panel {
        background-color: #1e1e1e;
        color: #d4d4d4;
        border-radius: 8px;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 0.85rem;
        min-height: 300px;
        max-height: 500px;
        overflow: auto;
    }
    .response-panel.success { border: 2px solid #28a745; }
    .response-panel.error { border: 2px solid #dc3545; }
    .status-badge {
        font-size: 1rem;
        padding: 4px 12px;
    }
    .token-field {
        font-family: monospace;
        font-size: 0.8rem;
    }
    .test-form-section {
        display: none;
    }
    .test-form-section.active {
        display: block;
    }
    .golden-loop-hint {
        background: linear-gradient(135deg, #ffd700, #ffb347);
        color: #333;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
    }
    pre.json-output {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar: Endpoint Selector --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-code me-2"></i>API Endpoints
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item endpoint-card" data-endpoint="driver-login">
                            <span class="method-badge method-post">POST</span>
                            <span class="ms-2">Driver Login</span>
                        </div>
                        <div class="list-group-item endpoint-card" data-endpoint="driver-request">
                            <span class="method-badge method-post">POST</span>
                            <span class="ms-2">Create Fueling Request</span>
                        </div>
                        <div class="list-group-item endpoint-card" data-endpoint="driver-active">
                            <span class="method-badge method-get">GET</span>
                            <span class="ms-2">Get Active Request</span>
                        </div>
                        <div class="list-group-item endpoint-card" data-endpoint="worker-login">
                            <span class="method-badge method-post">POST</span>
                            <span class="ms-2">Worker Login</span>
                        </div>
                        <div class="list-group-item endpoint-card" data-endpoint="worker-verify">
                            <span class="method-badge method-post">POST</span>
                            <span class="ms-2">Verify OTP</span>
                        </div>
                        <div class="list-group-item endpoint-card" data-endpoint="worker-confirm">
                            <span class="method-badge method-post">POST</span>
                            <span class="ms-2">Confirm & Execute</span>
                        </div>
                        <div class="list-group-item endpoint-card" data-endpoint="nearby-stations">
                            <span class="method-badge method-get">GET</span>
                            <span class="ms-2">Nearby Stations</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Full Cycle Simulator --}}
            <div class="card shadow-sm mt-3 bg-warning bg-opacity-10 border-warning">
                <div class="card-body text-center py-3">
                    <i class="fas fa-rocket fa-2x text-warning mb-2"></i>
                    <p class="mb-2 small">Run a complete fueling cycle with auto-generated test data</p>
                    <a href="{{ route('admin.apiSimulator.index') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-play me-1"></i>Full-Cycle Simulator
                    </a>
                </div>
            </div>

            {{-- Token Management --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-key me-2"></i>Token Management
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Driver Token</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control token-field" id="driverToken" placeholder="No token" readonly>
                            <button class="btn btn-outline-danger btn-sm" type="button" onclick="clearToken('driver')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Worker Token</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control token-field" id="workerToken" placeholder="No token" readonly>
                            <button class="btn btn-outline-danger btn-sm" type="button" onclick="clearToken('worker')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main: Test Bench --}}
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-flask me-2"></i>
                        <span id="currentEndpointTitle">Select an Endpoint</span>
                    </span>
                    <span class="badge bg-secondary" id="currentEndpointPath">/api/mobile/...</span>
                </div>
                <div class="card-body position-relative">
                    <div id="loadingOverlay" class="loading-overlay d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>

                    {{-- Default State --}}
                    <div id="defaultState" class="text-center py-5">
                        <i class="fas fa-arrow-left fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Select an endpoint from the sidebar to begin testing</p>
                    </div>

                    {{-- Driver Login Form --}}
                    <div class="test-form-section" id="form-driver-login">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Select Driver</label>
                                    <select class="form-select" id="loginDriverSelect"></select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="text" class="form-control" id="loginDriverPassword" value="123456">
                                    <small class="text-muted">Default test password</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary" onclick="runDriverLogin()">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login via API
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="quickLoginDriver()">
                                        <i class="fas fa-bolt me-2"></i>Quick Token (Skip Password)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Worker Login Form --}}
                    <div class="test-form-section" id="form-worker-login">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Select Worker</label>
                                    <select class="form-select" id="loginWorkerSelect"></select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="text" class="form-control" id="loginWorkerPassword" value="123456">
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success" onclick="runWorkerLogin()">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login via API
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="quickLoginWorker()">
                                        <i class="fas fa-bolt me-2"></i>Quick Token (Skip Password)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Create Fueling Request Form --}}
                    <div class="test-form-section" id="form-driver-request">
                        <div class="golden-loop-hint mb-3">
                            <i class="fas fa-star me-2"></i>
                            <strong>Golden Loop:</strong> After success, OTP will auto-fill in Worker Verify form!
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Fuel Type</label>
                                    <select class="form-select" id="requestFuelType"></select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Amount (Liters)</label>
                                    <input type="number" class="form-control" id="requestAmount" value="20" min="1" max="500">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Price Preview</label>
                                    <input type="text" class="form-control" id="requestPricePreview" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="requestLat" value="24.7136">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="requestLng" value="46.6753">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary" onclick="runCreateRequest()">
                            <i class="fas fa-gas-pump me-2"></i>Create Fueling Request
                        </button>
                    </div>

                    {{-- Get Active Request Form --}}
                    <div class="test-form-section" id="form-driver-active">
                        <p class="text-muted mb-3">This will fetch the currently active fueling request for the logged-in driver.</p>
                        <button class="btn btn-info" onclick="runGetActiveRequest()">
                            <i class="fas fa-search me-2"></i>Get Active Request
                        </button>
                    </div>

                    {{-- Worker Verify OTP Form --}}
                    <div class="test-form-section" id="form-worker-verify">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">OTP Code</label>
                                    <input type="text" class="form-control form-control-lg text-center" id="verifyOtpCode" 
                                           maxlength="6" placeholder="000000" style="letter-spacing: 8px; font-weight: bold;">
                                </div>
                                <button class="btn btn-warning" onclick="runVerifyOtp()">
                                    <i class="fas fa-check-circle me-2"></i>Verify OTP
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Worker Confirm Form --}}
                    <div class="test-form-section" id="form-worker-confirm">
                        <div class="golden-loop-hint mb-3">
                            <i class="fas fa-star me-2"></i>
                            <strong>Golden Loop:</strong> Request ID auto-filled from successful verification!
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Request ID</label>
                                    <input type="number" class="form-control" id="confirmRequestId" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Actual Liters Dispensed</label>
                                    <input type="number" class="form-control" id="confirmActualLiters" value="20" step="0.1">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-success" onclick="runConfirmFueling()">
                            <i class="fas fa-check-double me-2"></i>Confirm & Execute Fueling
                        </button>
                    </div>

                    {{-- Nearby Stations Form --}}
                    <div class="test-form-section" id="form-nearby-stations">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="stationsLat" value="24.7136">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="stationsLng" value="46.6753">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Radius (km)</label>
                                    <input type="number" class="form-control" id="stationsRadius" value="10" min="1" max="50">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fuel Type (Optional)</label>
                                    <select class="form-select" id="stationsFuelType">
                                        <option value="">All Types</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-info" onclick="runNearbyStations()">
                            <i class="fas fa-map-marker-alt me-2"></i>Find Nearby Stations
                        </button>
                    </div>
                </div>
            </div>

            {{-- Response Panel --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-terminal me-2"></i>Response</span>
                    <div>
                        <span class="badge status-badge bg-secondary" id="responseStatus">-</span>
                        <span class="badge bg-light text-dark ms-2" id="responseTime">-</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="response-panel p-3" id="responsePanel">
                        <pre class="json-output" id="responseBody">// Response will appear here...</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Configure toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };
    const API_BASE = '/api/mobile';
    let lastRequestId = null;
    let lastOtpCode = null;
    let selectedFuelTypePrice = 0;

    // Initialize Select2 for all selects
    initSelect2();
    loadFuelTypes();

    // Endpoint card click handlers
    $('.endpoint-card').on('click', function() {
        $('.endpoint-card').removeClass('active');
        $(this).addClass('active');
        
        const endpoint = $(this).data('endpoint');
        showEndpointForm(endpoint);
    });

    function initSelect2() {
        $('#loginDriverSelect').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search for a driver...',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.apiTester.drivers") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { q: params.term }; },
                processResults: function(data) { return data; }
            }
        });

        $('#loginWorkerSelect').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search for a worker...',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.apiTester.workers") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { q: params.term }; },
                processResults: function(data) { return data; }
            }
        });

        $('#requestFuelType, #stationsFuelType').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select fuel type...'
        });
    }

    function loadFuelTypes() {
        $.get('{{ route("admin.apiTester.fuelTypes") }}', function(data) {
            let options = '<option value="">Select Fuel Type</option>';
            let stationOptions = '<option value="">All Types</option>';
            data.results.forEach(function(type) {
                options += `<option value="${type.id}" data-price="${type.price}">${type.text}</option>`;
                stationOptions += `<option value="${type.id}">${type.text}</option>`;
            });
            $('#requestFuelType').html(options);
            $('#stationsFuelType').html(stationOptions);
        });
    }

    $('#requestFuelType').on('change', function() {
        selectedFuelTypePrice = parseFloat($(this).find(':selected').data('price')) || 0;
        updatePricePreview();
    });

    $('#requestAmount').on('input', function() {
        updatePricePreview();
    });

    function updatePricePreview() {
        const amount = parseFloat($('#requestAmount').val()) || 0;
        const total = (amount * selectedFuelTypePrice).toFixed(2);
        $('#requestPricePreview').val(total + ' SAR');
    }

    function showEndpointForm(endpoint) {
        $('#defaultState').hide();
        $('.test-form-section').removeClass('active');
        
        const endpointMap = {
            'driver-login': { title: 'Driver Login', path: '/login', form: 'form-driver-login' },
            'worker-login': { title: 'Worker Login', path: '/login', form: 'form-worker-login' },
            'driver-request': { title: 'Create Fueling Request', path: '/driver/request', form: 'form-driver-request' },
            'driver-active': { title: 'Get Active Request', path: '/driver/request/active', form: 'form-driver-active' },
            'worker-verify': { title: 'Verify OTP', path: '/worker/verify-request', form: 'form-worker-verify' },
            'worker-confirm': { title: 'Confirm & Execute', path: '/worker/confirm-fueling', form: 'form-worker-confirm' },
            'nearby-stations': { title: 'Nearby Stations', path: '/driver/nearby-stations', form: 'form-nearby-stations' },
        };

        const config = endpointMap[endpoint];
        if (config) {
            $('#currentEndpointTitle').text(config.title);
            $('#currentEndpointPath').text(API_BASE + config.path);
            $('#' + config.form).addClass('active');
        }
    }

    window.clearToken = function(type) {
        if (type === 'driver') {
            $('#driverToken').val('');
        } else {
            $('#workerToken').val('');
        }
    };

    function setLoading(loading) {
        if (loading) {
            $('#loadingOverlay').removeClass('d-none');
        } else {
            $('#loadingOverlay').addClass('d-none');
        }
    }

    function displayResponse(response, status, time) {
        const panel = $('#responsePanel');
        const statusBadge = $('#responseStatus');
        const timeBadge = $('#responseTime');

        panel.removeClass('success error');
        
        if (status >= 200 && status < 300) {
            panel.addClass('success');
            statusBadge.removeClass('bg-danger bg-secondary').addClass('bg-success');
        } else {
            panel.addClass('error');
            statusBadge.removeClass('bg-success bg-secondary').addClass('bg-danger');
        }

        statusBadge.text(status);
        timeBadge.text(time + 'ms');
        
        let json = typeof response === 'string' ? response : JSON.stringify(response, null, 2);
        json = syntaxHighlight(json);
        $('#responseBody').html(json);
    }

    function syntaxHighlight(json) {
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'color: #ce9178;';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'color: #9cdcfe;';
                } else {
                    cls = 'color: #ce9178;';
                }
            } else if (/true|false/.test(match)) {
                cls = 'color: #569cd6;';
            } else if (/null/.test(match)) {
                cls = 'color: #569cd6;';
            } else {
                cls = 'color: #b5cea8;';
            }
            return '<span style="' + cls + '">' + match + '</span>';
        });
    }

    function makeApiRequest(method, url, data, token) {
        setLoading(true);
        const startTime = Date.now();

        $.ajax({
            method: method,
            url: url,
            data: data,
            headers: {
                'Accept': 'application/json',
                'Authorization': token ? 'Bearer ' + token : ''
            },
            success: function(response) {
                const elapsed = Date.now() - startTime;
                displayResponse(response, 200, elapsed);
                handleGoldenLoop(url, response);
            },
            error: function(xhr) {
                const elapsed = Date.now() - startTime;
                displayResponse(xhr.responseJSON || xhr.responseText, xhr.status, elapsed);
            },
            complete: function() {
                setLoading(false);
            }
        });
    }

    function handleGoldenLoop(url, response) {
        if (url.includes('/driver/request') && !url.includes('active') && response.status && response.data) {
            lastRequestId = response.data.request_id;
            lastOtpCode = response.data.otp_code;
            
            $('#verifyOtpCode').val(lastOtpCode);
            
            toastr.success('OTP auto-filled in Worker Verify form!', 'Golden Loop');
        }
        
        if (url.includes('/worker/verify-request') && response.status && response.data) {
            $('#confirmRequestId').val(response.data.request_id);
            $('#confirmActualLiters').val(response.data.requested_liters);
            
            toastr.success('Request ID auto-filled in Confirm form!', 'Golden Loop');
        }

        if (url.includes('/login') && response.status && response.token) {
            const category = response.data?.category;
            if (category === 'driver') {
                $('#driverToken').val(response.token);
                toastr.success('Driver token saved!');
            } else if (category === 'worker') {
                $('#workerToken').val(response.token);
                toastr.success('Worker token saved!');
            }
        }
    }

    // API Actions
    window.runDriverLogin = function() {
        const driverData = $('#loginDriverSelect').select2('data')[0];
        if (!driverData) {
            toastr.error('Please select a driver');
            return;
        }

        if (!driverData.username) {
            toastr.error('Driver has no username set');
            return;
        }

        makeApiRequest('POST', API_BASE + '/login', {
            username: driverData.username,
            password: $('#loginDriverPassword').val()
        }, null);
    };

    window.runWorkerLogin = function() {
        const workerData = $('#loginWorkerSelect').select2('data')[0];
        if (!workerData) {
            toastr.error('Please select a worker');
            return;
        }

        if (!workerData.username) {
            toastr.error('Worker has no username set');
            return;
        }

        makeApiRequest('POST', API_BASE + '/login', {
            username: workerData.username,
            password: $('#loginWorkerPassword').val()
        }, null);
    };

    window.quickLoginDriver = function() {
        const driverData = $('#loginDriverSelect').select2('data')[0];
        if (!driverData) {
            toastr.error('Please select a driver');
            return;
        }

        setLoading(true);
        $.ajax({
            method: 'POST',
            url: '{{ route("admin.apiTester.quickLogin") }}',
            data: { user_id: driverData.id, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    $('#driverToken').val(response.token);
                    displayResponse(response, 200, 0);
                    toastr.success('Driver token generated (bypassed password)!');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Quick login failed');
            },
            complete: function() {
                setLoading(false);
            }
        });
    };

    window.quickLoginWorker = function() {
        const workerData = $('#loginWorkerSelect').select2('data')[0];
        if (!workerData) {
            toastr.error('Please select a worker');
            return;
        }

        setLoading(true);
        $.ajax({
            method: 'POST',
            url: '{{ route("admin.apiTester.quickLogin") }}',
            data: { user_id: workerData.id, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    $('#workerToken').val(response.token);
                    displayResponse(response, 200, 0);
                    toastr.success('Worker token generated (bypassed password)!');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Quick login failed');
            },
            complete: function() {
                setLoading(false);
            }
        });
    };

    window.runCreateRequest = function() {
        const token = $('#driverToken').val();
        if (!token) {
            toastr.error('Please login as driver first');
            return;
        }

        makeApiRequest('POST', API_BASE + '/driver/request', {
            fuel_type_id: $('#requestFuelType').val(),
            amount: $('#requestAmount').val(),
            latitude: $('#requestLat').val(),
            longitude: $('#requestLng').val()
        }, token);
    };

    window.runGetActiveRequest = function() {
        const token = $('#driverToken').val();
        if (!token) {
            toastr.error('Please login as driver first');
            return;
        }

        makeApiRequest('GET', API_BASE + '/driver/request/active', {}, token);
    };

    window.runVerifyOtp = function() {
        const token = $('#workerToken').val();
        if (!token) {
            toastr.error('Please login as worker first');
            return;
        }

        makeApiRequest('POST', API_BASE + '/worker/verify-request', {
            otp_code: $('#verifyOtpCode').val()
        }, token);
    };

    window.runConfirmFueling = function() {
        const token = $('#workerToken').val();
        if (!token) {
            toastr.error('Please login as worker first');
            return;
        }

        makeApiRequest('POST', API_BASE + '/worker/confirm-fueling', {
            request_id: $('#confirmRequestId').val(),
            actual_liters: $('#confirmActualLiters').val()
        }, token);
    };

    window.runNearbyStations = function() {
        const token = $('#driverToken').val();
        if (!token) {
            toastr.error('Please login as driver first');
            return;
        }

        let params = {
            lat: $('#stationsLat').val(),
            lng: $('#stationsLng').val(),
            radius: $('#stationsRadius').val()
        };

        const fuelType = $('#stationsFuelType').val();
        if (fuelType) params.fuel_type_id = fuelType;

        makeApiRequest('GET', API_BASE + '/driver/nearby-stations?' + $.param(params), {}, token);
    };
});
</script>
@endpush
