<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialite extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'niveau_id','departement_id'];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }
    public function etudiants()
{
    return $this->hasMany(Etudiant::class);
}
public function departement()
{
    return $this->belongsTo(Departement::class);
}



}
