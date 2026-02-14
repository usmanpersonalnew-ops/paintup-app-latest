<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected Google_Client $client;
    protected Google_Service_Drive $drive;
    
    public function __construct()
    {
        $this->initializeClient();
    }
    
    /**
     * Check if Google Drive is connected
     */
    public function isConnected(): bool
    {
        $tokenPath = storage_path('app/google-drive-token.json');
        return file_exists($tokenPath);
    }
    
    /**
     * Initialize Google Client with stored token
     */
    protected function initializeClient(): void
    {
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        
        $tokenPath = storage_path('app/google-drive-token.json');
        
        if (!file_exists($tokenPath)) {
            throw new \Exception('Google Drive is not connected. Please ask an admin to authenticate at /admin/google-drive/auth');
        }
        
        $token = json_decode(file_get_contents($tokenPath), true);
        
        if (empty($token)) {
            throw new \Exception('Google Drive token is empty. Please re-authenticate at /admin/google-drive/auth');
        }
        
        // Refresh token if expired
        if ($this->client->isAccessTokenExpired()) {
            if (isset($token['refresh_token'])) {
                $this->client->refreshToken($token['refresh_token']);
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
                
                if (isset($newToken['error'])) {
                    throw new \Exception('Failed to refresh Google Drive token: ' . $newToken['error'] . '. Please re-authenticate.');
                }
                
                // Save refreshed token
                Storage::put('google-drive-token.json', json_encode($newToken));
                $token = $newToken;
            } else {
                throw new \Exception('Google Drive access token expired. Please re-authenticate at /admin/google-drive/auth');
            }
        }
        
        $this->client->setAccessToken($token);
        $this->drive = new Google_Service_Drive($this->client);
    }
    
    /**
     * Get or create PaintUp folder
     */
    protected function getPaintUpFolderId(): string
    {
        $folderName = 'PaintUp';
        
        // Search for existing folder
        $response = $this->drive->files->listFiles([
            'q' => "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id)',
        ]);
        
        if (!empty($response->getFiles())) {
            return $response->getFiles()[0]->getId();
        }
        
        // Create new folder
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($folderName);
        $file->setMimeType('application/vnd.google-apps.folder');
        
        $createdFile = $this->drive->files->create($file);
        
        return $createdFile->getId();
    }
    
    /**
     * Get or create Project folder
     */
    protected function getProjectFolderId(int $projectId): string
    {
        $folderName = "Project-{$projectId}";
        
        // Search for existing folder
        $response = $this->drive->files->listFiles([
            'q' => "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id)',
        ]);
        
        if (!empty($response->getFiles())) {
            return $response->getFiles()[0]->getId();
        }
        
        // Create new folder inside PaintUp folder
        $paintUpFolderId = $this->getPaintUpFolderId();
        
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($folderName);
        $file->setMimeType('application/vnd.google-apps.folder');
        $file->setParents([$paintUpFolderId]);
        
        $createdFile = $this->drive->files->create($file);
        
        return $createdFile->getId();
    }
    
    /**
     * Get or create Site Photos folder
     */
    protected function getSitePhotosFolderId(int $projectId): string
    {
        $folderName = 'Site Photos';
        $projectFolderId = $this->getProjectFolderId($projectId);
        
        // Search for existing folder
        $response = $this->drive->files->listFiles([
            'q' => "name='{$folderName}' and '{$projectFolderId}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id)',
        ]);
        
        if (!empty($response->getFiles())) {
            return $response->getFiles()[0]->getId();
        }
        
        // Create new folder
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($folderName);
        $file->setMimeType('application/vnd.google-apps.folder');
        $file->setParents([$projectFolderId]);
        
        $createdFile = $this->drive->files->create($file);
        
        return $createdFile->getId();
    }
    
    /**
     * Upload file to Google Drive
     */
    public function uploadFile(string $filePath, string $fileName, int $projectId, string $stage = 'before'): array
    {
        $folderId = $this->getStageFolderId($projectId, $stage);
        
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($fileName);
        $file->setParents([$folderId]);
        
        // Determine mime type
        $mimeType = mime_content_type($filePath);
        $file->setMimeType($mimeType);
        
        // Upload content
        $content = file_get_contents($filePath);
        
        $createdFile = $this->drive->files->create($file, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
        ]);
        
        // Make file publicly accessible using Permission object
        $permission = new \Google_Service_Drive_Permission();
        $permission->setType('anyone');
        $permission->setRole('reader');
        $this->drive->permissions->create($createdFile->getId(), $permission);
        
        return [
            'id' => $createdFile->getId(),
            'name' => $createdFile->getName(),
            'link' => "https://drive.google.com/uc?id={$createdFile->getId()}&export=view",
            'webViewLink' => $createdFile->getWebViewLink(),
        ];
    }
    
    /**
     * Upload file from uploaded file object
     */
    public function uploadUploadedFile($uploadedFile, int $projectId, string $stage = 'before'): array
    {
        $filePath = $uploadedFile->getRealPath();
        $fileName = $uploadedFile->getClientOriginalName();
        
        return $this->uploadFile($filePath, $fileName, $projectId, $stage);
    }
    
    /**
     * Get or create stage subfolder (before, in-progress, after)
     */
    protected function getStageFolderId(int $projectId, string $stage): string
    {
        $sitePhotosFolderId = $this->getSitePhotosFolderId($projectId);
        
        // Map stage names to folder names
        $stageFolderNames = [
            'before' => 'Before',
            'in-progress' => 'In Progress',
            'after' => 'After',
        ];
        
        $folderName = $stageFolderNames[$stage] ?? 'Before';
        
        // Search for existing folder
        $response = $this->drive->files->listFiles([
            'q' => "name='{$folderName}' and '{$sitePhotosFolderId}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id)',
        ]);
        
        if (!empty($response->getFiles())) {
            return $response->getFiles()[0]->getId();
        }
        
        // Create new folder
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($folderName);
        $file->setMimeType('application/vnd.google-apps.folder');
        $file->setParents([$sitePhotosFolderId]);
        
        $createdFile = $this->drive->files->create($file);
        
        return $createdFile->getId();
    }
    
}