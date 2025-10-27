<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'type',
        'statut',
        'client_id'
    ];

    /**
     * Génère automatiquement le numéro de compte
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($compte) {
            if (empty($compte->numero)) {
                $compte->numero = static::generateNumero();
            }
        });
    }

    /**
     * Génère un numéro de compte unique
     */
    public static function generateNumero()
    {
        do {
            $numero = 'CMPT' . date('Y') . strtoupper(substr(md5(microtime()), 0, 8));
        } while (static::where('numero', $numero)->exists());

        return $numero;
    }

    /**
     * Relation avec le client propriétaire du compte
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec les transactions du compte
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Calcule le solde du compte dynamiquement
     * Solde = Total dépôts - Total retraits
     */
    public function getSoldeAttribute()
    {
        $depots = $this->transactions()
            ->where('type', 'depot')
            ->sum('montant');

        $retraits = $this->transactions()
            ->whereIn('type', ['retrait', 'virement', 'paiement'])
            ->sum('montant');

        return $depots - $retraits;
    }
}
