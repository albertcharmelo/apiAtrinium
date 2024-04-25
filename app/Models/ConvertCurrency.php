<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvertCurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency',
        'rate',
        'last_updated',
    ];
}
