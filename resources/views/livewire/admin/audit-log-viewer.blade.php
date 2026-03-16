<div class="mb-6 grid grid-cols-2 md:grid-cols-5 gap-4">

    <input
        type="text"
        placeholder="User ID"
        wire:model.live="userId"
        class="border rounded px-3 py-2 text-sm"
    />

    <input
        type="text"
        placeholder="Tag (auth, reporting)"
        wire:model.live="tag"
        class="border rounded px-3 py-2 text-sm"
    />

    <select wire:model.live="event" class="border rounded px-3 py-2 text-sm">
        <option value="">All Events</option>
        <option value="login_success">login_success</option>
        <option value="login_failure">login_failure</option>
        <option value="logout">logout</option>
        <option value="access_denied">access_denied</option>
        <option value="reporting_trends_view">reporting_trends_view</option>
        <option value="reporting_summary_view">reporting_summary_view</option>
        <option value="researcher_cohort_generated">researcher_cohort_generated</option>
        <option value="researcher_aggregated_report_viewed">report viewed</option>
        <option value="researcher_aggregated_report_exported">report exported</option>
    </select>

    <input
        type="date"
        wire:model.live="from"
        class="border rounded px-3 py-2 text-sm"
    />

    <input
        type="date"
        wire:model.live="to"
        class="border rounded px-3 py-2 text-sm"
    />

</div>

<div class="flex justify-between mb-4">

    <button
        wire:click="exportCsv"
        class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700"
    >
        Export CSV
    </button>

</div>
