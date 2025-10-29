<?php

namespace App\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'numero',
        'type',
        'statut',
        'client_id',
        'devise',
        'date_creation',
        'motif_blocage',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'date_creation' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec le modèle Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    /**
     * Relation avec les transactions du compte
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Global scope désactivé car SoftDeletes gère déjà deleted_at
        // static::addGlobalScope('notDeleted', function (Builder $builder) {
        //     $builder->whereNull('deleted_at');
        // });

        static::creating(function ($compte) {
            if (empty($compte->numero)) {
                $compte->numero = static::generateNumero();
            }
            if (empty($compte->date_creation)) {
                $compte->date_creation = now();
            }
            if (empty($compte->devise)) {
                $compte->devise = 'FCFA';
            }
        });
    }

    public static function generateNumero()
    {
        do {
            $numero = 'CMPT' . date('Y') . strtoupper(substr(md5(microtime()), 0, 8));
        } while (static::where('numero', $numero)->exists());

        return $numero;
    }

    /**
     * Scope local: rechercher un compte par son numéro.
     */
    public function scopeNumero(Builder $query, string $numero): Builder
    {
        return $query->where('numero', $numero);
    }

    /**
     * Scope local: filtrer les comptes par téléphone du client.
     */
    public function scopeClient(Builder $query, string $telephone): Builder
    {
        return $query->whereHas('client', function (Builder $q) use ($telephone) {
            $q->where('telephone', $telephone);
        });
    }

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
