<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiusUser extends Model
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
