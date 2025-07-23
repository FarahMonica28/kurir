<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Http\Requests\StorePenggunaRequest;
use App\Http\Requests\UpdatePenggunaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\select;

class PenggunaController extends Controller
{
    /**
     * Get paginated list of pengguna
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        // $data = Pengguna::select('pengguna_id', 'name', 'email', 'phone', 'alamat', , 'photo')
        $data = Pengguna::select('pengguna_id', 'alamat', );
        $data = Pengguna::with('user')->select('pengguna_id', 'user_id', 'alamat') // Tambahkan relasi user
            ->when($request->search, function ($query, $search) {
                // $query->where('name', 'like', "%$search%")
                    $query->where('pengguna_id', 'like', "%$search%")
                        // ->orWhere('email', 'like', "%$search%")
                        // ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('alamat', 'like', "%$search%");
            })->latest()->paginate($per);

        $no = ($data->currentPage()-1) * $per + 1;
        foreach($data as $item){
            $item->no = $no++;
        }

        return response()->json($data);
    }

    /**
     * Store a newly created pengguna
     */

    public function getVerifiedUsers()
    {
        $pengguna = Pengguna::with('user')
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email_verified_at');
            })
            ->get();

        return response()->json($pengguna);
    }
    public function store(StorePenggunaRequest $request)
    {
        $validatedData = $request->validated();
        // $validatedData['password'] = Hash::make($validatedData['password']);
        // $validatedData[] = $validatedData[] ?? 5;

        if ($request->hasFile('photo')) {
            if ($pengguna->user->photo) {
                Storage::disk('public')->delete($pengguna->user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }
        // $pengguna = Pengguna::create($validatedData);

        // return response()->json([
        //     'success' => true,
        //     'pengguna' => [
        //         'pengguna_id' => $pengguna->pengguna_id,
        //         // 'name' => $pengguna->name,
        //         // 'email' => $pengguna->email,
        //         // 'phone' => $pengguna->phone,
        //         'alamat' => $pengguna->alamat,
        //          => $pengguna->rating,
        //         // 'photo' => $pengguna->photo
        //     ]
        // ]);

        $pengguna = Pengguna::create($validatedData);
        $pengguna->load('user'); // load relasi user

        return response()->json([
            'success' => true,
            'pengguna' => [
                'pengguna_id' => $pengguna->pengguna_id,
                'alamat' => $pengguna->alamat,
                //  => $pengguna->rating,
                'user' => [
                    'name' => $pengguna->user->name,
                    'email' => $pengguna->user->email,
                    'phone' => $pengguna->user->phone,
                    'photo' => $pengguna->user->photo,
                ],
            ],
        ]);
    }

    /**
     * Show a specific pengguna
     */
    public function show(Pengguna $pengguna)
    {
        $pengguna->load('user');
    
        return response()->json([
            // 'pengguna'=> ['alamat' => $pengguna->alamat],
            'user' => [
                    'name' => $pengguna->user->name,
                    'email' => $pengguna->user->email,
                    'phone' => $pengguna->user->phone,
                    'photo' => $pengguna->user->photo,
                    'alamat' => $pengguna->alamat,
                    // 'password' => $pengguna->user->password,
                ],
            ]);
    
    }

    /**
     * Update an existing pengguna
     */
    public function update(UpdatePenggunaRequest $request, Pengguna $pengguna)
    {
        $validatedData = $request->validated();
        

        // if (!$request->filled('pengguna_id')) {
        //     return response()->json(['message' => 'pengguna_id wajib diisi'], 422);
        // }
        
        $validatedData['pengguna_id'] = $request->input('pengguna_id'); 
        
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        // if ($request->filled()) {
        //     $validatedData[] = max(1, min(5, $validatedData[]));
        // }

        if ($request->hasFile('photo')) {
            if ($pengguna->user->photo) {
                Storage::disk('public')->delete($pengguna->user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }

        
        $pengguna->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            // 'password' => $request->password,
            'photo' => $validatedData['photo'] ?? $pengguna->user->photo,
            // 'photo' => $request->photo,
        ]);
        
        $pengguna->update($validatedData);
        return response()->json([
            'success' => true,
            'pengguna' => [
                'pengguna_id' => $pengguna->pengguna_id,
                // 'name' => $pengguna->name,
                // 'email' => $pengguna->email,
                // 'phone' => $pengguna->phone,
                'alamat' => $pengguna->alamat,
                //  => $pengguna->rating,
                // 'photo' => $pengguna->photo
            ]
        ]);
    }

    /**
     * Get all pengguna
     */
    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Pengguna::select('pengguna_id', 'alamat', )->get()
            // 'data' => Pengguna::select('pengguna_id', 'name', 'email', 'phone', 'alamat', , 'photo')->get()
        ]);
    }

    public function list()
    {
        $pengguna = Pengguna::with('user:id,name')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->user->name,
            ];
        });

        return response()->json([
            'pengguna' => $pengguna,
        ]);
    }


    public function destroy(Pengguna $pengguna)
{
    // Hapus foto dari storage jika user memiliki foto
    if ($pengguna->user && $pengguna->user->photo) {
        Storage::disk('public')->delete($pengguna->user->photo);
    }

    // Hapus data user yang terkait
    if ($pengguna->user) {
        $pengguna->user->delete();
    }

    // Hapus data pengguna
    $pengguna->delete();

    return response()->json([
        'success' => true,
        'message' => 'Data pengguna berhasil dihapus'
    ]);
}
}


    // }



