<x-app-layout>
    <h1>Welcome Researcher</h1>
    <p>You are logged in as: {{ Auth::user()->role->name }}</p>
</x-app-layout>