<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Http\Requests\StoreKurirRequest;
use App\Http\Requests\UpdateKurirRequest;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\select;

class KurirController extends Controller
{
    /**
     * Get paginated list of kurir
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        // $data = Kurir::select('kurir_id', 'name', 'email', 'phone', 'status', 'rating', 'photo')
        $data = Kurir::select('kurir_id', 'status', 'rating');
        $data = Kurir::with('user')->select('kurir_id', 'user_id', 'status', 'rating') // Tambahkan relasi user
            ->when($request->search, function ($query, $search) {
                // $query->where('name', 'like', "%$search%")
                    $query->where('kurir_id', 'like', "%$search%")
                        ->orWhere('status', 'like', "%$search%")
                        ->orWhere('rating', 'like', "%$search%");
            })->latest()->paginate($per);

        $no = ($data->currentPage()-1) * $per + 1;
        foreach($data as $item){
            $item->no = $no++;
        }

        return response()->json($data);
    }

    /**
     * Store a newly created kurir
     */
    public function store(StoreKurirRequest $request)
    {
        $validatedData = $request->validated();
        // $validatedData['password'] = Hash::make($validatedData['password']);
        // $validatedData['rating'] = $validatedData['rating'] ?? 5;

        if ($request->hasFile('photo')) {
            if ($kurir->user->photo) {
                Storage::disk('public')->delete($kurir->user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }

        $kurir = Kurir::create($validatedData);
        $kurir->load('user'); // load relasi user

        return response()->json([
            'success' => true,
            'kurir' => [
                'kurir_id' => $kurir->kurir_id,
                'status' => $kurir->status,
                // 'rating' => $kurir->rating,
                'user' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
                    'photo' => $kurir->user->photo,
                ],
            ],
        ]);
    }

    /**
     * Show a specific kurir
     */
    public function show(Kurir $kurir)
    {
        $kurir->load('user');
    
        return response()->json([
            // 'kurir'=> ['status' => $kurir->status],
            'user' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
                    'photo' => $kurir->user->photo,
                    'status' => $kurir->status,
                    // 'password' => $kurir->user->password,
                ],
            ]);
    
    }

    /**
     * Update an existing kurir
     */
    public function update(UpdateKurirRequest $request, Kurir $kurir)
    {
        $validatedData = $request->validated();
        

        // if (!$request->filled('kurir_id')) {
        //     return response()->json(['message' => 'kurir_id wajib diisi'], 422);
        // }
        
        $validatedData['kurir_id'] = $request->input('kurir_id'); 
        
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        // if ($request->filled('rating')) {
        //     $validatedData['rating'] = max(1, min(5, $validatedData['rating']));
        // }

        if ($request->hasFile('photo')) {
            if ($kurir->user->photo) {
                Storage::disk('public')->delete($kurir->user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }

        
        $kurir->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            // 'password' => $request->password,
            'photo' => $validatedData['photo'] ?? $kurir->user->photo,
            // 'photo' => $request->photo,
        ]);
        
        $kurir->update($validatedData);
        return response()->json([
            'success' => true,
            'kurir' => [
                'kurir_id' => $kurir->kurir_id,
                // 'name' => $kurir->name,
                // 'email' => $kurir->email,
                // 'phone' => $kurir->phone,
                'status' => $kurir->status,
                // 'rating' => $kurir->rating,
                // 'photo' => $kurir->photo
            ]
        ]);
    }

//     public function ringkasanKurir()
// {
//     $kurirId = auth()->user()->kurir->kurir_id;

//     $averageRating = Transaksi::where('kurir_id', $kurirId)
//         ->whereNotNull('penilaian')
//         ->avg('penilaian'); // asumsi kolomnya 'penilaian' adalah angka (1–5)

//     return response()->json([
//         'avg_rating' => round($averageRating, 2),
//     ]);
// }

    public function toggleStatus($kurir_id)
    {
        $kurir = Kurir::findOrFail($kurir_id);

        $kurir->status = $kurir->status === 'aktif' ? 'nonaktif' : 'aktif';
        $kurir->save();

        return response()->json([
            'message' => 'Status berhasil diperbarui.',
            'status' => $kurir->status
        ]);
    }


    /**
     * Get all kurir
     */
    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Kurir::select('kurir_id', 'status', 'rating')->get()
            // 'data' => Kurir::select('kurir_id', 'name', 'email', 'phone', 'status', 'rating', 'photo')->get()
        ]);
    }

    public function list()
    {
        $kurir = Kurir::with('user:id,name')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->user->name,
            ];
        });

        return response()->json([
            'kurir' => $kurir,
        ]);
    }

    public function destroy(Kurir $kurir)
{
    // Hapus foto dari storage jika user memiliki foto
    if ($kurir->user && $kurir->user->photo) {
        Storage::disk('public')->delete($kurir->user->photo);
    }

    // Hapus data user yang terkait
    if ($kurir->user) {
        $kurir->user->delete();
    }

    // Hapus data kurir
    $kurir->delete();

    return response()->json([
        'success' => true,
        'message' => 'Data kurir berhasil dihapus'
    ]);
}
}


