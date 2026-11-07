<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    /**
     * Obtener un resumen general de estadísticas (Top goleadores y rendimiento de equipos) (Público).
     */
    public function index(): JsonResponse
    {
        $topScorers = Player::with('team')
            ->orderBy('goals', 'desc')
            ->take(5)
            ->get();

        $teamsPerformance = Team::withCount('players')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'top_scorers' => $topScorers,
                'teams_performance' => $teamsPerformance,
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas detalladas de un jugador en específico (Público).
     */
    public function show($id): JsonResponse
    {
        $player = Player::with('team')->findOrFail($id);

        $goals = $player->goals ?? 0;
        $assists = $player->assists ?? 0;
        $matchesPlayed = $player->matches_played ?? 0;

        $goalsPerMatch = $matchesPlayed > 0 ? round($goals / $matchesPlayed, 2) : $goals;

        return response()->json([
            'success' => true,
            'data' => [
                'player' => $player,
                'stats' => [
                    'goals' => $goals,
                    'assists' => $assists,
                    'yellow_cards' => $player->yellow_cards ?? 0,
                    'red_cards' => $player->red_cards ?? 0,
                    'matches_played' => $matchesPlayed,
                    'goals_per_match' => $goalsPerMatch,
                ]
            ]
        ], 200);
    }
}
