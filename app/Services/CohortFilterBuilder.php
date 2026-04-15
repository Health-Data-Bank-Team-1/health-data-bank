<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Account;

class CohortFilterBuilder
{
    public function build(array $filters = []): Builder
    {
        return Account::query()
            ->select('accounts.*')
            ->when(
                !empty($filters['account_type']),
                fn (Builder $query) => $query->where('accounts.account_type', $filters['account_type'])
            )
            ->when(
                !empty($filters['account_status']),
                fn (Builder $query) => $query->where('accounts.status', $filters['account_status'])
            )
            ->when(
                !empty($filters['gender']),
                fn (Builder $query) => $query->where('accounts.gender', $filters['gender'])
            )
            ->when(
                !empty($filters['location']),
                fn (Builder $query) => $query->where('accounts.location', $filters['location'])
            )
            ->when(
                isset($filters['age_min']),
                fn (Builder $query) => $query->whereRaw(
                    'TIMESTAMPDIFF(YEAR, accounts.date_of_birth, CURDATE()) >= ?',
                    [$filters['age_min']]
                )
            )
            ->when(
                isset($filters['age_max']),
                fn (Builder $query) => $query->whereRaw(
                    'TIMESTAMPDIFF(YEAR, accounts.date_of_birth, CURDATE()) <= ?',
                    [$filters['age_max']]
                )
            )
            ->when(
                !empty($filters['created_from']),
                fn (Builder $query) => $query->whereDate('accounts.created_at', '>=', $filters['created_from'])
            )
            ->when(
                !empty($filters['created_to']),
                fn (Builder $query) => $query->whereDate('accounts.created_at', '<=', $filters['created_to'])
            );
    }
}
