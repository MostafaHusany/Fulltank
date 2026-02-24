<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Google_Client;
use Google_Service_Drive;

class GoogleDriveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Storage::extend('google', function ($app, $config) {
            $client = new Google_Client();
            $client->setAuthConfig($config['serviceAccountJsonFilePath']);
            $client->addScope(Google_Service_Drive::DRIVE);
            
            $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

            $service = new Google_Service_Drive($client);

            $options = [];
            if (isset($config['folderId'])) {
                $options['teamDriveId'] = $config['folderId'];
            }

            $adapter = new GoogleDriveAdapter($service, $config['folderId'] ?? null);

            // return new Filesystem($adapter);
            
            $driver = new Filesystem($adapter);
            
            return new FilesystemAdapter($driver, $adapter, $config);
        });

    }
}
