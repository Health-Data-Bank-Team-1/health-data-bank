<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class DatabaseManagement extends Component
{
    public ?string $table = null;
    public int $perPage = 25;
    public int $page = 1;

    /**
     * Optional allowlist. If empty => show all tables.
     * Recommendation: enable this in production.
     */
    protected array $allowlist = [
        // Auditing / compliance
        'audit_logs',
        // 'audits', // OPTIONAL: uncomment if you want to view the laravel-auditing table too

        // Admin workflow / forms metadata (do NOT include form_submissions by default)
        'form_templates',
        'form_template_versions',
        'form_fields',

        // Reporting workflow objects (metadata)
        'reports',
        'report_updates',

        // Operational / notifications
        'notifications',
        'reminder_settings',

        // RBAC (admin troubleshooting)
        'roles',
        'permissions',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',

        // Queue / infra troubleshooting
        'jobs',
        // 'cache', // OPTIONAL: uncomment if you want cache visibility
    ];

    protected array $maskedColumnPatterns = [
        'password',
        'remember_token',
        'token',
        'secret',
        'encrypted',
        'api_key',
        'private',
        'ssn',
    ];

    public function mount(): void
    {
        $this->table = request()->query('table');

        $this->perPage = (int) request()->query('per_page', $this->perPage);
        $this->perPage = max(5, min(100, $this->perPage));

        $this->page = (int) request()->query('page', $this->page);
        $this->page = max(1, $this->page);
    }

    private function isValidIdentifier(?string $name): bool
    {
        if (empty($name)) return false;
        // MySQL identifiers can include more, but we intentionally restrict for safety.
        return (bool) preg_match('/^[A-Za-z0-9_]+$/', $name);
    }

    private function qi(string $identifier): string
    {
        // Quote identifier with backticks, doubling any backticks (shouldn’t appear due to validation).
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function shouldMaskColumn(string $col): bool
    {
        $c = strtolower($col);
        foreach ($this->maskedColumnPatterns as $p) {
            if (str_contains($c, $p)) return true;
        }
        return false;
    }

    private function listTablesMysql(): array
    {
        // SHOW FULL TABLES returns:
        // - Tables_in_<db>
        // - Table_type (BASE TABLE / VIEW)
        $rows = DB::select('SHOW FULL TABLES');

        $tables = [];
        foreach ($rows as $r) {
            $arr = (array) $r;
            $values = array_values($arr);

            $name = $values[0] ?? null;
            $type = $values[1] ?? null;

            if (!is_string($name) || $name === '') continue;

            // Skip views (optional). If you want to include them, remove this condition.
            if (is_string($type) && strtoupper($type) === 'VIEW') {
                continue;
            }

            $tables[] = $name;
        }

        $tables = array_values(array_unique($tables));
        sort($tables);

        // Apply allowlist (if set)
        if (!empty($this->allowlist)) {
            $tables = array_values(array_filter(
                $tables,
                fn ($t) => in_array($t, $this->allowlist, true)
            ));
        }

        // Ensure table exists (avoids confusion if migrations not run in an env)
        $tables = array_values(array_filter($tables, fn ($t) => Schema::hasTable($t)));

        return $tables;
    }

    private function countTableRowsMysql(string $table): ?int
    {
        if (!$this->isValidIdentifier($table) || !Schema::hasTable($table)) {
            return null;
        }

        $connection = config('database.default');

        return Cache::remember("admin_db_count:{$connection}:{$table}", 30, function () use ($table) {
            try {
                // Use raw SQL with quoted identifier to avoid any ambiguity.
                $sql = "SELECT COUNT(*) AS c FROM " . $this->qi($table);
                $row = DB::selectOne($sql);
                $arr = (array) $row;
                return isset($arr['c']) ? (int) $arr['c'] : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    private function previewTableMysql(string $table): array
    {
        if (!$this->isValidIdentifier($table)) {
            return ['error' => 'Invalid table name.', 'columns' => [], 'rows' => [], 'pagination' => null];
        }

        if (!Schema::hasTable($table)) {
            return ['error' => "Table not found: {$table}", 'columns' => [], 'rows' => [], 'pagination' => null];
        }

        $columns = Schema::getColumnListing($table);
        $offset = ($this->page - 1) * $this->perPage;

        try {
            // Best-effort deterministic ordering: id desc if present, else no explicit order.
            $sql = "SELECT * FROM " . $this->qi($table);
            if (in_array('id', $columns, true)) {
                $sql .= " ORDER BY " . $this->qi('id') . " DESC";
            }
            $sql .= " LIMIT ? OFFSET ?";

            $rows = DB::select($sql, [$this->perPage, $offset]);

            $maskedRows = [];
            foreach ($rows as $row) {
                $arr = (array) $row;
                foreach ($arr as $k => $v) {
                    if ($this->shouldMaskColumn((string) $k)) {
                        $arr[$k] = '[MASKED]';
                    } elseif (is_array($v) || is_object($v)) {
                        $arr[$k] = json_encode($v);
                    }
                }
                $maskedRows[] = $arr;
            }

            $total = $this->countTableRowsMysql($table);

            $hasNext = is_int($total)
                ? ($offset + $this->perPage) < $total
                : (count($maskedRows) === $this->perPage);

            return [
                'error' => null,
                'columns' => $columns,
                'rows' => $maskedRows,
                'pagination' => [
                    'page' => $this->page,
                    'per_page' => $this->perPage,
                    'total' => $total,
                    'has_prev' => $this->page > 1,
                    'has_next' => $hasNext,
                ],
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage(), 'columns' => $columns, 'rows' => [], 'pagination' => null];
        }
    }

    public function render()
    {
        // Non-sensitive DB config
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");
        $database = config("database.connections.$connection.database");
        $host = config("database.connections.$connection.host");
        $port = config("database.connections.$connection.port");
        $appEnv = config('app.env');
        $debug = (bool) config('app.debug');

        $tables = [];
        $tableListError = null;

        try {
            if ($driver !== 'mysql' && $driver !== 'mariadb') {
                $tableListError = "This Database Management view is configured for MySQL/MariaDB, but driver is: " . ($driver ?? 'unknown');
                $tables = [];
            } else {
                $tables = $this->listTablesMysql();
            }
        } catch (\Throwable $e) {
            $tableListError = $e->getMessage();
            $tables = [];
        }

        $tablesWithCounts = [];
        foreach ($tables as $t) {
            $tablesWithCounts[] = [
                'name' => $t,
                'count' => $this->countTableRowsMysql($t),
            ];
        }

        $preview = null;
        if (!empty($this->table)) {
            if (!in_array($this->table, $tables, true)) {
                $preview = [
                    'error' => "Table not found (or not allowed): {$this->table}",
                    'columns' => [],
                    'rows' => [],
                    'pagination' => null,
                ];
            } else {
                $preview = $this->previewTableMysql($this->table);
            }
        }

        return view('livewire.admin.database-management', [
            'dbInfo' => compact('connection', 'driver', 'database', 'host', 'port', 'appEnv', 'debug'),
            'tables' => $tablesWithCounts,
            'selectedTable' => $this->table,
            'preview' => $preview,
            'perPage' => $this->perPage,
            'tableListError' => $tableListError,
        ])
            ->layout('layouts.admin')
            ->layoutData([
                'header' => 'Database Management',
            ]);
    }
}