<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Generate Anonymous Aggregated Report</h2>

        <form id="reportForm" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="mt-1 block w-full border rounded p-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="mt-1 block w-full border rounded p-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Gender</label>
                    <input type="text" name="gender" class="mt-1 block w-full border rounded p-2" placeholder="e.g. female">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" class="mt-1 block w-full border rounded p-2" placeholder="e.g. PEI">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Minimum Age</label>
                    <input type="number" name="age_min" class="mt-1 block w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Maximum Age</label>
                    <input type="number" name="age_max" class="mt-1 block w-full border rounded p-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Health Metrics</label>
                <select id="keys" name="keys" multiple class="mt-1 block w-full border rounded p-2 min-h-[140px]">
                    @foreach($metricOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple metrics.</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded">
                    Generate Report
                </button>
            </div>
        </form>

        <form id="exportForm" method="POST" action="/api/researcher/reports/aggregated/export.csv" class="mt-4">
            @csrf
            <input type="hidden" id="export_from" name="from">
            <input type="hidden" id="export_to" name="to">
            <input type="hidden" id="export_gender" name="gender">
            <input type="hidden" id="export_location" name="location">
            <input type="hidden" id="export_age_min" name="age_min">
            <input type="hidden" id="export_age_max" name="age_max">
            <input type="hidden" id="export_keys" name="keys">

            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
                Export CSV
            </button>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Report Output</h2>
        <pre id="reportResult" class="bg-gray-100 p-4 rounded text-sm overflow-auto min-h-[200px]"></pre>
        <div id="reportError" class="mt-4 text-red-700"></div>
    </div>

    <script>
        function selectedKeysAsCsv(selectEl) {
            return Array.from(selectEl.selectedOptions).map(o => o.value).join(',');
        }

        const reportForm = document.getElementById('reportForm');
        const exportForm = document.getElementById('exportForm');
        const csrf = document.querySelector('input[name="_token"]').value;

        reportForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(reportForm);
            const payload = Object.fromEntries(formData.entries());
            payload.keys = selectedKeysAsCsv(document.getElementById('keys'));

            const response = await fetch('/api/researcher/reports/aggregated', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            document.getElementById('reportResult').textContent = '';
            document.getElementById('reportError').textContent = '';

            if (response.ok) {
                document.getElementById('reportResult').textContent = JSON.stringify(data, null, 2);

                document.getElementById('export_from').value = payload.from ?? '';
                document.getElementById('export_to').value = payload.to ?? '';
                document.getElementById('export_gender').value = payload.gender ?? '';
                document.getElementById('export_location').value = payload.location ?? '';
                document.getElementById('export_age_min').value = payload.age_min ?? '';
                document.getElementById('export_age_max').value = payload.age_max ?? '';
                document.getElementById('export_keys').value = payload.keys ?? '';
            } else {
                document.getElementById('reportError').textContent =
                    data.message ?? 'Failed to generate report.';
            }
        });
    </script>
</div>
