<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Societe extends Model
{
    use HasFactory;
     protected $fillable = [
        'nom', 'adresse', 'secteur_activite', 'telephone', 'email', 'website', 'linkedin'
    ];

    public function professionalSupervisors()
    {
        return $this->hasMany(EncadrantProfessionnel::class);
    }

    public function stages()
    {
        return $this->hasMany(stage::class);
    }
}
