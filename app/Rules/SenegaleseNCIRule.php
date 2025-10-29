<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SenegaleseNCIRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove any spaces or dashes
        $cleaned = preg_replace('/[\s\-]/', '', $value);

        // Senegalese NCI format: 1 letter + 7 digits + 1 letter (e.g., A1234567B)
        if (!preg_match('/^[A-Z]\d{7}[A-Z]$/', $cleaned)) {
            $fail('Le numéro NCI doit être au format sénégalais valide (1 lettre + 7 chiffres + 1 lettre).');
            return;
        }

        // Additional validation: check if the number part is reasonable
        $numberPart = substr($cleaned, 1, 7);
        if ($numberPart < 1000000 || $numberPart > 9999999) {
            $fail('Le numéro NCI contient une séquence numérique invalide.');
        }
    }
}
