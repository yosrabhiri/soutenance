<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateRapport extends Model
{
    use HasFactory;
     protected $table = 'dates_rapport';
    protected $fillable = ['date_ouverture', 'date_fermeture', 'type_diplome'];
}
