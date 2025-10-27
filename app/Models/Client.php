<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'statut'
    ];

    /**
     * Relation avec les comptes du client
     */
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}
