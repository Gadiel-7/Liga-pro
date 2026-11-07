<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Standing;
use Illuminate\Http\JsonResponse;

class StandingsController extends Controller
{
    public function index(): JsonResponse
    {
        $standings = Standing::with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $standings
        ], 200);
    }
}