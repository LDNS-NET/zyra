<?php

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    protected $connection = 'mysql_radius';
    protected $table = 'radcheck';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'attribute',
        'op',
        'value',
    ];
}
