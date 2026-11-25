<?php

namespace App\Http\Controllers;

use App\Mail\UsuarioCreadoMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with('roles')->orderByDesc('id')->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function conectados(): Response
    {
        $duracion = (int) config('session.lifetime');
        if (config('session.driver') !== 'database') {
            return response()->view('admin.usuarios.conectados', [
                'sesiones' => collect(),
                'duracion' => $duracion,
                'error' => 'El listado requiere que las sesiones se almacenen en la base de datos.',
            ], 503)->header('Cache-Control', 'no-store, private');
        }

        $sesiones = DB::connection(config('session.connection'))->table(config('session.table'))
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes($duracion)->timestamp)
            ->orderByDesc('last_activity')
            ->get(['user_id', 'ip_address', 'last_activity']);
        $usuarios = User::whereIn('id', $sesiones->pluck('user_id')->unique())
            ->get(['id', 'name'])->keyBy('id');
        $sesiones = $sesiones->filter(fn ($sesion) => $usuarios->has($sesion->user_id))
            ->map(function ($sesion) use ($usuarios) {
                $sesion->usuario = $usuarios->get($sesion->user_id);

                return $sesion;
            });

        return response()->view('admin.usuarios.conectados', [
            'sesiones' => $sesiones,
            'duracion' => $duracion,
            'error' => null,
        ])->header('Cache-Control', 'no-store, private');
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
        abort_if(! auth()->user()->hasRole('Administrador') && $usuario->hasRole('Administrador'), 403);

        return view('admin.usuarios.edit', compact('usuario'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'activo' => ['required', 'boolean'],
        ]);

        $role = Role::where('guard_name', 'web')->where('name', $validated['role'])->firstOrFail();
        abort_if(! $request->user()->hasRole('Administrador') && $role->name === 'Administrador', 403);

        $usuario = User::create([
            'name' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'],
            'password' => Hash::make($validated['password']),
            'activo' => $validated['activo'],
        ]);

        $usuario->assignRole($role);

        Mail::to($usuario->email)->send(new UsuarioCreadoMail(
            $usuario,
            $validated['password']
        ));

        return redirect()->route($request->user()->canAccessMenu('usuarios.index') ? 'usuarios.index' : 'usuarios.create')
            ->with('mensaje', 'Usuario creado correctamente y se envió el correo de acceso.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'telefono' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'activo' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $usuario, $validated) {
            $adminRole = Role::where('guard_name', 'web')->where('name', 'Administrador')->lockForUpdate()->firstOrFail();
            $usuario = User::whereKey($usuario->id)->lockForUpdate()->firstOrFail();
            $role = Role::where('guard_name', 'web')->where('name', $validated['role'])->firstOrFail();

            abort_if(! $request->user()->hasRole('Administrador')
                && ($usuario->hasRole('Administrador') || $role->is($adminRole)), 403);
            abort_if(! $request->user()->hasRole('Administrador') && ! $request->user()->is($usuario)
                && ($validated['email'] !== $usuario->email || ! empty($validated['password'])), 403);

            if ($usuario->activo && $usuario->hasRole('Administrador')
                && (! $validated['activo'] || ! $role->is($adminRole))
                && User::role($adminRole)->where('activo', true)->count() <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'Debe permanecer al menos un administrador activo.',
                ]);
            }

            $usuario->name = $validated['nombre'];
            $usuario->email = $validated['email'];
            $usuario->telefono = $validated['telefono'];
            $usuario->activo = $validated['activo'];

            if (! empty($validated['password'])) {
                $usuario->password = Hash::make($validated['password']);
            }

            $usuario->save();
            $usuario->syncRoles($role);
        });

        $usuario->refresh();

        if (!empty($validated['password'])) {
            Mail::to($usuario->email)->send(new UsuarioCreadoMail(
                $usuario,
                $validated['password']
            ));
        }

        return redirect()->route('usuarios.index')
            ->with('mensaje', !empty($validated['password'])
                ? 'Usuario actualizado correctamente y se envió el correo con la nueva contraseña.'
                : 'Usuario actualizado correctamente.');
    }
}
