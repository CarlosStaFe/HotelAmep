@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Subrubros</li>
            <li class="breadcrumb-item active" aria-current="page">Crear Subrubro</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form action="{{ url('/admin/subrubros/create') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="rubro_id" class="form-label">Rubro <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="rubro_id" name="rubro_id" required>
                                <option value="">Seleccione Rubro</option>
                                @foreach ($rubros as $rubro)
                                    <option value="{{ $rubro->id }}" {{ old('rubro_id') == $rubro->id ? 'selected' : '' }}>
                                        {{ $rubro->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <b style="color: red;">(*)</b></label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                placeholder="Ingrese nombre del subrubro" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="activo" class="form-label">Estado <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="activo" name="activo" required>
                                <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>                        
                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="2"
                                placeholder="Ingrese observación del subrubro">{{ old('observacion') }}</textarea>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ url('/admin/subrubros/index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
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
