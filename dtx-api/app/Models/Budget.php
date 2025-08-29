<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Budget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'anio',
        'value',
        'spent',
        'city',
        'area',
        'is_main',
    ];


    /**
     * Get the available budget based on filters.
     *
     * @param  array  $filters
     * @return double
     */
    public static function getAvailableBudget($filters = [])
    {
        $query = self::query();

        if (isset($filters['area'])) {
            $query->where('area', $filters['area']);
        }

        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (isset($filters['anio'])) {
            $query->where('anio', $filters['anio']);
        }

        $budgets = $query->get();

        $totalAvailable = 0;

        foreach ($budgets as $budget) {
            $totalAvailable += ($budget->value - $budget->spent);
        }

        return $totalAvailable;
    }
}

