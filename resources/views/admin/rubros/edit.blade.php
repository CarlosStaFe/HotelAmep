@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rubros</li>
            <li class="breadcrumb-item active" aria-current="page">Editar Rubro</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form action="{{ url('/admin/rubros/' . $rubro->id) }}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <b style="color: red;">(*)</b></label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                placeholder="Ingrese nombre del rubro" value="{{ old('nombre', $rubro->nombre) }}" required>
                            @error('nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="tipo" name="tipo" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="Administrativo" {{ old('tipo', $rubro->tipo) == 'Administrativo' ? 'selected' : '' }}>Administrativo</option>
                                <option value="Consumibles" {{ old('tipo', $rubro->tipo) == 'Consumibles' ? 'selected' : '' }}>Consumibles</option>
                                <option value="Bienes/Muebles" {{ old('tipo', $rubro->tipo) == 'Bienes/Muebles' ? 'selected' : '' }}>Bienes/Muebles</option>
                                <option value="Mantenimiento" {{ old('tipo', $rubro->tipo) == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                                <option value="Producción" {{ old('tipo', $rubro->tipo) == 'Producción' ? 'selected' : '' }}>Producción</option>
                                <option value="Varios" {{ old('tipo', $rubro->tipo) == 'Varios' ? 'selected' : '' }}>Varios</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="2"
                                placeholder="Ingrese observación del rubro">{{ old('observacion', $rubro->observacion) }}</textarea>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ url('/admin/rubros/index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-info">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
