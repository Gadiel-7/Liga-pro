<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    /**
     * Listar todos los jugadores con su respectivo equipo.
     */
    public function index(): JsonResponse
    {
        $players = Player::with('team')->get();

        return response()->json([
            'success' => true,
            'data' => $players
        ], 200);
    }

    /**
     * Registrar un nuevo jugador de fútbol.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
            'number' => 'nullable|integer',
            'position' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'age' => 'nullable|integer',
            'goals' => 'nullable|integer|min:0',
            'assists' => 'nullable|integer|min:0',
            'yellow_cards' => 'nullable|integer|min:0',
            'red_cards' => 'nullable|integer|min:0',
            'matches_played' => 'nullable|integer|min:0',
            'suspended_matches' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('players/photos', 'public');
        }

        $player = Player::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jugador registrado exitosamente',
            'data' => $player->load('team')
        ], 201);
    }

    /**
     * Mostrar los detalles de un jugador específico.
     */
    public function show($id): JsonResponse
    {
        $player = Player::with('team')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $player
        ], 200);
    }

    /**
     * Actualizar los datos o estadísticas de un jugador.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $player = Player::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'team_id' => 'sometimes|required|exists:teams,id',
            'number' => 'nullable|integer',
            'position' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'age' => 'nullable|integer',
            'goals' => 'nullable|integer|min:0',
            'assists' => 'nullable|integer|min:0',
            'yellow_cards' => 'nullable|integer|min:0',
            'red_cards' => 'nullable|integer|min:0',
            'matches_played' => 'nullable|integer|min:0',
            'suspended_matches' => 'nullable|integer|min:0', 
        ]);

        if ($request->hasFile('photo')) {
            if ($player->photo && Storage::disk('public')->exists($player->photo)) {
                Storage::disk('public')->delete($player->photo);
            }
            $validated['photo'] = $request->file('photo')->store('players/photos', 'public');
        }

        $player->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jugador actualizado exitosamente',
            'data' => $player->load('team')
        ], 200);
    }

    /**
     * Eliminar un jugador del sistema.
     */
    public function destroy($id): JsonResponse
    {
        $player = Player::findOrFail($id);

        if ($player->photo && Storage::disk('public')->exists($player->photo)) {
            Storage::disk('public')->delete($player->photo);
        }

        $player->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jugador eliminado exitosamente'
        ], 200);
    }
}
