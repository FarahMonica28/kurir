<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
{
    return [
        'pengguna_id' => 'sometimes|integer|exists:pengguna_id', // Tambahkan validasi ID
        'alamat' => 'required|string',
    ];
}

    protected $table = 'pengguna';
}
