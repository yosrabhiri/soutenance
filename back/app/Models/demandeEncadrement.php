<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class demandeEncadrement extends Model
{
    use HasFactory;

    protected $table = 'demandes_encadrement';

    // Les colonnes qu’on peut remplir en masse
    protected $fillable = [
        'etudiant_id',
        'enseignant_id',
        'statut',
        'message',
    ];

    /**
     * Relation : une demande appartient à un étudiant
     */
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    /**
     * Relation : une demande appartient à un enseignant
     */
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id');
    }
}
