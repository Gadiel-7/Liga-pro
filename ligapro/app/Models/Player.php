<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'team_id',
        'number',
        'position',
        'photo',
        'age',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
        'matches_played',
        'suspended_matches',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function matchPlayers()
{
    return $this->hasMany(MatchPlayer::class);
}
}
