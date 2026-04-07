<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Models\EnseignantDisponibilite;
class Enseignant extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    protected $table = 'enseignants';

    protected $primaryKey = 'Code_enseignant';
    public $incrementing = false;
    protected $keyType = 'string';
     
    protected $fillable = [
        'Code_enseignant',
        'password',
        'Année_recrutement',
        'NomEnseignant',
        'PrenomEnseignant',
        'Nom_Prenom_Enseignant',
        'Code_EnsCh',
        'Code_Grade',
        'Coef_Kilometrique',
        'Type_Impot',
        'Code_Discpline',
        'Code_Departement',
        'Code_Perm',
        'Sirveillance',
        'Cide_Stat',
        'Nom_Prenom_Ar',
        'Orre_paiement',
        'Code_Diplome',
        'Email',
        'Sexe',
        'CIN',
        'Nouveau-Ancien',
        'RIB',
        'role',
        'disponibilites',
        'departement_id'
    ];
    
public function user()
{
    return $this->belongsTo(User::class);
}
 public function getJWTIdentifier()
    {
        return (string) $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function stages()
{
    return $this->hasMany(Stage::class);
}
public function departement()
{
    return $this->belongsTo(Departement::class);
}
public function disponibilitesSoutenances()
{
    return $this->hasMany(EnseignantDisponibilite::class, 'enseignant_id', 'Code_enseignant');
}


}
