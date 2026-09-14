<?php

namespace App\Http\Controllers;

use App\Models\CtroCosto;
use App\Models\rubro;
use Illuminate\Http\Request;

class RubroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rubros = rubro::with('ctrocosto')->get();
        return view('admin.rubros.index', compact('rubros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ctrocostos = CtroCosto::orderBy('nombre')->get();
        return view('admin.rubros.create', compact('ctrocostos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ctrocosto_id' => 'required|exists:ctrocostos,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:100',
            'activo' => 'required|boolean',
        ]);

        $rubro = new rubro();
        $rubro->ctrocosto_id = $request->input('ctrocosto_id');
        $rubro->nombre = $request->input('nombre');
        $rubro->descripcion = $request->input('descripcion');
        $rubro->activo = $request->boolean('activo');
        $rubro->fecha_creacion = now();
        $rubro->fecha_actualizacion = now();
        $rubro->save();

        return redirect()->route('rubros.index')
            ->with('mensaje', 'Rubro creado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rubro = rubro::with('ctrocosto')->findOrFail($id);
        return view('admin.rubros.show', compact('rubro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rubro = rubro::findOrFail($id);
        $ctrocostos = CtroCosto::orderBy('nombre')->get();

        return view('admin.rubros.edit', compact('rubro', 'ctrocostos'));
    }   

    /**
     * Show delete confirmation form for the specified resource.
     */
    public function delete($id)
    {
        $rubro = rubro::with('ctrocosto')->findOrFail($id);
        return view('admin.rubros.delete', compact('rubro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'ctrocosto_id' => 'required|exists:ctrocostos,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:100',
            'activo' => 'required|boolean',
        ]);

        $rubro = rubro::findOrFail($id);
        $rubro->ctrocosto_id = $request->input('ctrocosto_id');
        $rubro->nombre = $request->input('nombre');
        $rubro->descripcion = $request->input('descripcion');
        $rubro->activo = $request->boolean('activo');
        $rubro->fecha_actualizacion = now();
        $rubro->save();

        return redirect()->route('rubros.index')
            ->with('mensaje', 'Rubro actualizado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rubro = rubro::findOrFail($id);
        $rubro->delete();

        return redirect()->route('rubros.index')
            ->with('mensaje', 'Rubro eliminado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }
}
