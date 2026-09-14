@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Centro de Costos</li>
            <li class="breadcrumb-item active" aria-current="page">Editar Centro de Costos</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form action="{{ url('/admin/ctrocostos/' . $ctrocosto->id) }}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <b style="color: red;">(*)</b></label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                placeholder="Ingrese nombre del centro de costo" value="{{ old('nombre', $ctrocosto->nombre) }}" required>
                            @error('nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="tipo" name="tipo" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="Administrativo" {{ old('tipo', $ctrocosto->tipo) == 'Administrativo' ? 'selected' : '' }}>Administrativo</option>
                                <option value="Operativo" {{ old('tipo', $ctrocosto->tipo) == 'Operativo' ? 'selected' : '' }}>Operativo</option>
                                <option value="Producción" {{ old('tipo', $ctrocosto->tipo) == 'Producción' ? 'selected' : '' }}>Producción</option>
                                <option value="Varios" {{ old('tipo', $ctrocosto->tipo) == 'Varios' ? 'selected' : '' }}>Varios</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="2"
                                placeholder="Ingrese observación del centro de costo">{{ old('observacion', $ctrocosto->observacion) }}</textarea>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <form action="{{ url('/admin/ctrocostos/' . $ctrocosto->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PUT')
                                        <a href="{{ url('/admin/ctrocostos/index') }}" class="btn btn-secondary">Cancelar</a>
                                        <button type="submit" class="btn btn-info">Guardar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
