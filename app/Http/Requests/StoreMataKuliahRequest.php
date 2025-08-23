<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PrasyaratSemesterRule;

class StoreMataKuliahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asumsikan otorisasi ditangani oleh middleware
    }

    public function rules(): array
    {
        return [
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'kode_mk' => 'required|unique:mata_kuliahs|max:10',
            'nama_mk' => 'required|max:255',
            'sks' => 'required|integer|min:1',
            'semester' => 'required|integer|min:1|max:8',
            'dosen_id' => 'required|exists:dosens,id',
            'prasyarat_id' => 'nullable|array',
            'prasyarat_id.*' => ['exists:mata_kuliahs,id', new PrasyaratSemesterRule((int)$this->input('semester'))],
            'jadwals' => 'required|array|min:1',
            'jadwals.*.hari' => 'required|string',
            'jadwals.*.jam_mulai' => 'required',
            'jadwals.*.jam_selesai' => 'required|after:jadwals.*.jam_mulai',
        ];
    }
}