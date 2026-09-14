<?php

namespace App\Http\Controllers;

use App\Models\CtroCosto;
use Illuminate\Http\Request;

class CtroCostoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ctrocostos = CtroCosto::all();
        return view('admin.ctrocostos.index', compact('ctrocostos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.ctrocostos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:100',
            'tipo' => 'required|string|max:100',
        ]);

        $ctrocosto = new CtroCosto();
        $ctrocosto->nombre = $request->input('nombre');
        $ctrocosto->descripcion = $request->input('descripcion');
        $ctrocosto->tipo = $request->input('tipo');
        $ctrocosto->fecha_creacion = now();
        $ctrocosto->fecha_actualizacion = now();
        $ctrocosto->save();

        return redirect()->route('ctrocostos.index')
        ->with('mensaje', 'Centro de Costo creado exitosamente.')
        ->with('icono', 'success')
        ->with('showConfirmButton', false)
        ->with('textoBoton', '');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ctrocosto = CtroCosto::findOrFail($id);
        return view('admin.ctrocostos.show', compact('ctrocosto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ctrocosto = CtroCosto::findOrFail($id);
        return view('admin.ctrocostos.edit', compact('ctrocosto'));
    }

    /**
     * Show delete confirmation form for the specified resource.
     */
    public function delete($id)
    {
        $ctrocosto = CtroCosto::findOrFail($id);
        return view('admin.ctrocostos.delete', compact('ctrocosto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:100',
            'tipo' => 'required|string|max:100',
        ]);

        $ctrocosto = CtroCosto::findOrFail($id);
        $ctrocosto->nombre = $request->input('nombre');
        $ctrocosto->descripcion = $request->input('descripcion');
        $ctrocosto->tipo = $request->input('tipo');
        $ctrocosto->fecha_actualizacion = now();
        $ctrocosto->save();

        return redirect()->route('ctrocostos.index')
        ->with('mensaje', 'Centro de Costo actualizado exitosamente.')
        ->with('icono', 'success')
        ->with('showConfirmButton', false)
        ->with('textoBoton', '');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ctrocosto = CtroCosto::findOrFail($id);
        $ctrocosto->delete();

        return redirect()->route('ctrocostos.index')
        ->with('mensaje', 'Centro de Costo eliminado exitosamente.')
        ->with('icono', 'success')
        ->with('showConfirmButton', false)
        ->with('textoBoton', '');
    }
}
