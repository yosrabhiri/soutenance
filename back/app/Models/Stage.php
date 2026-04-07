<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;
    protected $fillable = [
        'type','etudiant_id', 'societe_id', 'encadrant_professionnel_id', 'enseignant_id',
        'description_taches', 'date_debut', 'date_fin', 'statut','chemin_document', 'url_overleaf','etat_validation','chemin_attestation','validation_academique','version_pdf','traite_par' ,'code_sujet','mots_cles'   ];
        
            protected $casts = [
    'mots_cles' => 'array',
];
    public function Etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }



    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }

    public function EncadrantProfessionnel()
    {
        return $this->belongsTo(EncadrantProfessionnel::class);
    }

    public function Enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id', 'Code_enseignant');
    }

     public function enseignant_traitant()
{
    return $this->belongsTo(Enseignant::class, 'traite_par', 'Code_enseignant');
}
}

