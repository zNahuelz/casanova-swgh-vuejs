<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{

    public function getPatient($id)
    {
        $patient = Patient::with(['appointments' => function($query) {
            $query->orderBy('date','desc')->orderBy('time','desc')->limit(5);
        }])->find($id);

        return response()->json($patient);
    }
    public function getPatientByDni($dni)
    {
        $patient = Patient::with(['appointments' => function($query) {
            $query->orderBy('date','desc')->orderBy('time','desc')->limit(5);
        }])->where('dni',$dni)->first();

        if(!$patient){
            return response()->json([
                'message' => "Paciente de DNI: {$dni} no encontrado."
            ],404);
        }

        return response()->json($patient,200);
    }
}
