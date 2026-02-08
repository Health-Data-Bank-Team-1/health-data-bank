<div class="max-w-md w-full bg-white shadow rounded-lg overflow-hidden">
    <ul class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
        @foreach ($forms as $form)
            <li>
                <a href="{{ route('forms.show', $form) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition">
                    <span class="text-gray-900 font-medium">{{ $form->name }}</span>
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </li>
        @endforeach
    </ul>
</div>

