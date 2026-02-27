<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home',         [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('/feedback',    App\Http\Controllers\FeedbackController::class,)->only(['index', 'store']);
Route::resource('/questionar', App\Http\Controllers\QuestionarController::class,)->only(['index', 'store']);

Route::group([
    'middleware' => ['auth:web', 'admin.permissions'], 
    'namespace'  => 'App\Http\Controllers\Admin',
    'prefix'     => LaravelLocalization::setLocale() . '/admin'
], function () {

    Route::get('error',     [App\Http\Controllers\Admin\ErrorsController::class, 'has_no_permission'])->name('admin.error.no_permission');
    Route::get('disabled',  [App\Http\Controllers\Admin\ErrorsController::class, 'account_is_disabled'])->name('admin.error.is_disabled');

    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard.index');

    Route::get('my-profile',  [App\Http\Controllers\Admin\UserController::class, 'myProfile'])->name('admin.profile.index');
    Route::post('my-profile', [App\Http\Controllers\Admin\UserController::class, 'updateProfile'])->name('admin.profile.update');

    Route::resource('users', UserController::class, [
        'names' => [
            'index'     => 'admin.users.index',
            'store'     => 'admin.users.store',
            'show'      => 'admin.users.show',
            'edit'      => 'admin.users.edit',
            'update'    => 'admin.users.update',
            'destroy'   => 'admin.users.destroy'
        ]
    ]);

    Route::resource('roles', RoleController::class, [
        'names' => [
            'index'     => 'admin.roles.index',
            'store'     => 'admin.roles.store',
            'show'      => 'admin.roles.show',
            'edit'      => 'admin.roles.edit',
            'update'    => 'admin.roles.update',
            'destroy'   => 'admin.roles.destroy'
        ]
    ]);

    Route::resource('clients', ClientController::class, [
        'names' => [
            'index'   => 'admin.clients.index',
            'store'   => 'admin.clients.store',
            'show'    => 'admin.clients.show',
            'edit'    => 'admin.clients.edit',
            'update'  => 'admin.clients.update',
            'destroy' => 'admin.clients.destroy'
        ]
    ]);

    Route::resource('vehicles', VehicleController::class, [
        'names' => [
            'index'   => 'admin.vehicles.index',
            'store'   => 'admin.vehicles.store',
            'show'    => 'admin.vehicles.show',
            'edit'    => 'admin.vehicles.edit',
            'update'  => 'admin.vehicles.update',
            'destroy' => 'admin.vehicles.destroy'
        ]
    ]);

    Route::resource('drivers', DriverController::class, [
        'names' => [
            'index'   => 'admin.drivers.index',
            'store'   => 'admin.drivers.store',
            'show'    => 'admin.drivers.show',
            'edit'    => 'admin.drivers.edit',
            'update'  => 'admin.drivers.update',
            'destroy' => 'admin.drivers.destroy'
        ]
    ]);

    Route::get('clients/{clientId}/documents', [App\Http\Controllers\Admin\ClientController::class, 'indexDocuments'])->name('admin.clients.documents.index');
    Route::post('clients/{clientId}/documents', [App\Http\Controllers\Admin\ClientController::class, 'storeDocument'])->name('admin.clients.documents.store');
    Route::get('client-documents/{documentId}/view', [App\Http\Controllers\Admin\ClientController::class, 'viewDocument'])->name('admin.clients.documents.view');
    Route::get('client-documents/{documentId}/download', [App\Http\Controllers\Admin\ClientController::class, 'downloadDocument'])->name('admin.clients.documents.download');
    Route::delete('client-documents/{documentId}', [App\Http\Controllers\Admin\ClientController::class, 'destroyDocument'])->name('admin.clients.documents.destroy');

    // START SETTINGS ROUTES
    Route::resource('users-files', UserFileContoller::class, [
        'names' => [
            'index'     => 'admin.usersFiles.index',
            'store'     => 'admin.usersFiles.store',
            'show'      => 'admin.usersFiles.show',
            'destroy'   => 'admin.usersFiles.destroy'
        ]
    ])->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::resource('draft', Settings\DraftController::class, [
        'names' => [
            'index'   => 'admin.draft.index',
            'store'   => 'admin.draft.store',
            'show'    => 'admin.draft.show',
            'destroy' => 'admin.draft.destroy'
        ]
    ])->only(['index', 'store', 'show', 'destroy']);

    Route::resource('settings', SettingController::class, [
        'names' => [
            'index'   => 'admin.settings.index',
            'store'   => 'admin.settings.store'
        ]
    ]);

    // FAST AJAX SEARCH
    Route::get('/users-search',                         [App\Http\Controllers\Admin\UserController::class,                   'dataAjax'])->name('admin.search.users');
    
    Route::get('/media-files-search',                   [App\Http\Controllers\Admin\MediaFileController::class,              'dataAjax'])->name('admin.search.mediaFiles');
    Route::get('/roles-search',                         [App\Http\Controllers\Admin\RoleController::class,                   'roleAjax'])->name('admin.search.roles');
    Route::get('/permissions-search',                   [App\Http\Controllers\Admin\RoleController::class,                   'permissionAjax'])->name('admin.search.permissions');
    Route::get('/draft-search',                         [App\Http\Controllers\Admin\Settings\DraftController::class,         'dataAjax'])->name('admin.search.drafts');
    Route::get('/clients-search',                       [App\Http\Controllers\Admin\ClientController::class,                  'dataAjax'])->name('admin.search.clients');
    Route::get('/client-categories-search',             [App\Http\Controllers\Admin\ClientController::class,                  'categoriesAjax'])->name('admin.search.clientCategories');
    Route::get('/vehicles-by-client',                    [App\Http\Controllers\Admin\DriverController::class,                 'vehiclesByClient'])->name('admin.search.vehiclesByClient');

    Route::resource('governorates', App\Http\Controllers\Admin\GovernorateController::class, [
        'names' => [
            'index'   => 'admin.governorates.index',
            'store'   => 'admin.governorates.store',
            'show'    => 'admin.governorates.show',
            'edit'    => 'admin.governorates.edit',
            'update'  => 'admin.governorates.update',
            'destroy' => 'admin.governorates.destroy'
        ]
    ]);
    Route::post('governorates/districts', [App\Http\Controllers\Admin\GovernorateController::class, 'storeDistrict'])->name('admin.governorates.districts.store');
    Route::put('governorates/districts/{id}', [App\Http\Controllers\Admin\GovernorateController::class, 'updateDistrict'])->name('admin.governorates.districts.update');
    Route::delete('governorates/districts/{id}', [App\Http\Controllers\Admin\GovernorateController::class, 'destroyDistrict'])->name('admin.governorates.districts.destroy');
    Route::get('governorates-search', [App\Http\Controllers\Admin\GovernorateController::class, 'dataAjax'])->name('admin.search.governorates');
    Route::get('districts-search', [App\Http\Controllers\Admin\GovernorateController::class, 'districtsAjax'])->name('admin.search.districts');

    Route::get('stations', [App\Http\Controllers\Admin\StationController::class, 'index'])->name('admin.stations.index');
    Route::post('stations', [App\Http\Controllers\Admin\StationController::class, 'store'])->name('admin.stations.store');
    Route::get('stations/{id}', [App\Http\Controllers\Admin\StationController::class, 'show'])->name('admin.stations.show');
    Route::put('stations/{id}', [App\Http\Controllers\Admin\StationController::class, 'update'])->name('admin.stations.update');
    Route::delete('stations/{id}', [App\Http\Controllers\Admin\StationController::class, 'destroy'])->name('admin.stations.destroy');
    Route::put('stations/{id}/toggle-account', [App\Http\Controllers\Admin\StationController::class, 'toggleAccountStatus'])->name('admin.stations.toggleAccount');

    Route::get('vehicle-quotas', [App\Http\Controllers\Admin\VehicleQuotaController::class, 'index'])->name('admin.vehicleQuotas.index');
    Route::get('vehicle-quotas/vehicles', [App\Http\Controllers\Admin\VehicleQuotaController::class, 'vehicles'])->name('admin.vehicleQuotas.vehicles');
    Route::put('vehicle-quotas/{id}', [App\Http\Controllers\Admin\VehicleQuotaController::class, 'update'])->name('admin.vehicleQuotas.update');
    Route::post('vehicle-quotas/bulk', [App\Http\Controllers\Admin\VehicleQuotaController::class, 'bulkAllocate'])->name('admin.vehicleQuotas.bulk');

    Route::get('wallets', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('admin.wallets.index');
    Route::post('wallets/deposit', [App\Http\Controllers\Admin\WalletController::class, 'deposit'])->name('admin.wallets.deposit');
    Route::put('wallets/{walletId}/toggle-status', [App\Http\Controllers\Admin\WalletController::class, 'toggleStatus'])->name('admin.wallets.toggleStatus');
    Route::get('wallets/{walletId}/transactions', [App\Http\Controllers\Admin\WalletController::class, 'transactions'])->name('admin.wallets.transactions');

    Route::get('deposit-requests', [App\Http\Controllers\Admin\DepositRequestController::class, 'index'])->name('admin.depositRequests.index');
    Route::get('deposit-requests/{id}/proof-image', [App\Http\Controllers\Admin\DepositRequestController::class, 'viewProofImage'])->name('admin.depositRequests.viewProofImage');
    Route::post('deposit-requests', [App\Http\Controllers\Admin\DepositRequestController::class, 'store'])->name('admin.depositRequests.store');
    Route::put('deposit-requests/{id}', [App\Http\Controllers\Admin\DepositRequestController::class, 'update'])->name('admin.depositRequests.update');
    Route::post('deposit-requests/{id}/generate-balance', [App\Http\Controllers\Admin\DepositRequestController::class, 'generateBalance'])->name('admin.depositRequests.generateBalance');
    Route::get('deposit-requests/calculate-fee', [App\Http\Controllers\Admin\DepositRequestController::class, 'calculateFee'])->name('admin.depositRequests.calculateFee');
    Route::get('deposit-requests/analytics', [App\Http\Controllers\Admin\DepositRequestController::class, 'analytics'])->name('admin.depositRequests.analytics');
    Route::get('deposit-requests/{id}/generated-record', [App\Http\Controllers\Admin\DepositRequestController::class, 'generatedRecord'])->name('admin.depositRequests.generatedRecord');

    Route::get('financial-settings', [App\Http\Controllers\Admin\FinancialSettingsController::class, 'index'])->name('admin.financialSettings.index');
    Route::get('payment-methods', [App\Http\Controllers\Admin\PaymentMethodController::class, 'index'])->name('admin.paymentMethods.index');
    Route::post('payment-methods', [App\Http\Controllers\Admin\PaymentMethodController::class, 'store'])->name('admin.paymentMethods.store');
    Route::put('payment-methods/{id}', [App\Http\Controllers\Admin\PaymentMethodController::class, 'update'])->name('admin.paymentMethods.update');
    Route::delete('payment-methods/{id}', [App\Http\Controllers\Admin\PaymentMethodController::class, 'destroy'])->name('admin.paymentMethods.destroy');
    Route::get('payment-methods-list', [App\Http\Controllers\Admin\PaymentMethodController::class, 'listActive'])->name('admin.paymentMethods.list');
    Route::get('financial-settings/fee', [App\Http\Controllers\Admin\FinancialSettingController::class, 'index'])->name('admin.financialSettings.fee');
    Route::put('financial-settings/fee', [App\Http\Controllers\Admin\FinancialSettingController::class, 'update'])->name('admin.financialSettings.updateFee');

    Route::get('fuel-types', [App\Http\Controllers\Admin\FuelTypeController::class, 'index'])->name('admin.fuelTypes.index');
    Route::post('fuel-types', [App\Http\Controllers\Admin\FuelTypeController::class, 'store'])->name('admin.fuelTypes.store');
    Route::get('fuel-types/{id}', [App\Http\Controllers\Admin\FuelTypeController::class, 'show'])->name('admin.fuelTypes.show');
    Route::put('fuel-types/{id}', [App\Http\Controllers\Admin\FuelTypeController::class, 'update'])->name('admin.fuelTypes.update');
    Route::delete('fuel-types/{id}', [App\Http\Controllers\Admin\FuelTypeController::class, 'destroy'])->name('admin.fuelTypes.destroy');
    Route::put('fuel-types/{id}/toggle-status', [App\Http\Controllers\Admin\FuelTypeController::class, 'toggleStatus'])->name('admin.fuelTypes.toggleStatus');
    Route::get('fuel-types-list', [App\Http\Controllers\Admin\FuelTypeController::class, 'listActive'])->name('admin.fuelTypes.list');

    Route::get('station-wallets', [App\Http\Controllers\Admin\StationWalletController::class, 'index'])->name('admin.stationWallets.index');
    Route::put('station-wallets/{walletId}/toggle-status', [App\Http\Controllers\Admin\StationWalletController::class, 'toggleStatus'])->name('admin.stationWallets.toggleStatus');
    Route::get('station-wallets/{walletId}/transactions', [App\Http\Controllers\Admin\StationWalletController::class, 'transactions'])->name('admin.stationWallets.transactions');

    Route::get('station-workers', [App\Http\Controllers\Admin\StationWorkerController::class, 'index'])->name('admin.stationWorkers.index');
    Route::post('station-workers', [App\Http\Controllers\Admin\StationWorkerController::class, 'store'])->name('admin.stationWorkers.store');
    Route::get('station-workers/{id}', [App\Http\Controllers\Admin\StationWorkerController::class, 'show'])->name('admin.stationWorkers.show');
    Route::put('station-workers/{id}', [App\Http\Controllers\Admin\StationWorkerController::class, 'update'])->name('admin.stationWorkers.update');
    Route::delete('station-workers/{id}', [App\Http\Controllers\Admin\StationWorkerController::class, 'destroy'])->name('admin.stationWorkers.destroy');

    Route::get('fuel-transactions', [App\Http\Controllers\Admin\FuelTransactionController::class, 'index'])->name('admin.fuelTransactions.index');
    Route::post('fuel-transactions', [App\Http\Controllers\Admin\FuelTransactionController::class, 'store'])->name('admin.fuelTransactions.store');
    Route::get('fuel-transactions/{id}', [App\Http\Controllers\Admin\FuelTransactionController::class, 'show'])->name('admin.fuelTransactions.show');
    Route::put('fuel-transactions/{id}', [App\Http\Controllers\Admin\FuelTransactionController::class, 'update'])->name('admin.fuelTransactions.update');
    Route::get('fuel-transactions/{id}/meter-image', [App\Http\Controllers\Admin\FuelTransactionController::class, 'viewMeterImage'])->name('admin.fuelTransactions.viewImage');

    Route::get('settlements', [App\Http\Controllers\Admin\SettlementController::class, 'index'])->name('admin.settlements.index');
    Route::post('settlements', [App\Http\Controllers\Admin\SettlementController::class, 'store'])->name('admin.settlements.store');
    Route::get('settlements/{id}', [App\Http\Controllers\Admin\SettlementController::class, 'show'])->name('admin.settlements.show');
    Route::get('settlements/{id}/receipt', [App\Http\Controllers\Admin\SettlementController::class, 'viewReceipt'])->name('admin.settlements.viewReceipt');

    Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::get('dashboard/chart-data', [App\Http\Controllers\Admin\DashboardController::class, 'getChartData'])->name('admin.dashboard.chartData');
    Route::get('dashboard/map-data', [App\Http\Controllers\Admin\DashboardController::class, 'getMapData'])->name('admin.dashboard.mapData');
    Route::get('dashboard/stats', [App\Http\Controllers\Admin\DashboardController::class, 'getStats'])->name('admin.dashboard.stats');

    Route::get('reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('reports/client-statement', [App\Http\Controllers\Admin\ReportController::class, 'clientStatement'])->name('admin.reports.clientStatement');
    Route::get('reports/station-report', [App\Http\Controllers\Admin\ReportController::class, 'stationReport'])->name('admin.reports.stationReport');
    Route::get('reports/vehicle-consumption', [App\Http\Controllers\Admin\ReportController::class, 'vehicleConsumption'])->name('admin.reports.vehicleConsumption');
    Route::get('reports/vehicle-detail', [App\Http\Controllers\Admin\ReportController::class, 'vehicleDetail'])->name('admin.reports.vehicleDetail');
    Route::get('reports/overall-summary', [App\Http\Controllers\Admin\ReportController::class, 'overallSummary'])->name('admin.reports.overallSummary');
    Route::get('reports/export-pdf', [App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])->name('admin.reports.exportPdf');

    Route::get('stations-search', [App\Http\Controllers\Admin\StationController::class, 'dataAjax'])->name('admin.search.stations');
    Route::get('vehicles-search', [App\Http\Controllers\Admin\VehicleController::class, 'dataAjax'])->name('admin.search.vehicles');

});


use Illuminate\Support\Facades\Http;

Route::get('/get-location', function () {
    $address = request('address');

    if (!$address) {
        return response()->json(['error' => 'No address provided'], 400);
    }

    $response = Http::withOptions([
        'verify' => false,
    ])->get('https://maps.googleapis.com/maps/api/geocode/json', [
        'address' => $address,
        'key' => env('GOOGLE_MAPS_KEYS'),
    ]);

    $data = $response->json();
    
    if (!isset($data['results'][0])) {
        return response()->json(['error' => 'Location not found'], 404);
    }

    $location = $data['results'][0]['geometry']['location'];

    return response()->json([
        'address' => $address,
        'latitude' => $location['lat'],
        'longitude' => $location['lng'],
    ]);
})->name('getLocation');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

Route::get('/upload-test', function () {
    return '
        <form action="/upload-test" method="POST" enctype="multipart/form-data">
            '.csrf_field().'
            <input type="file" name="file">
            <button type="submit">Upload</button>
        </form>
    ';
});

Route::post('/upload-test', function (Request $request) {
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->getClientOriginalName();

        // رفع الملف على Google Drive
        Storage::disk('google')->put($path, fopen($file->getRealPath(), 'r+'));

        // جلب قائمة الملفات للتأكد
        $files = Storage::disk('google')->listContents('/', false);
        
        // حوّل Generator إلى Array
        $files = iterator_to_array($files);
        dd($files);
        // هات آخر ملف
        $lastFile = end($files);

        // الـ fileId بتاع Google Drive
        $fileId = $lastFile['basename'];

        return "
            <p>تم رفع الملف بنجاح ✅</p>
            <p><b>File ID:</b> {$fileId}</p>
            <p>رابط العرض:</p>
            <iframe src='https://drive.google.com/file/d/{$fileId}/preview' width='640' height='480'></iframe>
        ";
    }

    return "لم يتم اختيار ملف!";
});

use GuzzleHttp\Client as GuzzleClient;

Route::get('/drive-list', function (Request $request) {
    // dd(storage_path('certs/cacert.pem'));
    $client = new \Google_Client();
    $client->setAuthConfig(storage_path('app/google/service-account.json'));
    $client->addScope(\Google_Service_Drive::DRIVE);
    
    $guzzleClient = new GuzzleClient([
        'verify' => false,
        // 'verify' => storage_path('certs/cacert.pem'),
    ]);

    $client->setHttpClient($guzzleClient);

    $token = $client->fetchAccessTokenWithAssertion();
    dd($token['access_token']);

    $service = new \Google_Service_Drive($client);

    // $folderId = '1hKExbHLPp1fhYJGIKSTp3GuO5HOK12Li';
    $folderId = '1y-p0832U_yITxDyjJuN3Ktwy0BtgWVqe';

    $response = $service->files->listFiles([
        'q' => "'{$folderId}' in parents and trashed = false",
        'fields' => 'files(id, name, mimeType)',
    ]);

    foreach ($response->files as $file) {
        echo "📄 {$file->name} ({$file->mimeType}) - ID: {$file->id}<br>";
    }

});

Route::get('/check-service-account', function () {
    $path = storage_path('app/google/service-account.json');
    if (!file_exists($path)) {
        return "❌ ملف service-account.json مش موجود في: $path";
    }

    $json = json_decode(file_get_contents($path), true);

    if (isset($json['client_email'])) {
        return "✅ Service Account Email: " . $json['client_email'];
    }

    return "❌ مفيش client_email في ملف JSON";
});

Route::get('/drive-test', function () {
    
    $client = new Google_Client();
    $client->setAuthConfig(storage_path('app/google/service-account.json'));
    $client->addScope(Google_Service_Drive::DRIVE);

    $service = new Google_Service_Drive($client);

    // $folderId = '1hKExbHLPp1fhYJGIKSTp3GuO5HOK12Li';
    $folderId = '1y-p0832U_yITxDyjJuN3Ktwy0BtgWVqe';

    $response = $service->files->listFiles([
        'q' => "'$folderId' in parents",
        'fields' => 'files(id, name, mimeType)'
    ]);

    dd(sizeof($response->files), $response->files);
});
