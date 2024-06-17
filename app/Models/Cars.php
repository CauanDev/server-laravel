<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cars extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'owner_id',
        'brand',
        'model',
        'year',
        'last_service',
        'created_at',
        'update_at',
        'number_services'
    ];
}
