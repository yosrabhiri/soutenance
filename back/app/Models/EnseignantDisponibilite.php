<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnseignantDisponibilite extends Model
{
 use HasFactory;

    protected $table = 'enseignant_disponibilites';

    protected $fillable = [
        'enseignant_id',
        'creneau_id',
        'statut',
    ];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id', 'Code_enseignant');
    }

    public function creneau()
    {
        return $this->belongsTo(CreneauSoutenance::class, 'creneau_id');
    }
}
