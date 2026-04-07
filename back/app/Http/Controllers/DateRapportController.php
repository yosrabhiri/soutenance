<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DateRapport;
class DateRapportController extends Controller
{
    public function getDates()
{
    $dates = DateRapport::latest()->first();
    return response()->json($dates);
}

public function updateDates(Request $request)
{
    $request->validate([
        'date_ouverture' => 'required|date',
        'date_fermeture' => 'required|date|after_or_equal:date_ouverture',
    ]);

    $dates = DateRapport::latest()->first();

    if ($dates) {
        $dates->update($request->all());
    } else {
        $dates = DateRapport::create($request->all());
    }

    return response()->json(['message' => 'Dates mises à jour', 'dates' => $dates]);
}

}
