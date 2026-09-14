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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $usuario = User::create([
            'name' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $usuario->assignRole($validated['role']);

        return redirect()->route('usuarios.index')
            ->with('mensaje', 'Usuario creado correctamente.');
    }
}
