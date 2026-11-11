<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    /**
     * Muestra la lista de ligas del usuario autenticado (o todas si es público).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        $query = League::with(['categories', 'teams.players']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $leagues = $query->get();

        return response()->json($leagues, 200);
    }

    /**
     * Devuelve la primera liga del usuario logueado.
     */
    public function myLeague(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $league = League::with(['categories', 'teams.players'])
            ->where('user_id', $user->id)
            ->first();

        if (!$league) {
            return response()->json(['message' => 'No se encontró ninguna liga para este usuario'], 404);
        }

        return response()->json($league, 200);
    }

    /**
     * Muestra una liga en específico por ID con sus categorías, equipos y jugadores.
     */
    public function show($id): JsonResponse
    {
        $league = League::with(['categories', 'teams.players'])->find($id);

        if (!$league) {
            return response()->json(['message' => 'Liga no encontrada'], 404);
        }

        return response()->json($league, 200);
    }

    /**
     * Registra una nueva liga y su categoría inicial.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => $request->hasFile('logo') ? 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' : 'nullable|string'
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('leagues', 'public');
            $logoUrl = asset('storage/' . $path);
        } elseif ($request->filled('logo')) {
            $logoUrl = $request->logo;
        }

        $league = League::create([
            'name' => $request->name,
            'description' => $request->description,
            'logo' => $logoUrl,
            'user_id' => $user->id,
        ]);

        $category = Category::create([
            'name' => $request->category_name,
            'league_id' => $league->id,
            'description' => 'Categoría principal',
        ]);

        $league->load(['categories', 'teams']);

        return response()->json([
            'status' => 'success',
            'message' => 'Liga y categoría creadas correctamente',
            'data' => [
                'league' => $league,
                'category' => $category
            ]
        ], 201);
    }

    /**
     * Actualiza la información de una liga existente.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $league = League::find($id);

        if (!$league) {
            return response()->json(['message' => 'Liga no encontrada'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'logo' => $request->hasFile('logo') ? 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' : 'nullable|string'
        ]);

        $logoUrl = $league->logo;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('leagues', 'public');
            $logoUrl = asset('storage/' . $path);
        } elseif ($request->has('logo') && $request->logo !== null) {
            $logoUrl = $request->logo;
        }

        $league->update([
            'name' => $request->name ?? $league->name,
            'description' => $request->has('description') ? $request->description : $league->description,
            'logo' => $logoUrl,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Liga actualizada correctamente',
            'league' => $league
        ], 200);
    }
}
