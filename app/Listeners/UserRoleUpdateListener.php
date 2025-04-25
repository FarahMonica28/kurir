<?php

namespace App\Listeners;

use App\Events\UserRoleUpdated;
use App\Models\Kurir;
use App\Models\Pengguna;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UserRoleUpdateListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRoleUpdated $event): void
    {
        $user = $event->user;

        $currentRole = $user->roles()->pluck('name')->first();

        
        // Jika user menjadi Dokter dan belum ada di tabel dokter, tambahkan
        if ($currentRole === 'kurir' && !$user->kurir) {
            Kurir::create([
                'user_id' => $user->id,
                'status' => 'nonaktif', // atur
            ]);  
        } 
        // Jika user tidak lagi menjadi Dokter, hapus dari tabel dokter
        if ($currentRole !== 'kurir' && $user->kurir) {
            $user->kurir->delete();
        }


        if ($currentRole === 'pengguna' && !$user->pengguna) {
            Pengguna::create([
                'user_id' => $user->id,
                'alamat' => 'alamat',
            ]);  
        }
        // Jika user tidak lagi menjadi Dokter, hapus dari tabel dokter
        if ($currentRole !== 'pengguna' && $user->pengguna) {
            $user->pengguna->delete();
        }
    }
}