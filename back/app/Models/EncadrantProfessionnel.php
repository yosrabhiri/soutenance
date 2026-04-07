<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncadrantProfessionnel extends Model
{
    use HasFactory;
protected $fillable = [
        'societe_id', 'nom_complet', 'fonction', 'departement', 'email'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function internships()
    {
        return $this->hasMany(Internship::class);
    }
}
