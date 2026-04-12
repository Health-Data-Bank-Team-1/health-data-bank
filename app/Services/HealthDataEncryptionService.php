<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

/**
 * Service for encrypting/decrypting health data
 * Uses Laravel's built-in AES-256-CBC encryption
 */
class HealthDataEncryptionService
{
    /**
     * Encrypt health data and return as JSON-compatible structure
     * 
     * @param array $data Health data to encrypt
     * @return array JSON-compatible encrypted data: ['data' => encrypted_string]
     */
    public function encrypt(array $data): array
    {
        $json = json_encode($data);
        $encrypted = Crypt::encryptString($json);
        
        // Return as JSON object (MySQL jsonb compatible)
        return ['data' => $encrypted];
    }

    /**
     * Decrypt encrypted health data
     * 
     * @param array|string $encryptedData Encrypted data (should be array with 'data' key)
     * @return array Decrypted health data
     * @throws \RuntimeException If decryption fails
     */
    public function decrypt($encryptedData): array
    {
        if (empty($encryptedData)) {
            throw new \RuntimeException('Failed to decrypt health data. No encrypted data provided.');
        }

        // Extract encrypted string from array
        $encryptedString = null;
        if (is_array($encryptedData)) {
            $encryptedString = $encryptedData['data'] ?? null;
        } elseif (is_string($encryptedData)) {
            $encryptedString = $encryptedData;
        }

        if (empty($encryptedString)) {
            throw new \RuntimeException('Failed to decrypt health data. No encrypted payload found.');
        }

        try {
            // Attempt to decrypt - this may throw DecryptException
            $json = Crypt::decryptString($encryptedString);
            
            // Decode the JSON
            $data = json_decode($json, true);

            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decrypt health data. Invalid JSON in decrypted payload: ' . json_last_error_msg());
            }

            return $data;
        } catch (\Throwable $e) {
            // Catch any exception (including DecryptException) and wrap it
            if ($e instanceof \RuntimeException && strpos($e->getMessage(), 'Failed to decrypt health data') === 0) {
                // Already one of our wrapped exceptions, re-throw as-is
                throw $e;
            }
            
            // Wrap all other exceptions (including Illuminate\Encryption\DecryptException)
            throw new \RuntimeException('Failed to decrypt health data. Invalid encryption key or corrupted data.', 0, $e);
        }
    }

    /**
     * Check if data is encrypted format
     * 
     * @param mixed $data Data to check
     * @return bool True if data is in encrypted format
     */
    public function isEncrypted($data): bool
    {
        return is_array($data) && isset($data['data']) && is_string($data['data']);
    }

    /**
     * Batch decrypt multiple entries
     * 
     * @param array $entries Array of entries with encrypted values
     * @return array Entries with decrypted values
     */
    public function batchDecrypt(array $entries): array
    {
        return array_map(function ($entry) {
            if (isset($entry['encrypted_values']) && $this->isEncrypted($entry['encrypted_values'])) {
                try {
                    $entry['encrypted_values'] = $this->decrypt($entry['encrypted_values']);
                } catch (\Exception $e) {
                    \Log::warning('Failed to decrypt entry', ['error' => $e->getMessage()]);
                }
            }
            return $entry;
        }, $entries);
    }

    /**
     * Handle encryption errors
     * 
     * @param array $data Data to encrypt
     * @return array Encrypted data structure
     */
    public function safeEncrypt(array $data): array
    {
        try {
            return $this->encrypt($data);
        } catch (\Exception $e) {
            \Log::error('Health data encryption failed', [
                'error' => $e->getMessage(),
                'data_keys' => array_keys($data),
            ]);
            throw $e;
        }
    }
}