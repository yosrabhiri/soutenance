<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncadrantAcademique extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom_complet', 'email','role'
    ];

    public function internships()
    {
        return $this->hasMany(Stage::class);
    }
}
