<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('health-summary') }}">Health Summary</a>
                        </h2>
                    </div>
                    <div>
                        recent graphical report here
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <div class="flex items-center">
                            <h2 class="text-xl font-semibold text-gray-900">
                                <a href="{{ route('my-progress') }}">Health Goals</a>
                            </h2>
                        </div>
                        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                            health goals
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <h2 class="text-xl font-semibold text-gray-900">
                                <a href="{{ route('my-progress') }}">Compare to Group</a>
                            </h2>
                        </div>

                        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                            compare to group
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
