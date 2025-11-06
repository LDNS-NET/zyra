<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiusPostAuth extends Model
{
    protected $connection = 'mysql_radius';
    protected $table = 'radpostauth';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'pass',
        'reply',
        'authdate',
    ];
}

