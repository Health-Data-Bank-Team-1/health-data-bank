<x-app-layout>
@if(auth()->user()->role->name !== 'Administrator')
    @php abort(403, "Unauthorized"); @endphp
@endif

<h1>Welcome Administrator</h1>
<p>You are logged in as: {{ Auth::user()->role->name }}</p>
</x-app-layout>