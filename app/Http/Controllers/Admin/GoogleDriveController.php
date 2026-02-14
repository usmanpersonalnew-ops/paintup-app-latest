<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Google_Client;
use Google_Service_Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GoogleDriveController extends Controller
{
    /**
     * Redirect user to Google OAuth consent screen
     */
    public function redirectToGoogle()
    {
        $client = new Google_Client();
        
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(route('admin.google-drive.callback'));
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        
        $authUrl = $client->createAuthUrl();
        
        return redirect()->away($authUrl);
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        
        $client = new Google_Client();
        
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(route('admin.google-drive.callback'));
        
        try {
            $token = $client->fetchAccessTokenWithAuthCode($request->code);
            
            Log::info('Google OAuth token response: ' . json_encode($token));
            
            if (isset($token['error'])) {
                Log::error('Google OAuth Error: ' . $token['error']);
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Failed to connect Google Drive: ' . $token['error']);
            }
            
            // Store token in storage/app/google-drive-token.json
            $tokenPath = storage_path('app/google-drive-token.json');
            $result = file_put_contents($tokenPath, json_encode($token));
            
            Log::info('Google Drive OAuth token saved to: ' . $tokenPath);
            Log::info('Save result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            Log::info('File exists after save: ' . (file_exists($tokenPath) ? 'YES' : 'NO'));
            
            if (!$result || !file_exists($tokenPath)) {
                Log::error('Failed to write token file. Check directory permissions.');
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Failed to save Google Drive token. Check storage directory permissions.');
            }
            
            return redirect()->route('admin.dashboard')
                ->with('success', 'Google Drive connected successfully!');
                
        } catch (\Exception $e) {
            Log::error('Google OAuth Exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed to connect Google Drive: ' . $e->getMessage());
        }
    }
}
