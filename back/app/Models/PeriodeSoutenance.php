<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeSoutenance extends Model


{
    use HasFactory;

    protected $table = 'periodes_soutenances';

    protected $fillable = [
        'diplome_id',
        'date_debut',
        'date_fin',
        'duree_minutes',
        'heure_debut',
        'heure_fin',
        'generation_status',
        'generation_algorithm',
        'generation_started_at',
        'generation_finished_at',
        'generation_error',
        'generation_meta',
    ];

    protected $casts = [
        'generation_started_at' => 'datetime',
        'generation_finished_at' => 'datetime',
        'generation_meta' => 'array',
    ];

    public function diplome()
    {
        return $this->belongsTo(Diplome::class);
    }

    public function salles()
    {
        return $this->belongsToMany(Salle::class, 'periode_soutenance_salles');
    }

    public function creneaux()
    {
        return $this->hasMany(CreneauSoutenance::class, 'periode_soutenance_id');
    }
}
