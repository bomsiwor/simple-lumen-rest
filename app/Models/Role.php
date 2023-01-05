<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    protected $table = 'role';

    protected $hidden = [];

    public function user()
    {
        return $this->hasMany(User::class);
    }
}
