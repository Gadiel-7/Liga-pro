<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchPlayer extends Model
{
    use HasFactory;

    protected $table = 'match_players';

    protected $fillable = [
        'match_id',
        'player_id',
        'team_id',
        'type', // 'starter' o 'substitute'
    ];

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
