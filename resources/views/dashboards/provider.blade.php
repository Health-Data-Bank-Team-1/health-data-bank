<x-app-layout>
@if(auth()->user()->role->name !== 'Healthcare Provider')
    @php abort(403, "Unauthorized"); @endphp
@endif

<h1>Welcome Healthcare Provider</h1>
<p>You are logged in as: {{ Auth::user()->role->name }}</p>
</x-app-layout>
