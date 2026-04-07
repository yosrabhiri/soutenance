<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class departement extends Model
{
    use HasFactory;
     protected $fillable = ['nom', 'code'];


    public function enseignants()
{
    return $this->hasMany(Enseignant::class);
}
public function specialites()
{
    return $this->hasMany(Specialite::class);
}

}
