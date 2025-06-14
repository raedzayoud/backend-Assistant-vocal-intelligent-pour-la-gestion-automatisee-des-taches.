<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $fillable = [
        'titre',
        'description',
        'projet_id',
    ];


    public function projet()
    {
        return $this->belongsTo(Projet::class);
    }
}
