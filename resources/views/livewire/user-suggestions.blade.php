<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">

                @if(!empty($result['suggestions']))
                    <div class="space-y-4">
                        @foreach($result['suggestions'] as $suggestion)
                            <div class="rounded-lg border border-gray-200 p-4 bg-white shadow-sm">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-base font-semibold text-gray-900">{{ $suggestion['title'] }}</h3>
                                            @if($suggestion['severity'] === 'high')
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">High</span>
                                            @elseif($suggestion['severity'] === 'medium')
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Medium</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Low</span>
                                            @endif
                                        </div>
                                        @if($suggestion['metric'])
                                            <p class="text-sm text-gray-500 mb-1">{{ $suggestion['context']['label'] ?? $suggestion['metric'] }}</p>
                                        @endif
                                        <p class="text-sm text-gray-700">{{ $suggestion['message'] }}</p>
                                        @if(!empty($suggestion['context']))
                                            <div class="mt-2 flex flex-wrap gap-3">
                                                @foreach($suggestion['context'] as $key => $value)
                                                    @if(!is_array($value))
                                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                                            <span class="font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                            {{ $value }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif(!empty($result))
                    <div class="rounded-md bg-gray-50 border border-dashed border-gray-300 p-4">
                        <p class="text-sm text-gray-600">No suggestions could be generated for this period.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
