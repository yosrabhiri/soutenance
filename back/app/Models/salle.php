<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;
    
    protected $fillable = ['nom', 'departement_id'];

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }
    

    public function soutenances()
    {
        return $this->hasMany(Soutenance::class);
    }
    public function periodesSoutenances()
{
    return $this->belongsToMany(PeriodeSoutenance::class, 'periode_soutenance_salles');
}
}
