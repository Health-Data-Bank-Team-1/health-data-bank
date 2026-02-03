<x-app-layout>
    <h1>Welcome Healthcare Provider</h1>
    <p>You are logged in as: {{ Auth::user()->role->name }}</p>
</x-app-layout>