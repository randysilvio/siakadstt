<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueTahunSemesterRule implements ValidationRule
{
    private $exceptId;

    public function __construct($exceptId = null)
    {
        $this->exceptId = $exceptId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $attribute akan menjadi 'tahun' atau 'semester',
        // kita perlu mendapatkan nilai dari request
        $tahun = request()->input('tahun');
        $semester = request()->input('semester');

        $query = DB::table('tahun_akademiks')
                    ->where('tahun', $tahun)
                    ->where('semester', $semester);

        if ($this->exceptId) {
            $query->where('id', '!=', $this->exceptId);
        }

        if ($query->exists()) {
            $fail('Kombinasi Tahun Akademik dan Semester sudah ada.');
        }
    }
}