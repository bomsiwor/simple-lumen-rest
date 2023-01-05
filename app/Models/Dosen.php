<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{

    protected $table = 'dosen';
    public $timestamps = false;

    protected $fillable = [
        'nama', 'id', 'user_id'
    ];
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
