<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class Etudiant  extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
     protected $fillable = [
        'numero_inscription', 'cin', 'date_delivrance_cin', 'lieu_delivrance_cin',
        'prenom', 'nom', 'adresse', 'code_postal', 'date_naissance', 'lieu_naissance',
        'nationalite', 'telephone', 'cnss', 'profession', 'employeur',
        'etat_civil', 'etat_militaire', 'genre', 'photo', 'annee_universitaire',
        'annee_bac', 'moyenne_bac', 'session_bac', 'mention_bac', 'section_bac',
        'pays_bac', 'statut_universitaire', 'diplome_id', 'niveau_id', 'specialite_id','email','password'
    ];
     public function reunions()
    {
        return $this->belongsToMany(Reunion::class, 'etudiant_reunion');
    }
    public function stages()
{
    return $this->hasMany(Stage::class);
}


    public function diplome()
    {
        return $this->belongsTo(Diplome::class);
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
     public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}

