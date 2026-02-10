<x-app-layout>
@if(auth()->user()->role->name !== 'User')
    @php abort(403, "Unauthorized"); @endphp
@endif

<h1>Welcome User</h1>
<p>You are logged in as: {{ Auth::user()->role->name }}</p>
</x-app-layout>