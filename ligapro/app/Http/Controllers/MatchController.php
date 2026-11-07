<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use App\Models\MatchEvent;
use App\Models\Team;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MatchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MatchModel::with(['teamA', 'teamB']);

        if ($request->has('league_id')) {
            $query->whereHas('teamA', function ($q) use ($request) {
                $q->where('league_id', $request->query('league_id'));
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $matches = $query->orderBy('date', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => $matches
        ], 200);
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'league_id' => 'required|exists:leagues,id',
        ]);

        $teams = Team::where('league_id', $validated['league_id'])->pluck('id')->toArray();

        if (count($teams) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Se necesitan al menos 2 equipos en la liga para generar el calendario.'
            ], 400);
        }

        if (count($teams) % 2 != 0) {
            $teams[] = null;
        }

        $numTeams = count($teams);
        $rounds = $numTeams - 1;
        $createdMatches = [];
        $currentDate = Carbon::now();

        for ($round = 0; $round < $rounds; $round++) {
            for ($i = 0; $i < $numTeams / 2; $i++) {
                $teamA = $teams[$i];
                $teamB = $teams[$numTeams - 1 - $i];

                if ($teamA !== null && $teamB !== null) {
                    $match = MatchModel::create([
                        'team_a_id' => $teamA,
                        'team_b_id' => $teamB,
                        'date'      => $currentDate->format('Y-m-d H:i:s'),
                        'location'  => 'Campo Principal',
                        'status'    => 'scheduled',
                        'score_a'   => 0,
                        'score_b'   => 0,
                    ]);

                    $createdMatches[] = $match;
                }
            }

            $currentDate->addWeek();
            array_splice($teams, 1, 0, array_pop($teams));
        }

        return response()->json([
            'success' => true,
            'message' => 'Calendario de partidos generado exitosamente',
            'total_matches' => count($createdMatches),
            'data' => $createdMatches
        ], 201);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_a_id' => 'required|exists:teams,id|different:team_b_id',
            'team_b_id' => 'required|exists:teams,id',
            'date'      => 'required|date',
            'location'  => 'nullable|string|max:255',
            'status'    => 'nullable|in:scheduled,live,completed',
        ]);

        $match = MatchModel::create([
            'team_a_id' => $validated['team_a_id'],
            'team_b_id' => $validated['team_b_id'],
            'date'      => $validated['date'],
            'location'  => $validated['location'] ?? null,
            'status'    => $validated['status'] ?? 'scheduled',
            'score_a'   => 0,
            'score_b'   => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Partido agendado exitosamente',
            'data'    => $match->load(['teamA', 'teamB'])
        ], 201);
    }

    public function show($id): JsonResponse
    {
        try {
            $match = MatchModel::with([
                'teamA.players',
                'teamB.players',
                'events' => function ($query) {
                    $query->orderBy('minute', 'asc');
                },
                'events.player',
                'events.team'
            ])->find($id);

            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partido no encontrado'
                ], 404);
            }

            $events = $match->events ?? collect();

            $stats = [
                'team_a' => [
                    'goals'        => $events->where('team_id', $match->team_a_id)->where('type', 'goal')->count(),
                    'yellow_cards' => $events->where('team_id', $match->team_a_id)->where('type', 'yellow_card')->count(),
                    'red_cards'    => $events->where('team_id', $match->team_a_id)->where('type', 'red_card')->count(),
                ],
                'team_b' => [
                    'goals'        => $events->where('team_id', $match->team_b_id)->where('type', 'goal')->count(),
                    'yellow_cards' => $events->where('team_id', $match->team_b_id)->where('type', 'yellow_card')->count(),
                    'red_cards'    => $events->where('team_id', $match->team_b_id)->where('type', 'red_card')->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'match'      => $match,
                    'statistics' => $stats,
                    'timeline'   => $events
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $match = MatchModel::findOrFail($id);

        $validated = $request->validate([
            'team_a_id'          => 'sometimes|required|exists:teams,id|different:team_b_id',
            'team_b_id'          => 'sometimes|required|exists:teams,id',
            'date'               => 'sometimes|required|date',
            'location'           => 'nullable|string|max:255',
            'score_a'            => 'nullable|integer|min:0',
            'score_b'            => 'nullable|integer|min:0',
            'status'             => 'sometimes|required|in:scheduled,live,completed',
            'events'             => 'nullable|array',
            'events.*.player_id' => 'required|exists:players,id',
            'events.*.team_id'   => 'required|exists:teams,id',
            'events.*.type'      => 'required|in:goal,yellow_card,red_card',
            'events.*.minute'    => 'nullable|integer|min:1|max:120',
        ]);

        DB::transaction(function () use ($match, $validated) {
            $match->update([
                'team_a_id' => $validated['team_a_id'] ?? $match->team_a_id,
                'team_b_id' => $validated['team_b_id'] ?? $match->team_b_id,
                'date'      => $validated['date'] ?? $match->date,
                'location'  => array_key_exists('location', $validated) ? $validated['location'] : $match->location,
                'score_a'   => $validated['score_a'] ?? $match->score_a,
                'score_b'   => $validated['score_b'] ?? $match->score_b,
                'status'    => $validated['status'] ?? $match->status,
            ]);

            if (array_key_exists('events', $validated)) {
                MatchEvent::where('match_id', $match->id)->delete();

                if (!empty($validated['events'])) {
                    foreach ($validated['events'] as $event) {
                        MatchEvent::create([
                            'match_id'  => $match->id,
                            'player_id' => $event['player_id'],
                            'team_id'   => $event['team_id'],
                            'type'      => $event['type'],
                            'minute'    => $event['minute'] ?? null,
                        ]);
                    }
                }
            }

            // Recalcular solamente goles y tarjetas (los partidos suspendidos se gestionan manual)
            $players = Player::all();
            foreach ($players as $player) {
                $player->goals        = MatchEvent::where('player_id', $player->id)->where('type', 'goal')->count();
                $player->yellow_cards = MatchEvent::where('player_id', $player->id)->where('type', 'yellow_card')->count();
                $player->red_cards    = MatchEvent::where('player_id', $player->id)->where('type', 'red_card')->count();
                $player->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Partido e incidencias guardados exitosamente',
            'data'    => $match->load(['teamA', 'teamB', 'events.player'])
        ], 200);
    }

    public function updateResult(Request $request, $id): JsonResponse
    {
        return $this->update($request, $id);
    }

    public function destroy($id): JsonResponse
    {
        $match = MatchModel::findOrFail($id);
        $match->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partido eliminado exitosamente'
        ], 200);
    }
}
