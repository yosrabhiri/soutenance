<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreneauSoutenance extends Model
{
    protected $table = 'creneaux_soutenances';
    use HasFactory;
     protected $fillable = [
        'periode_soutenance_id',
        'date',
        'code_slot',
        'heure_debut',
        'heure_fin',
        'ordre',
    ];

    public function periodeSoutenance()
    {
        return $this->belongsTo(PeriodeSoutenance::class, 'periode_soutenance_id');
    }

    public function soutenances()
    {
        return $this->hasMany(Soutenance::class, 'creneau_id');
    }
    public function disponibilitesEnseignants()
{
    return $this->hasMany(EnseignantDisponibilite::class, 'creneau_id');
}
}
