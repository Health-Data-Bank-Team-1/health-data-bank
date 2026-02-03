protected $listen = [
    \Illuminate\Auth\Events\Login::class => [
        \App\Listeners\LogLoginSuccess::class,
    ],
    \Illuminate\Auth\Events\Failed::class => [
        \App\Listeners\LogLoginFailure::class,
    ],
    \Illuminate\Auth\Events\Logout::class => [
        \App\Listeners\LogLogout::class,
    ],
    \Illuminate\Auth\Events\Registered::class => [
        \App\Listeners\LogRegistered::class,
    ],
];
