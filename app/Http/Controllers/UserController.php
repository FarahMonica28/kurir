<?php

namespace App\Http\Controllers;
use App\Events\UserRegistered;

use App\Mail\OtpMail;
use App\Models\Kurir;
use App\Models\Transaksi;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\EmailVerification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => User::when($request->role_id, function (Builder $query, string $role_id) {
                $query->role($role_id);
            })->get()
        ]);
    }

    public function dashboardSummary()
    {
        // Jumlah kurir berdasarkan status
        $totalKurir = Kurir::count();
        $aktifKurir = Kurir::where('status', 'aktif')->count();
        $nonAktifKurir = Kurir::where('status', 'nonaktif')->count();

        // Jumlah transaksi
        $totalTransaksi = Transaksi::count();
        $transaksiSelesai = Transaksi::where('status', 'selesai')->count();
        $transaksiProses = Transaksi::where('status', 'proses')->count();

        // Rata-rata penilaian kurir
        $rataPenilaian = Transaksi::whereNotNull('penilaian')->avg('penilaian');

        return response()->json([
            'total_kurir' => $totalKurir,
            'aktif_kurir' => $aktifKurir,
            'nonaktif_kurir' => $nonAktifKurir,
            'total_transaksi' => $totalTransaksi,
            'transaksi_selesai' => $transaksiSelesai,
            'transaksi_proses' => $transaksiProses,
            'rata_penilaian' => round($rataPenilaian, 2),
        ]);
    }
    /**
     * Display a paginated list of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        // $data = User::select($request->search, function ( $query, $search));
        $data = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->when($request->search, function (Builder $query, string $search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('roles.name', 'like', "%$search%");
                // $query->where('id_role', 'like', "%$search%");
            })->select('users.*', 'roles.name as roles_name')->latest()->paginate($per);
        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }


        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */

    // public function store(StoreUserRequest $request)
    // {
    //     $validatedData = $request->validated();

    //     if ($request->hasFile('photo')) {
    //         $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
    //     }

    //     $user = User::create($validatedData);
    //     $role = Role::findById($validatedData['role_id']);
    //             $user->assignRole($role);
    //             $user->update($validatedData);
    //     // $user->assignRole(Role::findById($validatedData['role_id']));

    //     // Generate OTP
    //     $otp = rand(100000, 999999);
    //     EmailVerification::create([
    //         'user_id' => $user->id,
    //         'otp' => $otp,
    //         'expires_at' => now()->addMinutes(10),
    //     ]);

    //     // Kirim OTP via Email
    //     Mail::to($user->email)->send(new \App\Mail\OtpVerificationMail($otp));

    //     return response()->json([
    //         'message' => 'User created. OTP sent to email.',
    //     ]);
    // }
        public function store(StoreUserRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }

        $user = User::create($validatedData);

        $role = Role::findById($validatedData['role_id']);
        $user->assignRole($role);
        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);

        
    }


    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // $user->load('kurir'); // ← ini bagian pentingnya!
        $user['role_id'] = $user?->role?->id;
        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        } else {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
                $validatedData['photo'] = null;
            }
        }

        $user->update($validatedData);

        $role = Role::findById($validatedData['role_id']);
        $user->syncRoles($role);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return response()->json([
            'success' => true
        ]);
    }
    public function kurir()
    {
        return $this->hasOne(Kurir::class);
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'password' => 'nullable|string|min:8',
            'role_id' => 'required',
        ]);

        // Generate OTP
        $otp = rand(100000, 999999);
        $key = 'otp_' . Str::uuid(); // unique key

        // Simpan data sementara + OTP ke cache selama 5 menit
        Cache::put($key, [
            'data' => $request->all(),
            'otp' => $otp,
        ], now()->addMinutes(5));

        // Kirim email OTP
        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json([
            'message' => 'OTP telah dikirim ke email',
            'key' => $key, // frontend simpan untuk submit OTP
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'otp' => 'required|numeric',
        ]);

        $cacheData = Cache::get($request->key);
        if (!$cacheData) {
            return response()->json(['message' => 'OTP tidak ditemukan atau kadaluarsa'], 422);
        }

        if ($cacheData['otp'] != $request->otp) {
            return response()->json(['message' => 'OTP salah'], 422);
        }

        $data = $cacheData['data'];

        // Simpan user ke database
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        // Assign role
        $user->assignRole($data['role_id']);

        // Hapus cache
        Cache::forget($request->key);

        return response()->json(['message' => 'User berhasil disimpan']);
}

    public function sendOtp(Request $request)
    {
        $otp = rand(100000, 999999); // kode 6 digit

        $request->session()->put('otp_data', [
            'otp' => $otp,  
            'email' => $request->email,
            'expires_at' => now()->addMinutes(5),
            'data' => $request->all(), // simpan data form sementara
        ]);

        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP berhasil dikirim ke email']);
    }



}
