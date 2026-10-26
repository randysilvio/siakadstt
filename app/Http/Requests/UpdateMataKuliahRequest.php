<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PrasyaratSemesterRule;

class UpdateMataKuliahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mataKuliahId = $this->route('mata_kuliah')->id;
        return [
            'kurikulum_id'         => 'required|exists:kurikulums,id',
            'kode_mk'              => 'required|max:15|unique:mata_kuliahs,kode_mk,' . $mataKuliahId,
            'nama_mk'              => 'required|max:255',
            'sks'                  => 'required|integer|min:1',
            'semester'             => 'required|integer|min:1|max:8',
            'dosen_id'             => 'required|exists:dosens,id',
            'team_dosen_ids'       => 'nullable|array',
            'team_dosen_ids.*'     => 'exists:dosens,id',
            'prasyarat_id'         => 'nullable|array',
            'prasyarat_id.*'       => ['exists:mata_kuliahs,id', new PrasyaratSemesterRule((int) $this->input('semester'))],
            'jadwals'              => 'required|array|min:1',
            'jadwals.*.nama_kelas' => 'nullable|string|max:30',
            'jadwals.*.hari'       => 'required|string',
            'jadwals.*.jam_mulai'  => 'required',
            'jadwals.*.jam_selesai'=> 'required|after:jadwals.*.jam_mulai',
            'jadwals.*.ruang'      => 'nullable|string|max:100',
            'jadwals.*.kuota'      => 'nullable|integer|min:1|max:500',
        ];
    }
}