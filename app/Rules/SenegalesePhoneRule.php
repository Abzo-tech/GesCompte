<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SenegalesePhoneRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove any spaces, dashes, or parentheses
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $value);

        // Check if it starts with +221 or 221 or just the number
        if (preg_match('/^\+?221(77|78|76|70|75|33)\d{7}$/', $cleaned)) {
            return;
        }

        // Check if it starts with 0
        if (preg_match('/^0(77|78|76|70|75|33)\d{7}$/', $cleaned)) {
            return;
        }

        $fail('Le numéro de téléphone doit être un numéro sénégalais valide.');
    }
}
