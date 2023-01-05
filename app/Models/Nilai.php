<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Nilai extends Model
{
    protected $table = 'data_nilai';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nim', 'matkul_id', 'dosen_id', 'nilai', 'keterangan'
    ];
    protected $hidden = [
        'id'
    ];

    public $timestamps = false;
}
