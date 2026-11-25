<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CajaController extends Controller
{
    public function index(): View
    {
        $cajas = DB::table('cajas')
            ->leftJoin('users', 'cajas.user_id', '=', 'users.id')
            ->select('cajas.*', 'users.name as usuario')
            ->orderByDesc('cajas.id')
            ->get();

        return view('admin.caja.index', compact('cajas'));
    }

    public function actualizarControl(Request $request, Caja $caja): RedirectResponse
    {
        $datos = $request->validate([
            'controlado' => ['required', 'boolean'],
        ]);
        $caja->controlado = $datos['controlado'];
        $caja->save();

        return redirect()->route('caja.index');
    }
}
