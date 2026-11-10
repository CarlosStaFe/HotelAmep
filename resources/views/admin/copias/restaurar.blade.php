@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sistema Administrador</li>
            <li class="breadcrumb-item active" aria-current="page">Restaurar Copia de Seguridad</li>
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
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="alert alert-warning">
                <b>Atención:</b> restaurar reemplaza los datos actuales de la base de datos por los de la copia elegida.
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Restaurar desde una copia generada</b></h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('copias.restaurar.post') }}" method="POST"
                        onsubmit="return confirm('Se reemplazarán los datos actuales. ¿Continuar?');">
                        @csrf
                        <div class="form-group">
                            <label for="copia">Copia</label>
                            <select name="copia" id="copia" class="form-control" required>
                                <option value="">Seleccione una copia</option>
                                @foreach ($copias as $copia)
                                    <option value="{{ $copia['nombre'] }}">{{ $copia['nombre'] }} - {{ $copia['fecha'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Restaurar</button>
                    </form>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><b>Restaurar desde un archivo .sql</b></h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('copias.restaurar.post') }}" method="POST" enctype="multipart/form-data"
                        onsubmit="return confirm('Se reemplazarán los datos actuales. ¿Continuar?');">
                        @csrf
                        <div class="form-group">
                            <label for="archivo">Archivo</label>
                            <input type="file" name="archivo" id="archivo" class="form-control-file" accept=".sql" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Restaurar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
