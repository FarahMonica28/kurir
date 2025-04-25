<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengirimanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_resi' => 'required|unique:pengiriman,no_resi',
            'kurir_id' => 'required|exists:kurir,kurir_id',
            'paket' => 'required|string|max:255',
            'status' => 'required|in:dikemas,dikirim,diterima',
            'tanggal_dibuat' => 'nullable|date',
            'tanggal_pengiriman' => 'nullable|date',
            'tanggal_penerimaan' => 'nullable|date',
            'penerima' => 'required|string|max:255',
            'alamat' => 'required|string',
            'biaya' => 'required|numeric|min:0'
        ];
    }
}
