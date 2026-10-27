<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $proveedores = Proveedor::with('localidad')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.proveedores.index', compact('proveedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.proveedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'razonSocial' => 'required|string|max:100',
            'cuit' => 'required|string|max:11',
            'tipoIva' => 'required|string|max:50',
            'domicilio' => 'required|string|max:100',
            'provincia' => 'required|string|max:2',
            'localidad' => 'required|string|max:11',
            'cod_postal_id' => 'required|integer|exists:localidades,id',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'activo' => 'required|boolean',
            'echeck' => 'nullable|boolean',
            'cbu' => 'nullable|digits_between:1,22',
            'cvu' => 'nullable|digits_between:1,22',
            'alias' => 'nullable|string|max:100',
            'dias' => 'required|integer|min:0|max:100',
            'observacion' => 'nullable|string|max:100',
        ]);

        Proveedor::create([
            'nombre' => $validated['nombre'],
            'razonSocial' => $validated['razonSocial'],
            'cuit' => $validated['cuit'],
            'tipoIva' => $validated['tipoIva'],
            'domicilio' => $validated['domicilio'],
            'cod_postal_id' => $validated['cod_postal_id'],
            'telefono' => $validated['telefono'] ?? null,
            'activo' => $validated['activo'],
            'email' => $validated['email'] ?? null,
            'echeck' => $request->boolean('echeck'),
            'cbu' => $validated['cbu'] ?? null,
            'cvu' => $validated['cvu'] ?? null,
            'alias' => $validated['alias'] ?? null,
            'dias' => $validated['dias'],
            'observacion' => $validated['observacion'] ?? null,
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ]);

        return redirect()->route('proveedores.index')
            ->with('mensaje', 'Proveedor creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $proveedor = Proveedor::with('localidad')->findOrFail($id);

        return view('admin.proveedores.show', compact('proveedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $proveedor = Proveedor::with('localidad')->findOrFail($id);

        return view('admin.proveedores.edit', compact('proveedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'razonSocial' => 'required|string|max:100',
            'cuit' => 'required|string|max:11',
            'tipoIva' => 'required|string|max:50',
            'domicilio' => 'required|string|max:100',
            'provincia' => 'required|string|max:2',
            'localidad' => 'required|string|max:11',
            'cod_postal_id' => 'required|integer|exists:localidades,id',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'activo' => 'required|boolean',
            'echeck' => 'nullable|boolean',
            'cbu' => 'nullable|digits_between:1,22',
            'cvu' => 'nullable|digits_between:1,22',
            'alias' => 'nullable|string|max:100',
            'dias' => 'required|integer|min:0|max:100',
            'observacion' => 'nullable|string|max:100',
        ]);

        $proveedor->update([
            'nombre' => $validated['nombre'],
            'razonSocial' => $validated['razonSocial'],
            'cuit' => $validated['cuit'],
            'tipoIva' => $validated['tipoIva'],
            'domicilio' => $validated['domicilio'],
            'cod_postal_id' => $validated['cod_postal_id'],
            'telefono' => $validated['telefono'] ?? null,
            'email' => $validated['email'] ?? null,
            'echeck' => $request->boolean('echeck'),
            'cbu' => $validated['cbu'] ?? null,
            'cvu' => $validated['cvu'] ?? null,
            'alias' => $validated['alias'] ?? null,
            'dias' => $validated['dias'],
            'activo' => $validated['activo'],
            'observacion' => $validated['observacion'] ?? null,
            'fecha_actualizacion' => now(),
        ]);

        return redirect()->route('proveedores.index')
            ->with('mensaje', 'Proveedor actualizado correctamente.');
    }

    /**
     * Show delete confirmation for the specified resource.
     */
    public function delete($id)
    {
        $proveedor = Proveedor::with('localidad')->findOrFail($id);

        return view('admin.proveedores.delete', compact('proveedor'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        return redirect()->route('proveedores.index')
            ->with('mensaje', 'Proveedor eliminado correctamente.');
    }
}
