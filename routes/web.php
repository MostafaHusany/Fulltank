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

    Route::resource('districts', DistrictController::class, [
        'names' => [
            'index'   => 'admin.districts.index',
            'store'   => 'admin.districts.store',
            'show'    => 'admin.districts.show',
            'edit'    => 'admin.districts.edit',
            'update'  => 'admin.districts.update',
            'destroy' => 'admin.districts.destroy'
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
    Route::get('/districts-search',                     [App\Http\Controllers\Admin\DistrictController::class,               'dataAjax'])->name('admin.search.districts');
    Route::get('/clients-search',                       [App\Http\Controllers\Admin\ClientController::class,                  'dataAjax'])->name('admin.search.clients');
    Route::get('/client-categories-search',             [App\Http\Controllers\Admin\ClientController::class,                  'categoriesAjax'])->name('admin.search.clientCategories');

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
