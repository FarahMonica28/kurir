<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengirimanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_resi' => 'required|unique:pengiriman,no_resi',
            'kurir_id' => 'sometimes|exists:kurir,kurir_id',
            'paket' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:dikemas,dikirim,diterima',
            'tanggal_dibuat' => 'nullable|date',
            'tanggal_pengiriman' => 'nullable|date',
            'tanggal_penerimaan' => 'nullable|date',
            'penerima' => 'sometimes|string|max:255',
            'alamat' => 'sometimes|string',
            'biaya' => 'sometimes|numeric|min:0'
        ];
    }
}
