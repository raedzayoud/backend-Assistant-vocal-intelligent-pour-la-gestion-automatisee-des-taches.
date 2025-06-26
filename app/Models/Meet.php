<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meet extends Model
{
     protected $fillable = [
        'user_id',
        'title',
        'start_time',
        'room',
        'link',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
