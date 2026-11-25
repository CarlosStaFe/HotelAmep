<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuAccessController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate(['usuario' => ['nullable', 'integer', 'exists:users,id']]);
        $usuarios = User::with('roles')->orderBy('name')->get();
        $usuario = isset($validated['usuario']) ? User::findOrFail($validated['usuario']) : null;
        $opciones = collect(config('menu_access'))->groupBy('grupo', true);
        $seleccionadas = $usuario ? $usuario->menuAccessKeys() : [];

        return view('admin.permisos.index', compact('usuarios', 'usuario', 'opciones', 'seleccionadas'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        abort_if($usuario->hasRole('Administrador'), 403);

        $validated = $request->validate([
            'opciones' => ['sometimes', 'array'],
            'opciones.*' => ['string', 'distinct', Rule::in(array_keys(config('menu_access')))],
        ]);

        DB::transaction(function () use ($usuario, $validated) {
            User::whereKey($usuario->id)->lockForUpdate()->firstOrFail();
            DB::table('user_menu_access')->where('user_id', $usuario->id)->delete();
            $rows = array_map(fn ($key) => ['user_id' => $usuario->id, 'menu_key' => $key], $validated['opciones'] ?? []);
            if ($rows) {
                DB::table('user_menu_access')->insert($rows);
            }
        });

        return redirect()->route('permisos.index', ['usuario' => $usuario->id])
            ->with('success', 'Permisos actualizados correctamente.');
    }
}
