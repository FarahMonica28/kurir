<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan ini 'true' agar request bisa diproses
    }

    public function rules()
    {
        return [
            'pengguna_id' => 'sometimes|integer,', // Tambahkan validasi ID
            'alamat' => 'required|string',
        ];
    }

    protected $table = 'pengguna';
}
