<?php

namespace App\Services;

use App\Exceptions\CohortSuppressedException;

class KThresholdService
{
    public function enforce(int $count, int $minimum = 10): void
    {
        if ($count < $minimum) {
            throw new CohortSuppressedException(
                "Cohort suppressed because size {$count} is below minimum threshold {$minimum}."
            );
        }
    }
}
