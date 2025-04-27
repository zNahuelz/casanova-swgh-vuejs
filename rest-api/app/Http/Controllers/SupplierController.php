<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function createSupplier(Request $request)
    {
        $request->validate([
            'name' => ['required','string','min:2','max:150'],
            'ruc' => ['required','string','min:11','max:11',Rule::unique('suppliers','ruc')],
            'address' => ['required','max:100'],
            'phone' => ['required','string','min:6','max:15'],
            'email' => ['required','email','max:50'],
            'description' => ['max:150'],
            'created_by' => ['required','numeric','exists:users,id']
        ]);

        $supplier = Supplier::create([
            'name' => trim(strtoupper($request->name)),
            'ruc' => trim($request->ruc),
            'address' => trim(strtoupper($request->address)),
            'phone' => trim($request->phone),
            'email' => trim(strtoupper($request->email)),
            'description' => trim(strtoupper($request->description)) ?? null, //TODO: CHECK THIS SHIT!!
            'created_by' => $request->created_by, 
        ]);

        return response()->json([
            'message' => 'Proveedor creado con exito.',
            'supplier' => $supplier
        ],201);
    }

    public function updateSupplier(Request $request, $id)
    {
        $oldSupplier = Supplier::find($id);
        if(!$oldSupplier){
            return response()->json(['message' => 'Proveedor de ID: '.$id.' no encontrado.'],404);
        }

        $request->validate([
            'name' => ['required','string','min:2','max:150'],
            'ruc' => ['required','string','min:11','max:11',Rule::unique('suppliers','ruc')->ignore($id)],
            'address' => ['required','max:100'],
            'phone' => ['required','string','min:6','max:15'],
            'email' => ['required','email','max:50'],
            'description' => ['max:150'],
        ]);

        $oldSupplier->update([
            'name' => trim(strtoupper($request->name)),
            'ruc' => trim($request->ruc),
            'address' => trim(strtoupper($request->address)),
            'phone' => trim($request->phone),
            'email' => trim(strtoupper($request->email)),
            'description' => trim(strtoupper($request->description)),
        ]);

        return response()->json(['message' => 'Proveedor de ID: '.$id.' actualizado con exito.'],200);
    }

    public function getSuppliers(Request $request)
    {
        $query = Supplier::query();
        // Filtrar por nombre si esta presente.
        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('name') . '%');
            //ILIKE -> Exclusivo de posgres. Non-case-sensitive search.
        }
        // Filtrar por RUC si esta presente.
        if ($request->has('ruc')) {
            $query->where('ruc', 'like', '%' . $request->input('ruc') . '%');
        }
        if ($request->has('email')) {
            $query->where('email', 'ilike', '%' . $request->input('email') . '%');
        }
        //Ordenar.
        $sortField = $request->input('sort_by', 'id'); // Ordenar por ID (Default)
        $sortDirection = $request->input('sort_dir', 'asc'); // Asc or Desc
        if (in_array($sortField, ['name', 'ruc', 'email', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } //Verificar si el ordenado esta en el array.
        // Pagination
        $perPage = $request->input('per_page', 10); // Default: 10.
        $suppliers = $query->paginate($perPage);
        return response()->json($suppliers);
    }

    public function getSupplier($id)
    {
        $supplier = Supplier::find($id);
        if(!$supplier)
        {
            return response()->json(['message' => 'Proveedor de ID: '.$id.' no encontrado.'],404);
        }
        return response()->json($supplier);
    }

    public function getSupplierByRUC($ruc)
    {
        $supplier = Supplier::where('ruc',$ruc)->first();
        if(!$supplier)
        {
            return response()->json(['message' => 'Proveedor de RUC: '.$ruc.' no encontrado.'],404);
        }
        return response()->json($supplier);
    }
}
