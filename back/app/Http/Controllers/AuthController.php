<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Enseignant;
use Validator;
use App\Models\Etudiant;
class AuthController extends Controller
{
    public function login(Request $request)
    {
         $credentials = $request->only(['email', 'password']);

        // Vérifier si c'est un enseignant
        if ($token = Auth::guard('enseignant')->attempt($credentials)) {
            return response()->json([
                'status' => 'success',
                'role' => 'enseignant',
                'user' => Auth::guard('enseignant')->user(),
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        }

        // Vérifier si c'est un étudiant
        if ($token = Auth::guard('etudiant')->attempt($credentials)) {
            return response()->json([
                'status' => 'success',
                'role' => 'etudiant',
                'user' => Auth::guard('etudiant')->user(),
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }
     public function registerEtudiant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_inscription' => 'required|unique:etudiants',
            'prenom' => 'required',
            'nom' => 'required',
            'email' => 'required|email|unique:etudiants',
            'password' => 'required|confirmed|min:8',
            'adresse' => 'required',
            'code_postal' => 'required',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required',
            'nationalite' => 'required',
            'genre' => 'required|in:masculin,féminin',
            'annee_universitaire' => 'required',
            'annee_bac' => 'required|digits:4',
            'moyenne_bac' => 'required|numeric',
            'session_bac' => 'required|in:principale,contrôle',
            'mention_bac' => 'required|in:passable,assez bien,bien,très bien,excellent',
            'section_bac' => 'required',
            'pays_bac' => 'required',
            'statut_universitaire' => 'required',
            'diplome_id' => 'required|exists:diplomes,id',
            'niveau_id' => 'required|exists:niveaux,id',
            'specialite_id' => 'required|exists:specialites,id',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $etudiant = Etudiant::create([
            'numero_inscription' => $request->numero_inscription,
            'cin' => $request->cin,
            'date_delivrance_cin' => $request->date_delivrance_cin,
            'lieu_delivrance_cin' => $request->lieu_delivrance_cin,
            'prenom' => $request->prenom,
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => bcrypt(request()->password),
            'adresse' => $request->adresse,
            'code_postal' => $request->code_postal,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'nationalite' => $request->nationalite,
            'telephone' => $request->telephone,
            'cnss' => $request->cnss,
            'profession' => $request->profession,
            'employeur' => $request->employeur,
            'etat_civil' => $request->etat_civil,
            'etat_militaire' => $request->etat_militaire,
            'genre' => $request->genre,
            'photo' => $request->photo,
            'annee_universitaire' => $request->annee_universitaire,
            'annee_bac' => $request->annee_bac,
            'moyenne_bac' => $request->moyenne_bac,
            'session_bac' => $request->session_bac,
            'mention_bac' => $request->mention_bac,
            'section_bac' => $request->section_bac,
            'pays_bac' => $request->pays_bac,
            'statut_universitaire' => $request->statut_universitaire,
            'diplome_id' => $request->diplome_id,
            'niveau_id' => $request->niveau_id,
            'specialite_id' => $request->specialite_id,
        ]);

        return response()->json(['etudiant' => $etudiant], 201);
    }

    // REGISTER ETUDIANT
    public function registerEnseignant(Request $request)
    {
        $validator=Validator::make($request->all(), [
        'Code_enseignant' => 'required|string|unique:enseignants,Code_enseignant',
        'email' => 'required|email|unique:enseignants,Email',
        'password' => 'required|string|confirmed|min:8',
        'NomEnseignant' => 'required|string',
        'PrenomEnseignant' => 'required|string',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $enseignant = Enseignant::create([
        'Code_enseignant' => $request->Code_enseignant,
        'Email' => $request->email,
        'password' => bcrypt($request->password),
        'Année_recrutement' => $request->Année_recrutement,
        'NomEnseignant' => $request->NomEnseignant,
        'PrenomEnseignant' => $request->PrenomEnseignant,
        'Nom_Prenom_Enseignant' => $request->Nom_Prenom_Enseignant,
        'Code_EnsCh' => $request->Code_EnsCh,
        'Code_Grade' => $request->Code_Grade,
        'Coef_Kilometrique' => $request->Coef_Kilometrique,
        'Type_Impot' => $request->Type_Impot,
        'Code_Discpline' => $request->Code_Discpline,
        'Code_Departement' => $request->Code_Departement,
        'Code_Perm' => $request->Code_Perm,
        'Sirveillance' => $request->Sirveillance,
        'Cide_Stat' => $request->Cide_Stat,
        'Nom_Prenom_Ar' => $request->Nom_Prenom_Ar,
        'Orre_paiement' => $request->Orre_paiement,
        'Code_Diplome' => $request->Code_Diplome,
        'Sexe' => $request->Sexe,
        'CIN' => $request->CIN,
        'Nouveau_Ancien' => $request->{'Nouveau_Ancien'},
        'RIB' => $request->RIB,
        'role' => $request->role ?? 'enseignant',
        'departement_id'=>$request->departement_id
    ]);
        return response()->json($enseignant, 201);
    }
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}