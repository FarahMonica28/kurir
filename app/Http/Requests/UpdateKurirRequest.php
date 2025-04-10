<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKurirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
{
    return [
        'kurir_id' => 'sometimes|integer|exists:kurir_id', // Tambahkan validasi ID
        // 'name' => 'required|string',
        // 'email' => 'required|email|unique:kurir,email,' . $this->kurir->id,
        // 'phone' => 'required|string',
        'password' => 'nullable|min:6', // Password opsional saat edit
        'status' => 'required|string',
        // 'rating' => 'required|integer|min:1|max:5',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ];
}


    public function messages()
    {
        return [
            // 'kurir_id.integer' => 'ID kurir harus berupa angka.',
            // 'kurir_id.exists' => 'ID kurir harus diisi.'
            // 'name.required' => 'Nama harus diisi.',
            // 'email.required' => 'Email harus diisi.',
            // 'email.unique' => 'Email sudah digunakan oleh kurir lain.',
            // 'phone.required' => 'Nomor telepon harus diisi.',
            // 'phone.unique' => 'Nomor telepon sudah digunakan oleh kurir lain.',
            // 'photo.image' => 'File foto harus berupa gambar.',
            // 'photo.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            // 'photo.max' => 'Ukuran gambar maksimal 2MB.',
            'status.required' => 'Status harus diisi.',
            'status.in' => 'Status harus "aktif" atau "nonaktif".',
            // 'password.min' => 'Password minimal 6 karakter.',
            // 'password.confirmed' => 'Konfirmasi password tidak cocok.',
            // 'rating.numeric' => 'Rating harus berupa angka.',
            // 'rating.min' => 'Rating minimal 1.',
            // 'rating.max' => 'Rating maksimal 5.',
        ];
    }

    protected $table = 'kurir';
}
