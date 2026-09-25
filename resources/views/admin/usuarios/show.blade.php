@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
            <li class="breadcrumb-item active" aria-current="page">Detalle del Usuario</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><b>Detalle del usuario</b></h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $usuario->name }}</p>
                            <p><strong>Email:</strong> {{ $usuario->email }}</p>
                            <p><strong>Teléfono:</strong> {{ $usuario->telefono }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Rol:</strong> {{ $usuario->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</p>
                            <p><strong>Estado:</strong>
                                @if ($usuario->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </p>
                            <p><strong>Creado:</strong> {{ $usuario->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ url('/admin/usuarios/index') }}" class="btn btn-secondary">Volver</a>
                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-success">Editar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
