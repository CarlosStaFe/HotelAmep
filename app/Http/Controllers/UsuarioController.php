<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with('roles')->orderByDesc('id')->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('admin.usuarios.create');
    }

    public function show(int $id): View
    {
        $usuario = User::with('roles')->findOrFail($id);

        return view('admin.usuarios.show', compact('usuario'));
    }

    public function edit(int $id): View
    {
        $usuario = User::with('roles')->findOrFail($id);

        return view('admin.usuarios.edit', compact('usuario'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'activo' => ['required', 'boolean'],
        ]);

        $usuario = User::create([
            'name' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'],
            'password' => Hash::make($validated['password']),
            'activo' => $validated['activo'],
        ]);

        $usuario->assignRole($validated['role']);

        return redirect()->route('usuarios.index')
            ->with('mensaje', 'Usuario creado correctamente.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'telefono' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'activo' => ['required', 'boolean'],
        ]);

        $usuario->name = $validated['nombre'];
        $usuario->email = $validated['email'];
        $usuario->telefono = $validated['telefono'];
        $usuario->activo = $validated['activo'];

        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }

        $usuario->save();
        $usuario->syncRoles($validated['role']);

        return redirect()->route('usuarios.index')
            ->with('mensaje', 'Usuario actualizado correctamente.');
    }
}
