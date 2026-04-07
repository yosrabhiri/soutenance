<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StageController;
use App\Http\Controllers\EnseignantAuthController;
use App\Http\Controllers\EtudiantAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DateRapportController;
use App\Http\Controllers\SoutenanceController;
use App\Http\Controllers\PeriodeSoutenanceController;


Route::middleware('auth:enseignant')->group(function () {
    Route::get('/mes-etudiants', [EnseignantController::class, 'getEtudiantsEncadres']);
    Route::post('/stage/{stage}/valider', [EnseignantController::class, 'validerStage']);
      Route::post('/reunions', [ReunionController::class, 'store']);
      Route::get('/reunions/etudiants', [ReunionController::class, 'getReunionsEtudiants']);
      Route::get('/reunions/calendar', [ReunionController::class, 'getReunionsAvecEtudiants']);
      
      Route::get('/mesDispos', [PeriodeSoutenanceController::class, 'mesDisponibilites']);

});

Route::middleware('auth:etudiant')->group(function () {
    Route::get('/stage/form', [StageController::class, 'formData']); // récupère les données initiales
    Route::post('/stage/save', [StageController::class, 'store']); // enregistrement
     Route::get('/stage/historique', [StageController::class, 'historique']); // ⚡ historique
    // routes/api.php
   Route::post('/stage/depose', [StageController::class, 'deposerRapport']); // étudiant
    Route::get('/etudiant/reunion', [reunionController::class, 'getReunionsEtudiantConnecte']); // récupère les données initiales
    Route::post('/stage/search', [StageController::class, 'searchRapport']); //motcle

});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/enseignant', [AuthController::class, 'registerEnseignant']);
Route::post('/register/etudiant', [AuthController::class, 'registerEtudiant']);
Route::get('/diplomes', [StageController::class, 'Diplome']);
Route::get('/niveaux', [StageController::class, 'niveau']);
Route::get('/specialites', [StageController::class, 'specialite']);
Route::get('/salles', [PeriodeSoutenanceController::class, 'salles']);
Route::post('/logout', [AuthController::class, 'logout']);


    Route::get('/diplomes', [StageController::class, 'Diplome']);
    Route::get('/niveaux', [StageController::class, 'niveau']);
    Route::get('/specialites', [StageController::class, 'specialite']);


Route::middleware('auth:etudiant')->group(function () {
    Route::post('/demandes', [DemandeController::class, 'store']); // étudiant

});
Route::get('/periodes-soutenances', [PeriodeSoutenanceController::class, 'index']);
Route::middleware('auth:enseignant')->group(function () {
    Route::get('/demandes', [DemandeController::class, 'index']);   // enseignant
    Route::post('/demandes/{id}/repondre', [DemandeController::class, 'repondre']); // enseignant
    //Route::get('/notification', [NotificationController::class, 'index']);
    //Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
    Route::get('/stagesListes',[StageController::class,'AffecteAStage']);
     Route::get('/stages', [StageController::class, 'getAllStages']);
    Route::put('/stages/{id}/update-status', [StageController::class, 'updateStatus']);
    // ✅ Récupérer les dates actuelles
Route::get('/dates-rapport', [DateRapportController::class, 'getDates']);

// ✅ Mettre à jour les dates (admin)
Route::post('/dates-rapport/update', [DateRapportController::class, 'updateDates']);
});


Route::middleware(['auth:etudiant,enseignant'])->group(function () {
    Route::get('/notification', [NotificationController::class, 'index']);
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
    
});

Route::middleware('auth:enseignant')->group(function () {
    Route::get('/dashboard/etudiants-affectes', [DashboardController::class, 'etudiantsAffectes']);
    Route::get('/dashboard/etudiants-issat', [DashboardController::class, 'etudiantsStageISSAT']);
    Route::get('/dashboard/etudiants-externes', [DashboardController::class, 'etudiantsStageExterne']);
    /*Route::get('/dashboard/etudiants-sections', [DashboardController::class, 'etudiantsParSpecialiteLicence']);
    Route::get('/dashboard/etudiants-specialites', [DashboardController::class, 'etudiantsParSpecialiteSuperieur']);*/
    Route::get('/dashboard/etudiants-par-specialite-licence', [DashboardController::class, 'etudiantsParSpecialiteLicence']);
    Route::get('/dashboard/etudiants-par-specialite-superieur', [DashboardController::class, 'etudiantsParSpecialiteSuperieur']);
    Route::get('/sout', [SoutenanceController::class, 'genererSoutenances']);
    Route::get('/mes-soutenances', [PeriodeSoutenanceController::class, 'mesSoutenances']);
    Route::get('/soutenances', [PeriodeSoutenanceController::class, 'soutenances']);
    
    //Route::post('/updateSout', [SoutenanceController::class, 'updateDisponibilite']);
    // Route::get('/defaultsout', [SoutenanceController::class, 'initialiserDisponibilitesTous']);
      Route::get('/periodesout', [PeriodeSoutenanceController::class, 'index']);
      
    
});
Route::post('/periodes-soutenances', [PeriodeSoutenanceController::class, 'store']);
    Route::put('/periodes-soutenances/{periodeId}', [PeriodeSoutenanceController::class, 'update']);
    Route::post('/periodes-soutenances/{periodeId}/salles', [PeriodeSoutenanceController::class, 'assignerSalles']);
    Route::post('/periodes-soutenances/{periodeId}/generate-creneaux', [PeriodeSoutenanceController::class, 'genererCreneaux']);
    Route::get('/periodes-soutenances/{periodeId}/creneaux', [PeriodeSoutenanceController::class, 'creneaux']);
    Route::get('/periodes-soutenances/{periodeId}/planning-input', [PeriodeSoutenanceController::class, 'planningInput']);
    Route::get('/periodes-soutenances/{periodeId}/cp-sat-preview', [PeriodeSoutenanceController::class, 'cpSatPreview']);
    Route::post('/periodes-soutenances/{periodeId}/launch-generation', [PeriodeSoutenanceController::class, 'launchGeneration']);
    Route::get('/periodes-soutenances/{periodeId}/generation-status', [PeriodeSoutenanceController::class, 'generationStatus']);
    Route::get('/periodes-soutenances/{periodeId}/mes-disponibilites', [PeriodeSoutenanceController::class, 'mesDisponibilitesParPeriode']);
    Route::post('/periodes-soutenances/{periodeId}/mes-disponibilites', [PeriodeSoutenanceController::class, 'enregistrerMesDisponibilites']);
