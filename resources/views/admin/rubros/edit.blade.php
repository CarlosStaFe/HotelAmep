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
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form action="{{ url('/admin/rubros/' . $rubro->id) }}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="mb-3">
                            <label for="ctrocosto_id" class="form-label">Centro de Costo <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="ctrocosto_id" name="ctrocosto_id" required>
                                <option value="">Seleccione Centro de Costo</option>
                                @foreach ($ctrocostos as $ctrocosto)
                                    <option value="{{ $ctrocosto->id }}" {{ old('ctrocosto_id', $rubro->ctrocosto_id) == $ctrocosto->id ? 'selected' : '' }}>
                                        {{ $ctrocosto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <b style="color: red;">(*)</b></label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                placeholder="Ingrese nombre del rubro" value="{{ old('nombre', $rubro->nombre) }}" required>
                            @error('nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="activo" class="form-label">Estado <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="activo" name="activo" required>
                                <option value="1" {{ old('activo', $rubro->activo) == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo', $rubro->activo) == '0' ? 'selected' : '' }}>Inactivo</option>
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
            <!-- /.card -->
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
