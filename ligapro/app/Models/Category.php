<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'league_id',
        'description',
    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }
}
