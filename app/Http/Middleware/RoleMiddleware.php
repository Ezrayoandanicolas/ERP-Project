<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role, $guard)
    {
        // Ambil path panel
        $currentPanel = $request->segment(1);
        $currentLoginUrl = "/$currentPanel/login";

        // Izinkan halaman login
        if ($request->is("$currentPanel/login") || $request->is("$currentPanel/login/*")) {
            return $next($request);
        }

        // Ambil user
        $user = Auth::guard($guard)->user();

        // Kalau belum login → redirect login panel ini
        if (! $user) {
            return redirect($currentLoginUrl);
        }

        // Kalau role salah:
        if (! $user->hasRole($role)) {

            // Tentukan panel yang benar berdasarkan role user
            $redirectPanel = match (true) {
                $user->hasRole('superadmin') => '/superadmin/login',
                $user->hasRole('admin_store') => '/admin-store/login',
                $user->hasRole('cashier')    => '/cashier/login',
                $user->hasRole('member')     => '/member/login',
                default => $currentLoginUrl,
            };

            // Logout user untuk hapus session
            Auth::guard($guard)->logout();

            // Kirim NOTIFIKASI via session (FILAMENT MENAMPILKANNYA)
            session()->flash('filament.notifications', [
                [
                    'title' => 'Akses ditolak',
                    'body' => "Anda tidak punya akses ke panel $currentPanel.",
                    'status' => 'danger',
                ],
            ]);

            // Redirect ke panel yang BENAR
            return redirect($redirectPanel);
        }

        // Role cocok → lanjut
        return $next($request);
    }
}
