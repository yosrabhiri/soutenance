<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class soutenance extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'stage_id',
        'periode_soutenance_id',
        'creneau_id',
        'date_heure',
        'salle_id',
    ];

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function jurySoutenances()
    {
        return $this->hasMany(JurySoutenance::class);
    }

    public function creneau()
    {
        return $this->belongsTo(CreneauSoutenance::class, 'creneau_id');
    }

     
}
