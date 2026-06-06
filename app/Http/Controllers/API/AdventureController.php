<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Adventure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdventureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'adventures' => Adventure::all()
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // solo admin puede crear aventuras igual que hago en player para tener consistencia en el proyecto
        $admin = auth()->user();


        if (!$admin || $admin->role->name !== 'Admin') {
            return response()->json(['error' => 'Solo un administrador puede crear aventuras'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:15',
            'image' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // creamos la aventura
        $adventure = Adventure::create($request->all());


        return response()->json([
            'message' => 'Aventura creada correctamente',
            'adventure' => $adventure
        ], 201);
    }


    /**
     * Display the specified resource.
     */


    public function show(string $id)
    {
        $adventure = Adventure::find($id);
        if (!$adventure) {
            return response()->json(['error' => 'Aventura no encontrada'],404);
        }
        return response()->json([
            'adventure' => $adventure],200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // solo admin

        $admin = auth()->user();
        if (!$admin || $admin->role->name !== 'Admin') {
            return response()->json(['error' => 'Solo un administrador puede actualizar aventuras'], 403);
        }

        $adventure = Adventure::find($id);

        if (!$adventure) {
            return response()->json(['error' => 'Aventura no encontrada'], 404);
        }

        // validación 
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:15',
            'image' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $adventure->update($request->all());

        return response()->json([
            'message' => 'Aventura  actualizada correctamente',
            'adventure' => $adventure
        ], 200);
    }


    /** 
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // solo admin puede eliminar aventuras

        $admin = auth()->user();
        if (!$admin || $admin->role->name !== 'Admin') {
            return response()->json(['error' => 'Solo un administrador puede eliminar aventuras'], 403);
        }

        $adventure = Adventure::find($id);

        if (!$adventure) {
            return response()->json(['error' => 'Aventura no encontrada'], 404);
        }

        $adventure->delete();

        return response()->json([
            'message' => 'Aventura  eliminada correctamente'
        ], 200);
    }
}
