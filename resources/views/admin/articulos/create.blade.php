@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Artículos</li>
            <li class="breadcrumb-item active" aria-current="page">Crear Artículo</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-9">
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form action="{{ url('/admin/articulos/create') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rubro_id" class="form-label">Rubro <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="rubro_id" name="rubro_id" required>
                                    <option value="">Seleccione Rubro</option>
                                    @foreach ($rubros as $rubro)
                                        <option value="{{ $rubro->id }}"
                                            {{ old('rubro_id') == $rubro->id ? 'selected' : '' }}>
                                            {{ $rubro->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    placeholder="Ingrese nombre del artículo" value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock"
                                placeholder="Ingrese stock del artículo" value="{{ old('stock') }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio"
                                placeholder="Ingrese precio del artículo" value="{{ old('precio') }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="comanda" class="form-label">Comanda <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="comanda" name="comanda" required>
                                <option value="0" {{ old('comanda', '0') == '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('comanda') === '1' ? 'selected' : '' }}>Si</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="activo" class="form-label">Estado <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="activo" name="activo" required>
                                <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="2"
                                placeholder="Ingrese observación del artículo">{{ old('observacion') }}</textarea>
                        </div>
                    </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ url('/admin/articulos/index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
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
