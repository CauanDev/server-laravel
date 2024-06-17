<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarsService extends Model
{
    use HasFactory;
    protected $table = 'car_service';

    protected $fillable = [
        'name_service',
        'price',
        'car_id',
        'owner_id',
        'worker_id',
        'date_service',
        'created_at',
        'update_at'
    ];
}
