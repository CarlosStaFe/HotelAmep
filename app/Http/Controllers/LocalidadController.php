<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class LocalidadController extends Controller
{
    public function getLocalidades(string $idProv): JsonResponse
    {
        try {
            $localidades = DB::table('localidades')
                ->where('id_prov', $idProv)
                ->select('id_local', 'localidad')
                ->distinct()
                ->orderBy('localidad', 'asc')
                ->get();

            return response()->json($localidades);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCodigosPostales(string $idLocal): JsonResponse
    {
        try {
            $codigosPostales = DB::table('localidades')
                ->where('id_local', $idLocal)
                ->select('id', 'cod_postal')
                ->orderBy('cod_postal', 'asc')
                ->get();

            return response()->json($codigosPostales);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
