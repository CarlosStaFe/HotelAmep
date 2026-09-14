<?php

namespace App\Http\Controllers;

use App\Models\articulo;
use App\Models\Rubro;
use Illuminate\Http\Request;

class ArticuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articulos = articulo::with('rubro.ctrocosto')->get();
        return view('admin.articulos.index', compact('articulos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rubros = Rubro::orderBy('nombre')->get();
        return view('admin.articulos.create', compact('rubros'));
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

        $articulo = new articulo();
        $articulo->rubro_id = $request->input('rubro_id');
        $articulo->nombre = $request->input('nombre');
        $articulo->stock = $request->input('stock');
        $articulo->precio = $request->input('precio');
        $articulo->activo = $request->boolean('activo');
        $articulo->observacion = $request->input('observacion');
        $articulo->fecha_creacion = now();
        $articulo->fecha_actualizacion = now();
        $articulo->save();

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Artículo creado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $articulo = articulo::with('rubro.ctrocosto')->findOrFail($id);
        return view('admin.articulos.show', compact('articulo'));
    }
     
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $articulo = articulo::findOrFail($id);
        $rubros = Rubro::orderBy('nombre')->get();

        return view('admin.articulos.edit', compact('articulo', 'rubros'));
    }

    /**
     * Show delete confirmation form for the specified resource.
     */
    public function delete($id)
    {
        $articulo = articulo::with('rubro.ctrocosto')->findOrFail($id);
        return view('admin.articulos.delete', compact('articulo'));
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

        $articulo = articulo::findOrFail($id);
        $articulo->rubro_id = $request->input('rubro_id');
        $articulo->nombre = $request->input('nombre');
        $articulo->stock = $request->input('stock');
        $articulo->precio = $request->input('precio');
        $articulo->activo = $request->boolean('activo');
        $articulo->observacion = $request->input('observacion');
        $articulo->fecha_actualizacion = now();
        $articulo->save();

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Artículo actualizado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $articulo = articulo::findOrFail($id);
        $articulo->delete();

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Artículo eliminado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }
}
