<?php

namespace App\Livewire\Provider;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\AuditLogger;
use App\Services\HealthMetricRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PatientRenderer extends Component
{
    public $patientAccount;

    public string $startDate;

    public string $endDate;

    public array $metrics = [];

    public array $availableMetrics = [];

    public array $chartLabels = [];

    public array $chartDatasets = [];

    public function mount($patient)
    {
        $providerAccountId = Auth::user()->account_id;

        $this->patientAccount = Account::query()
            ->where('id', $patient)
            ->where('account_type', 'User')
            ->whereHas('providers', fn ($q) => $q->where('provider_id', $providerAccountId))
            ->firstOrFail();

        $this->startDate = now()->subWeek()->startOfDay()->toDateString();
        $this->endDate = now()->endOfDay()->toDateString();

        $this->loadData();

        AuditLogger::log(
            'provider_patient_record_view',
            ['provider', 'resource:patient_record'],
            null,
            [],
            [
                'patient_id' => $this->patientAccount->id,
            ]
        );
    }

    public function updatedStartDate(): void
    {
        $this->loadData();
    }

    public function updatedEndDate(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $from = CarbonImmutable::parse($this->startDate)->startOfDay();
        $to = CarbonImmutable::parse($this->endDate)->endOfDay();

        $registry = app(HealthMetricRegistry::class);
        $numericKeys = collect($registry->all())
            ->filter(fn ($m) => $m['type'] === 'number')
            ->keys()
            ->toArray();

        $entries = HealthEntry::query()
            ->where('account_id', (string) $this->patientAccount->id)
            ->whereBetween('timestamp', [$from, $to])
            ->orderBy('timestamp')
            ->get();

        $daily = [];
        $allValues = [];

        foreach ($entries as $entry) {
            $values = $entry->encrypted_values;
            if (! is_array($values)) {
                continue;
            }

            $day = $entry->timestamp->format('Y-m-d');

            foreach ($values as $key => $value) {
                if (! in_array($key, $numericKeys, true)) {
                    continue;
                }

                if (! is_numeric($value)) {
                    continue;
                }

                $v = (float) $value;
                $daily[$key][$day][] = $v;
                $allValues[$key][] = $v;
            }
        }

        $this->metrics = [];
        $this->availableMetrics = [];

        foreach ($allValues as $key => $vals) {
            $this->availableMetrics[$key] = $registry->labelFor($key) ?? $key;

            $avg = array_sum($vals) / count($vals);

            $this->metrics[$key] = [
                'count' => count($vals),
                'min' => round(min($vals), 2),
                'max' => round(max($vals), 2),
                'avg' => round($avg, 2),
                'latest' => round(end($vals), 2),
            ];
        }

        $allDays = [];
        foreach ($daily as $metricDays) {
            $allDays = array_merge($allDays, array_keys($metricDays));
        }
        $allDays = array_unique($allDays);
        sort($allDays);

        $this->chartLabels = array_map(
            fn ($d) => CarbonImmutable::parse($d)->format('M j'),
            $allDays
        );

        $colors = [
            ['border' => 'rgba(59,130,246,0.8)', 'bg' => 'rgba(59,130,246,0.1)'],
            ['border' => 'rgba(234,88,12,0.8)',  'bg' => 'rgba(234,88,12,0.1)'],
            ['border' => 'rgba(16,185,129,0.8)', 'bg' => 'rgba(16,185,129,0.1)'],
            ['border' => 'rgba(139,92,246,0.8)', 'bg' => 'rgba(139,92,246,0.1)'],
            ['border' => 'rgba(236,72,153,0.8)', 'bg' => 'rgba(236,72,153,0.1)'],
        ];

        $this->chartDatasets = [];
        $i = 0;
        foreach ($daily as $key => $metricDays) {
            $color = $colors[$i % count($colors)];

            $data = [];
            foreach ($allDays as $day) {
                if (isset($metricDays[$day])) {
                    $vals = $metricDays[$day];
                    $data[] = round(array_sum($vals) / count($vals), 2);
                } else {
                    $data[] = null;
                }
            }

            $this->chartDatasets[] = [
                'label' => $registry->labelFor($key) ?? $key,
                'data' => $data,
                'borderColor' => $color['border'],
                'backgroundColor' => $color['bg'],
            ];

            $i++;
        }
    }

    public function render()
    {
        return view('livewire.provider.patient-renderer')
            ->layout('layouts.provider');
    }
}
