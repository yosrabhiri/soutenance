<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class NotificationController extends Controller
{
    // Récupérer toutes les notifications de l'utilisateur connecté
    public function index()
{
    $user = auth()->user(); // ou auth('enseignant')->user()
    // Test 1 : Vérifier si null
    if (!$user) {
        return response()->json(['message' => 'Aucun utilisateur trouvé'], 401);
    }
    //if($user->role=='enseignant'){
        // Test 2 : Retourner les infos de l'utilisateur
    return response()->json([
        'id' => $user->Code_enseignant,
        'nom' => $user->NomEnseignant,
        'prenom' => $user->PrenomEnseignant,
        'email' => $user->Email,
        'role'=>$user->role,
        'notifications' => $user->notifications
    ]);
    /*}
   else{
    return response()->json([
        'id' => $user->id,
        'nom' => $user->nom,
        'prenom' => $user->prenom,
        'email' => $user->email,
        'role'=>$user->role,
        'notifications' => $user->notifications
    ]);
}*/}


    // Marquer une notification comme lue
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'ok']);
    }
}
