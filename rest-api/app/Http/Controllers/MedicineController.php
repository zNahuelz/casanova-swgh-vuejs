<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineSupplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MedicineController extends Controller
{
    public function createMedicine(Request $request)
    {
        $request->validate([
            'name' => ['required','string','min:5','max:100'],
            'composition' => ['required','string','min:5','max:100'],
            'description' => ['required','string','min:5','max:150'],
            'barcode' => ['required','string','min:8','max:30','regex:/^[A-Za-z0-9]{8,30}$/', Rule::unique('medicines','barcode')],
            'buy_price' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'sell_price' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'igv' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'profit' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['required','numeric'],
            'salable' => ['required','boolean'],
            'presentation' => ['required','numeric','exists:presentations,id'],
            'supplier' => ['required','numeric','exists:suppliers,id'],
            'created_by' => ['required','numeric','exists:users,id'],
        ]);

        try
        {
            DB::beginTransaction();

            $medicine = Medicine::create([
                'name' => trim(strtoupper($request->name)),
                'composition' => trim(strtoupper($request->composition)),
                'description' => trim(strtoupper($request->description)),
                'barcode' => trim($request->barcode),
                'buy_price' => $request->buy_price,
                'sell_price' => $request->sell_price,
                'igv' => $request->igv,
                'profit' => $request->profit,
                'stock' => $request->stock,
                'salable' => $request->salable,
                'presentation' => $request->presentation,
                'created_by' => $request->created_by,
            ]);
    
            $medicineSupplier = MedicineSupplier::create([
                'supplier_id' => $request->supplier,
                'medicine_id' => $medicine->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Medicamento registrada correctamente.',
                'medicine' => $medicine
            ],201);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            //Check if $medicine != null - isset
            if (isset($medicine) && $medicine->exists) {
                $medicine->forceDelete(); 
            }
            return response()->json([
                'message' => 'Error en el registro del medicamento o asignación de proveedor. Vuelva a intentarlo.'
            ],500);
        }
    }

    public function updateMedicine(Request $request, $id)
    {
        $oldMedicine = Medicine::find($id);
        if(!$oldMedicine)
        {
            return response()->json([
                'message' => 'Error! Medicamento de ID: '.$id.' no encontrada.',
            ]);
        }

        $request->validate([
            'name' => ['required','string','min:5','max:100'],
            'composition' => ['required','string','min:5','max:100'],
            'description' => ['required','string','min:5','max:150'],
            'barcode' => ['required','string','min:8','max:30','regex:/^[A-Za-z0-9]{8,30}$/', Rule::unique('medicines','barcode')->ignore($id)],
            'buy_price' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'sell_price' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'igv' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'profit' => ['required','numeric','regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['required','numeric'],
            'salable' => ['required','boolean'],
            'presentation' => ['required','numeric','exists:presentations,id'],
            'supplier' => ['required','numeric','exists:suppliers,id'],
            'updated_by' => ['required','numeric','exists:users,id'],
        ]);

        try
        {
            DB::beginTransaction();

            $oldMedicine->update([
                'name' => trim(strtoupper($request->name)),
                'composition' => trim(strtoupper($request->composition)),
                'description' => trim(strtoupper($request->description)),
                'barcode' => trim($request->barcode),
                'buy_price' => $request->buy_price,
                'sell_price' => $request->sell_price,
                'igv' => $request->igv,
                'profit' => $request->profit,
                'stock' => $request->stock,
                'salable' => $request->salable,
                'presentation' => $request->presentation,
                'updated_by' => $request->updated_by,
            ]);

            MedicineSupplier::where('medicine_id', $id)->delete();
            MedicineSupplier::updateOrCreate([
                'medicine_id' => $id,
                'supplier_id' => $request->supplier,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Medicamento de ID: '.$id.' actualizado correctamente.'
            ],200);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar el medicamento de ID: '.$id.' Vuelva a intentarlo.'
            ],500);
        }
    }

    public function getMedicines(Request $request)
    {
        $query = Medicine::with(['presentation', 'suppliers']);

        if ($request->has('id')) { //TODO: Something fails? HERE!
            $query->where('id', $request->input('id'));
        }
        // Filtrar por nombre si esta presente.
        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('name') . '%');
        }
    
        if ($request->has('composition')) {
            $query->where('composition', 'ilike', '%' . $request->input('composition') . '%');
        }
    
        if ($request->has('description')) {
            $query->where('description', 'ilike', '%' . $request->input('description') . '%');
        }
    
        //Case-sensitive.
        if ($request->has('barcode')) {
            $query->where('barcode', 'like', '%' . $request->input('barcode') . '%');
        }
    
        // Ordenado
        $sortField = $request->input('sort_by', 'stock');
        $sortDirection = $request->input('sort_dir', 'desc');
    
        if (in_array($sortField, ['name', 'composition', 'barcode', 'buy_price', 'sell_price', 'stock'])) {
            $query->orderBy($sortField, $sortDirection);
        }
    
        // Pagination
        $perPage = $request->input('per_page', 10);
        $medicines = $query->paginate($perPage);
    
        return response()->json($medicines, 200);
    }

    public function getMedicineById($id)
    {
        $medicine = Medicine::with([
            'presentation',
            'suppliers',
            'createdBy.doctor',
            'createdBy.worker',
            'updatedBy.doctor',
            'updatedBy.worker'
        ])->find($id);
    
        if (!$medicine) {
            return response()->json([
                'message' => 'Medicamento de ID: ' . $id . ' no encontrado.'
            ], 404);
        }

    
        return response()->json([
            'id' => $medicine->id,
            'name' => $medicine->name,
            'composition' => $medicine->composition,
            'description' => $medicine->description,
            'barcode' => $medicine->barcode,
            'stock' => $medicine->stock,
            'buy_price' => $medicine->buy_price,
            'sell_price' => $medicine->sell_price,
            'igv' => $medicine->igv,
            'profit' => $medicine->profit,
            'salable' => $medicine->salable,
            'presentation' => $medicine->presentation()->first(),
            'created_at' => $medicine->created_at,
            'updated_at' => $medicine->updated_at,
            'created_by' => $medicine->created_by,
            'created_by_name' => $this->getUserDisplayName($medicine->createdBy),
            'updated_by' => $medicine->updated_by,
            'updated_by_name' => $this->getUserDisplayName($medicine->updatedBy),
            'suppliers' => $medicine->suppliers,
        ], 200);
    }

    public function getMedicineByBarcode($barcode)
    {
        $medicine = Medicine::where('barcode',$barcode)->with(['presentation'])->first();

        if(!$medicine)
        {
            return response()->json([
                'message' => 'Medicamento con código de barras: '.$barcode.' no encontrado.'
            ],404);
        }
        return response()->json($medicine,200);
    }

    public function generateRandomBarcode()
    {
        $validBarcode = false;
        while(!$validBarcode)
        {
            $barcode = $this->barcodeFactory();
            $medicine = Medicine::where('barcode',$barcode)->first();
            if(!$medicine)
            {
                $validBarcode = true;
            }
        }
        return response()->json([
            'message' => 'Código de barras generado exitosamente.',
            'barcode' => $barcode
        ]);
    }

    private function barcodeFactory()
    {
        $value = rand(1,99999);
        $barcode = str_pad((string)$value,13,'0',STR_PAD_LEFT);
        return $barcode;
    }

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
