<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurySoutenance extends Model
{
    use HasFactory;

    protected $table = 'jury_soutenance';

    protected $fillable = [
        'soutenance_id',
        'enseignant_id',
        'role',
    ];

    public function soutenance()
    {
        return $this->belongsTo(soutenance::class, 'soutenance_id');
    }

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id', 'Code_enseignant');
    }
}
