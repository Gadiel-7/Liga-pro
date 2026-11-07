<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Player;
use App\Models\MatchModel;
use App\Models\Standing;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Obtener estadísticas generales y resumen para el panel de control.
     */
    public function index(): JsonResponse
    {

        $totalTeams = Team::count();
        $totalPlayers = Player::count();
        $totalMatches = MatchModel::count();
        $completedMatches = MatchModel::where('status', 'completed')->count();

        $activeMatches = MatchModel::with(['teamA', 'teamB'])
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy('date', 'asc')
            ->take(5)
            ->get();

        $topStandings = Standing::with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_teams' => $totalTeams,
                    'total_players' => $totalPlayers,
                    'total_matches' => $totalMatches,
                    'completed_matches' => $completedMatches,
                ],
                'active_matches' => $activeMatches,
                'top_standings' => $topStandings,
            ]
        ], 200);
    }
}