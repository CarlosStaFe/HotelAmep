<?php

namespace App\Http\Controllers;

use App\Models\Rubro;
use Illuminate\Http\Request;

class RubroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rubros = Rubro::all();
        return view('admin.rubros.index', compact('rubros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.rubros.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'observacion' => 'nullable|string|max:100',
            'tipo' => 'required|string|max:100',
        ]);

        $rubro = new Rubro();
        $rubro->nombre = $request->input('nombre');
        $rubro->observacion = $request->input('observacion');
        $rubro->tipo = $request->input('tipo');
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
        $rubro = Rubro::findOrFail($id);
        return view('admin.rubros.show', compact('rubro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rubro = Rubro::findOrFail($id);
        return view('admin.rubros.edit', compact('rubro'));
    }

    /**
     * Show delete confirmation form for the specified resource.
     */
    public function delete($id)
    {
        $rubro = Rubro::findOrFail($id);
        return view('admin.rubros.delete', compact('rubro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'observacion' => 'nullable|string|max:100',
            'tipo' => 'required|string|max:100',
        ]);

        $rubro = Rubro::findOrFail($id);
        $rubro->nombre = $request->input('nombre');
        $rubro->observacion = $request->input('observacion');
        $rubro->tipo = $request->input('tipo');
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
        $rubro = Rubro::findOrFail($id);
        $rubro->delete();

        return redirect()->route('rubros.index')
        ->with('mensaje', 'Rubro eliminado exitosamente.')
        ->with('icono', 'success')
        ->with('showConfirmButton', false)
        ->with('textoBoton', '');
    }
}
