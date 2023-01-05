<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $primaryKey = 'nim';

    protected $fillable = [
        'nim', 'user_id', 'nama', 'tl', 'jurusan'
    ];
    protected $hidden = [];

    public $timestamps = false;

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function test()
    {
        $data = DB::table('mahasiswa')->where('user_id', 17)->get();
        return $data;
    }
}
