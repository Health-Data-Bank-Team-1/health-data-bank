<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;

class CohortFilterBuilder
{
    public function build(array $filters = []): Builder
    {
        return Account::query()
            ->when(
                !empty($filters['account_type']),
                fn (Builder $query) => $query->where('account_type', $filters['account_type'])
            )
            ->when(
                !empty($filters['account_status']),
                fn (Builder $query) => $query->where('status', $filters['account_status'])
            )
            ->when(
                !empty($filters['created_from']),
                fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['created_from'])
            )
            ->when(
                !empty($filters['created_to']),
                fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['created_to'])
            );
    }
}
