<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $table = 'clinic';

    protected $primaryKey = 'clinic_id';

    public $timestamps = false;

    protected $guarded = [];
}
