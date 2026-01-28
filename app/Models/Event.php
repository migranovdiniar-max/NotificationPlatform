<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        "type",
        "payload",
    ];

    protected $casts = [
        "payload" => "array",
    ];
}
