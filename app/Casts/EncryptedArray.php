<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use App\Services\HealthDataEncryptionService;

/**
 * Custom cast for automatically encrypting/decrypting array values
 * Stores encrypted data as JSON in jsonb columns
 */
class EncryptedArray implements CastsAttributes
{
    private HealthDataEncryptionService $encryptionService;

    public function __construct()
    {
        $this->encryptionService = app(HealthDataEncryptionService::class);
    }

    /**
     * Decrypt when retrieving from database
     * 
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return array|null Decrypted data
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) {
            return null;
        }

        // Value from jsonb column is already an array
        $data = is_array($value) ? $value : json_decode($value, true);

        try {
            if ($this->encryptionService->isEncrypted($data)) {
                return $this->encryptionService->decrypt($data);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt health entry', [
                'model' => get_class($model),
                'id' => $model->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        return $data;
    }

    /**
     * Encrypt when saving to database
     * Returns a string representation so Eloquent treats it as a single column value
     * 
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string JSON-encoded encrypted structure
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (empty($value)) {
            return json_encode(null);
        }

        // If already encrypted and in the right format, keep it
        if ($this->encryptionService->isEncrypted($value)) {
            return json_encode($value);
        }

        // Ensure value is an array before encrypting
        $arrayValue = is_array($value) ? $value : [$key => $value];

        try {
            $encrypted = $this->encryptionService->encrypt($arrayValue);
            // Return as JSON string so Eloquent stores it in the jsonb column
            return json_encode($encrypted);
        } catch (\Exception $e) {
            \Log::error('Failed to encrypt health entry', [
                'model' => get_class($model),
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}