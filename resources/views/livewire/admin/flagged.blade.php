<x-admin-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Flagged Reports
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Reports marked for review</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Review flagged user submissions and remove problematic ones from active use.
                    </p>
                </div>

                @if($reports->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Form
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Submitted By
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Flag Reason
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Flagged At
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Action
                                </th>
                            </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($reports as $report)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        {{ $report->formTemplate?->title ?? 'Unknown Form' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        {{ $report->account?->name ?? 'Unknown User' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm">
                                            <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">
                                                {{ $report->status ?? 'FLAGGED' }}
                                            </span>
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        {{ $report->flag_reason ?? 'No reason provided' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ optional($report->flagged_at)->format('Y-m-d H:i') ?? 'N/A' }}
                                    </td>

                                    <td class="px-4 py-4 text-right">
                                        <a href="{{ route('admin.reports.review', $report->id) }}"
                                           class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($reports, 'links'))
                        <div class="mt-6">
                            {{ $reports->links() }}
                        </div>
                    @endif
                @else
                    <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-6 text-center">
                        <p class="text-sm text-gray-600">There are no flagged reports to review right now.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
