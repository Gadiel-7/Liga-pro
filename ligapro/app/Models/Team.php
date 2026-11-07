<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tag',
        'logo',
        'league_id',    
        'category_id',  
        'user_id',      
    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function homeMatches()
    {
        return $this->hasMany(MatchModel::class, 'team_a_id');
    }

    public function awayMatches()
    {
        return $this->hasMany(MatchModel::class, 'team_b_id');
    }

    public function standing()
    {
        return $this->hasOne(Standing::class);
    }
}
