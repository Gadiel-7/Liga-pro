<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with(['players', 'standing', 'category']);

        if ($request->has('league_id')) {
            $query->where('league_id', $request->league_id);
        }

        $teams = $query->get();
        return response()->json($teams, 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', // Eliminado unique:teams para permitir el mismo nombre en ligas distintas
            'logo' => $request->hasFile('logo') ? 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' : 'nullable|string',
            'league_id' => 'required|exists:leagues,id',
            'category_id' => 'nullable|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $logoUrl = $request->file('logo')->store('teams', 'public');
        } elseif ($request->filled('logo')) {
            $logoUrl = $request->logo;
        }

        $categoryId = $request->category_id;
        if (!$categoryId) {
            $defaultCategory = Category::where('league_id', $request->league_id)->first();
            $categoryId = $defaultCategory ? $defaultCategory->id : null;
        }

        $team = Team::create([
            'name' => $request->name,
            'logo' => $logoUrl,
            'league_id' => $request->league_id,
            'category_id' => $categoryId,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'message' => 'Equipo registrado correctamente',
            'team' => $team->load(['players', 'category'])
        ], 201);
    }

    public function show(Team $team)
    {
        return response()->json($team->load(['players', 'standing', 'category']), 200);
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255', // Eliminado unique para evitar bloqueos al editar
            'logo' => $request->hasFile('logo') ? 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' : 'nullable|string',
            'league_id' => 'nullable|exists:leagues,id',
            'category_id' => 'nullable|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $logoUrl = $team->logo;
        if ($request->hasFile('logo')) {
            // Guardar solo la ruta relativa en la actualización también
            $logoUrl = $request->file('logo')->store('teams', 'public');
        } elseif ($request->has('logo')) {
            $logoUrl = $request->logo;
        }

        $team->update([
            'name' => $request->name,
            'logo' => $logoUrl,
            'league_id' => $request->league_id ?? $team->league_id,
            'category_id' => $request->category_id ?? $team->category_id,
            'user_id' => $request->user_id ?? $team->user_id,
        ]);

        return response()->json([
            'message' => 'Equipo actualizado correctamente',
            'team' => $team->load(['players', 'category'])
        ], 200);
    }
    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json([
            'message' => 'Equipo eliminado correctamente'
        ], 200);
    }
}
