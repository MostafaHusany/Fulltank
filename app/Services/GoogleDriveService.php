<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use GuzzleHttp\Client as GuzzleClient;

class GoogleDriveService
{
    private $service;

    public function __construct(bool $disableSSL = true)
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google/service-account.json'));
        $client->addScope(Google_Service_Drive::DRIVE);

        if ($disableSSL) {
            // إنشاء Guzzle client مع تعطيل SSL مؤقت
            
            $guzzleClient = new GuzzleClient([
                'verify' => false,
            ]);

            $client->setHttpClient($guzzleClient);
        }

        $this->service = new Google_Service_Drive($client);
    }

    public function listFilesInFolder($folderId)
    {
        $response = $this->service->files->listFiles([
            'q' => "'{$folderId}' in parents and trashed = false",
            'fields' => 'files(id, name, mimeType, webViewLink, webContentLink)',
        ]);

        return $response->files;
    }

    public function getFile($fileId)
    {
        return $this->service->files->get($fileId, [
            'fields' => 'id, name, mimeType, webViewLink, webContentLink'
        ]);
    }
}
