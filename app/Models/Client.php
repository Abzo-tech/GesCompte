<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'statut'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (empty($client->id)) {
                $client->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relation avec les comptes du client
     */
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }

    /**
     * Get the client's full name
     */
    public function getFullNameAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
