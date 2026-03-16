<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('admin.audit-log') }}">Audit Log</a>
                        </h2>
                    </div>
                    <div>
                        audit log here
                    </div>
                </div>

                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('admin.report-review') }}">Report Review</a>
                        </h2>
                    </div>
                    <div>
                        report review here
                    </div>
                </div>

                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('admin.forms.index') }}">Form Review</a>
                        </h2>
                    </div>
                    <div>
                        form review here
                    </div>
                </div>

                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('admin.database-management') }}">Database Management</a>
                        </h2>
                    </div>
                    <div>
                        db management here
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
