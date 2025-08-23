<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class PrasyaratSemesterRule implements ValidationRule
{
    private $semesterTarget;

    /**
     * @param int $semesterTarget Semester dari mata kuliah yang sedang divalidasi.
     */
    public function __construct(int $semesterTarget)
    {
        $this->semesterTarget = $semesterTarget;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $value adalah array dari ID prasyarat yang dipilih
        if (!is_array($value)) {
            return; // Lewati jika bukan array
        }

        $prasyaratTidakValid = DB::table('mata_kuliahs')
                                ->whereIn('id', $value)
                                ->where('semester', '>=', $this->semesterTarget)
                                ->pluck('nama_mk');

        if ($prasyaratTidakValid->isNotEmpty()) {
            $fail('Mata kuliah prasyarat harus berasal dari semester yang lebih rendah. Pelanggaran pada: ' . $prasyaratTidakValid->implode(', '));
        }
    }
}