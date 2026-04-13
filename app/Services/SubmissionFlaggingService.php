<?php

namespace App\Services;

use App\Models\FormSubmission;

class SubmissionFlaggingService
{
    public function evaluate(FormSubmission $submission): void
    {
        $submission->loadMissing(['healthEntries']);

        foreach ($submission->healthEntries as $entry) {
            $values = $entry->encrypted_values;

            if (! is_array($values)) {
                continue;
            }

            $metricKey = $values['metric_key'] ?? null;
            $value = $values['value'] ?? null;

            if ($metricKey === 'heart_rate' && is_numeric($value)) {
                $heartRate = (int) $value;

                if ($heartRate < 30 || $heartRate > 160) {
                    $this->flag($submission, 'Abnormal heart rate detected.');
                    return;
                }
            }

            if (is_string($value) && str_contains(strtolower($value), 'test delete me')) {
                $this->flag($submission, 'Suspicious or accidental submission text detected.');
                return;
            }

            if ($value === null || $value === '' || $value === []) {
                continue;
            }
        }
    }

    private function flag(FormSubmission $submission, string $reason): void
    {
        if ($submission->status === 'FLAGGED') {
            return;
        }

        $submission->update([
            'status' => 'FLAGGED',
            'flag_reason' => $reason,
            'flagged_at' => now(),
        ]);
    }
}
