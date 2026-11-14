<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use App\Models\MatchEvent;
use App\Models\MatchPlayer;
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
        $query = MatchModel::with(['league', 'teamA', 'teamB']);

        if ($request->has('league_id')) {
            $query->where('league_id', $request->query('league_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        // Ordenamos estrictamente por número de jornada y luego por fecha
        $matches = $query->orderBy('round', 'asc')->orderBy('date', 'asc')->get();

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

        $leagueId = $validated['league_id'];

        $teams = Team::where('league_id', $leagueId)
            ->get(['id', 'name'])
            ->toArray();

        if (empty($teams)) {
            $league = \App\Models\League::with(['teams', 'equipos'])->find($leagueId);
            if ($league) {
                $relatedTeams = $league->teams ?? $league->equipos ?? collect();
                $teams = $relatedTeams->map(fn($t) => [
                    'id'   => $t->id,
                    'name' => $t->name ?? $t->nombre
                ])->toArray();
            }
        }

        if (count($teams) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Se necesitan al menos 2 equipos en la liga para generar el calendario.'
            ], 400);
        }

        $isOdd = count($teams) % 2 != 0;
        if ($isOdd) {
            $teams[] = null;
        }

        $numTeams = count($teams);
        $rounds = $numTeams - 1;
        $half = $numTeams / 2;
        $roundsData = [];

        $currentDate = Carbon::now()->addWeek();

        // Obtenemos los partidos existentes para proteger los finalizados y manuales (Sin borrados)
        $existingMatches = MatchModel::where('league_id', $leagueId)->get();

        for ($round = 0; $round < $rounds; $round++) {
            $roundNum = $round + 1;
            $roundMatches = [];
            $restingTeam = null;

            for ($i = 0; $i < $half; $i++) {
                $teamA = $teams[$i];
                $teamB = $teams[$numTeams - 1 - $i];

                if ($teamA === null) {
                    $restingTeam = $teamB;
                } elseif ($teamB === null) {
                    $restingTeam = $teamA;
                } else {
                    // Alternancia de local / visitante justa
                    $home = ($round % 2 == 0) ? $teamA : $teamB;
                    $away = ($round % 2 == 0) ? $teamB : $teamA;

                    // Verificamos si este enfrentamiento ya existe en esta jornada (manual o previo)
                    $alreadyExists = $existingMatches->first(function ($m) use ($leagueId, $home, $away, $roundNum) {
                        return $m->league_id == $leagueId &&
                               $m->round == $roundNum &&
                               (($m->team_a_id == $home['id'] && $m->team_b_id == $away['id']) ||
                                ($m->team_a_id == $away['id'] && $m->team_b_id == $home['id']));
                    });

                    if ($alreadyExists) {
                        // Si ya existe (respetando partidos finalizados o creados a mano), lo conservamos
                        $roundMatches[] = $alreadyExists->load(['league', 'teamA', 'teamB']);
                    } else {
                        // Si no existe, lo creamos como nuevo partido programado para rellenar el rol
                        $match = MatchModel::create([
                            'league_id' => $leagueId,
                            'team_a_id' => $home['id'],
                            'team_b_id' => $away['id'],
                            'round'     => $roundNum,
                            'date'      => $currentDate->format('Y-m-d H:i:s'),
                            'location'  => 'Campo Principal',
                            'status'    => 'scheduled',
                            'score_a'   => 0,
                            'score_b'   => 0,
                        ]);

                        $roundMatches[] = $match->load(['league', 'teamA', 'teamB']);
                    }
                }
            }

            $roundsData[] = [
                'round'        => $roundNum,
                'date'         => $currentDate->format('Y-m-d'),
                'matches'      => $roundMatches,
                'resting_team' => $restingTeam
            ];

            // Rotación del algoritmo Round-Robin
            $lastElement = array_pop($teams);
            array_splice($teams, 1, 0, [$lastElement]);

            $currentDate->addWeek();
        }

        return response()->json([
            'success' => true,
            'message' => '¡Calendario sincronizado! Se respetaron los partidos finalizados/manuales y se completaron las jornadas faltantes.',
            'rounds'  => $roundsData
        ], 201);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'league_id' => 'nullable|exists:leagues,id',
            'team_a_id' => 'required|exists:teams,id|different:team_b_id',
            'team_b_id' => 'required|exists:teams,id',
            'date'      => 'required|date',
            'location'  => 'nullable|string|max:255',
            'status'    => 'nullable|in:scheduled,live,completed',
            'round'     => 'nullable|integer|min:1',
        ]);

        $leagueId = $validated['league_id'] ?? null;
        if (!$leagueId && !empty($validated['team_a_id'])) {
            $teamA = Team::find($validated['team_a_id']);
            $leagueId = $teamA?->league_id ?? null;
        }

        if (!$leagueId) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar la liga a la que pertenece el partido.'
            ], 422);
        }

        $match = MatchModel::create([
            'league_id' => $leagueId,
            'team_a_id' => $validated['team_a_id'],
            'team_b_id' => $validated['team_b_id'],
            'round'     => $validated['round'] ?? 1,
            'date'      => $validated['date'],
            'location'  => $validated['location'] ?? 'Campo Principal',
            'status'    => $validated['status'] ?? 'scheduled',
            'score_a'   => 0,
            'score_b'   => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Partido agendado exitosamente',
            'data'    => $match->load(['league', 'teamA', 'teamB'])
        ], 201);
    }

    public function show($id): JsonResponse
    {
        try {
            $match = MatchModel::with([
                'league',
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
                'error_message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $match = MatchModel::findOrFail($id);

        $validated = $request->validate([
            'league_id'          => 'sometimes|required|exists:leagues,id',
            'team_a_id'          => 'sometimes|required|exists:teams,id|different:team_b_id',
            'team_b_id'          => 'sometimes|required|exists:teams,id',
            'date'               => 'sometimes|required|date',
            'location'           => 'nullable|string|max:255',
            'score_a'            => 'nullable|integer|min:0',
            'score_b'            => 'nullable|integer|min:0',
            'status'             => 'sometimes|required|in:scheduled,live,completed',
            'round'              => 'sometimes|integer|min:1',
            'events'             => 'nullable|array',
            'events.*.player_id' => 'required|exists:players,id',
            'events.*.team_id'   => 'required|exists:teams,id',
            'events.*.type'      => 'required|in:goal,yellow_card,red_card',
            'events.*.minute'    => 'nullable|integer|min:1|max:120',
        ]);

        DB::transaction(function () use ($match, $validated) {
            $match->update([
                'league_id' => $validated['league_id'] ?? $match->league_id,
                'team_a_id' => $validated['team_a_id'] ?? $match->team_a_id,
                'team_b_id' => $validated['team_b_id'] ?? $match->team_b_id,
                'date'      => $validated['date'] ?? $match->date,
                'location'  => array_key_exists('location', $validated) ? $validated['location'] : $match->location,
                'score_a'   => $validated['score_a'] ?? $match->score_a,
                'score_b'   => $validated['score_b'] ?? $match->score_b,
                'status'    => $validated['status'] ?? $match->status,
                'round'     => $validated['round'] ?? $match->round,
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
            'data'    => $match->load(['league', 'teamA', 'teamB', 'events.player'])
        ], 200);
    }

    public function updateResult(Request $request, $id): JsonResponse
    {
        return $this->update($request, $id);
    }

    public function storeLineup(Request $request, $id): JsonResponse
    {
        $match = MatchModel::findOrFail($id);

        $validated = $request->validate([
            'team_id'       => 'required|exists:teams,id',
            'starters'      => 'required|array',
            'starters.*'    => 'exists:players,id',
            'substitutes'   => 'nullable|array',
            'substitutes.*' => 'exists:players,id',
        ]);

        DB::transaction(function () use ($match, $validated) {
            foreach ($validated['starters'] as $playerId) {
                $exists = MatchPlayer::where('match_id', $match->id)
                    ->where('player_id', $playerId)
                    ->exists();

                MatchPlayer::updateOrCreate(
                    ['match_id' => $match->id, 'player_id' => $playerId],
                    ['team_id' => $validated['team_id'], 'type' => 'starter']
                );

                if (!$exists) {
                    Player::where('id', $playerId)->increment('matches_played');
                }
            }

            if (!empty($validated['substitutes'])) {
                foreach ($validated['substitutes'] as $playerId) {
                    $exists = MatchPlayer::where('match_id', $match->id)
                        ->where('player_id', $playerId)
                        ->exists();

                    MatchPlayer::updateOrCreate(
                        ['match_id' => $match->id, 'player_id' => $playerId],
                        ['team_id' => $validated['team_id'], 'type' => 'substitute']
                    );

                    if (!$exists) {
                        Player::where('id', $playerId)->increment('matches_played');
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Alineación guardada y partidos jugados actualizados correctamente.'
        ], 200);
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
