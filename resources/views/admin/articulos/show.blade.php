@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Artículos</li>
            <li class="breadcrumb-item active" aria-current="page">Ver Artículo</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-9">
            <div class="card card-success">
                <div class="card-header">
                    <h2 class="card-title"><b>Ver Artículo</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <div class="mb-6">
                        <label for="ctrocosto_id" class="form-label">Rubro</label>
                        <input type="text" class="form-control" id="rubro_id" name="rubro_id" value="{{ $articulo->rubro->nombre }}" readonly>
                    </div>
                    <div class="mb-6">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $articulo->nombre }}" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" value="{{ $articulo->stock }}" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="{{ $articulo->precio }}" readonly>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="comanda" class="form-label">Comanda</label>
                            <input type="text" class="form-control" id="comanda" name="comanda"
                                value="{{ $articulo->comanda ? 'Sí' : 'No' }}" readonly>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="activo" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="activo" name="activo"
                                value="{{ $articulo->activo ? 'Activo' : 'Inactivo' }}" readonly>
                        </div>
                        <div class="mb-5">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="2" readonly>{{ $articulo->observacion }}</textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <a href="{{ url('/admin/articulos/index') }}" class="btn btn-success">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
