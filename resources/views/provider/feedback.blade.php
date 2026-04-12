<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Provider Feedback
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $patient->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $patient->email }}</p>
                </div>

                <form method="POST" action="/api/provider/feedback" class="space-y-6">
                    @csrf

                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">

                    <div>
                        <label for="feedback" class="block text-sm font-medium text-gray-700">Feedback</label>
                        <textarea
                            id="feedback"
                            name="feedback"
                            rows="6"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >{{ old('feedback') }}</textarea>
                        @error('feedback')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="recommended_actions" class="block text-sm font-medium text-gray-700">Recommended Actions</label>
                        <textarea
                            id="recommended_actions"
                            name="recommended_actions"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >{{ old('recommended_actions') }}</textarea>
                        @error('recommended_actions')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                        >
                            Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>