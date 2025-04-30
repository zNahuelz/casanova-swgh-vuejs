<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PresentationController extends Controller
{
    public function createPresentation(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'numeric_value' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'aux' => ['nullable','string', 'min:2', 'max:20'],
        ]);
    
        try {
            $presentation = Presentation::create([
                'name' => trim(strtoupper($request->name)),
                'numeric_value' => $request->numeric_value,
                'aux' => trim(strtoupper($request->aux)),
            ]);
    
            return response()->json([
                'message' => 'Presentación creada correctamente.',
                'presentation' => $presentation,
            ], 201);
    
        } catch (QueryException $e) {
            // Check for duplicate key error code
            if ($e->getCode() == '23505') {
                return response()->json([
                    'errors' => [
                        'presentation' => 'Ya existe una presentación con los datos ingresados.'
                    ]
                ], 422);
            }
    
            throw $e;
        }
    }

    public function updatePresentation(Request $request, $id)
    {
        $oldPresentation = Presentation::find($id);

        if (!$oldPresentation) {
            return response()->json([
                'message' => 'Presentación de ID: ' . $id . ' no encontrada.'
            ], 404);
        }
    
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'numeric_value' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'aux' => ['nullable','string', 'min:2', 'max:20']
        ]);
    
        try {
            $oldPresentation->update([
                'name' => trim(strtoupper($request->name)),
                'numeric_value' => $request->numeric_value,
                'aux' => trim(strtoupper($request->aux)),
            ]);
    
            return response()->json([
                'message' => 'Presentación de ID: ' . $id . ' actualizada correctamente.'
            ], 200);
    
        } catch (QueryException $e) {
            if ($e->getCode() == '23505') {
                return response()->json([
                    'errors' => [
                        'presentation' => 'Ya existe una presentación con los datos ingresados.'
                    ]
                ], 422);
            }
    
            throw $e; 
        }
    }

    public function getPresentations(Request $request)
    {
        $query = Presentation::query();

        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }
        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('name') . '%');
            //ILIKE -> Exclusivo de posgres. Non-case-sensitive search.
        }
        if ($request->has('aux')) {
            $query->where('aux', 'ilike', '%' . $request->input('aux') . '%');
        }
                //Ordenar.
        $sortField = $request->input('sort_by', 'name'); // Ordenar por name (Default)
        $sortDirection = $request->input('sort_dir', 'asc'); // Asc or Desc
        if (in_array($sortField, ['name', 'aux', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } //Verificar si el ordenado esta en el array.
        // Pagination
        $perPage = $request->input('per_page', 10); // Default: 10.
        $presentations = $query->paginate($perPage);
        return response()->json($presentations,200);
    }

    public function getPresentation($id)
    {
        $presentation = Presentation::find($id);

        if (!$presentation) {
            return response()->json(['message' => 'Presentación de ID: ' . $id . ' no encontrada.'], 404);
        }
        return response()->json([
            $presentation
        ],200);
    }
}
