@extends('adminlte::page')

@section('content_header')
    <h1>Permisos de Menú</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="GET" action="{{ route('permisos.index') }}" class="mb-4">
                <label for="usuario">Usuario</label>
                <div class="form-inline">
                    <select class="form-control mr-2" id="usuario" name="usuario" required>
                        <option value="">Seleccione un usuario</option>
                        @foreach ($usuarios as $item)
                            <option value="{{ $item->id }}" {{ $usuario && $usuario->id === $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->email }}){{ $item->hasRole('Administrador') ? ' - Administrador' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary" type="submit">Consultar</button>
                </div>
            </form>

            @if ($usuario && $usuario->hasRole('Administrador'))
                <div class="alert alert-info">Los administradores tienen acceso completo permanente.</div>
            @elseif ($usuario)
                <form method="POST" action="{{ route('permisos.update', $usuario) }}">
                    @csrf
                    @method('PUT')
                    <p>Seleccione las opciones habilitadas para {{ $usuario->name }}.</p>
                    <p class="text-muted">Cada opción incluye sus acciones relacionadas. Restaurar copias y administrar permisos son exclusivos de los administradores. Copia de Seguridad permite descargar toda la base de datos.</p>
                    @foreach ($opciones as $grupo => $items)
                        <fieldset class="mb-3">
                            <legend class="h5">{{ $grupo }}</legend>
                            @foreach ($items as $key => $opcion)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="opciones[]"
                                        id="opcion-{{ $loop->parent->index }}-{{ $loop->index }}"
                                        value="{{ $key }}" {{ in_array($key, old('opciones', $seleccionadas), true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="opcion-{{ $loop->parent->index }}-{{ $loop->index }}">{{ $opcion['texto'] }}</label>
                                </div>
                            @endforeach
                        </fieldset>
                    @endforeach
                    <button class="btn btn-primary" type="submit">Guardar permisos</button>
                </form>
            @endif
        </div>
    </div>
@stop
