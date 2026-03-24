<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Create Cohort
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <form id="cohortForm" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Cohort Name</label>
                    <input type="text" name="name" class="mt-1 block w-full border rounded p-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Purpose</label>
                    <textarea name="purpose" class="mt-1 block w-full border rounded p-2" required></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gender</label>
                        <input type="text" name="gender" class="mt-1 block w-full border rounded p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="location" class="mt-1 block w-full border rounded p-2">
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

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                    Create Cohort
                </button>
            </form>

            <div id="cohortSuccess" class="mt-6 text-green-700"></div>
            <div id="cohortError" class="mt-6 text-red-700"></div>
        </div>
    </div>
</div>

<script>
    document.getElementById('cohortForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());

        const response = await fetch('/api/researcher/cohorts', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        document.getElementById('cohortSuccess').innerHTML = '';
        document.getElementById('cohortError').innerHTML = '';

        if (response.ok) {
            document.getElementById('cohortSuccess').innerHTML =
                `Cohort created successfully. Cohort ID: <strong>${data.data.id}</strong>`;
        } else {
            document.getElementById('cohortError').innerText =
                data.message ?? 'Failed to create cohort.';
        }
    });
</script>
