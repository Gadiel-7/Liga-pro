<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\League;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()) {
            $league = League::where('user_id', $request->user()->id)->first();

            if (!$league) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ], 200);
            }

            $categories = Category::where('league_id', $league->id)
                ->with('teams')
                ->get();
        } else {
            $leagueId = $request->query('league_id');
            $query = Category::with('teams');

            if ($leagueId) {
                $query->where('league_id', $leagueId);
            }

            $categories = $query->get();
        }

        return response()->json([
            'success' => true,
            'data' => $categories
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $league = League::where('user_id', $request->user()->id)->first();

        if (!$league) {
            return response()->json([
                'success' => false,
                'message' => 'Debes registrar la información de tu liga antes de crear categorías.'
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'league_id' => $league->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => $category
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $category = Category::with('teams')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ], 200);
    }
}
