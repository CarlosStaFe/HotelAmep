<?php

namespace App\Http\Controllers;

use App\Models\Rubro;
use App\Models\Subrubro;
use Illuminate\Http\Request;

class SubrubroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subrubros = Subrubro::with('rubro')->get();
        return view('admin.subrubros.index', compact('subrubros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rubros = Rubro::orderBy('nombre')->get();
        return view('admin.subrubros.create', compact('rubros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rubro_id' => 'required|exists:rubros,id',
            'nombre' => 'required|string|max:255',
            'observacion' => 'nullable|string|max:100',
            'activo' => 'required|boolean',
        ]);

        $subrubro = new Subrubro();
        $subrubro->rubro_id = $request->input('rubro_id');
        $subrubro->nombre = $request->input('nombre');
        $subrubro->observacion = $request->input('observacion');
        $subrubro->activo = $request->boolean('activo');
        $subrubro->fecha_creacion = now();
        $subrubro->fecha_actualizacion = now();
        $subrubro->save();

        return redirect()->route('subrubros.index')
            ->with('mensaje', 'Subrubro creado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subrubro = Subrubro::with('rubro')->findOrFail($id);
        return view('admin.subrubros.show', compact('subrubro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $subrubro = Subrubro::findOrFail($id);
        $rubros = Rubro::orderBy('nombre')->get();

        return view('admin.subrubros.edit', compact('subrubro', 'rubros'));
    }   

    /**
     * Show delete confirmation form for the specified resource.
     */
    public function delete($id)
    {
        $subrubro = Subrubro::with('rubro')->findOrFail($id);
        return view('admin.subrubros.delete', compact('subrubro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'rubro_id' => 'required|exists:rubros,id',
            'nombre' => 'required|string|max:255',
            'observacion' => 'nullable|string|max:100',
            'activo' => 'required|boolean',
        ]);

        $subrubro = Subrubro::findOrFail($id);
        $subrubro->rubro_id = $request->input('rubro_id');
        $subrubro->nombre = $request->input('nombre');
        $subrubro->observacion = $request->input('observacion');
        $subrubro->activo = $request->boolean('activo');
        $subrubro->fecha_actualizacion = now();
        $subrubro->save();

        return redirect()->route('subrubros.index')
            ->with('mensaje', 'Subrubro actualizado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subrubro = Subrubro::findOrFail($id);
        $subrubro->delete();

        return redirect()->route('subrubros.index')
            ->with('mensaje', 'Subrubro eliminado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }
}
