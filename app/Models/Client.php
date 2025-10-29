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
        'id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'statut',
        'password',
        'code_verification'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'code_verification',
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
            if (empty($client->password)) {
                $client->password = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12));
            }
            if (empty($client->code_verification)) {
                $client->code_verification = strtoupper(\Illuminate\Support\Str::random(6));
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
