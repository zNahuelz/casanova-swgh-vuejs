<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TreatmentController extends Controller
{
    /**
     * Permite crear un tratamiento previa validacion.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function createTreatment(Request $request)
    {
        $request->validate([
            'name' => ['required','string','min:5','max:100', Rule::unique('treatments','name')],
            'description' => ['max:255'],
            'procedure' => ['required','string','min:5','max:255'],
            'price' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'igv' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'profit' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'created_by' => ['required','numeric','exists:users,id']
        ]);

        $treatment = Treatment::create([
            'name' => strtoupper(trim($request->name)),
            'description' => trim($request->description ?? '-----'),
            'procedure' => trim($request->procedure ?? '-----'),
            'price' => $request->price,
            'igv' => $request->igv,
            'profit' => $request->profit,
            'created_by' => $request->created_by
        ]);

        return response()->json([
            'message' => 'Tratamiento creado correctamente. Asignado ID: '.$treatment->id,
            'treatment' => $treatment,
        ],201);
    }

    /**
     * Permite actualizar un tratamiento por ID previa validacion.
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateTreatment(Request $request,$id)
    {
        $oldTreatment = Treatment::find($id);
        if(!$oldTreatment)
        {
            return response()->json([
                'message' => 'Tratamiento de ID: '.$id.' no encontrado.'
            ],404);
        }

        $request->validate([
            'name' => ['required','string','min:5','max:100', Rule::unique('treatments','name')->ignore($id)],
            'description' => ['max:255'],
            'procedure' => ['required','string','min:5','max:255'],
            'price' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'igv' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'profit' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'updated_by' => ['required','numeric','exists:users,id']
        ]);

        $oldTreatment->update([
            'name' => strtoupper(trim($request->name)),
            'description' => trim($request->description ?? '-----'),
            'procedure' => trim($request->procedure ?? '-----'),
            'price' => $request->price,
            'igv' => $request->igv,
            'profit' => $request->profit,
            'updated_by' => $request->updated_by
        ]);

        return response()->json([
            'message' => 'Tratamiento de ID: '.$id.' actualizado correctamente.',
        ],200);
    }

    /**
     * Retorna un listado de tratamientos con filtrado, paginado, ordenado y cantidad de elementos.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getTreatments(Request $request)
    {
        $query = Treatment::query();
        if ($request->has('id')) { 
            $query->where('id', $request->input('id'));
        }
        // Filtrar por nombre si esta presente.
        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('name') . '%');
            //ILIKE -> Exclusivo de posgres. Non-case-sensitive search.
        }
        if ($request->has('description')) {
            $query->where('description', 'like', '%' . $request->input('description') . '%');
        }
        if ($request->has('procedure')) {
            $query->where('procedure', 'ilike', '%' . $request->input('procedure') . '%');
        }
        //Ordenar.
        $sortField = $request->input('sort_by', 'id'); // Ordenar por ID (Default)
        $sortDirection = $request->input('sort_dir', 'asc'); // Asc or Desc
        if (in_array($sortField, ['id','name', 'description', 'procedure', 'price'])) {
            $query->orderBy($sortField, $sortDirection);
        } //Verificar si el ordenado esta en el array.
        // Pagination
        $perPage = $request->input('per_page', 10); // Default: 10.
        $treatments = $query->paginate($perPage);
        return response()->json($treatments,200);
    }

    /**
     * Retorna un tratamiento por ID, incluye informacion de auditoria.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getTreatment($id)
    {
        $treatment = Treatment::with([
            'createdBy.doctor',
            'createdBy.worker',
            'updatedBy.doctor',
            'updatedBy.worker'
        ])->find($id);
    
        if (!$treatment) {
            return response()->json([
                'message' => 'Tratamiento de ID: ' . $id . ' no encontrado.'
            ], 404);
        }

        return response()->json([
            'id' => $treatment->id,
            'name' => $treatment->name,
            'procedure' => $treatment->procedure,
            'description' => $treatment->description,
            'price' => $treatment->price,
            'igv' => $treatment->igv,
            'profit' => $treatment->profit,
            'created_at' => $treatment->created_at,
            'updated_at' => $treatment->updated_at,
            'created_by' => $treatment->created_by,
            'created_by_name' => $this->getUserDisplayName($treatment->createdBy),
            'updated_by' => $treatment->updated_by,
            'updated_by_name' => $this->getUserDisplayName($treatment->updatedBy),
        ], 200);
    }

    /**
     * Auxiliar de auditoria.
     * @param mixed $user
     */
    private function getUserDisplayName($user)
    {
        if (!$user) 
        {
            return null;
        }
        if ($user->doctor) 
        {
            return $user->doctor->name . ' ' . $user->doctor->paternal_surname;
        }
        if ($user->worker) 
        {
            return $user->worker->name . ' ' . $user->worker->paternal_surname;
        }

        return $user->username ?? $user->name ?? 'USUARIO SIN NOMBRE';
    }
}
