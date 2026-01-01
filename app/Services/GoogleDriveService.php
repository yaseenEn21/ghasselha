<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected Drive $drive;
    protected string $folderId;

    public function __construct()
    {
        // نقرأ مسار ملف الـ JSON من env
        $keyFilePath = env('GOOGLE_DRIVE_KEY_FILE', 'storage/app/google/service-account.json');

        $client = new Client();

        // نحول المسار لـ base_path لو هو نسبي
        $client->setAuthConfig(base_path($keyFilePath));

        // صلاحيات الوصول للـ Drive
        $client->setScopes([Drive::DRIVE]);

        // لو حاب تعمل impersonation لاحقًا بتحط ايميل يوزر GSuite
        // $client->setSubject('some-user@your-domain.com');

        $this->drive = new Drive($client);

        // ID الفولدر في درايف (تأكد إنه بدون query string)
        $this->folderId = env('GOOGLE_DRIVE_FOLDER_ID', '');
    }

    /**
     * رفع ملف إلى Google Drive مع لوج تفصيلي
     *
     * @return array [success => bool, data/ error => ...]
     */
    public function uploadWithDebug(UploadedFile $file): array
    {
        try {
            $driveFile = new Drive\DriveFile([
                'name' => $file->getClientOriginalName(),
                'parents' => $this->folderId ? [$this->folderId] : [],
            ]);

            $content = file_get_contents($file->getRealPath());

            $created = $this->drive->files->create(
                $driveFile,
                [
                    'data' => $content,
                    'mimeType' => $file->getMimeType(),
                    'uploadType' => 'multipart',
                    'fields' => 'id, name, parents, webViewLink, webContentLink, mimeType, size',
                ]
            );

            $result = [
                'success' => true,
                'file_id' => $created->id,
                'name' => $created->name,
                'parents' => $created->parents,
                'mimeType' => $created->mimeType,
                'size' => $created->size,
                'webViewLink' => $created->webViewLink ?? null,
                'webContentLink' => $created->webContentLink ?? null,
            ];

            Log::info('Google Drive upload success', $result);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Google Drive upload failed', [
                'message' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ];
        }
    }
}