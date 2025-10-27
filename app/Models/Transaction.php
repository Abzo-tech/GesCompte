<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'compte_id',
        'type',
        'montant',
        'description',
        'beneficiaire',
        'date_transaction'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_transaction' => 'datetime'
    ];

    /**
     * Génère automatiquement la référence de transaction
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = static::generateReference();
            }
        });
    }

    /**
     * Génère une référence de transaction unique
     */
    public static function generateReference()
    {
        do {
            $reference = 'TRX' . date('Ymd') . strtoupper(substr(md5(microtime()), 0, 6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Relation avec le compte de la transaction
     */
    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
