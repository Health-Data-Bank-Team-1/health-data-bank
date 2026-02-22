<body>
    <header>
        <nav></nav>
    </header>
    <main>
        <h1 hidden>Register</h1>
        <x-guest-layout>
            <x-authentication-card>
                <x-slot name="logo">
                    <span class="text-xl">{{ 'Health Data Bank' }}</span>
                </x-slot>
                <livewire:register-form />
            </x-authentication-card>
        </x-guest-layout>
    </main>
</body>
