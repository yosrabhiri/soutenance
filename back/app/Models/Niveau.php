<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
     use HasFactory;

    protected $fillable = ['nom'];

    /*public function niveaux()
    {
        return $this->hasMany(Niveau::class);
    }*/
    public function specialites()
{
    return $this->hasMany(Specialite::class);
}
public function etudiants()
{
    return $this->hasMany(Etudiant::class);
}

}
