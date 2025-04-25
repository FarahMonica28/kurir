<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Request;
use Validator;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Transaksi::class => \App\Policies\TransaksiPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
            Gate::define('kurir', function (User $user) {
                return $user->hasRole('kurir');
            });
        
            Gate::define('admin', function (User $user) {
                return $user->hasRole('admin');
            });
        
            Gate::define('pengguna', function (User $user) {
                return $user->hasRole('pengguna');
            });

        // // Contoh gate untuk role-based
        // Gate::define('kurir', fn($user) => $user->role === 'kurir');
        // Gate::define('pengguna', fn($user) => $user->role === 'pengguna');
        // Gate::define('transaksi', fn($user) => $user->role === 'admin' || $user->role === 'staff');
        // Gate::define('pengiriman', fn($user) => $user->role === 'admin' || $user->role === 'staff');
        // Gate::define('tracking', fn($user) => $user->role === 'admin');
        // Gate::define('setting', fn($user) => $user->role === 'admin');
        // Gate::define('master-user', fn($user) => $user->role === 'admin');
        // Gate::define('master-role', fn($user) => $user->role === 'admin');

        // Tambahkan gate lain sesuai kebutuhan
    }

    public function login(Request $request)
{
    $validator = Validator::make($request->post(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    if (!$token = auth()->attempt($validator->validated())) {
        return response()->json([
            'status' => false,
            'message' => 'Email / Password salah!'
        ], 401);
    }

    return response()->json([
        'status' => true,
        'user' => auth()->user()->load('kurir'),
        'role' => auth()->user()->getRoleNames(), // <--- Tambahan ini
        'token' => $token
    ]);
}

}
