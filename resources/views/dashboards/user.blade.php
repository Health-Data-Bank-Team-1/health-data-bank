<x-app-layout>
    <h1>Welcome User</h1>
    <p>You are logged in as: {{ Auth::user()->role->name }}</p>
</x-app-layout>