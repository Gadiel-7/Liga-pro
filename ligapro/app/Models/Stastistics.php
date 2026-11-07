<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    use HasFactory;

    protected $table = 'statistics';

    protected $fillable = [
        'player_id',
        'match_id',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
        'is_mvp',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }
}
