<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reunion extends Model
 
{
    use HasFactory;
     protected $table = 'reunion';
      protected $fillable = [
        'code_enseignant',
         'jour',
          'heure',
           'salle',
            'note',
            'titre'
    ];
     public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function etudiants()
    {
        return $this->belongsToMany(Etudiant::class, 'etudiant_reunion');
    }

}
