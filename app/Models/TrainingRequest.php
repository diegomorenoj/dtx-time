<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'shortname',
        'institute',
        'category',
        'hours',
        'start_date',
        'end_date',
        'permission',
        'schedule',
        'methodology',
        'comments',
        'fee',
        'user_id',
        'specialty_id',
        'create_user_id',
    ];


}
