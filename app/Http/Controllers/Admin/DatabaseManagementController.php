<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseManagementController extends Controller
{
    /**
     * Hard allowlist: only show tables you consider safe to expose in admin UI.
     * Expand as needed.
     */
    private array $allowedTables = [
        'accounts',
        'users',
        'health_entries',
        'researcher_cohorts',
        'audits',
        'form_templates',
        'form_submissions',
        // add more as appropriate
    ];

    /** Columns that should be masked in previews */
    private array $maskedColumnPatterns = [
        'password',
        'remember_token',
        'token',
        'api_token',
        'secret',
        'encrypted',
    ];

    public function index(Request $request)
    {
        $connection = config('database.default');

        $tables = collect($this->allowedTables)
            ->filter(fn ($t) => Schema::hasTable($t))
            ->values()
            ->map(function (string $table) use ($connection) {
                // counts can be expensive; cache briefly
                $count = Cache::remember("admin_db_count:{$connection}:{$table}", 30, function () use ($table) {
                    return (int) DB::table($table)->count();
                });

                return [
                    'name' => $table,
                    'count' => $count,
                ];
            });

        return view('admin.database-management', [
            'tables' => $tables,
        ]);
    }

    public function show(Request $request, string $table)
    {
        abort_unless(in_array($table, $this->allowedTables, true), 404);
        abort_unless(Schema::hasTable($table), 404);

        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(5, min(100, $perPage));

        // Best-effort ordering: use id if present, else no explicit order.
        $query = DB::table($table);
        if (Schema::hasColumn($table, 'id')) {
            $query->orderByDesc('id');
        }

        $rows = $query->paginate($perPage)->withQueryString();

        // Mask sensitive columns
        $maskedRows = $rows->through(function ($row) {
            $arr = (array) $row;

            foreach ($arr as $key => $value) {
                if ($this->shouldMaskColumn($key)) {
                    $arr[$key] = '[MASKED]';
                }
            }

            return $arr;
        });

        // Determine columns for rendering
        $columns = [];
        if ($maskedRows->count() > 0) {
            $columns = array_keys($maskedRows->first());
        } else {
            // fallback: columns from schema (may be large)
            $columns = Schema::getColumnListing($table);
        }

        return view('admin.database-table', [
            'table' => $table,
            'columns' => $columns,
            'rows' => $maskedRows,
        ]);
    }

    private function shouldMaskColumn(string $column): bool
    {
        $col = strtolower($column);

        foreach ($this->maskedColumnPatterns as $pattern) {
            if (str_contains($col, $pattern)) {
                return true;
            }
        }

        return false;
    }
}