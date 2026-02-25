<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\ClientDocument;
use App\Models\User;

class ClientDocumentService
{
    private const STORAGE_PATH = 'private/clients/documents';

    /**
     * Store a client document and create DB record. Returns ClientDocument or null on failure.
     */
    public function store(User $client, Request $request): ?ClientDocument
    {
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return null;
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sizeInKb = $file->getSize() / 1024;

        $filename = $originalName . '_' . time() . '.' . $extension;
        $path = self::STORAGE_PATH . '/' . $filename;

        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        return ClientDocument::create([
            'client_id'   => $client->id,
            'title'       => $request->input('title', $file->getClientOriginalName()),
            'path'        => $path,
            'extension'   => $extension,
            'size_in_kb'  => round($sizeInKb, 2),
        ]);
    }

    /**
     * Delete file from storage and DB record.
     */
    public function delete(ClientDocument $document): bool
    {
        if (Storage::disk('local')->exists($document->path)) {
            Storage::disk('local')->delete($document->path);
        }
        return $document->delete();
    }

    /**
     * Full path for a document.
     */
    public function fullPath(ClientDocument $document): string
    {
        return storage_path('app/' . $document->path);
    }
}
