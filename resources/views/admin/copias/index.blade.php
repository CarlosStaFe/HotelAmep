@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sistema Administrador</li>
            <li class="breadcrumb-item active" aria-current="page">Copia de Seguridad</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Copias de seguridad</b></h3>
                    <div class="card-tools">
                        <form action="{{ route('copias.store') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Generar copia de seguridad</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr style="background-color: #007bff; color: white; text-align: center;">
                                <th>Archivo</th>
                                <th>Fecha</th>
                                <th>Tamaño (KB)</th>
                                <th style="width: 220px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($copias as $copia)
                                <tr>
                                    <td>{{ $copia['nombre'] }}</td>
                                    <td class="text-center">{{ $copia['fecha'] }}</td>
                                    <td class="text-right">{{ $copia['tamano'] }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('copias.descargar', $copia['nombre']) }}" class="btn btn-sm btn-info">Descargar</a>
                                        <form action="{{ route('copias.destroy', $copia['nombre']) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('¿Eliminar esta copia?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay copias de seguridad generadas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
